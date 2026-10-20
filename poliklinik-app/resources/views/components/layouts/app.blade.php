<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

                @if (session('success'))
                    <div class="alert alert-success mb-4 rounded-xl shadow-sm">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
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

    @stack('scripts')

</body>

</html>