# SIAGA - Sistem Asisten Kerja Lapangan

Asisten chat berbasis peran (WhatsApp-first) untuk pekerja site konstruksi: briefing dan reminder harian,
checklist digital, pusat K3, permit to work, tanya SOP hanya dari dokumen proyek, laporan harian dengan
persetujuan pelaksana, dan AI Solusi Cepat berbasis playbook kasus lapangan yang disetujui perusahaan.

## Isi repo
- `app/`, `config/`, `database/`, `routes/` — paket backend Laravel 11 (ditumpangkan ke proyek Laravel baru, lihat "Menjalankan").
- `demo/SIAGA_Live.html` - demo satu berkas yang berjalan penuh di browser (simulator chat 10 peran + dashboard). Buka langsung atau sajikan sebagai halaman statis.
- `docs/konsep-siaga.pdf` - dokumen konsep (7 halaman). `docs/desain-alur-siaga.pdf` - desain alur/flow (7 halaman).

# SIAGA (Laravel) - paket backend, disempurnakan 3 November 2026
Berkas ini ditumpangkan ke proyek Laravel 11 baru. Versi ini adalah hasil penyempurnaan kerangka awal:
bug diperbaiki, data awal dilengkapi, dan tiga bagian yang sebelumnya belum ada kini ditambahkan
(Hazard Report, Briefing pagi, Solusi Cepat AI, ringkasan dashboard).

CATATAN VERIFIKASI: paket ini ditinjau baris demi baris. Uji jalan penuh (php artisan) belum dilakukan
karena lingkungan peninjau tidak memiliki runtime PHP; jalankan langkah "Menjalankan" di bawah untuk uji pertama.
Versi demo yang SUDAH berjalan dan terverifikasi ada di web app "SIAGA Live" (satu file HTML, logika sama).

## Menjalankan
1. `composer create-project laravel/laravel siaga && cd siaga`
2. Salin isi folder ini ke dalam `siaga/` (gabungkan `app/`, `config/`, `database/`, `routes/`).
3. Daftarkan provider di `bootstrap/providers.php`: `App\Providers\SiagaServiceProvider::class`.
4. Di `routes/web.php` tambahkan: `require __DIR__.'/siaga.php';` (webhook WhatsApp: kecualikan `wa/webhook` dari CSRF).
5. `.env`: `DB_CONNECTION=sqlite` (atau PostgreSQL), lalu `php artisan migrate --seed --seeder=SiagaSeeder`.
6. Uji tanpa WhatsApp (user id mengikuti urutan seeder: 1 pelaksana, 2 mandor, 3 tukang, 4 operator, 6 safety):
   - Checklist: `curl -X POST /sim/4/send -d kind=button -d value=cl:alat` lalu jawab `Cerah`, `Ya`/`Tidak`, kirim foto untuk item kritis (`-d kind=photo -d value=media123`).
   - Hazard: `curl -X POST /sim/3/send -d kind=button -d value=hz`, lalu foto, zona, kategori.
   - Solusi Cepat: `curl -X POST /sim/3/send -d kind=button -d value=qs`, lalu `-d kind=text -d "value=scaffolding goyang"`.
   - Briefing demo: `curl -X POST /sim/2/briefing`.
   - Baca balasan: `curl /sim/4/messages`.

## Yang diperbaiki dari kerangka awal
- Seeder dulu hanya membuat template `alat`, padahal menu mandor/QC menunjuk `scaf` dan `cor`: memilih menu itu membuat `ChecklistFlow::start()` membaca properti dari null (crash). Sekarang 5 template lengkap (alat, scaf, cor, tinggi, gal) dan `start()` memberi pesan ramah bila template tidak ada.
- Seeder sekarang memuat 10 pengguna sesuai 10 peran, 6 dokumen basis pengetahuan, dan 10 playbook kasus.
- Batas waktu tiket kritis tidak lagi "hari ini jam 12.00" (bisa lampau bila laporan masuk sore); sekarang 4 jam.
- Model AI default diganti ke nama yang valid dan tetap bisa diganti lewat `SIAGA_AI_MODEL`.
- ClaudeAssistant kini mengembalikan kosong bila API gagal, bukan menafsirkan respons error.
- Menu kini tersedia untuk semua peran; tombol umum Lapor Bahaya dan Solusi Cepat AI ditambahkan FlowEngine untuk semua peran.

## Yang ditambahkan
- `app/Flows/HazardFlow.php`: hazard report 3 langkah (foto, lokasi, kategori) menjadi tiket; kategori Ketinggian/Listrik/Alat berat otomatis level tinggi dan memberi tahu safety + atasan.
- `app/Services/BriefingService.php`: persiapan + briefing + safety talk (topik rotasi dari `config/siaga.php`), konfirmasi "Paham/Belum paham" tercatat di log. Pemicu demo `POST /sim/{user}/briefing`; produksi: panggil dari Laravel Scheduler.
- `app/Services/CaseService.php` + `app/Flows/QuickSolveFlow.php` + tabel `case_playbooks`: AI SOLUSI CEPAT. Prinsipnya ketat: AI (Claude atau pencocokan kata) hanya MEMILIH kode playbook; langkah yang diterima pekerja selalu isi tabel playbook yang disetujui perusahaan. Playbook berlabel STOP WORK otomatis membuat tiket tertahan. Kasus tanpa playbook tidak dijawab dengan tebakan: dieskalasi ke atasan + safety dan dicatat di `doc_gaps` sebagai celah playbook.
- `app/Http/Controllers/DashboardController.php`: `GET /dashboard/summary` (JSON) untuk ringkasan dashboard.

## Arsitektur (tidak berubah dari kerangka awal)
- Alur deterministik: `app/Flows/*` + `FlowEngine` (state di tabel `conversation_states`). AI tidak ada di sini.
- AI hanya lewat `AiAssistant` (Claude via API bila `ANTHROPIC_API_KEY` diisi, kalau tidak pencocokan kata). `QaService` menegakkan aturan keras: jawaban wajib bersumber dokumen yang ada, selain itu dicatat sebagai celah (`doc_gaps`).
- Persetujuan manusia: `ApprovalService` memeriksa peran (`config/siaga.php`) sebelum membuka penahanan atau mengunci laporan. Semua tindakan masuk `activity_logs`.
- Kanal: interface `Contracts\Channel`. Ganti ke WhatsApp dengan `SIAGA_CHANNEL=whatsapp`, `WA_TOKEN`, `WA_PHONE_ID`, `WA_VERIFY_TOKEN`; daftarkan `GET/POST /wa/webhook` di Meta. Logika alur tidak berubah.

## Masih belum (langkah berikutnya)
- Flow permit dan laporan lapangan/harian di Laravel (tabel dan ApprovalService sudah siap; di versi SIAGA Live keduanya sudah berjalan dan bisa jadi acuan porting).
- Tampilan dashboard Blade/Livewire (endpoint JSON sudah ada), UI simulator chat (pakai SIAGA Live sebagai acuan).
- Penjadwalan briefing otomatis (Laravel Scheduler), transkripsi voice note, uji otomatis PHPUnit.
