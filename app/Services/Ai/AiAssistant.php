<?php
namespace App\Services\Ai;
/** AI hanya untuk bahasa bebas: menjawab dari dokumen, menstrukturkan laporan, memilih playbook kasus.
 *  AI TIDAK PERNAH menulis langkah/prosedur sendiri: isi jawaban selalu dari dokumen/playbook di database. */
interface AiAssistant {
  /** @return array{doc:string,text:string}|null  null = tidak ada di dokumen */
  public function answerFromDocs(string $question, iterable $docs): ?array;
  /** @return array{tenaga?:string,selesai?:string,material?:string,kendala?:string} */
  public function parseReport(string $text): array;
  /** Pilih SATU kode playbook yang cocok untuk kasus lapangan, atau null bila tidak ada yang cocok.
   *  Langkah penanganan diambil dari baris playbook di database, bukan dari model. */
  public function classifyCase(string $text, iterable $playbooks): ?string;
}
