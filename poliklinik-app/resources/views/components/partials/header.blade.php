@php
    $authUser   = auth()->user();
    $notifs     = $authUser->notifications()->latest()->take(6)->get();
    $unread     = $authUser->unreadNotifications()->count();
    $profileUrl = \Illuminate\Support\Facades\Route::has($authUser->role . '.profile')
        ? route($authUser->role . '.profile')
        : '#';
@endphp

<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i class="fas fa-chevron-right text-xs text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- Notifikasi --}}
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-square btn-ghost relative" aria-label="Notifikasi">
            <i class="fas fa-bell w-5 h-5"></i>
            <span id="notifBadge"
                  data-count="{{ $unread }}"
                  class="{{ $unread > 0 ? '' : 'hidden' }} absolute top-1.5 right-1.5 min-w-4 h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        </div>

        <div tabindex="0"
             class="dropdown-content z-50 mt-2 w-80 rounded-2xl bg-base-100 shadow-xl border border-base-300 overflow-hidden">
            <div class="px-4 py-3 border-b border-base-300 flex items-center justify-between">
                <span class="font-bold text-slate-800 text-sm">Notifikasi</span>
                <form id="notifBacaSemua" method="POST" action="{{ route('notifikasi.baca-semua') }}"
                      class="{{ $unread > 0 ? '' : 'hidden' }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        Tandai semua dibaca
                    </button>
                </form>
            </div>

            <div id="notifList" data-baca-url="{{ url('/notifikasi') }}" class="max-h-80 overflow-y-auto">
                @forelse ($notifs as $n)
                    <form method="POST" action="{{ route('notifikasi.baca', $n->id) }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-4 py-3 border-b border-base-200 hover:bg-slate-50 transition {{ $n->read_at ? '' : 'bg-indigo-50/60' }}">
                            <div class="flex items-start gap-2">
                                @if (! $n->read_at)
                                    <span class="mt-1.5 w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-800">{{ $n->data['judul'] ?? 'Notifikasi' }}</div>
                                    <div class="text-xs text-slate-500 line-clamp-2">{{ $n->data['pesan'] ?? '' }}</div>
                                    <div class="text-[11px] text-slate-400 mt-1">{{ $n->created_at->locale('id')->diffForHumans() }}</div>
                                </div>
                            </div>
                        </button>
                    </form>
                @empty
                    <div id="notifKosong" class="px-4 py-8 text-center text-sm text-slate-400">
                        <i class="fas fa-bell-slash text-2xl mb-2 block"></i>
                        Belum ada notifikasi
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- User Info + menu profil --}}
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="flex items-center gap-3 cursor-pointer">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-semibold leading-tight">
                    {{ $authUser->nama ?? 'Pengguna' }}
                </div>
                <div class="text-xs text-base-content/50 leading-tight">
                    {{ $authUser->role ?? 'Admin Sistem' }}
                </div>
            </div>

            <div class="avatar">
                <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                    <span class="text-sm font-semibold leading-none">
                        {{ strtoupper(substr($authUser->nama ?? 'U', 0, 1)) }}
                    </span>
                </div>
            </div>
        </div>

        <ul tabindex="0"
            class="dropdown-content menu z-50 mt-2 w-52 rounded-2xl bg-base-100 shadow-xl border border-base-300 p-2">
            <li>
                <a href="{{ $profileUrl }}">
                    <i class="fas fa-user-gear w-4"></i> Profil &amp; Password
                </a>
            </li>
        </ul>
    </div>

</header>