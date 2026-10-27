<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mengganti ON DELETE CASCADE menjadi RESTRICT pada relasi yang memegang
 * data medis dan keuangan, supaya menghapus poli / obat / dokter / pasien
 * tidak diam-diam menghapus riwayat pemeriksaan, resep, dan pembayaran.
 *
 * Relasi detail_periksa.id_periksa -> periksa sengaja tetap CASCADE
 * (baris resep adalah bagian dari satu pemeriksaan).
 */
return new class extends Migration
{
    /** [tabel, kolom, tabel induk] */
    private array $relasi = [
        ['users',          'id_poli',        'poli'],
        ['jadwal_periksa', 'id_dokter',      'users'],
        ['daftar_poli',    'id_pasien',      'users'],
        ['daftar_poli',    'id_jadwal',      'jadwal_periksa'],
        ['periksa',        'id_daftar_poli', 'daftar_poli'],
        ['detail_periksa', 'id_obat',        'obat'],
    ];

    public function up(): void
    {
        foreach ($this->relasi as [$tabel, $kolom, $induk]) {
            Schema::table($tabel, function (Blueprint $table) use ($kolom, $induk) {
                $table->dropForeign([$kolom]);
                $table->foreign($kolom)->references('id')->on($induk)->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->relasi as [$tabel, $kolom, $induk]) {
            Schema::table($tabel, function (Blueprint $table) use ($kolom, $induk) {
                $table->dropForeign([$kolom]);
                $table->foreign($kolom)->references('id')->on($induk)->cascadeOnDelete();
            });
        }
    }
};
