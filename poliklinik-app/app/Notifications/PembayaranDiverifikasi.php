<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke PASIEN ketika admin memverifikasi pembayarannya (lunas).
 */
class PembayaranDiverifikasi extends Notification
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
        $biaya   = number_format($this->periksa->biaya_periksa, 0, ',', '.');
        $tanggal = $this->periksa->tgl_periksa->format('d/m/Y');

        return [
            'jenis'      => 'pembayaran_diverifikasi',
            'judul'      => 'Pembayaran lunas',
            'pesan'      => "Pembayaran Anda sebesar Rp {$biaya} untuk pemeriksaan tanggal {$tanggal} telah diverifikasi admin dan dinyatakan LUNAS. Struk pembayaran sudah bisa dicetak.",
            'periksa_id' => $this->periksa->id,
            'url'        => route('pasien.struk', $this->periksa->id, false),
        ];
    }
}