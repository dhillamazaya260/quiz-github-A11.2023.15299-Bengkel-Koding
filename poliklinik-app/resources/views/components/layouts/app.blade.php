<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="data:,">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>{{ $title ?? 'Poliklinik' }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="app-wrapper">

        {{-- SIDEBAR --}}
        <div id="appSidebar" class="sidebar-fixed">
            @include('components.partials.sidebar')
        </div>

        {{-- OVERLAY --}}
        <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

        {{-- MAIN CONTENT --}}
        <div class="main-content">

            @include('components.partials.header')

            <div class="main-scroll">

                @if (session('success', session('message')))
                    <div class="alert alert-success mb-4 rounded-xl shadow-sm">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success', session('message')) }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error mb-4 rounded-xl shadow-sm">
                        <i class="fas fa-circle-xmark"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{ $slot }}

            </div>

            @include('components.partials.footer')

        </div>

    </div>

    {{-- Toast notifikasi realtime --}}
    <div id="toastNotif" class="toast toast-top toast-end z-[100] mt-16"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        }

        function toggleFullscreen() {
            const icon = document.getElementById('fsIcon');

            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                icon.className = 'fas fa-compress';
            } else {
                document.exitFullscreen();
                icon.className = 'fas fa-expand';
            }
        }
    </script>

    @auth
    <script>
    /**
     * Notifikasi realtime (Laravel Echo + Reverb) lewat channel privat
     * "App.Models.User.{id}". Dipakai semua role: pasien (giliran tiba),
     * dokter (antrian baru), dsb. Tersimpan di DB, jadi tetap muncul di bell
     * walaupun WebSocket sedang mati.
     */
    (function () {
        'use strict';

        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (!userId || !window.Echo) return;

        const csrf   = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const badge  = document.getElementById('notifBadge');
        const list   = document.getElementById('notifList');
        const kosong = document.getElementById('notifKosong');
        const semua  = document.getElementById('notifBacaSemua');
        const toast  = document.getElementById('toastNotif');

        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
            { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
        ));

        function tambahBadge() {
            if (!badge) return;
            const n = (Number(badge.dataset.count) || 0) + 1;
            badge.dataset.count = n;
            badge.textContent = n > 9 ? '9+' : n;
            badge.classList.remove('hidden');
            if (semua) semua.classList.remove('hidden');
        }

        function tambahKeList(n) {
            if (!list) return;
            if (kosong) kosong.remove();

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = list.dataset.bacaUrl + '/' + encodeURIComponent(n.id) + '/baca';
            form.innerHTML =
                '<input type="hidden" name="_token" value="' + esc(csrf) + '">'
                + '<button type="submit" class="w-full text-left px-4 py-3 border-b border-base-200 hover:bg-slate-50 transition bg-indigo-50/60">'
                + '<div class="flex items-start gap-2">'
                + '<span class="mt-1.5 w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>'
                + '<div class="min-w-0">'
                + '<div class="text-sm font-semibold text-slate-800">' + esc(n.judul || 'Notifikasi') + '</div>'
                + '<div class="text-xs text-slate-500 line-clamp-2">' + esc(n.pesan) + '</div>'
                + '<div class="text-[11px] text-slate-400 mt-1">baru saja</div>'
                + '</div></div></button>';
            list.prepend(form);
        }

        function tampilToast(n) {
            if (!toast) return;
            const giliran = n.jenis === 'giliran_tiba';
            const el = document.createElement('a');
            el.href = n.url || '#';
            el.className = 'alert shadow-lg rounded-xl max-w-sm items-start cursor-pointer '
                + (giliran ? 'alert-success' : 'alert-info');
            el.innerHTML =
                '<i class="fas ' + (giliran ? 'fa-bell animate-bounce' : 'fa-user-plus') + ' mt-1"></i>'
                + '<div class="text-left">'
                + '<div class="font-bold text-sm">' + esc(n.judul) + '</div>'
                + '<div class="text-xs">' + esc(n.pesan) + '</div></div>';
            toast.appendChild(el);
            setTimeout(() => el.remove(), 8000);
        }

        window.Echo.private('App.Models.User.' + userId).notification(function (n) {
            tambahBadge();
            tambahKeList(n);
            tampilToast(n);
            // Halaman tertentu (mis. daftar periksa dokter) bisa ikut menyegarkan diri
            document.dispatchEvent(new CustomEvent('notifikasi:masuk', { detail: n }));
        });
    })();
    </script>
    @endauth

    @stack('scripts')

</body>

</html>