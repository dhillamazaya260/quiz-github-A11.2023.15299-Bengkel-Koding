# Aplikasi Poliklinik

Aplikasi web manajemen poliklinik berbasis **Laravel 10**. Pasien mendaftar ke poli dan mendapat nomor antrian real-time, dokter memeriksa dan meresepkan obat, admin mengelola data dan memverifikasi pembayaran.

Dibuat untuk kuis Bengkel Koding oleh Dhilla Mazaya Nasucha (A11.2023.15299), Universitas Dian Nuswantoro.

## Fitur

- **Admin:** kelola poli, dokter, pasien, dan obat; verifikasi pembayaran; export Excel
- **Dokter:** kelola jadwal praktik, periksa pasien dan resepkan obat, lihat riwayat pasien
- **Pasien:** registrasi, daftar ke poli, pantau antrian real-time, unggah bukti bayar, cetak struk
- Notifikasi dan antrian real-time memakai Laravel Reverb (WebSocket)

## Teknologi

Laravel 10, PHP 8.1+, MySQL, Blade, Tailwind CSS 4, DaisyUI, Vite, Laravel Reverb, maatwebsite/excel

## Spesifikasi

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
git clone https://github.com/<username>/quiz-github-A11.2023.15299-Bengkel-Koding.git
cd quiz-github-A11.2023.15299-Bengkel-Koding/poliklinik-app

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

Note: Akun ini hanya untuk demo.

## Lisensi

Proyek pembelajaran. Dibangun di atas [Laravel](https://laravel.com) (MIT).
