<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    // Nama tabel di migration adalah "poli" (bukan "polis")
    protected $table = 'poli';

    protected $fillable = ['nama_poli', 'keterangan'];
}