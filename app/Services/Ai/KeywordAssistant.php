<?php
namespace App\Services\Ai;
/** Cadangan tanpa API: pencocokan kata dari dokumen dan playbook. */
class KeywordAssistant implements AiAssistant {
  public function answerFromDocs(string $q, iterable $docs): ?array {
    $w = array_filter(preg_split('/\W+/', mb_strtolower($q)), fn($x)=>strlen($x)>=4 && !in_array($x,['yang','berapa','bagaimana','kapan','boleh']));
    $best = null; $bs = 0;
    foreach ($docs as $d) foreach (preg_split('/(?<=\.)\s+/', $d->body) as $s) {
      $c = count(array_filter($w, fn($x)=>str_contains(mb_strtolower($s), substr($x,0,max(4,strlen($x)-3)))));
      if ($c > $bs) { $bs = $c; $best = ['doc'=>$d->name,'text'=>$s]; }
    }
    return $bs >= 2 ? $best : null;
  }
  public function parseReport(string $t): array {
    $o = []; if (preg_match('/(\d+)\s*(orang|tukang|pekerja|kru)/i', $t, $m)) $o['tenaga'] = $m[1].' orang'; return $o;
  }
  public function classifyCase(string $text, iterable $playbooks): ?string {
    $t = mb_strtolower($text); $best = null; $bs = 0;
    foreach ($playbooks as $p) {
      $score = 0;
      foreach (explode(',', $p->keywords) as $phrase) { $phrase = trim($phrase); if ($phrase !== '' && str_contains($t, $phrase)) $score += 3; }
      foreach (preg_split('/\W+/', mb_strtolower($p->title)) as $w) if (strlen($w) >= 5 && str_contains($t, $w)) $score += 1;
      if ($score > $bs) { $bs = $score; $best = $p->code; }
    }
    return $bs >= 3 ? $best : null;
  }
}
