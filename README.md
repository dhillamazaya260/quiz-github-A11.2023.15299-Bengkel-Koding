# Aplikasi Poliklinik

Aplikasi web manajemen poliklinik berbasis **Laravel 10**. Pasien mendaftar ke poli dan mendapat nomor antrian real-time, dokter memeriksa dan meresepkan obat, admin mengelola data dan memverifikasi pembayaran.

Dibuat untuk kuis Bengkel Koding, Universitas Dian Nuswantoro.

## Fitur

- **Admin:** kelola poli, dokter, pasien, dan obat; verifikasi pembayaran; export Excel
- **Dokter:** kelola jadwal praktik, periksa pasien dan resepkan obat, lihat riwayat pasien
- **Pasien:** registrasi, daftar ke poli, pantau antrian real-time, unggah bukti bayar, cetak struk
- Notifikasi dan antrian real-time memakai Laravel Reverb (WebSocket)

## Penjelasan Fitur

### Autentikasi dan hak akses
- Login dan registrasi pasien. Nomor rekam medis dibuat otomatis berformat `YYYYMM-XXX`.
- Halaman dibatasi per peran (admin, dokter, pasien) lewat middleware `role`.
- Setiap peran memiliki halaman profil dan fitur ganti password.

### Admin
- CRUD data poli, dokter, pasien, dan obat (nama, kemasan, stok, harga).
- Verifikasi pembayaran: melihat bukti bayar, mengonfirmasi menjadi **Lunas**, atau menolak dengan alasan. Daftar bisa difilter per status (semua, belum, menunggu verifikasi, lunas).
- Export data dokter, pasien, dan obat ke Excel.

### Dokter
- Kelola jadwal praktik (hari, jam mulai, jam selesai) dan export jadwal ke Excel.
- Daftar pasien yang mendaftar ke jadwalnya. Tombol **Layani** menandai pasien yang sedang dilayani, lalu dokter mengisi catatan dan memilih obat.
- Total biaya dihitung otomatis: biaya jasa dokter (Rp 50.000) ditambah harga obat yang diresepkan.
- Riwayat pasien beserta export Excel.

### Pasien
- Mendaftar ke poli dengan memilih jadwal dan mengisi keluhan, lalu mendapat nomor antrian.
- Dashboard menampilkan banner antrian aktif (nomor saya dan nomor yang sedang dilayani) serta tabel semua jadwal poliklinik. Banner diperbarui otomatis tanpa memuat ulang halaman.
- Riwayat pendaftaran dan detail pemeriksaan, riwayat pembayaran, unggah bukti bayar, dan cetak struk setelah pembayaran lunas.

### Notifikasi real-time
- Lonceng notifikasi dan toast real-time lewat Laravel Reverb.
- Dokter diberi tahu saat ada antrian baru dan saat bukti bayar diunggah.
- Pasien diberi tahu saat giliran antrian tiba serta saat pembayaran diverifikasi atau ditolak.

## Alur Penggunaan

1. **Pasien** login, buka menu Daftar Poli, pilih poli dan jadwal, isi keluhan, lalu kirim. Nomor antrian muncul di banner dashboard.
2. **Dokter** buka Periksa Pasien, tekan **Layani** pada pasien, isi catatan dan obat, lalu simpan. Banner pasien otomatis berpindah ke nomor yang sedang dilayani, dan pasien berikutnya mendapat notifikasi giliran.
3. **Pasien** buka Riwayat, lihat rincian biaya, lalu unggah bukti pembayaran (jpg/jpeg/png, maksimal 2 MB).
4. **Admin** buka menu Pembayaran, lalu verifikasi atau tolak bukti bayar (penolakan wajib disertai alasan).
5. **Pasien** mencetak struk setelah status pembayaran **Lunas**.

## Constraint Teknis

| Constraint | Implementasi |
|---|---|
| Satu antrian aktif per pasien | Pasien tidak bisa mendaftar lagi sebelum pemeriksaan sebelumnya selesai. |
| Nomor antrian unik | Pembuatan nomor antrian memakai transaksi database dengan `lockForUpdate` pada jadwal, sehingga pendaftaran bersamaan tidak mendapat nomor sama. |
| Validasi jadwal terhadap poli | Jadwal yang dipilih harus milik dokter di poli yang dipilih. ID pasien selalu diambil dari user yang login, bukan dari input form. |
| Satu pasien dilayani per jadwal | Dokter tidak bisa menekan Layani pada pasien lain sebelum pasien yang sedang dilayani selesai diperiksa. Form pemeriksaan hanya bisa diisi setelah Layani ditekan. |
| Anti jadwal bentrok | Jam praktik dokter tidak boleh bertumpuk di hari yang sama, dan jam selesai harus setelah jam mulai. |
| Harga dihitung di server | Total biaya dihitung di server dari data obat, bukan dari input form. Harga obat disimpan sebagai snapshot di detail periksa sehingga riwayat tidak berubah saat harga obat diubah. |
| Transaksi pemeriksaan | Penyimpanan data periksa dan detail resep dilakukan dalam satu transaksi database. |
| Status pembayaran terkontrol | Alur belum, menunggu verifikasi, lunas. Perubahan status memakai `lockForUpdate` agar klik ganda tidak menghasilkan proses atau notifikasi ganda. Bukti bayar hanya bisa diunggah sekali; jika ditolak, status kembali ke belum dan file lama dihapus. |
| Validasi bukti bayar | Hanya jpg/jpeg/png, maksimal 2 MB, disimpan di disk privat (`local`), bukan folder publik. |
| Otorisasi kepemilikan data | Dokter hanya bisa mengakses pendaftaran dan jadwal miliknya, pasien hanya bisa melihat pemeriksaan miliknya. Akses halaman dibatasi per peran (403/404 jika tidak berhak). |
| Struk hanya untuk pembayaran lunas | Struk tidak bisa dicetak sebelum admin memverifikasi pembayaran. |
| Integritas data (foreign key RESTRICT) | Relasi data medis dan keuangan memakai `ON DELETE RESTRICT`, sehingga poli, obat, dokter, atau pasien yang punya riwayat tidak bisa dihapus. Obat yang sudah diresepkan tidak bisa dihapus; ubah stoknya menjadi 0. |
| Pembatasan login dan registrasi | Login dibatasi 5 percobaan gagal per kombinasi email dan IP (jeda 60 detik). Registrasi dibatasi 10 percobaan per IP (jeda 10 menit). |
| Validasi registrasi | No. KTP dan email harus unik, password minimal 6 karakter, password disimpan dalam bentuk hash. |
| Real-time tahan gagal | Kegagalan WebSocket (mis. Reverb belum berjalan) tidak membatalkan hasil pemeriksaan. Dashboard pasien juga memakai endpoint sinkronisasi dan polling cadangan setiap 5 detik. Notifikasi giliran hanya dikirim sekali per pendaftaran. |

