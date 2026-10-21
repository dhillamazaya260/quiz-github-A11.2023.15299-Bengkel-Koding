<?php

namespace App\Notifications;

use App\Models\DaftarPoli;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke PASIEN ketika pasien di depannya selesai diperiksa,
 * artinya giliran pasien ini sudah tiba.
 */
class GiliranAntrianTiba extends Notification
{
    public function __construct(public DaftarPoli $daftarPoli)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $daftar = $this->daftarPoli->loadMissing('jadwalPeriksa.dokter.poli');
        $nomor  = str_pad((string) $daftar->no_antrian, 3, '0', STR_PAD_LEFT);
        $dokter = $daftar->jadwalPeriksa->dokter->nama ?? '-';
        $poli   = $daftar->jadwalPeriksa->dokter->poli->nama_poli ?? '-';

        return [
            'jenis'          => 'giliran_tiba',
            'judul'          => 'Giliran Anda sudah tiba!',
            'pesan'          => "Antrian nomor {$nomor} ({$poli}, {$dokter}) sekarang giliran Anda. "
                              . 'Silakan menuju ruang periksa.',
            'daftar_poli_id' => $daftar->id,
            'url'            => route('pasien.dashboard', [], false),
        ];
    }
}
