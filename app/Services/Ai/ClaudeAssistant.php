<?php
namespace App\Services\Ai;
use Illuminate\Support\Facades\Http;
class ClaudeAssistant implements AiAssistant {
  private function call(string $system, string $user): array {
    $r = Http::withHeaders(['x-api-key'=>config('siaga.ai_key'),'anthropic-version'=>'2023-06-01'])->timeout(30)
      ->post('https://api.anthropic.com/v1/messages', ['model'=>config('siaga.ai_model'),'max_tokens'=>500,'system'=>$system,'messages'=>[['role'=>'user','content'=>$user]]]);
    if (!$r->successful()) return []; // gagal API = tidak ada jawaban; aturan keras di service yang memutuskan
    return json_decode(preg_replace('/```(json)?/','', $r->json('content.0.text') ?? '{}'), true) ?: [];
  }
  public function answerFromDocs(string $q, iterable $docs): ?array {
    $ctx = collect($docs)->map(fn($d)=>"[{$d->name}]\n{$d->body}")->implode("\n\n");
    $a = $this->call('Jawab HANYA dari dokumen. Jangan menambah prosedur, angka, atau aturan K3 dari luar dokumen. Balas JSON {"doc":"nama dokumen persis","text":"jawaban singkat"} atau {"doc":null} bila tidak ada.', "DOKUMEN:\n$ctx\n\nPERTANYAAN: $q");
    return !empty($a['doc']) && !empty($a['text']) ? ['doc'=>$a['doc'],'text'=>$a['text']] : null;
  }
  public function parseReport(string $t): array {
    return $this->call('Ubah laporan mandor jadi JSON dengan kunci tenaga, selesai, material, kendala. Kosongkan kunci yang tidak disebut. Jangan mengarang.', $t);
  }
  public function classifyCase(string $text, iterable $playbooks): ?string {
    $list = collect($playbooks)->map(fn($p)=>"{$p->code}: {$p->title} (kata kunci: {$p->keywords})")->implode("\n");
    $a = $this->call('Kamu hanya memilih kode playbook yang paling cocok. Jangan menulis langkah atau saran apa pun. Balas JSON {"code":"PB-xx"} atau {"code":null} bila tidak ada yang cocok.', "PLAYBOOK:\n$list\n\nKASUS: $text");
    return !empty($a['code']) ? (string) $a['code'] : null;
  }
}
