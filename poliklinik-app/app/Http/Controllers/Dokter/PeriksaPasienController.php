<?php

namespace App\Http\Controllers\Dokter;

use App\Events\AntrianDiperbarui;
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

        // Dokter menekan "Periksa": tandai pasien ini sedang dilayani, lalu kabari dashboard pasien
        $this->tandaiSedangDilayani($daftarPoli);

        return view('admin.dokter.periksa-pasien.create', [
            'obats'      => Obat::orderBy('nama_obat')->get(),
            'daftarPoli' => $daftarPoli,
            'id'         => $daftarPoli->id,
            'biayaJasa'  => Periksa::BIAYA_JASA_DOKTER,
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
$obats     = Obat::whereIn('id', $obatIds)->get();
$totalObat = (int) $obats->sum('harga');

DB::transaction(function () use ($daftarPoli, $data, $obats, $totalObat) {
    $periksa = Periksa::create([
        'id_daftar_poli' => $daftarPoli->id,
        'tgl_periksa'    => now(),
        'catatan'        => $data['catatan'] ?? null,
        'biaya_jasa'     => Periksa::BIAYA_JASA_DOKTER,
        'biaya_periksa'  => Periksa::BIAYA_JASA_DOKTER + $totalObat,
    ]);

    // Simpan harga obat saat ini sebagai snapshot
    foreach ($obats as $obat) {
        DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat'    => $obat->id,
            'harga'      => $obat->harga,
        ]);
    }
});

        // Setelah transaksi tersimpan, kabari semua dashboard pasien lewat Reverb
        $this->siarkanAntrian($daftarPoli->id_jadwal, $daftarPoli->jadwalPeriksa->hari);

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    /**
     * Tandai pendaftaran ini sebagai "sedang dilayani" (hanya satu per jadwal),
     * lalu siarkan ke dashboard pasien.
     */
    private function tandaiSedangDilayani(DaftarPoli $daftarPoli): void
    {
        DB::transaction(function () use ($daftarPoli) {
            // Pasien lain di jadwal yang sama yang sempat ditandai tapi belum diperiksa -> dilepas
            DaftarPoli::where('id_jadwal', $daftarPoli->id_jadwal)
                ->where('id', '!=', $daftarPoli->id)
                ->whereNotNull('dilayani_pada')
                ->whereDoesntHave('periksa')
                ->update(['dilayani_pada' => null]);

            if (! $daftarPoli->dilayani_pada) {
                $daftarPoli->forceFill(['dilayani_pada' => now()])->save();
            }
        });

        $this->siarkanAntrian($daftarPoli->id_jadwal, $daftarPoli->jadwalPeriksa->hari);
    }

    /**
     * Kirim nomor yang sedang dilayani pada jadwal ini (lihat DaftarPoli::nomorDilayani).
     * Kegagalan WebSocket (mis. Reverb belum jalan) tidak boleh membatalkan proses dokter.
     */
    private function siarkanAntrian(int $idJadwal, string $hari): void
    {
        try {
            $nomorDilayani = DaftarPoli::nomorDilayani($idJadwal);

            $hariIni = $hari === now()->locale('id')->isoFormat('dddd');

            AntrianDiperbarui::dispatch($idJadwal, $nomorDilayani, $hariIni);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Ambil pendaftaran poli, 404 jika bukan milik jadwal dokter yang login. */
    private function daftarMilikDokter($id): DaftarPoli
    {
        return DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksa'])
            ->whereHas('jadwalPeriksa', fn ($q) => $q->where('id_dokter', Auth::id()))
            ->findOrFail($id);
    }
}