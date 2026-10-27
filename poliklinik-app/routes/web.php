<?php

use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuktiBayarController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dokter\DashboardController as DokterDashboardController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\Pasien\PembayaranController as PasienPembayaranController;
use App\Http\Controllers\Pasien\PoliController as PasienPoliController;
use App\Http\Controllers\Pasien\RiwayatPendaftaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->role . '.dashboard');
    }

    return redirect()->route('login');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Notifikasi & bukti bayar (semua user yang login; akses dicek di controller)
Route::middleware('auth')->group(function () {
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::get('/bukti-bayar/{id}', [BuktiBayarController::class, 'show'])->name('bukti-bayar.show');
});

// Profil & ganti password (satu per role, supaya sidebar tiap role tetap tampil)
// Nama route: admin.profile, dokter.profile, pasien.profile (+ .password)
foreach (['admin', 'dokter', 'pasien'] as $role) {
    Route::middleware(['auth', 'role:' . $role])->prefix($role)->group(function () use ($role) {
        Route::get('/profile', [ProfileController::class, 'edit'])->name($role . '.profile');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name($role . '.profile.password');
    });
}

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Export Excel (didefinisikan sebelum resource)
    Route::get('dokter/export', [DokterController::class, 'export'])->name('dokter.export');
    Route::get('pasien/export', [PasienController::class, 'export'])->name('pasien.export');
    Route::get('obat/export', [ObatController::class, 'export'])->name('obat.export');

    Route::resource('polis', AdminPoliController::class)->except('show');
    Route::resource('dokter', DokterController::class)->except('show');
    Route::resource('pasien', PasienController::class)->except('show');
    Route::resource('obat', ObatController::class)->except('show');

    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/{id}/verifikasi', [AdminPembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
    Route::post('/pembayaran/{id}/tolak', [AdminPembayaranController::class, 'tolak'])->name('pembayaran.tolak');
});

// Dokter Routes
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', [DokterDashboardController::class, 'index'])->name('dokter.dashboard');

    Route::get('jadwal-periksa/export', [JadwalPeriksaController::class, 'export'])->name('jadwal-periksa.export');
    Route::resource('jadwal-periksa', JadwalPeriksaController::class)->except('show');

    Route::get('/periksa-pasien', [PeriksaPasienController::class, 'index'])->name('periksa-pasien.index');
    Route::post('/periksa-pasien', [PeriksaPasienController::class, 'store'])->name('periksa-pasien.store');
    Route::post('/periksa-pasien/{id}/layani', [PeriksaPasienController::class, 'layani'])->name('periksa-pasien.layani');
    Route::get('/periksa-pasien/{id}', [PeriksaPasienController::class, 'create'])->name('periksa-pasien.create');

    Route::get('/riwayat-pasien/export', [RiwayatPasienController::class, 'export'])->name('riwayat-pasien.export');
    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{id}', [RiwayatPasienController::class, 'show'])->name('riwayat-pasien.show');
});

// Pasien Routes
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', [PasienPoliController::class, 'dashboard'])->name('pasien.dashboard');
    Route::get('/antrian-status', [PasienPoliController::class, 'antrianStatus'])->name('pasien.antrian-status');

    Route::get('/daftar', [PasienPoliController::class, 'get'])->name('pasien.daftar');
    Route::post('/daftar', [PasienPoliController::class, 'submit'])->name('pasien.daftar.submit');

    // Riwayat pendaftaran poli + detail pemeriksaan
    Route::get('/riwayat-pendaftaran', [RiwayatPendaftaranController::class, 'index'])->name('pasien.histori.index');
    Route::get('/riwayat-pendaftaran/{id}', [RiwayatPendaftaranController::class, 'show'])->name('pasien.histori.show');

    // Riwayat pemeriksaan + pembayaran (pop-up)
    Route::get('/riwayat', [PasienPembayaranController::class, 'index'])->name('pasien.riwayat');
    Route::post('/riwayat/{id}/bayar', [PasienPembayaranController::class, 'bayar'])->name('pasien.bayar');

    // Struk pembayaran (hanya untuk pembayaran yang sudah diverifikasi / lunas)
    Route::get('/riwayat/{id}/struk', [PasienPembayaranController::class, 'struk'])->name('pasien.struk');
});