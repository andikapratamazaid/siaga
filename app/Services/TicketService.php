<?php
namespace App\Services;
use App\Contracts\Channel; use App\Models\User; use Illuminate\Support\Facades\DB;
class TicketService {
  public function __construct(private Channel $ch) {}
  public function open(User $reporter, string $kind, string $level, string $desc, ?string $zone, bool $hold, int $pic): int {
    $due = $level === 'kritis' ? now()->addHours(4) : now()->addDays($level === 'minor' ? 3 : 1); // kritis: 4 jam (batas jam 12.00 bisa lampau bila laporan masuk sore)
    $id = DB::table('tickets')->insertGetId(['kind'=>$kind,'level'=>$level,'description'=>$desc,'zone'=>$zone,'reporter_id'=>$reporter->id,'pic_id'=>$pic,'due_at'=>$due,'hold'=>$hold,'created_at'=>now(),'updated_at'=>now()]);
    ActivityLog::record($reporter, "Buka tiket #$id ($level)");
    if ($level !== 'minor') { // notifikasi otomatis ke pengawas dan safety
      $to = User::where('role','safety')->pluck('id')->push($reporter->supervisor_id)->filter()->unique();
      foreach (User::whereIn('id',$to)->get() as $t) $this->ch->send($t, "Temuan #$id ($level) di $zone: $desc.".($hold?' Unit/aktivitas DITAHAN.':''));
    }
    return $id;
  }
  public function reportFix(User $u, int $id, string $photoId): void {
    DB::table('tickets')->where('id',$id)->where('pic_id',$u->id)->update(['status'=>'verifikasi','fix_photo'=>$photoId,'updated_at'=>now()]);
    ActivityLog::record($u, "Lapor tindak lanjut #$id"); // penahanan TIDAK lepas di sini
  }
}
