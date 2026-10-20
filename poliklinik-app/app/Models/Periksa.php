<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periksa extends Model
{
    use HasFactory;

    // Nama tabel di migration adalah "periksa" (bukan "periksas")
    protected $table = 'periksa';

    protected $fillable = ['id_daftar_poli', 'tgl_periksa', 'catatan', 'biaya_periksa'];

    protected $casts = [
        'tgl_periksa' => 'datetime',
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
}