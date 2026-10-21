<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Notifications\AntrianBaru;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoliController extends Controller
{
    /**
     * Dashboard pasien — banner antrian aktif + tabel semua jadwal poliklinik.
     */
    public function dashboard()
    {
        $user     = Auth::user();
        $idPasien = $user->id;
        $hariIni  = now()->locale('id')->isoFormat('dddd'); // e.g. "Senin"

        // ------------------------------------------------------------------
        // 1. Antrian aktif pasien: DaftarPoli yang belum ada record Periksa-nya
        //    (bisa dari jadwal hari apapun, bukan hanya hari ini)
        // ------------------------------------------------------------------
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $idPasien)
            ->whereDoesntHave('periksa')
            ->first(); // Pasien hanya boleh punya 1 antrian aktif

        $bannerData = null;
        if ($antrianAktif) {
            $jadwal = $antrianAktif->jadwalPeriksa;

            // Nomor sedang dilayani = max no_antrian di jadwal ini yang sudah periksa
            $nomorDilayani = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->whereHas('periksa')
                ->max('no_antrian') ?? 0;

            $bannerData = [
                'id_daftar'      => $antrianAktif->id,
                'id_jadwal'      => $jadwal->id,
                'poli'           => $jadwal->dokter?->poli?->nama_poli ?? '-',
                'dokter'         => $jadwal->dokter?->nama ?? '-',
                'hari'           => $jadwal->hari,
                'jam_mulai'      => substr($jadwal->jam_mulai, 0, 5),
                'jam_selesai'    => substr($jadwal->jam_selesai, 0, 5),
                'antrian_saya'   => $antrianAktif->no_antrian,
                'nomor_dilayani' => (int) $nomorDilayani,
            ];
        }

        // ------------------------------------------------------------------
        // 2. Semua jadwal poliklinik (semua hari) untuk tabel referensi
        //    Nomor dilayani hanya relevan untuk jadwal hari ini
        // ------------------------------------------------------------------
        $semuaJadwal = JadwalPeriksa::with(['dokter.poli', 'daftarPolis.periksa'])
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->get()
            ->map(function ($jadwal) use ($hariIni) {
                // Hanya hitung nomor_dilayani untuk jadwal hari ini
                $nomorDilayani = 0;
                if ($jadwal->hari === $hariIni) {
                    $nomorDilayani = $jadwal->daftarPolis
                        ->filter(fn($d) => $d->periksa !== null)
                        ->max('no_antrian') ?? 0;
                }

                return [
                    'id'             => $jadwal->id,
                    'poli'           => $jadwal->dokter?->poli?->nama_poli ?? '-',
                    'dokter'         => $jadwal->dokter?->nama ?? '-',
                    'hari'           => $jadwal->hari,
                    'jam_mulai'      => substr($jadwal->jam_mulai, 0, 5),
                    'jam_selesai'    => substr($jadwal->jam_selesai, 0, 5),
                    'nomor_dilayani' => (int) $nomorDilayani,
                    'is_hari_ini'    => $jadwal->hari === $hariIni,
                ];
            });

        return view('admin.pasien.dashboard', compact('user', 'bannerData', 'semuaJadwal'));
    }

    /**
     * JSON endpoint untuk live update antrian (polling AJAX setiap 5 detik).
     * Mengembalikan nomor_dilayani untuk semua jadwal hari ini.
     */
    public function antrianStatus()
    {
        $hariIni  = now()->locale('id')->isoFormat('dddd');
        $idPasien = Auth::id();

        // Nomor dilayani per jadwal hari ini
        $jadwals = JadwalPeriksa::with(['daftarPolis.periksa'])
            ->where('hari', $hariIni)
            ->get()
            ->map(function ($jadwal) use ($idPasien) {
                $nomorDilayani = $jadwal->daftarPolis
                    ->filter(fn($d) => $d->periksa !== null)
                    ->max('no_antrian') ?? 0;

                return [
                    'id_jadwal'      => $jadwal->id,
                    'nomor_dilayani' => (int) $nomorDilayani,
                ];
            });

        // Antrian aktif pasien (untuk banner)
        $antrianAktif = DaftarPoli::where('id_pasien', $idPasien)
            ->whereDoesntHave('periksa')
            ->with('jadwalPeriksa')
            ->first();

        $bannerUpdate = null;
        if ($antrianAktif) {
            $jadwalId      = $antrianAktif->jadwalPeriksa->id;
            $nomorDilayani = DaftarPoli::where('id_jadwal', $jadwalId)
                ->whereHas('periksa')
                ->max('no_antrian') ?? 0;

            $bannerUpdate = [
                'id_jadwal'      => $jadwalId,
                'antrian_saya'   => $antrianAktif->no_antrian,
                'nomor_dilayani' => (int) $nomorDilayani,
            ];
        }

        return response()->json([
            'timestamp'    => now()->toISOString(),
            'jadwals'      => $jadwals->keyBy('id_jadwal'),
            'banner'       => $bannerUpdate,
        ]);
    }

    /**
     * Tampilkan form pendaftaran poli.
     */
    public function get()
    {
        $idPasien = Auth::id();

        // Cek apakah pasien sudah punya antrian aktif (belum diperiksa di poli manapun)
        $antrianAktif = DaftarPoli::where('id_pasien', $idPasien)
            ->whereDoesntHave('periksa')
            ->with(['jadwalPeriksa.dokter.poli'])
            ->first();

        return view('admin.pasien.daftar', [
            'user'         => Auth::user(),
            'polis'        => Poli::all(),
            'jadwals'      => JadwalPeriksa::with('dokter', 'dokter.poli')->get(),
            'antrianAktif' => $antrianAktif,
        ]);
    }

    /**
     * Proses pendaftaran poli.
     *
     * Aturan:
     *  - Pasien tidak dapat mendaftar ke dua poli sekaligus.
     *  - Pasien hanya dapat mendaftar kembali setelah proses pemeriksaan selesai.
     */
    public function submit(Request $request)
    {
        // id_pasien sengaja TIDAK diambil dari input: selalu pasien yang sedang login
        $data = $request->validate([
            'id_poli'   => ['required', 'exists:poli,id'],
            'id_jadwal' => ['required', 'exists:jadwal_periksa,id'],
            'keluhan'   => ['required', 'string'],
        ]);

        $idPasien = Auth::id();
        $jadwal   = JadwalPeriksa::with('dokter.poli')->findOrFail($data['id_jadwal']);

        // Jadwal yang dipilih harus milik dokter di poli yang dipilih
        if ((int) $jadwal->dokter?->id_poli !== (int) $data['id_poli']) {
            return back()
                ->withErrors(['id_jadwal' => 'Jadwal yang dipilih bukan milik poli tersebut.'])
                ->withInput();
        }

        // ---------------------------------------------------------------
        // CONSTRAINT UTAMA: Pasien tidak boleh punya antrian aktif di POLI
        // MANAPUN. Hanya boleh daftar ulang setelah pemeriksaan selesai.
        // ---------------------------------------------------------------
        $masihAktif = DaftarPoli::where('id_pasien', $idPasien)
            ->whereDoesntHave('periksa')
            ->exists();

        if ($masihAktif) {
            return back()
                ->withErrors([
                    'id_jadwal' =>
                        'Anda masih memiliki antrian yang belum selesai diperiksa. ' .
                        'Silakan selesaikan pemeriksaan terlebih dahulu sebelum mendaftar kembali.',
                ])
                ->withInput();
        }

        // Kunci baris jadwal agar dua pasien yang daftar bersamaan tidak dapat nomor antrian sama
        $daftar = DB::transaction(function () use ($data, $idPasien, $jadwal) {
            JadwalPeriksa::whereKey($jadwal->id)->lockForUpdate()->first();

            $noAntrian = ((int) DaftarPoli::where('id_jadwal', $jadwal->id)->max('no_antrian')) + 1;

            return DaftarPoli::create([
                'id_pasien'  => $idPasien,
                'id_jadwal'  => $jadwal->id,
                'keluhan'    => $data['keluhan'],
                'no_antrian' => $noAntrian,
            ]);
        });

        // Beri tahu dokter (bell + toast realtime) bahwa ada antrian baru
        Notifier::kirim($jadwal->dokter, new AntrianBaru($daftar));

        // Data untuk modal alert di dashboard pasien
        return redirect()
            ->route('pasien.dashboard')
            ->with('antrian_baru', [
                'nomor'  => $daftar->no_antrian,
                'poli'   => $jadwal->dokter?->poli?->nama_poli ?? '-',
                'dokter' => $jadwal->dokter?->nama ?? '-',
                'hari'   => $jadwal->hari,
                'jam'    => substr($jadwal->jam_mulai, 0, 5) . '–' . substr($jadwal->jam_selesai, 0, 5),
            ]);
    }
}
