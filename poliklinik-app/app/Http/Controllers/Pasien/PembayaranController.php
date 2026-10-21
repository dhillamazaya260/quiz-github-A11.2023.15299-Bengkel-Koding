<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDiunggah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /** Riwayat pemeriksaan milik pasien yang login + status pembayarannya. */
    public function index()
    {
        $riwayat = $this->periksaMilikPasien()
            ->with('daftarPoli.jadwalPeriksa.dokter.poli')
            ->orderByDesc('tgl_periksa')
            ->get();

        return view('admin.pasien.riwayat', compact('riwayat'));
    }

    /** Upload bukti bayar. Hanya boleh sekali (status harus 'belum'). */
    public function bayar(Request $request, $id)
    {
        // 404 jika pemeriksaan bukan milik pasien ini
        $periksa = $this->periksaMilikPasien()
            ->with('daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter')
            ->findOrFail($id);

        $request->validate([
            'bukti_bayar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'bukti_bayar.required' => 'Pilih file bukti pembayaran terlebih dahulu.',
            'bukti_bayar.image'    => 'Bukti pembayaran harus berupa gambar.',
            'bukti_bayar.mimes'    => 'Format bukti pembayaran harus jpg, jpeg, atau png.',
            'bukti_bayar.max'      => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ]);

        $path = $request->file('bukti_bayar')->store('bukti-bayar', 'local');

        // Baris dikunci: hanya SATU request yang bisa mengubah status belum -> menunggu,
        // sehingga klik ganda tidak menghasilkan notifikasi ganda.
        $berhasil = DB::transaction(function () use ($periksa, $path) {
            $terkunci = Periksa::lockForUpdate()->find($periksa->id);

            if ($terkunci->status_bayar !== Periksa::BAYAR_BELUM) {
                return false;
            }

            $terkunci->update([
                'status_bayar' => Periksa::BAYAR_MENUNGGU,
                'bukti_bayar'  => $path,
                'tgl_bayar'    => now(),
                'catatan_penolakan' => null,
            ]);

            return true;
        });

        if (! $berhasil) {
            Storage::disk('local')->delete($path);

            return back()->with('error', 'Bukti pembayaran untuk pemeriksaan ini sudah pernah diunggah.');
        }

        // Dikirim sekali, hanya oleh request yang berhasil mengubah status.
        $periksa->daftarPoli->jadwalPeriksa->dokter->notify(new PembayaranDiunggah($periksa));

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    private function periksaMilikPasien()
    {
        return Periksa::whereHas('daftarPoli', fn ($q) => $q->where('id_pasien', Auth::id()));
    }
}
