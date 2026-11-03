<?php
namespace App\Flows;
use App\Contracts\Channel; use App\Models\User; use App\Services\{TicketService,ActivityLog}; use App\Support\Incoming;
/** Hazard report 3 langkah: foto, lokasi, kategori. Deterministik, di bawah satu menit. */
class HazardFlow {
  public function __construct(private Channel $ch, private TicketService $tickets) {}
  public function start(User $u): array {
    $this->ch->send($u, 'Lapor bahaya. Langkah 1 dari 3: kirim 1 foto bahayanya.');
    return ['flow'=>'hazard','step'=>'photo'];
  }
  public function handle(User $u, array $s, Incoming $in): ?array {
    if ($s['step'] === 'photo') {
      if ($in->kind !== 'photo') { $this->ch->send($u, 'Kirim foto bahayanya dulu.'); return $s; }
      $s['step'] = 'loc';
      $this->ch->send($u, 'Langkah 2 dari 3: di mana lokasinya?', config('siaga.zones'));
      return $s;
    }
    if ($s['step'] === 'loc') {
      $s['zone'] = $in->value; $s['step'] = 'cat';
      $this->ch->send($u, 'Langkah 3 dari 3: kategori bahayanya?', config('siaga.hazard_categories'));
      return $s;
    }
    $cat = $in->value;
    $level = in_array($cat, config('siaga.hazard_high')) ? 'tinggi' : 'sedang';
    $id = $this->tickets->open($u, 'hazard', $level, "Bahaya kategori $cat", $s['zone'] ?? $u->zone, false, $u->supervisor_id ?? $u->id);
    ActivityLog::record($u, "Hazard report #$id ($cat, $level)");
    $this->ch->send($u, "Laporan bahaya #$id terkirim ke Safety Officer dan pengawas area. Tingkat risiko awal: $level. Terima kasih sudah melapor.");
    return null;
  }
}
