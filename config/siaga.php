<?php
return [
  'channel' => env('SIAGA_CHANNEL', 'simulator'),           // simulator | whatsapp
  'ai_key' => env('ANTHROPIC_API_KEY'), 'ai_model' => env('SIAGA_AI_MODEL', 'claude-sonnet-4-5'),
  'wa' => ['token'=>env('WA_TOKEN'),'phone_id'=>env('WA_PHONE_ID'),'verify'=>env('WA_VERIFY_TOKEN')],
  // peran berwenang menyetujui (aturan keras: persetujuan manusia)
  'approve' => ['ticket_kritis'=>['safety'],'ticket_minor'=>['pelaksana','safety'],'permit'=>['pelaksana','safety'],'daily_report'=>['pelaksana']],
  'zones' => ['Gedung A Lt.1','Gedung A Lt.2','Gedung A Lt.3','Gedung A Lt.4','Gedung A Lt.5','Area Luar'],
  'hazard_categories' => ['Ketinggian','Listrik','Alat berat','Material','Akses','Lainnya'],
  // kategori hazard yang otomatis berlevel tinggi
  'hazard_high' => ['Ketinggian','Listrik','Alat berat'],
  // menu per peran: [nilai tombol, label]. Tombol umum (hazard, solusi cepat, tanya) ditambah FlowEngine.
  'menu' => [
    'pelaksana' => [['cl:cor','Sebelum Cor']],
    'mandor'    => [['cl:scaf','Scaffolding'],['cl:tinggi','Ketinggian'],['rep','Lapor Harian']],
    'tukang'    => [['cl:gal','Galian'],['cl:tinggi','Ketinggian']],
    'operator'  => [['cl:alat','Inspeksi Alat']],
    'rigger'    => [['cl:tinggi','Ketinggian'],['ptw','Ajukan Permit']],
    'safety'    => [['cl:scaf','Scaffolding']],
    'qc'        => [['cl:cor','Sebelum Cor']],
    'surveyor'  => [],
    'logistik'  => [],
    'pm'        => [],
  ],
  // topik safety talk (rotasi harian). Pindahkan ke tabel bila admin perlu mengedit dari UI.
  'topics' => [
    ['Bekerja di ketinggian','Pakai harness dan kaitkan lifeline sebelum naik.'],
    ['Pemakaian APD','Helm, rompi, dan sepatu dipakai sejak masuk gerbang.'],
    ['Scaffolding aman','Jangan naik jika scafftag merah atau tidak ada.'],
    ['Kerja panas','Siapkan APAR dan singkirkan bahan mudah terbakar.'],
    ['Lifting dan sling','Jangan berdiri di bawah beban yang diangkat.'],
    ['Galian dan longsor','Tanah galian ditumpuk jauh dari tepi.'],
    ['Listrik di site','Kabel terkelupas dilaporkan, jangan disentuh.'],
    ['Alat berat dan blind spot','Jangan lewat di belakang unit tanpa isyarat operator.'],
    ['Jalan licin saat hujan','Jalan pelan, bersihkan lumpur di akses.'],
    ['Housekeeping','Rapikan sisa material agar tidak tersandung.'],
  ],
];
