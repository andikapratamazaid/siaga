<?php
namespace App\Services;
use App\Contracts\Channel; use App\Models\User; use App\Services\Ai\AiAssistant; use Illuminate\Support\Facades\DB;
/** AI Solusi Cepat: kasus lapangan dicocokkan ke playbook yang disetujui perusahaan.
 *  Langkah yang dikirim ke pekerja SELALU isi tabel case_playbooks. AI hanya memilih kodenya.
 *  Bila tidak ada playbook yang cocok: kasus dieskalasi ke manusia dan dicatat sebagai celah playbook. */
class CaseService {
  public function __construct(private AiAssistant $ai, private Channel $ch, private TicketService $tickets) {}
  public function solve(User $u, string $text): void {
    $books = DB::table('case_playbooks')->get();
    $code = $this->ai->classifyCase($text, $books);
    $pb = $code ? $books->firstWhere('code', $code) : null;
    if ($pb) {
      $level = ['Darurat'=>'kritis','Tinggi'=>'tinggi','Sedang'=>'sedang'][$pb->urgency] ?? 'sedang';
      $id = $this->tickets->open($u, 'kasus', $level, "{$pb->title}: $text", $u->zone, (bool) $pb->stop_work, $u->supervisor_id ?? $u->id);
      $steps = collect(json_decode($pb->steps, true) ?: [])->map(fn($s, $i) => ($i + 1).". $s")->implode("\n");
      $msg = "SOLUSI CEPAT: {$pb->title} (urgensi {$pb->urgency})\n"
        . ($pb->stop_work ? "STOP WORK: hentikan pekerjaan terkait sekarang.\n" : '')
        . "$steps\nSumber: Playbook {$pb->code} (dokumen perusahaan).\n"
        . "Tiket #$id dibuat dan pihak terkait sudah diberi tahu. Setelah tertangani, laporkan tindak lanjutnya.";
      $this->ch->send($u, $msg);
      ActivityLog::record($u, "Solusi Cepat {$pb->code} untuk kasus: ".mb_substr($text, 0, 60));
      return;
    }
    // Tidak ada playbook cocok: JANGAN mengarang langkah. Eskalasi ke manusia + catat celah.
    DB::table('doc_gaps')->insert(['user_id'=>$u->id,'question'=>'[kasus] '.$text,'created_at'=>now(),'updated_at'=>now()]);
    $id = $this->tickets->open($u, 'kasus', 'tinggi', "Kasus tanpa playbook: $text", $u->zone, false, $u->supervisor_id ?? $u->id);
    $sup = User::find($u->supervisor_id)?->name ?? 'pengawas Anda';
    $this->ch->send($u, "Kasus ini belum ada playbook resminya, jadi saya tidak memberi langkah tebakan. Kasus diteruskan ke $sup dan Safety Officer lewat tiket #$id. Untuk kondisi berbahaya, hentikan pekerjaan dan menjauh dulu.");
    ActivityLog::record($u, 'Kasus tanpa playbook (celah), dieskalasi');
  }
}
