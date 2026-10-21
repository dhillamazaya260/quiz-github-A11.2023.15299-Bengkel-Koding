<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DaftarPoli extends Model
{
    protected $table = 'daftar_poli';

    protected $fillable = ['id_pasien', 'id_jadwal', 'keluhan', 'no_antrian', 'dilayani_pada'];

    protected $casts = [
        'dilayani_pada' => 'datetime',
    ];

    /**
     * Nomor yang "Sedang Dilayani" pada sebuah jadwal.
     *  1. Jika dokter sudah menekan tombol "Periksa" pada seorang pasien yang belum
     *     selesai diperiksa -> nomor pasien itulah yang sedang dilayani.
     *  2. Jika tidak ada (dokter belum memanggil siapa pun / baru selesai) ->
     *     nomor terakhir yang sudah selesai diperiksa. 0 jika belum ada.
     */
    public static function nomorDilayani(int $idJadwal): int
    {
        $sedangDiperiksa = static::where('id_jadwal', $idJadwal)
            ->whereNotNull('dilayani_pada')
            ->whereDoesntHave('periksa')
            ->max('no_antrian');

        if ($sedangDiperiksa !== null) {
            return (int) $sedangDiperiksa;
        }

        return (int) static::where('id_jadwal', $idJadwal)
            ->whereHas('periksa')
            ->max('no_antrian');
    }

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