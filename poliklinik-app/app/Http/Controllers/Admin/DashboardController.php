<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // ---- Ringkasan data master ----
        $stat = [
            'dokter' => User::where('role', 'dokter')->count(),
            'pasien' => User::where('role', 'pasien')->count(),
            'poli'   => Poli::count(),
            'obat'   => Obat::count(),
        ];

        // ---- Ringkasan pembayaran ----
        $pembayaran = [
            'menunggu'   => Periksa::where('status_bayar', Periksa::BAYAR_MENUNGGU)->count(),
            'belum'      => Periksa::where('status_bayar', Periksa::BAYAR_BELUM)->count(),
            'lunas'      => Periksa::where('status_bayar', Periksa::BAYAR_LUNAS)->count(),
            'pendapatan' => (int) Periksa::where('status_bayar', Periksa::BAYAR_LUNAS)->sum('biaya_periksa'),
            'nominal_menunggu' => (int) Periksa::where('status_bayar', Periksa::BAYAR_MENUNGGU)->sum('biaya_periksa'),
            'nominal_belum'    => (int) Periksa::where('status_bayar', Periksa::BAYAR_BELUM)->sum('biaya_periksa'),
        ];

        // ---- Aktivitas poliklinik ----
        $aktivitas = [
            'daftar_hari_ini'  => DaftarPoli::whereDate('created_at', today())->count(),
            'periksa_hari_ini' => Periksa::whereDate('tgl_periksa', today())->count(),
            'antrian_aktif'    => DaftarPoli::whereDoesntHave('periksa')->count(),
        ];

        // ---- Jumlah pendaftaran per poli (poli -> dokter -> jadwal -> pendaftaran) ----
        $perPoli = Poli::select('poli.id', 'poli.nama_poli')
            ->selectRaw('COUNT(daftar_poli.id) as total')
            ->leftJoin('users', 'users.id_poli', '=', 'poli.id')
            ->leftJoin('jadwal_periksa', 'jadwal_periksa.id_dokter', '=', 'users.id')
            ->leftJoin('daftar_poli', 'daftar_poli.id_jadwal', '=', 'jadwal_periksa.id')
            ->groupBy('poli.id', 'poli.nama_poli')
            ->orderByDesc('total')
            ->orderBy('poli.nama_poli')
            ->get();

        // ---- Pendapatan (pembayaran lunas) 6 bulan terakhir, berdasarkan tanggal verifikasi ----
        $awal  = now()->startOfMonth()->subMonths(5);
        $lunas = Periksa::where('status_bayar', Periksa::BAYAR_LUNAS)
            ->where('tgl_verifikasi', '>=', $awal)
            ->get(['biaya_periksa', 'tgl_verifikasi']);

        $pendapatanBulanan = collect(range(0, 5))->map(function ($i) use ($awal, $lunas) {
            $bulan = $awal->copy()->addMonths($i);

            return [
                'label' => $bulan->locale('id')->isoFormat('MMM YY'),
                'total' => (int) $lunas
                    ->filter(fn ($p) => $p->tgl_verifikasi->format('Y-m') === $bulan->format('Y-m'))
                    ->sum('biaya_periksa'),
            ];
        });

        // ---- Pembayaran yang menunggu verifikasi (5 terlama dulu) ----
        $menungguVerifikasi = Periksa::with('daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter')
            ->where('status_bayar', Periksa::BAYAR_MENUNGGU)
            ->orderBy('tgl_bayar')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stat',
            'pembayaran',
            'aktivitas',
            'perPoli',
            'pendapatanBulanan',
            'menungguVerifikasi'
        ));
    }
}