<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periksa extends Model
{
    use HasFactory;

    // Status pembayaran
    public const BAYAR_BELUM    = 'belum';
    public const BAYAR_MENUNGGU = 'menunggu_verifikasi';
    public const BAYAR_LUNAS    = 'lunas';
    public const BIAYA_JASA_DOKTER = 50000;

    // Nama tabel di migration adalah "periksa" (bukan "periksas")
    protected $table = 'periksa';

    protected $fillable = [
        'id_daftar_poli',
        'tgl_periksa',
        'catatan',
        'biaya_periksa',
        'biaya_jasa',
        'status_bayar',
        'bukti_bayar',
        'tgl_bayar',
        'tgl_verifikasi',
        'catatan_penolakan',
    ];

    protected $casts = [
        'tgl_periksa'    => 'datetime',
        'tgl_bayar'      => 'datetime',
        'tgl_verifikasi' => 'datetime',
    ];

    public function daftarPoli()
    {
        return $this->belongsTo(DaftarPoli::class, 'id_daftar_poli');
    }

    public function detailPeriksas()
    {
        return $this->hasMany(DetailPeriksa::class, 'id_periksa');
    }

    public function obats()
    {
        return $this->belongsToMany(Obat::class, 'detail_periksa', 'id_periksa', 'id_obat');
    }

    public function getTotalObatAttribute(): int
{
    return (int) $this->detailPeriksas->sum('harga');
}

    /** Label status pembayaran untuk ditampilkan di view. */
    public function getStatusBayarLabelAttribute(): string
    {
        return match ($this->status_bayar) {
            self::BAYAR_MENUNGGU => 'Menunggu Verifikasi',
            self::BAYAR_LUNAS    => 'Lunas',
            default              => 'Belum Bayar',
        };
    }

    /** Kelas badge daisyUI sesuai status pembayaran. */
    public function getStatusBayarBadgeAttribute(): string
    {
        return match ($this->status_bayar) {
            self::BAYAR_MENUNGGU => 'badge-info',
            self::BAYAR_LUNAS    => 'badge-success',
            default              => 'badge-warning',
        };
    }
}