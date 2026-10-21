<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('periksa', function (Blueprint $table) {
        $table->string('status_bayar', 30)->default('belum')->after('biaya_periksa');
        $table->string('bukti_bayar')->nullable()->after('status_bayar');
        $table->dateTime('tgl_bayar')->nullable()->after('bukti_bayar');
        $table->dateTime('tgl_verifikasi')->nullable()->after('tgl_bayar');
    });
}

public function down(): void
{
    Schema::table('periksa', function (Blueprint $table) {
        $table->dropColumn(['status_bayar', 'bukti_bayar', 'tgl_bayar', 'tgl_verifikasi']);
    });
}
};
