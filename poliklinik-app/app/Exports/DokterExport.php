<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;

class DokterExport extends BaseExport
{
    private int $no = 0;

    protected function judul(): string
    {
        return 'Data Dokter';
    }

    public function collection(): Collection
    {
        return User::where('role', 'dokter')->with('poli')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli'];
    }

    public function map($dokter): array
    {
        return [
            ++$this->no,
            $dokter->nama,
            $dokter->email,
            $dokter->no_ktp ?? '-',
            $dokter->no_hp ?? '-',
            $dokter->alamat ?? '-',
            $dokter->poli->nama_poli ?? 'Belum Dipilih',
        ];
    }
}
