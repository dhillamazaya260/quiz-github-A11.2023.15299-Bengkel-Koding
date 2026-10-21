<?php

namespace App\Support;

use Illuminate\Notifications\Notification;
use Throwable;

class Notifier
{
    /**
     * Kirim notifikasi (database + broadcast realtime) tanpa menggagalkan request.
     *
     * Channel "database" dijalankan lebih dulu, jadi notifikasi tetap tersimpan
     * di bell walaupun WebSocket (Reverb) sedang mati dan broadcast melempar error.
     */
    public static function kirim(?object $penerima, Notification $notifikasi): void
    {
        if (! $penerima) {
            return;
        }

        try {
            $penerima->notify($notifikasi);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
