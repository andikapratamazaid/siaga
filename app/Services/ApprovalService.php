<?php
namespace App\Services;
use App\Contracts\Channel; use App\Models\User; use Illuminate\Support\Facades\DB;
/** Semua data resmi lewat sini: cek peran berwenang, lalu catat log. */
class ApprovalService {
  public function __construct(private Channel $ch) {}
  private function allow(User $u, string $key): void { abort_unless(in_array($u->role, config("siaga.approve.$key")), 403, 'Peran tidak berwenang'); }
  public function approveTicket(User $by, int $id): void {
    $t = DB::table('tickets')->find($id); abort_unless($t && $t->status === 'verifikasi', 422);
    $this->allow($by, $t->level === 'minor' ? 'ticket_minor' : 'ticket_kritis');
    DB::table('tickets')->where('id',$id)->update(['status'=>'tutup','hold'=>false,'updated_at'=>now()]);
    ActivityLog::record($by, "Verifikasi tiket #$id".($t->hold ? ', penahanan dilepas' : ''));
    if ($pic = User::find($t->pic_id)) $this->ch->send($pic, "Tiket #$id ditutup".($t->hold ? ' dan penahanan dilepas.' : '.'));
  }
  public function approveDailyReport(User $by, string $date): void {
    $this->allow($by, 'daily_report');
    DB::table('daily_reports')->where('date',$date)->where('status','DRAF')->update(['status'=>'DISETUJUI','approved_by'=>$by->id,'locked_at'=>now(),'updated_at'=>now()]);
    ActivityLog::record($by, "Setujui Laporan Harian $date (terkunci)");
  }
}