## Teknologi

Laravel 10, PHP 8.1+, MySQL, Blade, Tailwind CSS 4, DaisyUI, Vite, Laravel Reverb, maatwebsite/excel

## Kebutuhan

- PHP 8.1+ (ekstensi `gd`, `zip`, `xml`, `mbstring`, `pdo_mysql` aktif)
- Composer 2
- MySQL atau MariaDB
- Node.js 18+ dan npm

## Struktur File

```text
poliklinik-app/
├── app/
│   ├── Events/            # Event siaran real-time (antrian)
│   ├── Exports/           # Export Excel (dokter, pasien, obat, jadwal, riwayat)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/     # Dashboard, poli, dokter, pasien, obat, pembayaran
│   │   │   ├── Dokter/    # Dashboard, jadwal, periksa pasien, riwayat
│   │   │   └── Pasien/    # Daftar poli, riwayat pendaftaran, pembayaran
│   │   ├── Middleware/    # Pembatasan akses per peran
│   │   └── Requests/      # Validasi form
│   ├── Models/            # User, Poli, JadwalPeriksa, DaftarPoli, Periksa, DetailPeriksa, Obat
│   ├── Notifications/     # Notifikasi antrian dan pembayaran
│   └── Support/           # Helper pengirim notifikasi
├── database/
│   ├── migrations/        # Skema tabel
│   └── seeders/           # Data awal (akun, poli, dokter, pasien, obat, jadwal)
├── resources/
│   ├── css/               # Tailwind + DaisyUI
│   ├── js/                # Konfigurasi Echo / Reverb
│   └── views/             # Template Blade (auth, admin, dokter, pasien)
├── routes/
│   ├── web.php            # Semua route aplikasi
│   └── channels.php       # Channel notifikasi
├── public/                # Entry point web server
├── .env.example           # Contoh konfigurasi
└── vite.config.js
```

## Instalasi

```bash
# 1. Clone dan masuk ke folder aplikasi
git clone https://github.com/<username>/<nama-repository>.git
cd <nama-repository>/poliklinik-app

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate
```

Buat database kosong (misalnya `poliklinik`), lalu atur koneksinya di `.env`:

```env
DB_DATABASE=poliklinik
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:

```bash
# 4. Migrasi dan data awal
php artisan migrate --seed
php artisan db:seed --class=JadwalSeeder   # jadwal dokter contoh (opsional)

# 5. Build aset
npm run build
```

## Cara Menjalankan

Buka **tiga terminal** di folder `poliklinik-app`, lalu jalankan masing-masing:

| Terminal | Perintah | Fungsi |
|---|---|---|
| 1 | `php artisan serve` | Menjalankan aplikasi di http://localhost:8000 |
| 2 | `php artisan reverb:start` | Server WebSocket (port 8080) untuk antrian dan notifikasi real-time |
| 3 | `npm run dev` | Vite untuk aset frontend (tidak wajib jika sudah `npm run build`) |

Lalu buka http://localhost:8000 di browser dan login memakai akun demo di bawah.

Catatan:
- Tanpa terminal 2, aplikasi tetap berjalan, tetapi antrian dan notifikasi baru muncul setelah halaman dimuat ulang.
- Jika mengubah `.env`, hentikan lalu jalankan ulang ketiga perintah di atas. Untuk nilai `VITE_*`, jalankan ulang juga `npm run build`.
- Untuk menghentikan, tekan `Ctrl + C` di tiap terminal.

## Akun Demo

| Peran | Email | Password |
|---|---|---|
| Admin | admin@gmail.com | admin |
| Dokter | dokter@gmail.com | dokter |
| Pasien | pasien@gmail.com | pasien |

Akun ini hanya untuk demo. Jangan dipakai di server sungguhan.

## Lisensi

Proyek pembelajaran. Dibangun di atas [Laravel](https://laravel.com) (MIT).
