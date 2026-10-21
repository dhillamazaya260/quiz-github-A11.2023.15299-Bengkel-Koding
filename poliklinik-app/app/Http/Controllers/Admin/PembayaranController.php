<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDiverifikasi;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    /** Daftar pembayaran; yang menunggu verifikasi ditampilkan paling atas. */
    public function index()
    {
        $pembayarans = Periksa::with('daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter')
            ->orderByRaw("CASE status_bayar WHEN 'menunggu_verifikasi' THEN 0 WHEN 'belum' THEN 1 ELSE 2 END")
            ->orderByDesc('tgl_periksa')
            ->get();

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    /** Verifikasi pembayaran -> lunas, lalu beri notifikasi ke pasien. */
    public function verifikasi($id)
    {
        $periksa = Periksa::with('daftarPoli.pasien')->findOrFail($id);

        $berhasil = DB::transaction(function () use ($periksa) {
            $terkunci = Periksa::lockForUpdate()->find($periksa->id);

            if ($terkunci->status_bayar !== Periksa::BAYAR_MENUNGGU) {
                return false;
            }

            $terkunci->update([
                'status_bayar'   => Periksa::BAYAR_LUNAS,
                'tgl_verifikasi' => now(),
            ]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('error', 'Pembayaran ini tidak sedang menunggu verifikasi (mungkin sudah diverifikasi).');
        }

        $periksa->daftarPoli->pasien->notify(new PembayaranDiverifikasi($periksa));

        return back()->with('success', 'Pembayaran berhasil diverifikasi (lunas).');
    }
}
