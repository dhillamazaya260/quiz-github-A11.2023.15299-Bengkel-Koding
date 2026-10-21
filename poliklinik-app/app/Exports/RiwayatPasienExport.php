<?php

namespace App\Exports;

use App\Models\Periksa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

/** Riwayat pasien yang pernah diperiksa SATU dokter (dokter yang sedang login). */
class RiwayatPasienExport extends BaseExport implements WithColumnFormatting
{
    public function __construct(private int $idDokter)
    {
    }

    protected function judul(): string
    {
        return 'Riwayat Pasien';
    }

    public function collection(): Collection
    {
        return Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($query) {
            $query->where('id_dokter', $this->idDokter);
        })->with([
            'daftarPoli.pasien',
            'detailPeriksas.obat',
        ])->orderBy('tgl_periksa', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No. Antrian',
            'No. RM',
            'Nama Pasien',
            'Keluhan',
            'Tanggal Periksa',
            'Catatan',
            'Obat',
            'Jasa Dokter (Rp)',
            'Biaya Obat (Rp)',
            'Total Biaya (Rp)',
            'Status Bayar',
        ];
    }

    public function map($periksa): array
    {
        $daftar = $periksa->daftarPoli;

        $obat = $periksa->detailPeriksas
            ->map(fn ($d) => $d->obat?->nama_obat ?? 'Obat (sudah dihapus)')
            ->implode(', ');

        return [
            $daftar->no_antrian,
            $daftar->pasien->no_rm ?? '-',
            $daftar->pasien->nama ?? '-',
            $daftar->keluhan,
            $periksa->tgl_periksa?->format('d/m/Y') ?? '-',
            $periksa->catatan ?? '-',
            $obat !== '' ? $obat : '-',
            (int) $periksa->biaya_jasa,
            $periksa->total_obat,
            (int) $periksa->biaya_periksa,
            $periksa->status_bayar_label,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '"Rp" #,##0',
            'I' => '"Rp" #,##0',
            'J' => '"Rp" #,##0',
        ];
    }
}
