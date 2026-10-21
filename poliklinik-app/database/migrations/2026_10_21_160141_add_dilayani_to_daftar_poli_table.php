<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dijaga supaya aman jika kolom sudah ada di database Anda.
        if (Schema::hasColumn('daftar_poli', 'dilayani_pada')) {
            return;
        }

        Schema::table('daftar_poli', function (Blueprint $table) {
            // Diisi saat dokter menekan tombol "Periksa" pada pasien ini
            $table->timestamp('dilayani_pada')->nullable()->after('no_antrian');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('daftar_poli', 'dilayani_pada')) {
            Schema::table('daftar_poli', function (Blueprint $table) {
                $table->dropColumn('dilayani_pada');
            });
        }
    }
};