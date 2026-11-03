<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
/** Ringkasan dashboard dalam JSON. Tampilan Blade/Livewire tinggal membaca endpoint ini. */
class DashboardController extends Controller {
  public function summary() {
    $open = DB::table('tickets')->where('status','!=','tutup');
    return [
      'tiket_terbuka' => (clone $open)->count(),
      'tiket_kritis' => (clone $open)->where('level','kritis')->count(),
      'unit_ditahan' => (clone $open)->where('hold',true)->count(),
      'permit_aktif' => DB::table('permits')->where('status','aktif')->count(),
      'checklist_selesai_hari_ini' => DB::table('checklist_runs')->where('status','selesai')->whereDate('created_at', today())->count(),
      'laporan_harian_hari_ini' => DB::table('daily_reports')->where('date', today())->value('status') ?? 'BELUM ADA',
      'celah_dokumen' => DB::table('doc_gaps')->count(),
    ];
  }
}
