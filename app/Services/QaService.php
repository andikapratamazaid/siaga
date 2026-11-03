<?php
namespace App\Services;
use App\Contracts\Channel; use App\Models\User; use App\Services\Ai\AiAssistant; use Illuminate\Support\Facades\DB;
class QaService {
  public function __construct(private AiAssistant $ai, private Channel $ch) {}
  public function answer(User $u, string $q): void {
    $docs = DB::table('documents')->get(); $hit = $this->ai->answerFromDocs($q, $docs);
    // ATURAN KERAS ditegakkan di kode: sumber harus dokumen yang benar-benar ada
    if ($hit && $docs->contains('name', $hit['doc'])) { $this->ch->send($u, $hit['text']."\n📄 Sumber: ".$hit['doc']); return; }
    DB::table('doc_gaps')->insert(['user_id'=>$u->id,'question'=>$q,'created_at'=>now(),'updated_at'=>now()]);
    ActivityLog::record($u, 'Pertanyaan tanpa jawaban dokumen (celah)');
    $sup = User::find($u->supervisor_id)?->name ?? 'pengawas';
    $this->ch->send($u, "Saya tidak menemukan jawabannya di dokumen proyek, dan saya tidak mau menebak. Silakan tanya ke $sup. Pertanyaan Anda sudah dicatat.");
  }
}
