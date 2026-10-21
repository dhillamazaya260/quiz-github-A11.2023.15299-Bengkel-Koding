<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDitolak;
use App\Notifications\PembayaranDiverifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /**
     * Daftar pembayaran dengan filter status (?status=menunggu_verifikasi|belum|lunas).
     * Yang menunggu verifikasi selalu ditampilkan paling atas.
     */
    public function index(Request $request)
    {
        $statusValid = [Periksa::BAYAR_MENUNGGU, Periksa::BAYAR_BELUM, Periksa::BAYAR_LUNAS];
        $filter      = $request->query('status');

        $query = Periksa::with('daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli', 'detailPeriksas.obat')
            ->orderByRaw("CASE status_bayar WHEN 'menunggu_verifikasi' THEN 0 WHEN 'belum' THEN 1 ELSE 2 END")
            ->orderByDesc('tgl_periksa');

        if (in_array($filter, $statusValid, true)) {
            $query->where('status_bayar', $filter);
        } else {
            $filter = 'semua';
        }

        $pembayarans = $query->get();

        // Jumlah per status untuk badge pada tab filter
        $jumlah = Periksa::select('status_bayar', DB::raw('COUNT(*) as total'))
            ->groupBy('status_bayar')
            ->pluck('total', 'status_bayar');

        return view('admin.pembayaran.index', compact('pembayarans', 'filter', 'jumlah'));
    }

    /** Konfirmasi pembayaran -> lunas, lalu beri notifikasi ke pasien. */
    public function verifikasi($id)
    {
        $periksa = Periksa::with('daftarPoli.pasien')->findOrFail($id);

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
            return back()->with('error', 'Pembayaran ini tidak sedang menunggu verifikasi (mungkin sudah diproses).');
        }

        $periksa->daftarPoli->pasien->notify(new PembayaranDiverifikasi($periksa));

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi (lunas).');
    }

    /**
     * Tolak bukti bayar (mis. buram / nominal tidak sesuai).
     * Status dikembalikan ke "belum" agar pasien bisa mengunggah ulang.
     */
    public function tolak(Request $request, $id)
    {
        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:255'],
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
            'alasan.max'      => 'Alasan penolakan maksimal 255 karakter.',
        ]);

        $periksa    = Periksa::with('daftarPoli.pasien')->findOrFail($id);
        $buktiLama  = null;

        $berhasil = DB::transaction(function () use ($periksa, $data, &$buktiLama) {
            $terkunci = Periksa::lockForUpdate()->find($periksa->id);

            if ($terkunci->status_bayar !== Periksa::BAYAR_MENUNGGU) {
                return false;
            }

            $buktiLama = $terkunci->bukti_bayar;

            $terkunci->update([
                'status_bayar'      => Periksa::BAYAR_BELUM,
                'bukti_bayar'       => null,
                'tgl_bayar'         => null,
                'catatan_penolakan' => $data['alasan'],
            ]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('error', 'Pembayaran ini tidak sedang menunggu verifikasi (mungkin sudah diproses).');
        }

        // File dihapus setelah transaksi sukses
        if ($buktiLama) {
            Storage::disk('local')->delete($buktiLama);
        }

        $periksa->daftarPoli->pasien->notify(new PembayaranDitolak($periksa, $data['alasan']));

        return back()->with('success', 'Bukti pembayaran ditolak. Pasien diminta mengunggah ulang.');
    }
}
