<?php
namespace App\Services;
use App\Contracts\Channel; use App\Flows\{ChecklistFlow,HazardFlow,QuickSolveFlow}; use App\Models\User; use App\Support\Incoming; use Illuminate\Support\Facades\DB;
/** Satu pintu masuk untuk simulator maupun WhatsApp. Alur deterministik dirutekan di sini; AI hanya lewat QaService/CaseService. */
class FlowEngine {
  public function __construct(private Channel $ch, private ChecklistFlow $cl, private HazardFlow $hz, private QuickSolveFlow $qs, private QaService $qa) {}
  public function handle(User $u, Incoming $in): void {
    $row = DB::table('conversation_states')->where('user_id',$u->id)->first(); $s = $row && $row->state ? json_decode($row->state, true) : null;
    if ($in->kind === 'button' && str_starts_with($in->value, 'cl:')) $s = $this->cl->start($u, substr($in->value, 3));
    elseif ($in->kind === 'button' && $in->value === 'hz') $s = $this->hz->start($u);
    elseif ($in->kind === 'button' && $in->value === 'qs') $s = $this->qs->start($u);
    elseif ($s && $s['flow'] === 'checklist') $s = $this->cl->handle($u, $s, $in);
    elseif ($s && $s['flow'] === 'hazard') $s = $this->hz->handle($u, $s, $in);
    elseif ($s && $s['flow'] === 'qs') $s = $this->qs->handle($u, $s, $in);
    elseif ($in->kind === 'button' && in_array($in->value, ['Paham','Belum paham'])) { // konfirmasi safety talk dari BriefingService
      ActivityLog::record($u, $in->value === 'Paham' ? 'Hadir dan paham safety talk' : 'Safety talk belum paham, perlu penjelasan atasan');
      $this->ch->send($u, $in->value === 'Paham' ? 'Terima kasih, kehadiran tercatat.' : 'Baik. Mandor atau safety akan menjelaskan langsung.'); $s = null;
    }
    elseif ($in->kind === 'text') { $this->qa->answer($u, $in->value); $s = null; }
    else $this->ch->send($u, 'Mau apa hari ini?', $this->menu($u));
    DB::table('conversation_states')->updateOrInsert(['user_id'=>$u->id], ['state'=>$s ? json_encode($s) : null,'updated_at'=>now()]);
  }
  private function menu(User $u): array {
    return array_merge(config('siaga.menu.'.$u->role, []), [['hz','Lapor Bahaya'],['qs','Solusi Cepat AI']]);
  }
}
