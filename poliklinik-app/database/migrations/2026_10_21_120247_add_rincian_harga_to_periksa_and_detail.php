<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            $table->integer('biaya_jasa')->default(0)->after('biaya_periksa');
        });

        Schema::table('detail_periksa', function (Blueprint $table) {
            $table->integer('harga')->default(0)->after('id_obat');
        });

        // Backfill data lama
        DB::table('detail_periksa')
            ->join('obat', 'obat.id', '=', 'detail_periksa.id_obat')
            ->update(['detail_periksa.harga' => DB::raw('obat.harga')]);

        $totalObat = DB::table('detail_periksa')
            ->select('id_periksa', DB::raw('SUM(harga) as total'))
            ->groupBy('id_periksa')
            ->pluck('total', 'id_periksa');

        DB::table('periksa')->select('id', 'biaya_periksa')->orderBy('id')->each(function ($p) use ($totalObat) {
            DB::table('periksa')->where('id', $p->id)->update([
                'biaya_jasa' => max(0, (int) $p->biaya_periksa - (int) ($totalObat[$p->id] ?? 0)),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('detail_periksa', fn (Blueprint $t) => $t->dropColumn('harga'));
        Schema::table('periksa', fn (Blueprint $t) => $t->dropColumn('biaya_jasa'));
    }
};