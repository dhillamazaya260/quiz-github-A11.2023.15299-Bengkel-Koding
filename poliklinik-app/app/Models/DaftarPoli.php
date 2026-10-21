<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DaftarPoli extends Model
{
    protected $table = 'daftar_poli';

    protected $fillable = ['id_pasien', 'id_jadwal', 'keluhan', 'no_antrian'];

    public function pasien()
    {
        return $this->belongsTo(User::class, 'id_pasien');
    }

    public function jadwalPeriksa()
    {
        return $this->belongsTo(JadwalPeriksa::class, 'id_jadwal');
    }

    public function periksas()
    {
        return $this->hasMany(Periksa::class, 'id_daftar_poli');
    }

    // Satu pendaftaran hanya punya satu hasil periksa
    public function periksa()
    {
        return $this->hasOne(Periksa::class, 'id_daftar_poli');
    }

    /**
     * Tanggal jadwal = tanggal terdekat (pada/setelah tanggal daftar)
     * yang jatuh pada hari jadwal. Tabel jadwal_periksa hanya menyimpan
     * nama hari, jadi tanggalnya dihitung, bukan disimpan.
     *
     * Pakai: $daftar->tanggal_jadwal?->format('d/m/Y')
     * Relasi jadwalPeriksa sebaiknya sudah di-eager load.
     */
    public function getTanggalJadwalAttribute(): ?Carbon
    {
        $hari = $this->jadwalPeriksa?->hari;

        if (! $hari || ! $this->created_at) {
            return null;
        }

        $nomorHari = [
            'Minggu' => Carbon::SUNDAY,
            'Senin'  => Carbon::MONDAY,
            'Selasa' => Carbon::TUESDAY,
            'Rabu'   => Carbon::WEDNESDAY,
            'Kamis'  => Carbon::THURSDAY,
            'Jumat'  => Carbon::FRIDAY,
            'Sabtu'  => Carbon::SATURDAY,
        ];

        if (! isset($nomorHari[$hari])) {
            return null;
        }

        $tanggal = $this->created_at->copy()->startOfDay();
        $selisih = ($nomorHari[$hari] - $tanggal->dayOfWeek + 7) % 7;

        return $tanggal->addDays($selisih);
    }
}