<?php

namespace Database\Seeders;

use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /**
     * Poli yang hanya punya 1 dokter: praktik 4 hari per minggu (tidak 6 hari
     * penuh supaya wajar). Polanya diputar per poli agar hari terisi merata.
     */
    private const POLA_DOKTER_TUNGGAL = [
        ['Senin', 'Selasa', 'Kamis', 'Jumat'],
        ['Senin', 'Rabu', 'Kamis', 'Sabtu'],
        ['Selasa', 'Rabu', 'Jumat', 'Sabtu'],
        ['Senin', 'Selasa', 'Rabu', 'Jumat'],
    ];

    public function run(): void
    {
        $jumlah = 0;

        foreach (Poli::orderBy('id')->get() as $indexPoli => $poli) {
            $dokters = User::where('role', 'dokter')
                ->where('id_poli', $poli->id)
                ->orderBy('id')
                ->get();

            $n = $dokters->count();
            if ($n === 0) {
                continue;
            }

            // Poli bergantian shift: genap = pagi, ganjil = siang
            if ($indexPoli % 2 === 0) {
                $mulai = '08:00';
                $selesai = '12:00';
            } else {
                $mulai = '13:00';
                $selesai = '17:00';
            }

            foreach ($dokters as $k => $dokter) {
                if ($n === 1) {
                    $hariKerja = self::POLA_DOKTER_TUNGGAL[$indexPoli % count(self::POLA_DOKTER_TUNGGAL)];
                } else {
                    // Dokter bergantian hari sehingga Senin-Sabtu selalu ada yang praktik
                    $hariKerja = [];
                    foreach (self::HARI as $i => $hari) {
                        if ($i % $n === $k) {
                            $hariKerja[] = $hari;
                        }
                    }
                }

                // Hapus jadwal lama dokter ini yang belum dipakai pendaftaran manapun.
                // Jadwal yang sudah punya pendaftaran sengaja TIDAK dihapus.
                JadwalPeriksa::where('id_dokter', $dokter->id)
                    ->whereDoesntHave('daftarPolis')
                    ->delete();

                foreach ($hariKerja as $hari) {
                    // Sabtu setengah hari: selalu pagi 08:00-12:00
                    if ($hari === 'Sabtu') {
                        $jamMulai = '08:00';
                        $jamSelesai = '12:00';
                    } else {
                        $jamMulai = $mulai;
                        $jamSelesai = $selesai;
                    }

                    JadwalPeriksa::updateOrCreate(
                        ['id_dokter' => $dokter->id, 'hari' => $hari, 'jam_mulai' => $jamMulai],
                        ['jam_selesai' => $jamSelesai]
                    );
                    $jumlah++;
                }
            }
        }

        if ($this->command) {
            $this->command->info("Jadwal periksa selesai dibuat/diperbarui: {$jumlah} jadwal.");
        }
    }
}