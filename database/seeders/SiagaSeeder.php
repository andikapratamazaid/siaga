<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; use Illuminate\Support\Facades\{DB,Hash};
class SiagaSeeder extends Seeder { public function run(): void {
  $u = fn($n,$r,$z,$p,$s=null)=>DB::table('users')->insertGetId(['name'=>$n,'email'=>strtolower(str_replace(' ','.',$n)).'@siaga.test','password'=>Hash::make('password'),'role'=>$r,'zone'=>$z,'phone'=>$p,'supervisor_id'=>$s,'created_at'=>now(),'updated_at'=>now()]);
  // 10 peran. Atasan default: pelaksana (id pertama). PM tanpa atasan.
  $pl = $u('Pak Hendra','pelaksana','Gedung A Lt.1-5','6281210000001');
  $u('Pak Dedi','mandor','Gedung A Lt.3','6281210000002',$pl);
  $u('Joko','tukang','Gedung A Lt.3','6281210000003',$pl);
  $u('Pak Slamet','operator','Area Luar','6281210000004',$pl);
  $u('Pak Yanto','rigger','Gedung A Lt.2','6281210000005',$pl);
  $u('Bu Sari','safety','Seluruh proyek','6281210000006',$pl);
  $u('Bu Rina','qc','Gedung A Lt.2','6281210000007',$pl);
  $u('Pak Bagus','surveyor','Gedung A Lt.1','6281210000008',$pl);
  $u('Bu Wati','logistik','Area Luar','6281210000009',$pl);
  $u('Pak Arif','pm','Seluruh proyek','6281210000010');

  $tpl = function (string $type, string $name, array $items) {
    $t = DB::table('checklist_templates')->insertGetId(['type'=>$type,'name'=>$name,'created_at'=>now(),'updated_at'=>now()]);
    foreach ($items as [$x,$c,$k]) DB::table('checklist_items')->insert(['template_id'=>$t,'text'=>$x,'critical'=>$c,'condition'=>$k,'created_at'=>now(),'updated_at'=>now()]);
  };
  // [teks, kritis?, kondisi]  kondisi 'hujan' = item adaptif saat hujan
  $tpl('alat','Inspeksi Alat Berat',[['Kondisi mesin dan oli normal?',0,null],['Rem berfungsi baik?',1,null],['Hidrolik tidak bocor?',1,null],['Lampu dan klakson berfungsi?',0,null],['Sabuk pengaman dan alarm mundur ada?',1,null],['Jalan licin sudah dibersihkan?',0,'hujan']]);
  $tpl('scaf','Inspeksi Scaffolding',[['Scafftag hijau terpasang?',1,null],['Semua komponen lengkap dan kencang?',1,null],['Papan kerja rapat, tidak retak?',0,null],['Akses naik aman?',0,null],['Pagar tepi dan toe board ada?',1,null]]);
  $tpl('cor','Checklist Sebelum Pengecoran',[['Bekisting kuat dan bersih?',1,null],['Pembesian sesuai gambar?',1,null],['Hasil uji slump sesuai?',1,null],['Vibrator siap?',0,null],['Pencahayaan dan jalur pompa siap?',0,null],['Genset cadangan siap?',0,null]]);
  $tpl('tinggi','Pekerjaan di Ketinggian',[['Harness tidak rusak?',1,null],['Lifeline dan angkur terpasang?',1,null],['Platform kerja aman?',1,null],['Tidak ada alat yang bisa jatuh?',0,null]]);
  $tpl('gal','Galian',[['Dinding galian ditahan?',1,null],['Akses keluar masuk ada?',1,null],['Tanah galian jauh dari tepi?',0,null],['Area galian dipagari?',0,null]]);

  foreach ([
    ['Metode Kerja Bekisting Kolom (MK-01)','Bekisting kolom dipasang tegak lurus dengan unting-unting. Bekisting kolom boleh dibuka setelah beton berumur minimal 24 jam dan disetujui QC. Pen pengunci dikencangkan sebelum pengecoran.'],
    ['Metode Pengecoran Plat (MK-02)','Pengecoran plat dimulai setelah checklist sebelum cor lolos penuh dan izin cor disetujui pelaksana. Slump rencana 10 sampai 12 cm. Pemadatan dengan vibrator, jangan menyentuh pembesian.'],
    ['SOP Bekerja di Ketinggian (SOP-03)','Pekerjaan di atas 1,8 meter wajib memakai harness dan dikaitkan ke lifeline. Angkur lifeline diperiksa sebelum dipakai. Pekerja tidak boleh bekerja sendirian di ketinggian.'],
    ['SOP Lifting (SOP-04)','Lifting hanya dengan izin lifting aktif dan rigger bersertifikat. Zona di bawah beban dilarang untuk semua orang. Sling diperiksa sebelum setiap pengangkatan.'],
    ['Toleransi QC Pembesian dan Bekisting (QC-05)','Toleransi elevasi plat plus minus 10 mm. Selimut beton minimal 25 mm. Toleransi ketegakan kolom 5 mm per 3 meter.'],
    ['Prosedur Darurat (PD-01)','Bunyikan alarm dan hentikan semua pekerjaan. Semua orang menuju titik kumpul zona masing-masing. Hubungi Safety Officer dan pelaksana, jangan kembali ke area sebelum diizinkan.'],
  ] as [$n,$b]) DB::table('documents')->insert(['name'=>$n,'body'=>$b,'created_at'=>now(),'updated_at'=>now()]);

