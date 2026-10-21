<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;

class PasienExport extends BaseExport
{
    private int $no = 0;

    protected function judul(): string
    {
        return 'Data Pasien';
    }

    public function collection(): Collection
    {
        return User::where('role', 'pasien')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'No. RM', 'Nama Pasien', 'Email', 'No. KTP', 'No. HP', 'Alamat'];
    }

    public function map($pasien): array
    {
        return [
            ++$this->no,
            $pasien->no_rm ?? '-',
            $pasien->nama,
            $pasien->email,
            $pasien->no_ktp ?? '-',
            $pasien->no_hp ?? '-',
            $pasien->alamat ?? '-',
        ];
    }
}
