<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriksaPasienController extends Controller
{
    /** Jasa dokter, ditambahkan ke total harga obat. */
    private const BIAYA_JASA_DOKTER = 150000;

    public function index()
    {
        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksa'])
            ->whereHas('jadwalPeriksa', fn ($q) => $q->where('id_dokter', Auth::id()))
            ->orderBy('id_jadwal')
            ->orderBy('no_antrian')
            ->get();

        return view('admin.dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $daftarPoli = $this->daftarMilikDokter($id);

        if ($daftarPoli->periksa) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah diperiksa.');
        }

        return view('admin.dokter.periksa-pasien.create', [
            'obats'      => Obat::orderBy('nama_obat')->get(),
            'daftarPoli' => $daftarPoli,
            'id'         => $daftarPoli->id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_daftar_poli' => ['required', 'integer'],
            'obat'           => ['nullable', 'array'],
            'obat.*'         => ['integer', 'distinct', 'exists:obat,id'],
            'catatan'        => ['nullable', 'string'],
        ]);

        // Pastikan pendaftaran ini memang milik pasien dokter yang sedang login
        $daftarPoli = $this->daftarMilikDokter($data['id_daftar_poli']);

        if ($daftarPoli->periksa) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah diperiksa.');
        }

        $obatIds = $data['obat'] ?? [];

        // Harga dihitung di server dari data obat, bukan dipercaya dari input form
        $totalObat = (int) Obat::whereIn('id', $obatIds)->sum('harga');

        DB::transaction(function () use ($daftarPoli, $data, $obatIds, $totalObat) {
            $periksa = Periksa::create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa'    => now(),
                'catatan'        => $data['catatan'] ?? null,
                'biaya_periksa'  => self::BIAYA_JASA_DOKTER + $totalObat,
            ]);

            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat'    => $idObat,
                ]);
            }
        });

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    /** Ambil pendaftaran poli, 404 jika bukan milik jadwal dokter yang login. */
    private function daftarMilikDokter($id): DaftarPoli
    {
        return DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksa'])
            ->whereHas('jadwalPeriksa', fn ($q) => $q->where('id_dokter', Auth::id()))
            ->findOrFail($id);
    }
}