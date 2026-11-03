<?php
namespace App\Flows;
use App\Contracts\Channel; use App\Models\User; use App\Services\CaseService; use App\Support\Incoming;
/** Alur Solusi Cepat: pekerja menceritakan masalah (teks, boleh didahului foto), CaseService menjawab dari playbook. */
class QuickSolveFlow {
  public function __construct(private Channel $ch, private CaseService $cases) {}
  public function start(User $u): array {
    $this->ch->send($u, 'Butuh solusi cepat untuk kasus apa? Ceritakan singkat. Contoh: "scaffolding Lt.3 goyang", "genset mati", "hujan saat cor". Boleh kirim foto dulu, lalu keterangan.');
    return ['flow'=>'qs','step'=>'describe'];
  }
  public function handle(User $u, array $s, Incoming $in): ?array {
    if ($in->kind === 'photo') { $this->ch->send($u, 'Foto diterima. Sekarang tulis keterangan singkat masalahnya.'); return $s; }
    $this->cases->solve($u, $in->value);
    return null;
  }
}
