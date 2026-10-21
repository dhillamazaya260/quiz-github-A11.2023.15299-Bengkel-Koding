<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dijaga supaya aman jika kolom stok sudah ada di database Anda.
        if (Schema::hasColumn('obat', 'stok')) {
            return;
        }

        Schema::table('obat', function (Blueprint $table) {
            $table->unsignedInteger('stok')->default(0)->after('kemasan');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('obat', 'stok')) {
            Schema::table('obat', function (Blueprint $table) {
                $table->dropColumn('stok');
            });
        }
    }
};
