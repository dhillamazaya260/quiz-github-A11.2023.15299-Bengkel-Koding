<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $dokter  = Auth::user();
        $hariIni = now()->locale('id')->isoFormat('dddd'); // mis. "Senin"

        // Semua pemeriksaan milik pasien dokter yang login
        $periksaSaya = fn () => Periksa::whereHas(
            'daftarPoli.jadwalPeriksa',
            fn ($q) => $q->where('id_dokter', $dokter->id)
        );

        // ---- Jadwal praktik + jumlah pasien yang masih menunggu per jadwal ----
        $jadwal = JadwalPeriksa::where('id_dokter', $dokter->id)
            ->withCount(['daftarPolis as menunggu_count' => fn ($q) => $q->whereDoesntHave('periksa')])
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get();

        // ---- Antrian hari ini: pasien jadwal hari ini yang belum diperiksa, urut nomor antrian ----
        $antrianHariIni = DaftarPoli::with(['pasien', 'jadwalPeriksa'])
            ->whereHas('jadwalPeriksa', fn ($q) => $q
                ->where('id_dokter', $dokter->id)
                ->where('hari', $hariIni))
            ->whereDoesntHave('periksa')
            ->orderBy('id_jadwal')
            ->orderBy('no_antrian')
            ->get();

        $berikutnya = $antrianHariIni->first();

        // ---- Statistik ----
        $stat = [
            'menunggu_hari_ini' => $antrianHariIni->count(),
            'menunggu_total'    => DaftarPoli::whereHas('jadwalPeriksa', fn ($q) => $q->where('id_dokter', $dokter->id))
                ->whereDoesntHave('periksa')
                ->count(),
            'periksa_hari_ini'  => $periksaSaya()->whereDate('tgl_periksa', today())->count(),
            'periksa_total'     => $periksaSaya()->count(),
        ];

        // ---- Status pembayaran pasien-pasien saya ----
        $pembayaran = [
            'belum'    => $periksaSaya()->where('status_bayar', Periksa::BAYAR_BELUM)->count(),
            'menunggu' => $periksaSaya()->where('status_bayar', Periksa::BAYAR_MENUNGGU)->count(),
            'lunas'    => $periksaSaya()->where('status_bayar', Periksa::BAYAR_LUNAS)->count(),
        ];

        // ---- 5 pasien terakhir yang diperiksa ----
        $terakhirDiperiksa = $periksaSaya()
            ->with('daftarPoli.pasien')
            ->orderByDesc('tgl_periksa')
            ->take(5)
            ->get();

        return view('admin.dokter.dashboard', compact(
            'dokter',
            'hariIni',
            'jadwal',
            'antrianHariIni',
            'berikutnya',
            'stat',
            'pembayaran',
            'terakhirDiperiksa'
        ));
    }
}