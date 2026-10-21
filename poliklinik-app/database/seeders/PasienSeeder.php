<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [nama, email, alamat]
        $pasiens = [
            ['Ahmad Fauzi',        'ahmad.fauzi@pasien.test',        'Jl. Tembalang Raya No. 10, Semarang'],
            ['Budi Hartono',       'budi.hartono@pasien.test',       'Jl. Kedungmundu No. 22, Semarang'],
            ['Citra Maharani',     'citra.maharani@pasien.test',     'Jl. Banyumanik No. 5, Semarang'],
            ['Dian Puspitasari',   'dian.puspitasari@pasien.test',   'Jl. Ngaliyan No. 18, Semarang'],
            ['Eko Prasetyo',       'eko.prasetyo@pasien.test',       'Jl. Gunungpati No. 3, Semarang'],
            ['Fitri Handayani',    'fitri.handayani@pasien.test',    'Jl. Pedurungan No. 40, Semarang'],
            ['Gilang Ramadhan',    'gilang.ramadhan@pasien.test',    'Jl. Genuk No. 15, Semarang'],
            ['Hana Safitri',       'hana.safitri@pasien.test',       'Jl. Candisari No. 27, Semarang'],
            ['Indra Kusuma',       'indra.kusuma@pasien.test',       'Jl. Gajahmungkur No. 9, Semarang'],
            ['Joko Susilo',        'joko.susilo@pasien.test',        'Jl. Mijen No. 31, Semarang'],
            ['Kartika Sari',       'kartika.sari@pasien.test',       'Jl. Tugu No. 12, Semarang'],
            ['Lukman Hakim',       'lukman.hakim@pasien.test',       'Jl. Semarang Barat No. 8, Semarang'],
            ['Mega Wulandari',     'mega.wulandari@pasien.test',     'Jl. Semarang Timur No. 20, Semarang'],
            ['Nur Aini',           'nur.aini@pasien.test',           'Jl. Semarang Utara No. 6, Semarang'],
            ['Oki Setiawan',       'oki.setiawan@pasien.test',       'Jl. Semarang Selatan No. 14, Semarang'],
        ];

        foreach ($pasiens as $i => [$nama, $email, $alamat]) {
            $n = $i + 1;

            $user = User::firstOrNew(['email' => $email]);

            // No. RM hanya dibuat saat pasien baru, supaya tidak berubah kalau seeder diulang
            if (! $user->exists) {
                $user->no_rm = $this->nomorRmBerikutnya();
            }

            $user->fill([
                'nama'     => $nama,
                'alamat'   => $alamat,
                'no_ktp'   => sprintf('3374%012d', 200000 + $n), // 16 digit
                'no_hp'    => sprintf('0857%08d', 20000 + $n),
                'role'     => 'pasien',
                'password' => Hash::make('pasien123'),
            ])->save();
        }
    }

    /** Format no_rm sama dengan form register: YYYYMM-XXX */
    private function nomorRmBerikutnya(): string
    {
        $prefix = date('Ym');

        $terakhir = User::where('role', 'pasien')
            ->where('no_rm', 'like', $prefix . '-%')
            ->orderBy('no_rm', 'desc')
            ->value('no_rm');

        $urut = $terakhir ? ((int) explode('-', $terakhir)[1]) + 1 : 1;

        return $prefix . '-' . str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }
}