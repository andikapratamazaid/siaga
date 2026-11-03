<?php
namespace App\Flows;
use App\Contracts\Channel; use App\Models\User; use App\Services\{TicketService,ActivityLog}; use App\Support\Incoming; use Illuminate\Support\Facades\DB;
/** State machine checklist. Deterministik: tanpa AI. */
class ChecklistFlow {
  public function __construct(private Channel $ch, private TicketService $tickets) {}
  public function start(User $u, string $type): ?array {
    $t = DB::table('checklist_templates')->where('type',$type)->first();
    if (!$t) { $this->ch->send($u, 'Checklist itu belum tersedia. Hubungi admin proyek.'); return null; } // dulu: crash membaca properti dari null
    $run = DB::table('checklist_runs')->insertGetId(['user_id'=>$u->id,'template_id'=>$t->id,'created_at'=>now(),'updated_at'=>now()]);
    $this->ch->send($u, "{$t->name}. Kondisi cuaca sekarang?", ['Cerah','Hujan']);
    return ['flow'=>'checklist','tpl'=>$t->id,'run'=>$run,'step'=>'cond','i'=>0,'items'=>[],'fails'=>[]];
  }
  public function handle(User $u, array $s, Incoming $in): ?array {
    if ($s['step'] === 'cond') {
      $cond = $in->value === 'Hujan' ? 'hujan' : '__none';
      $s['items'] = DB::table('checklist_items')->where('template_id',$s['tpl'])->where(fn($w)=>$w->whereNull('condition')->orWhere('condition',$cond))->orderBy('id')->pluck('id')->all();
      DB::table('checklist_runs')->where('id',$s['run'])->update(['weather'=>$in->value]);
      return $this->ask($u, $s);
    }
    if ($s['step'] === 'q') {
      if (!in_array($in->value, ['Ya','Tidak'])) { $this->ch->send($u,'Jawab dengan tombol Ya atau Tidak.',['Ya','Tidak']); return $s; }
      $s['answer'] = $in->value; $it = DB::table('checklist_items')->find($s['items'][$s['i']]);
      if ($it->critical) { $s['step'] = 'photo'; $this->ch->send($u,'Foto wajib untuk item ini. Kirim foto.'); return $s; }
      return $this->record($u, $s, null);
    }
    if ($in->kind !== 'photo') { $this->ch->send($u,'Foto wajib. Kirim foto dulu.'); return $s; }
    return $this->record($u, $s, $in->value);
  }
  private function ask(User $u, array $s): array {
    $it = DB::table('checklist_items')->find($s['items'][$s['i']]); $s['step'] = 'q';
    $this->ch->send($u, '('.($s['i']+1).'/'.count($s['items']).") {$it->text}".($it->critical?' 📷 foto wajib':''), ['Ya','Tidak']); return $s;
  }
  private function record(User $u, array $s, ?string $photo): ?array {
    $id = $s['items'][$s['i']]; $ok = $s['answer'] === 'Ya';
    DB::table('checklist_answers')->insert(['run_id'=>$s['run'],'item_id'=>$id,'passed'=>$ok,'photo'=>$photo,'created_at'=>now(),'updated_at'=>now()]);
    if (!$ok) $s['fails'][] = $id;
    if (++$s['i'] < count($s['items'])) return $this->ask($u, $s);
    $crit = 0; $tpl = DB::table('checklist_templates')->find($s['tpl']);
    foreach ($s['fails'] as $f) { $it = DB::table('checklist_items')->find($f); $crit += $it->critical;
      $this->tickets->open($u,'temuan',$it->critical?'kritis':'minor',"{$tpl->name}: {$it->text} tidak lolos",$u->zone,(bool)$it->critical,$u->id); }
    DB::table('checklist_runs')->where('id',$s['run'])->update(['status'=>'selesai']); ActivityLog::record($u,"Checklist {$tpl->name} selesai, ".count($s['fails']).' temuan');
    $this->ch->send($u, !$s['fails'] ? 'Checklist lolos semua ✅.' : ($crit ? 'Ada temuan kritis. Unit/aktivitas DITAHAN sampai diverifikasi. Pengawas dan safety sudah diberi tahu.' : 'Temuan minor dicatat. Boleh lanjut dengan catatan.'));
    return null;
  }
}