  // Playbook Solusi Cepat: langkah SELALU dari tabel ini, AI hanya memilih kode kasus.
  $pb = function (string $code, string $title, string $kw, string $urg, bool $stop, array $steps) {
    DB::table('case_playbooks')->insert(['code'=>$code,'title'=>$title,'keywords'=>$kw,'urgency'=>$urg,'stop_work'=>$stop,'steps'=>json_encode($steps),'created_at'=>now(),'updated_at'=>now()]);
  };
  $pb('PB-01','Excavator atau alat berat mogok','excavator mogok,alat berat mati,mesin mati,tidak bisa starter,unit mogok','Sedang',false,['Matikan mesin dan pasang rem parkir.','Cek bahan bakar dan indikator panel, catat kode error bila ada.','Jangan bongkar komponen mesin sendiri.','Tiket dibuat otomatis untuk mekanik, sertakan posisi unit.']);
  $pb('PB-02','Kebocoran hidrolik','hidrolik bocor,oli bocor,selang pecah,hidrolik','Tinggi',true,['Hentikan unit dan turunkan attachment ke tanah.','Matikan mesin, jauhkan orang dari area semprotan oli.','Tampung tumpahan dengan pasir atau spill kit, jangan masuk saluran air.','Tiket kritis dibuat, unit DITAHAN sampai mekanik memverifikasi.']);
  $pb('PB-03','Hujan deras saat pengecoran','hujan saat cor,hujan deras pengecoran,cor kehujanan','Tinggi',false,['Tutup permukaan beton segar dengan terpal.','Hentikan penambahan air ke adukan.','Catat jam mulai hujan dan volume yang sudah terpasang.','Keputusan lanjut atau berhenti cor ada di pelaksana, asisten meneruskan laporan Anda.']);
  $pb('PB-04','Bekisting bergeser atau jebol saat cor','bekisting jebol,bekisting bergeser,bekisting bocor saat cor','Darurat',true,['Hentikan pengecoran di titik itu sekarang.','Jauhkan semua orang dari area bekisting.','Jangan naik ke bekisting yang bergeser.','Pelaksana dan QC dipanggil otomatis lewat tiket darurat ini.']);
  $pb('PB-05','Scaffolding goyang atau tidak stabil','scaffolding goyang,perancah goyang,scaffolding miring,perancah tidak stabil','Darurat',true,['Turun dari scaffolding dengan tenang, jangan lanjut bekerja di atasnya.','Pasang tanda larangan naik dan ganti scafftag menjadi merah bila terjangkau dari bawah.','Jauhkan orang dari bawah scaffolding.','Tiket darurat dibuat untuk safety dan pengawas area.']);
  $pb('PB-06','Pekerja pusing, lemas, atau cedera ringan','pekerja pusing,cedera ringan,terjatuh,lemas,dehidrasi,pekerja sakit','Darurat',true,['Hentikan pekerjaan orang tersebut, bawa ke tempat teduh dan aman.','Cedera: jangan pindahkan bila dicurigai cedera tulang, tunggu petugas P3K.','Hubungi safety officer, nomor ada di pesan darurat.','Tiket darurat dibuat, atasan diberi tahu otomatis.']);
  $pb('PB-07','Genset mati atau listrik padam','genset mati,listrik padam,mcb turun,daya mati','Tinggi',true,['Hentikan pekerjaan yang bergantung pada listrik (vibrator, pompa, cutting).','Jangan menyentuh panel atau kabel yang terbuka.','Cek dari jarak aman apakah ada bau terbakar, laporkan bila ada.','Tiket dibuat untuk tim elektrikal, pekerjaan listrik ditahan sampai dinyatakan aman.']);
  $pb('PB-08','Slump beton tidak sesuai','slump tidak sesuai,slump terlalu encer,slump terlalu kental,beton encer','Sedang',false,['Jangan tambahkan air ke truck mixer.','Pisahkan truck ini, jangan dipasang dulu.','Panggil QC untuk uji ulang dan keputusan diterima atau ditolak.','Catat nomor truck dan jam kedatangan di laporan.']);
  $pb('PB-09','Material terlambat atau habis','material terlambat,material habis,besi belum datang,semen habis,stok kosong','Sedang',false,['Hitung sisa stok nyata di lokasi, jangan mengira-ngira.','Alihkan kru ke pekerjaan persiapan yang tidak menunggu material itu.','Tiket dibuat untuk logistik dengan rincian kebutuhan dan jam dibutuhkan.','Mandor menerima urutan prioritas pengiriman dari logistik.']);
  $pb('PB-10','Angin kencang untuk crane atau lifting','angin kencang,crane berhenti,lifting ditunda,cuaca buruk crane','Darurat',true,['Hentikan lifting sekarang, turunkan beban bila aman.','Semua orang menjauh dari jangkauan jib dan beban.','Jangan paksa lanjut meski dikejar target.','Lifting hanya dilanjut setelah rigger dan safety menyatakan aman.']);
} }
