<?php

namespace App\Notifications;

use App\Models\DaftarPoli;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke DOKTER ketika ada pasien baru mendaftar ke jadwalnya.
 * Tidak di-queue (sinkron) supaya tidak butuh queue worker.
 */
class AntrianBaru extends Notification
{
    public function __construct(public DaftarPoli $daftarPoli)
    {
    }

    public function via(object $notifiable): array
    {
        // database dulu (agar tersimpan), baru broadcast realtime lewat Reverb
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $daftar = $this->daftarPoli->loadMissing('pasien', 'jadwalPeriksa');
        $nomor  = str_pad((string) $daftar->no_antrian, 3, '0', STR_PAD_LEFT);

        return [
            'jenis'          => 'antrian_baru',
            'judul'          => 'Antrian baru masuk',
            'pesan'          => "Pasien {$daftar->pasien->nama} mendaftar dengan nomor antrian {$nomor} "
                              . "untuk jadwal {$daftar->jadwalPeriksa->hari}. Keluhan: {$daftar->keluhan}",
            'daftar_poli_id' => $daftar->id,
            'url'            => route('periksa-pasien.index', [], false),
        ];
    }
}
