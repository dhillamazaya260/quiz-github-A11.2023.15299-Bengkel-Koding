<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            // Alasan admin menolak bukti bayar (diisi saat ditolak, dikosongkan saat pasien unggah ulang)
            $table->string('catatan_penolakan', 255)->nullable()->after('tgl_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            $table->dropColumn('catatan_penolakan');
        });
    }
};
