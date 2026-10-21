<?php

namespace App\Exports;

use App\Models\Obat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ObatExport extends BaseExport implements WithColumnFormatting
{
    private int $no = 0;

    protected function judul(): string
    {
        return 'Data Obat';
    }

    public function collection(): Collection
    {
        return Obat::orderBy('nama_obat')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Obat', 'Kemasan', 'Stok', 'Harga (Rp)'];
    }

    public function map($obat): array
    {
        return [
            ++$this->no,
            $obat->nama_obat,
            $obat->kemasan ?? '-',
            (int) ($obat->stok ?? 0),
            (int) $obat->harga,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '"Rp" #,##0',
        ];
    }
}
