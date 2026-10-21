<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dikirim lewat Laravel Reverb setiap kali dokter selesai memeriksa pasien,
 * sehingga nomor "Sedang Dilayani" di dashboard pasien berubah tanpa reload.
 *
 * ShouldBroadcastNow: dikirim langsung (tidak perlu queue worker).
 */
class AntrianDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $idJadwal,
        public int $nomorDilayani,
        public bool $hariIni,
    ) {
    }

    /** Channel publik: nomor antrian bukan data sensitif. */
    public function broadcastOn(): array
    {
        return [new Channel('antrian')];
    }

    /** Di Echo didengarkan sebagai ".antrian.diperbarui" (pakai titik di depan). */
    public function broadcastAs(): string
    {
        return 'antrian.diperbarui';
    }

    public function broadcastWith(): array
    {
        return [
            'id_jadwal'      => $this->idJadwal,
            'nomor_dilayani' => $this->nomorDilayani,
            'hari_ini'       => $this->hariIni,
        ];
    }
}