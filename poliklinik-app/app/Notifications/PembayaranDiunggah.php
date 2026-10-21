<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke DOKTER ketika pasien yang ia periksa mengunggah bukti bayar.
 * Tidak di-queue (sinkron) supaya tidak butuh queue worker.
 */
class PembayaranDiunggah extends Notification
{
    public function __construct(public Periksa $periksa)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $periksa = $this->periksa->loadMissing('daftarPoli.pasien');
        $pasien  = $periksa->daftarPoli->pasien->nama;
        $biaya   = number_format($periksa->biaya_periksa, 0, ',', '.');
        $tanggal = $periksa->tgl_periksa->format('d/m/Y');

        return [
            'jenis'      => 'pembayaran_diunggah',
            'judul'      => 'Bukti pembayaran diunggah',
            'pesan'      => "Pasien {$pasien} mengunggah bukti pembayaran sebesar Rp {$biaya} untuk pemeriksaan tanggal {$tanggal}.",
            'periksa_id' => $periksa->id,
            'url'        => route('riwayat-pasien.show', $periksa->id, false),
        ];
    }
}