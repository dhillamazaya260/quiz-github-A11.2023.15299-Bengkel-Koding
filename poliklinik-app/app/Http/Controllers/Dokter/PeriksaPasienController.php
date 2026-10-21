<?php

namespace App\Http\Controllers\Dokter;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Notifications\GiliranAntrianTiba;
use App\Support\Notifier;
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

        // Beri tahu pasien berikutnya bahwa giliran mereka sudah tiba
        $this->beritahuGiliranBerikutnya($daftarPoli->id_jadwal);

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    /**
     * Kirim nomor yang sedang dilayani pada jadwal ini (nomor terbesar yang sudah diperiksa).
     * Kegagalan WebSocket (mis. Reverb belum jalan) tidak boleh membatalkan hasil periksa.
     */
    private function siarkanAntrian(int $idJadwal, string $hari): void
    {
        try {
            $nomorDilayani = (int) DaftarPoli::where('id_jadwal', $idJadwal)
                ->whereHas('periksa')
                ->max('no_antrian');

            $hariIni = $hari === now()->locale('id')->isoFormat('dddd');

            AntrianDiperbarui::dispatch($idJadwal, $nomorDilayani, $hariIni);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Pasien berikutnya = nomor antrian terkecil di jadwal ini yang belum diperiksa.
     * Dikirim sekali saja per pendaftaran (aman jika dokter memeriksa tidak berurutan).
     */
    private function beritahuGiliranBerikutnya(int $idJadwal): void
    {
        $berikutnya = DaftarPoli::with(['pasien', 'jadwalPeriksa.dokter.poli'])
            ->where('id_jadwal', $idJadwal)
            ->whereDoesntHave('periksa')
            ->orderBy('no_antrian')
            ->first();

        if (! $berikutnya || ! $berikutnya->pasien) {
            return;
        }

        $sudahDiberitahu = $berikutnya->pasien->notifications()
            ->where('type', GiliranAntrianTiba::class)
            ->get()
            ->contains(fn ($n) => (int) ($n->data['daftar_poli_id'] ?? 0) === (int) $berikutnya->id);

        if (! $sudahDiberitahu) {
            Notifier::kirim($berikutnya->pasien, new GiliranAntrianTiba($berikutnya));
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