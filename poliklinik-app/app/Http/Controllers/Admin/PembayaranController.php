<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDitolak;
use App\Notifications\PembayaranDiverifikasi;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /** Daftar semua pembayaran + filter status (tab), terbaru di atas. */
    public function index(Request $request)
    {
        $statusValid = [
            Periksa::BAYAR_MENUNGGU,
            Periksa::BAYAR_BELUM,
            Periksa::BAYAR_LUNAS,
        ];

        $filter = $request->query('status');
        $filter = in_array($filter, $statusValid, true) ? $filter : 'semua';

        $pembayarans = Periksa::with('daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli', 'detailPeriksas.obat')
            ->when($filter !== 'semua', fn ($q) => $q->where('status_bayar', $filter))
            ->orderByDesc('tgl_periksa')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Jumlah per status untuk badge di tab: ['belum' => 3, 'lunas' => 5, ...]
        $jumlah = Periksa::select('status_bayar', DB::raw('count(*) as total'))
            ->groupBy('status_bayar')
            ->pluck('total', 'status_bayar');

        return view('admin.pembayaran.index', compact('pembayarans', 'jumlah', 'filter'));
    }

    /** Admin mengonfirmasi bukti bayar -> status LUNAS. */
    public function verifikasi($id)
    {
        $periksa = Periksa::with('daftarPoli.pasien')->findOrFail($id);

        // Baris dikunci: hanya SATU request yang bisa mengubah menunggu -> lunas,
        // sehingga klik ganda tidak menghasilkan notifikasi ganda.
        $berhasil = DB::transaction(function () use ($periksa) {
            $terkunci = Periksa::lockForUpdate()->find($periksa->id);

            if ($terkunci->status_bayar !== Periksa::BAYAR_MENUNGGU) {
                return false;
            }

            $terkunci->update([
                'status_bayar'      => Periksa::BAYAR_LUNAS,
                'tgl_verifikasi'    => now(),
                'catatan_penolakan' => null,
            ]);

            return true;
        });

        if (! $berhasil) {
            return redirect()
                ->route('pembayaran.index')
                ->with('error', 'Pembayaran ini sudah diproses atau belum menunggu verifikasi.');
        }

        Notifier::kirim($periksa->daftarPoli->pasien, new PembayaranDiverifikasi($periksa->fresh()));

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil dikonfirmasi sebagai LUNAS.');
    }

    /** Admin menolak bukti bayar -> status kembali "belum" agar pasien unggah ulang. */
    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan' => ['required', 'string', 'max:255'],
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
            'alasan.max'      => 'Alasan penolakan maksimal 255 karakter.',
        ]);

        $periksa = Periksa::with('daftarPoli.pasien')->findOrFail($id);
        $alasan  = trim($request->input('alasan'));
        $buktiLama = null;

        $berhasil = DB::transaction(function () use ($periksa, $alasan, &$buktiLama) {
            $terkunci = Periksa::lockForUpdate()->find($periksa->id);

            if ($terkunci->status_bayar !== Periksa::BAYAR_MENUNGGU) {
                return false;
            }

            $buktiLama = $terkunci->bukti_bayar;

            $terkunci->update([
                'status_bayar'      => Periksa::BAYAR_BELUM,
                'bukti_bayar'       => null,
                'tgl_bayar'         => null,
                'tgl_verifikasi'    => null,
                'catatan_penolakan' => $alasan,
            ]);

            return true;
        });

        if (! $berhasil) {
            return redirect()
                ->route('pembayaran.index')
                ->with('error', 'Pembayaran ini sudah diproses atau belum menunggu verifikasi.');
        }

        // Hapus file bukti yang ditolak (pasien akan mengunggah yang baru)
        if ($buktiLama) {
            Storage::disk('local')->delete($buktiLama);
        }

        Notifier::kirim($periksa->daftarPoli->pasien, new PembayaranDitolak($periksa->fresh(), $alasan));

        return redirect()
            ->route('pembayaran.index')
            ->with('success', 'Bukti pembayaran ditolak. Pasien diminta mengunggah ulang.');
    }
}