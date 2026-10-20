<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoliController extends Controller
{
    public function get()
    {
        return view('admin.pasien.daftar', [
            'user'    => Auth::user(),
            'polis'   => Poli::all(),
            'jadwals' => JadwalPeriksa::with('dokter', 'dokter.poli')->get(),
        ]);
    }

    public function submit(Request $request)
    {
        // id_pasien sengaja TIDAK diambil dari input: selalu pasien yang sedang login
        $data = $request->validate([
            'id_poli'   => ['required', 'exists:poli,id'],
            'id_jadwal' => ['required', 'exists:jadwal_periksa,id'],
            'keluhan'   => ['required', 'string'],
        ]);

        $idPasien = Auth::id();
        $jadwal = JadwalPeriksa::with('dokter')->findOrFail($data['id_jadwal']);

        // Jadwal yang dipilih harus milik dokter di poli yang dipilih
        if ((int) $jadwal->dokter?->id_poli !== (int) $data['id_poli']) {
            return back()
                ->withErrors(['id_jadwal' => 'Jadwal yang dipilih bukan milik poli tersebut.'])
                ->withInput();
        }

        // Cegah daftar dobel di jadwal yang sama selama belum diperiksa
        $sudahAda = DaftarPoli::where('id_pasien', $idPasien)
            ->where('id_jadwal', $jadwal->id)
            ->whereDoesntHave('periksa')
            ->exists();

        if ($sudahAda) {
            return back()
                ->withErrors(['id_jadwal' => 'Anda sudah terdaftar di jadwal ini dan belum diperiksa.'])
                ->withInput();
        }

        // Kunci baris jadwal agar dua pasien yang daftar bersamaan tidak dapat nomor antrian sama
        $noAntrian = DB::transaction(function () use ($data, $idPasien, $jadwal) {
            JadwalPeriksa::whereKey($jadwal->id)->lockForUpdate()->first();

            $noAntrian = ((int) DaftarPoli::where('id_jadwal', $jadwal->id)->max('no_antrian')) + 1;

            DaftarPoli::create([
                'id_pasien'  => $idPasien,
                'id_jadwal'  => $jadwal->id,
                'keluhan'    => $data['keluhan'],
                'no_antrian' => $noAntrian,
            ]);

            return $noAntrian;
        });

        return redirect()
            ->back()
            ->with('success', 'Berhasil mendaftar ke poli. Nomor antrian Anda: ' . $noAntrian);
    }
}