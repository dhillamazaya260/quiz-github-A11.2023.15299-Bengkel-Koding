# Aplikasi Poliklinik

Aplikasi web manajemen poliklinik berbasis **Laravel 10**. Mencakup pendaftaran pasien ke poli, antrian real-time, pemeriksaan dan resep obat oleh dokter, pembayaran dengan unggah bukti bayar, notifikasi, dan export data ke Excel. Memiliki tiga peran pengguna: **Admin**, **Dokter**, dan **Pasien**.

Proyek ini dibuat untuk kuis Bengkel Koding.

- **Pembuat:** Dhilla Mazaya Nasucha (A11.2023.15299)
- **Kampus:** Universitas Dian Nuswantoro (UDINUS), Semarang
- **Dokumentasi lengkap:** lihat file PDF `Dokumentasi_Aplikasi_Poliklinik` (penjelasan tiap controller, query, dan fungsi)

---

## Daftar Isi

1. [Fitur](#fitur)
2. [Teknologi](#teknologi)
3. [Kebutuhan Sistem](#kebutuhan-sistem)
4. [Instalasi](#instalasi)
5. [Menjalankan Aplikasi](#menjalankan-aplikasi)
6. [Akun Contoh](#akun-contoh)
7. [Konfigurasi .env](#konfigurasi-env)
8. [Struktur Folder](#struktur-folder)
9. [Alur Penggunaan](#alur-penggunaan)
10. [Perintah Artisan yang Berguna](#perintah-artisan-yang-berguna)
11. [Pengujian](#pengujian)
12. [Deploy ke Production](#deploy-ke-production)
13. [Troubleshooting](#troubleshooting)

---

## Fitur

### Admin
- Kelola data **Poli**, **Dokter**, **Pasien**, dan **Obat** (tambah, ubah, hapus)
- **Verifikasi atau tolak** bukti pembayaran pasien (dengan catatan penolakan)
- **Export Excel** data dokter, pasien, dan obat
- Dashboard ringkasan data
- Penghapusan aman: data yang masih dipakai tidak bisa dihapus dan ditampilkan pesan yang jelas

### Dokter
- Kelola **jadwal praktik** sendiri (aturan: hari Senin sampai Minggu, jam selesai harus setelah jam mulai, dan jadwal tidak boleh bentrok dengan jadwal lain milik dokter yang sama)
- **Periksa pasien**: panggil pasien (Layani), isi catatan, pilih obat, simpan pemeriksaan
- **Riwayat pasien** beserta detail dan rincian biaya
- Dashboard antrian hari ini dan statistik pasien
- Export Excel jadwal dan riwayat pasien

### Pasien
- **Registrasi dan login** mandiri, dengan nomor rekam medis otomatis (format `YYYYMM-XXX`)
- **Daftar ke poli**: pilih poli dan jadwal dokter, lalu dapat nomor antrian
- **Antrian real-time**: nomor yang sedang dilayani berubah otomatis tanpa muat ulang halaman (Laravel Reverb / WebSocket)
- **Riwayat pendaftaran** dan detail pemeriksaan
- **Pembayaran**: unggah bukti bayar (JPG/PNG, maksimal 2 MB) dan cetak struk
- **Notifikasi**: antrian baru, giliran tiba, status verifikasi pembayaran

### Keamanan
- Pembatasan akses berdasarkan peran (middleware `role`)
- Pembatasan login: maksimal 5 percobaan per 60 detik
- Pembatasan registrasi: maksimal 10 percobaan per 10 menit
- Bukti bayar disimpan di disk privat (`storage/app`), hanya bisa dibuka lewat route yang memeriksa kepemilikan
- Data sensitif (id pasien, id dokter, role, harga, total biaya) selalu ditentukan server, bukan form
- Operasi yang rawan benturan (nomor antrian, unggah, verifikasi) memakai transaksi dan kunci baris

---

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP 8.1+, Laravel 10 |
| Database | MySQL / MariaDB |
| Real-time | Laravel Reverb (WebSocket), Laravel Echo, pusher-js |
| Frontend | Blade, Tailwind CSS 4, DaisyUI 5, Vite 5 |
| Export | maatwebsite/excel 3.1 |
| Autentikasi API | Laravel Sanctum (bawaan, aplikasi memakai session) |

---

## Kebutuhan Sistem

Pastikan sudah terpasang:

| Perangkat | Versi |
|---|---|
| PHP | 8.1 atau lebih baru |
| Composer | 2.x |
| MySQL atau MariaDB | 5.7+ / 10.3+ |
| Node.js dan npm | Node 18 atau lebih baru (disarankan 20 LTS) |
| Git | Terbaru |

**Ekstensi PHP yang dibutuhkan:** `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
Ekstensi `gd`, `zip`, dan `xml` wajib karena dipakai fitur export Excel.

> **Pengguna Windows:** paling mudah memakai [Laragon](https://laragon.org/) atau XAMPP (PHP 8.1+). Di XAMPP, aktifkan ekstensi `gd` dan `zip` di `php.ini` (hapus tanda `;` di depan `extension=gd` dan `extension=zip`), lalu restart Apache.

Cek versi terpasang:

```bash
php -v
composer -V
node -v
npm -v
mysql --version
```

---

## Instalasi

### 1. Clone repository

```bash
git clone https://github.com/<username>/quiz-github-A11.2023.15299-Bengkel-Koding.git
cd quiz-github-A11.2023.15299-Bengkel-Koding/poliklinik-app
```

Jika memakai file ZIP, ekstrak lalu masuk ke folder `poliklinik-app`.

### 2. Pasang dependensi PHP

```bash
composer install
```

### 3. Pasang dependensi JavaScript

```bash
npm install
```

### 4. Siapkan file environment

Linux / macOS / Git Bash:

```bash
cp .env.example .env
```

Windows (Command Prompt):

```cmd
copy .env.example .env
```

### 5. Buat application key

```bash
php artisan key:generate
```

### 6. Buat database

Masuk ke MySQL lalu buat database kosong:

```sql
CREATE DATABASE poliklinik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atau lewat terminal:

```bash
mysql -u root -p -e "CREATE DATABASE poliklinik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 7. Atur koneksi database di `.env`

Buka file `.env`, lalu sesuaikan:

```env
APP_NAME="Poliklinik"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=poliklinik
DB_USERNAME=root
DB_PASSWORD=
```

Isi `DB_PASSWORD` sesuai password MySQL-mu (kosong jika tidak memakai password, seperti default XAMPP dan Laragon).

### 8. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

Perintah ini membuat seluruh tabel (19 file migrasi) dan mengisi data awal: 3 akun utama, 6 poli, 8 dokter, 15 pasien, dan daftar obat contoh.

**Opsional, isi jadwal praktik dokter contoh.** `JadwalSeeder` tidak ikut dijalankan otomatis, jadi jalankan terpisah bila ingin jadwal siap pakai:

```bash
php artisan db:seed --class=JadwalSeeder
```

Pasien baru baru bisa mendaftar ke poli bila dokter sudah punya jadwal. Jadi langkah ini sangat disarankan untuk mencoba aplikasi.

### 9. Build aset frontend

Untuk pemakaian biasa (sekali build):

```bash
npm run build
```

Atau jalankan mode pengembangan dengan `npm run dev` (lihat bagian berikutnya).

Instalasi selesai. Lanjut ke bagian berikutnya untuk menjalankan aplikasi.

---

## Menjalankan Aplikasi

Aplikasi membutuhkan **tiga proses** yang berjalan bersamaan. Buka tiga jendela terminal di folder `poliklinik-app`.

**Terminal 1: server aplikasi**

```bash
php artisan serve
```

Aplikasi tersedia di <http://localhost:8000>.

**Terminal 2: server WebSocket (Reverb)**, untuk antrian real-time dan notifikasi

```bash
php artisan reverb:start
```

Server berjalan di port `8080` sesuai `REVERB_PORT`.

**Terminal 3: Vite**, untuk aset frontend

```bash
npm run dev
```

> Jika sudah menjalankan `npm run build`, Terminal 3 tidak wajib. Namun bila `.env` diubah (terutama `VITE_*`), jalankan ulang `npm run build` atau restart `npm run dev`.

> Antrian dan notifikasi memakai `QUEUE_CONNECTION=sync`, jadi **tidak perlu** menjalankan `queue:work`.

Aplikasi tetap bisa dipakai tanpa Reverb. Bedanya, angka antrian dan notifikasi baru muncul setelah halaman dimuat ulang.

---

## Akun Contoh

Dibuat otomatis oleh seeder.

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@gmail.com` | `admin` |
| Dokter | `dokter@gmail.com` | `dokter` |
| Pasien | `pasien@gmail.com` | `pasien` |

Akun tambahan dari seeder:

| Peran | Contoh email | Password |
|---|---|---|
| Dokter (8 akun) | `andi.prasetyo@poliklinik.test`, `siti.rahmawati@poliklinik.test`, `budi.santoso@poliklinik.test`, dan lainnya | `dokter123` |
| Pasien (15 akun) | `ahmad.fauzi@pasien.test`, `budi.hartono@pasien.test`, `citra.maharani@pasien.test`, dan lainnya | `pasien123` |

> **Penting:** ganti semua password contoh ini sebelum aplikasi dipakai di server sungguhan.

Poli yang tersedia dari seeder: Poli Umum, Poli Gigi, Poli Anak, Poli Kandungan, Poli Mata, dan Poli THT.

---

## Konfigurasi .env

Variabel penting selain koneksi database:

| Variabel | Nilai default | Keterangan |
|---|---|---|
| `APP_ENV` | `local` | Ganti `production` saat deploy |
| `APP_DEBUG` | `true` | **Wajib `false`** di production |
| `APP_URL` | `http://localhost` | Isi alamat aplikasi, misalnya `http://localhost:8000` |
| `BROADCAST_DRIVER` | `reverb` | Driver siaran real-time |
| `QUEUE_CONNECTION` | `sync` | Notifikasi diproses langsung, tanpa worker |
| `SESSION_DRIVER` | `file` | Session disimpan di `storage/framework/sessions` |
| `FILESYSTEM_DISK` | `local` | Bukti bayar memakai disk `local` (privat) |
| `REVERB_APP_ID` | `poliklinik` | ID aplikasi Reverb |
| `REVERB_APP_KEY` | `poliklinik-key` | Kunci publik Reverb |
| `REVERB_APP_SECRET` | `poliklinik-secret` | Secret Reverb (**ganti di production**) |
| `REVERB_HOST` | `localhost` | Host server WebSocket |
| `REVERB_PORT` | `8080` | Port server WebSocket |
| `REVERB_SCHEME` | `http` | `http` untuk lokal, `https` untuk production |
| `VITE_REVERB_*` | mengikuti `REVERB_*` | Dipakai browser; nilainya harus sama dengan `REVERB_*` |

Zona waktu aplikasi: **Asia/Jakarta** (diatur di `config/app.php`).

---

## Struktur Folder

```text
poliklinik-app/
├── app/
│   ├── Events/              # AntrianDiperbarui (siaran WebSocket)
│   ├── Exports/             # Kelas export Excel (Dokter, Pasien, Obat, Jadwal, Riwayat)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/       # Dashboard, Poli, Dokter, Pasien, Obat, Pembayaran
│   │   │   ├── Dokter/      # Dashboard, Jadwal, Periksa Pasien, Riwayat Pasien
│   │   │   ├── Pasien/      # Poli (daftar), Riwayat Pendaftaran, Pembayaran
│   │   │   ├── AuthController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── NotifikasiController.php
│   │   │   └── BuktiBayarController.php
│   │   ├── Middleware/      # RoleMiddleware, RedirectIfAuthenticated, dll.
│   │   └── Requests/        # UpdatePasswordRequest
│   ├── Models/              # User, Poli, JadwalPeriksa, DaftarPoli, Periksa, DetailPeriksa, Obat
│   ├── Notifications/       # AntrianBaru, GiliranAntrianTiba, Pembayaran*
│   ├── Providers/
│   └── Support/             # Notifier (pengirim notifikasi)
├── database/
│   ├── migrations/          # 19 file migrasi
│   └── seeders/             # User, Poli, Dokter, Pasien, Obat, Jadwal
├── resources/
│   ├── css/app.css          # Tailwind + DaisyUI
│   ├── js/                  # app.js, bootstrap.js (konfigurasi Echo / Reverb)
│   └── views/               # Blade: auth, admin, dokter, pasien, layouts
├── routes/
│   ├── web.php              # Seluruh route aplikasi
│   └── channels.php         # Otorisasi channel privat notifikasi
├── storage/app/bukti-bayar/ # Bukti bayar (privat, dibuat otomatis saat unggah)
└── .env.example
```

---

## Alur Penggunaan

Urutan untuk mencoba seluruh fitur dari awal:

1. **Login sebagai Admin** (`admin@gmail.com`). Periksa data poli, dokter, dan obat, atau tambahkan data baru.
2. **Login sebagai Dokter**. Buka menu **Jadwal Periksa** dan pastikan ada jadwal (atau jalankan `JadwalSeeder`).
3. **Login sebagai Pasien** atau daftar akun baru. Buka **Daftar Poli**, pilih poli dan jadwal, lalu kirim. Pasien mendapat nomor antrian.
4. **Kembali ke Dokter**. Di dashboard atau **Periksa Pasien**, tekan **Layani** pada pasien, lalu **Resepkan Obat**, isi catatan, pilih obat, dan simpan. Pantau halaman pasien: nomor antrian berubah otomatis bila Reverb aktif.
5. **Pasien** membuka menu **Pembayaran**, mengunggah bukti bayar, lalu menunggu verifikasi.
6. **Admin** membuka **Pembayaran**, lalu memverifikasi atau menolak bukti bayar (dengan catatan).
7. **Pasien** menerima notifikasi hasil verifikasi dan bisa mencetak struk setelah status lunas.

Status pembayaran: belum bayar, menunggu verifikasi, lunas, atau ditolak (pasien bisa mengunggah ulang).

---

## Perintah Artisan yang Berguna

```bash
# Migrasi
php artisan migrate                      # jalankan migrasi yang belum berjalan
php artisan migrate:status               # lihat status migrasi
php artisan migrate:fresh --seed         # HAPUS semua tabel, buat ulang, isi data awal
php artisan migrate:rollback             # batalkan batch migrasi terakhir

# Seeder
php artisan db:seed                      # semua seeder di DatabaseSeeder
php artisan db:seed --class=JadwalSeeder # satu seeder saja

# Membuat migrasi baru (jangan menyalin file bertimestamp dari proyek lain)
php artisan make:migration add_kolom_ke_tabel --table=nama_tabel
php artisan make:migration create_tabel_baru --create=tabel_baru

# Cache dan debugging
php artisan route:list                   # daftar semua route
php artisan optimize:clear               # bersihkan cache config, route, view
php artisan tinker                       # konsol interaktif
```

> `migrate:fresh` menghapus seluruh data. Pakai hanya di lingkungan pengembangan.

---

## Pengujian

Proyek hanya memiliki contoh test bawaan Laravel (`tests/Feature/ExampleTest.php` dan `tests/Unit/ExampleTest.php`).

```bash
php artisan test
```

Format kode mengikuti Laravel Pint:

```bash
./vendor/bin/pint
```

---

## Deploy ke Production

Ringkasan langkah (sesuaikan dengan server):

1. Pasang kebutuhan sistem yang sama seperti di atas, lalu unggah kode.
2. Pasang dependensi tanpa paket pengembangan:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

3. Atur `.env` untuk production:

   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com
   REVERB_APP_SECRET=<ganti dengan nilai acak yang kuat>
   REVERB_HOST=domain-anda.com
   REVERB_PORT=443
   REVERB_SCHEME=https
   ```

4. Jalankan migrasi dan optimasi:

   ```bash
   php artisan key:generate        # hanya jika APP_KEY masih kosong
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. Arahkan *document root* web server ke folder `public/`.
6. Beri izin tulis pada `storage/` dan `bootstrap/cache/` untuk user web server.
7. Jalankan `php artisan reverb:start` sebagai proses latar belakang yang dijaga (misalnya dengan Supervisor), dan teruskan koneksi WebSocket lewat reverse proxy (Nginx) dengan HTTPS.
8. **Ganti semua password akun contoh** atau hapus akun contoh yang tidak dipakai.

---

## Troubleshooting

| Masalah | Penyebab dan solusi |
|---|---|
| `Class "ZipArchive" not found` atau export Excel gagal | Ekstensi `zip` / `gd` / `xml` belum aktif. Aktifkan di `php.ini`, lalu restart server. |
| `SQLSTATE[HY000] [1049] Unknown database` | Database belum dibuat atau nama di `DB_DATABASE` salah. Ulangi langkah 6 dan 7. |
| `SQLSTATE[HY000] [1045] Access denied` | `DB_USERNAME` atau `DB_PASSWORD` salah. |
| `No application encryption key has been specified` | Jalankan `php artisan key:generate`. |
| Halaman tampil tanpa gaya (polos) | Aset belum dibuat. Jalankan `npm run build` atau `npm run dev`. |
| `Vite manifest not found` | Sama seperti di atas: jalankan `npm run build`. |
| Antrian dan notifikasi tidak berubah otomatis | Pastikan `php artisan reverb:start` berjalan, nilai `VITE_REVERB_*` sama dengan `REVERB_*`, lalu build ulang aset (`npm run build`). |
| Pasien tidak bisa memilih jadwal saat daftar poli | Belum ada jadwal dokter. Jalankan `php artisan db:seed --class=JadwalSeeder` atau buat jadwal dari akun dokter. |
| "Terlalu banyak percobaan login" | Pembatasan 5 percobaan per 60 detik. Tunggu satu menit. |
| Bukti bayar tidak tampil | File tersimpan di `storage/app/bukti-bayar`. Pastikan folder `storage` bisa ditulis dan file tidak terhapus. |
| Perubahan `.env` tidak terbaca | Jalankan `php artisan optimize:clear`, lalu restart `php artisan serve`. |
| Port 8000 atau 8080 sudah dipakai | Gunakan port lain: `php artisan serve --port=8001`, atau ubah `REVERB_PORT` dan `VITE_REVERB_PORT`. |

---

## Lisensi

Dibuat untuk keperluan pembelajaran. Kerangka aplikasi memakai [Laravel](https://laravel.com) yang berlisensi MIT.
