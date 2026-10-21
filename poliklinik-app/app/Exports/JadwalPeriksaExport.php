<?php

namespace App\Exports;

use App\Models\JadwalPeriksa;
use Illuminate\Support\Collection;

/** Jadwal periksa milik SATU dokter (dokter yang sedang login). */
class JadwalPeriksaExport extends BaseExport
{
    private int $no = 0;

    public function __construct(private int $idDokter)
    {
    }

    protected function judul(): string
    {
        return 'Jadwal Periksa';
    }

    public function collection(): Collection
    {
        // Urutan sama dengan halaman Jadwal Periksa
        return JadwalPeriksa::where('id_dokter', $this->idDokter)
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Hari', 'Jam Mulai', 'Jam Selesai'];
    }

    public function map($jadwal): array
    {
        return [
            ++$this->no,
            $jadwal->hari,
            substr($jadwal->jam_mulai, 0, 5),
            substr($jadwal->jam_selesai, 0, 5),
        ];
    }
}
