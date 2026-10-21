<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Support\Facades\Auth;

class RiwayatPendaftaranController extends Controller
{
    /** Semua pendaftaran poli milik pasien yang login, terbaru di atas. */
    public function index()
    {
        $riwayat = DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksa'])
            ->where('id_pasien', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id') // pembeda jika dua pendaftaran dibuat di detik yang sama
            ->get();

        return view('admin.pasien.histori.index', compact('riwayat'));
    }

    /** Detail satu pendaftaran + hasil pemeriksaan (404 jika bukan milik pasien ini). */
    public function show($id)
    {
        $daftar = DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksa.detailPeriksas.obat'])
            ->where('id_pasien', Auth::id())
            ->findOrFail($id);

        if (! $daftar->periksa) {
            return redirect()
                ->route('pasien.histori.index')
                ->with('error', 'Pendaftaran ini belum diperiksa, detail belum tersedia.');
        }

        return view('admin.pasien.histori.show', compact('daftar'));
    }
}