<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke PASIEN ketika admin menolak bukti pembayarannya,
 * sehingga pasien bisa mengunggah ulang bukti yang benar.
 */
class PembayaranDitolak extends Notification
{
    public function __construct(public Periksa $periksa, public string $alasan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $tanggal = $this->periksa->tgl_periksa->format('d/m/Y');

        return [
            'jenis'      => 'pembayaran_ditolak',
            'judul'      => 'Bukti pembayaran ditolak',
            'pesan'      => "Bukti pembayaran untuk pemeriksaan tanggal {$tanggal} ditolak admin. Alasan: {$this->alasan}. Silakan unggah ulang.",
            'periksa_id' => $this->periksa->id,
            'url'        => route('pasien.riwayat', [], false),
        ];
    }
}
