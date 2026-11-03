<?php
namespace App\Services;
use App\Contracts\Channel; use App\Models\User; use Illuminate\Support\Facades\DB;
/** Pengingat dan briefing pagi. Di produksi dipanggil Laravel Scheduler (05.45 persiapan, 06.15 briefing, 06.30 safety talk).
 *  Untuk demo tersedia pemicu manual POST /sim/{user}/briefing. */
class BriefingService {
  public function __construct(private Channel $ch) {}
  public function morning(User $u): void {
    $open = DB::table('tickets')->where('status','!=','tutup')->where('zone',$u->zone)->count();
    $this->ch->send($u, "Pagi {$u->name}. Persiapan hari ini: area {$u->zone}. APD wajib: helm, rompi, sepatu safety."
      .($open ? " Ada $open tiket terbuka di zona Anda." : ' Tidak ada tiket terbuka di zona Anda.'));
    [$title, $body] = config('siaga.topics')[now()->dayOfYear % count(config('siaga.topics'))];
    $this->ch->send($u, "Briefing pagi ({$u->role}): selesaikan checklist peran Anda sebelum mulai bekerja. Cuaca dan rencana kerja mengikuti arahan pelaksana di lapangan.");
    $this->ch->send($u, "Safety talk hari ini: $title. $body", ['Paham','Belum paham']);
    ActivityLog::record($u, 'Briefing pagi terkirim');
  }
}
