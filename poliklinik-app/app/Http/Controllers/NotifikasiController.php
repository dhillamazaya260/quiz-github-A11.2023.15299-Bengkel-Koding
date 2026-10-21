<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Tandai satu notifikasi sebagai sudah dibaca, lalu buka halaman tujuannya.
     * Query dibatasi ke notifikasi milik user yang login, sehingga notifikasi
     * milik user lain menghasilkan 404 (tidak bisa dilihat/ditandai).
     */
    public function baca(Request $request, string $id): RedirectResponse
    {
        $notifikasi = $request->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();

        $url = $notifikasi->data['url'] ?? null;

        // Hanya path internal (diawali "/" tapi bukan "//") agar tidak jadi open redirect
        if (is_string($url) && str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return redirect($url);
        }

        return back();
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}