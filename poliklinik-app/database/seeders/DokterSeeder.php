<?php

namespace Database\Seeders;

use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [nama, email, nama poli, alamat]
        $dokters = [
            ['dr. Andi Prasetyo',        'andi.prasetyo@poliklinik.test',   'Poli Umum',      'Jl. Pandanaran No. 12, Semarang'],
            ['dr. Siti Rahmawati',       'siti.rahmawati@poliklinik.test',  'Poli Umum',      'Jl. Pemuda No. 45, Semarang'],
            ['drg. Budi Santoso',        'budi.santoso@poliklinik.test',    'Poli Gigi',      'Jl. Veteran No. 8, Semarang'],
            ['drg. Maya Anggraini',      'maya.anggraini@poliklinik.test',  'Poli Gigi',      'Jl. Sultan Agung No. 21, Semarang'],
            ['dr. Rina Kartika, Sp.A',   'rina.kartika@poliklinik.test',    'Poli Anak',      'Jl. Gajah Mada No. 7, Semarang'],
            ['dr. Hendra Wijaya, Sp.OG', 'hendra.wijaya@poliklinik.test',   'Poli Kandungan', 'Jl. MT Haryono No. 33, Semarang'],
            ['dr. Dewi Lestari, Sp.M',   'dewi.lestari@poliklinik.test',    'Poli Mata',      'Jl. Imam Bonjol No. 19, Semarang'],
            ['dr. Fajar Nugroho, Sp.THT', 'fajar.nugroho@poliklinik.test',  'Poli THT',       'Jl. Pahlawan No. 5, Semarang'],
        ];

        foreach ($dokters as $i => [$nama, $email, $namaPoli, $alamat]) {
            $poli = Poli::where('nama_poli', $namaPoli)->first();

            if (! $poli) {
                $this->command?->warn("Poli '{$namaPoli}' tidak ditemukan. Jalankan PoliSeeder dulu.");
                continue;
            }

            $n = $i + 1;

            User::updateOrCreate(
                ['email' => $email],
                [
                    'nama'     => $nama,
                    'alamat'   => $alamat,
                    'no_ktp'   => sprintf('3374%012d', 100000 + $n), // 16 digit
                    'no_hp'    => sprintf('0812%08d', 10000 + $n),
                    'id_poli'  => $poli->id,
                    'role'     => 'dokter',
                    'password' => Hash::make('dokter123'),
                ]
            );
        }
    }
}