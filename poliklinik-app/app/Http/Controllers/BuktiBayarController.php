<?php

namespace App\Http\Controllers;

use App\Models\Periksa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BuktiBayarController extends Controller
{
    /**
     * Tampilkan file bukti bayar (disimpan di disk privat).
     * Hanya admin, pasien pemilik, atau dokter pemeriksa yang boleh membuka.
     */
    public function show($id)
    {
        $periksa = Periksa::with('daftarPoli.jadwalPeriksa')->findOrFail($id);
        $user    = Auth::user();

        $boleh = $user->role === 'admin'
            || ($user->role === 'pasien' && $periksa->daftarPoli->id_pasien === $user->id)
            || ($user->role === 'dokter' && $periksa->daftarPoli->jadwalPeriksa->id_dokter === $user->id);

        abort_unless($boleh, 403, 'Anda tidak punya akses ke bukti pembayaran ini.');
        abort_unless($periksa->bukti_bayar && Storage::disk('local')->exists($periksa->bukti_bayar), 404);

        return Storage::disk('local')->response($periksa->bukti_bayar);
    }
}
