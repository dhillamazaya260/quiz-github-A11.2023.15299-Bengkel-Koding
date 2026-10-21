<?php

namespace Database\Seeders;

use App\Models\Poli;
use Illuminate\Database\Seeder;

class PoliSeeder extends Seeder
{
    public function run(): void
    {
        $polis = [
            ['nama_poli' => 'Poli Umum',      'keterangan' => 'Pemeriksaan kesehatan umum dan penyakit ringan'],
            ['nama_poli' => 'Poli Gigi',      'keterangan' => 'Pemeriksaan dan perawatan gigi dan mulut'],
            ['nama_poli' => 'Poli Anak',      'keterangan' => 'Pemeriksaan kesehatan bayi dan anak'],
            ['nama_poli' => 'Poli Kandungan', 'keterangan' => 'Pemeriksaan kehamilan dan kesehatan reproduksi'],
            ['nama_poli' => 'Poli Mata',      'keterangan' => 'Pemeriksaan dan pengobatan penyakit mata'],
            ['nama_poli' => 'Poli THT',       'keterangan' => 'Pemeriksaan telinga, hidung, dan tenggorokan'],
        ];

        // updateOrCreate: aman dijalankan berulang, tidak membuat data ganda
        foreach ($polis as $poli) {
            Poli::updateOrCreate(
                ['nama_poli' => $poli['nama_poli']],
                ['keterangan' => $poli['keterangan']]
            );
        }
    }
}