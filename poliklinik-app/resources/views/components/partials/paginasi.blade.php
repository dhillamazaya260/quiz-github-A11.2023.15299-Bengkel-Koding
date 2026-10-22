{{--
    Footer paginasi (info "Menampilkan X–Y dari Z" + tombol halaman).
    Pakai di dalam card, setelah div overflow-x-auto:
    @include('components.partials.paginasi', ['paginator' => $data])
--}}
@if ($paginator->count() > 0)
    @php
        $sekarang = $paginator->currentPage();
        $terakhir = $paginator->lastPage();
        $awal     = max(1, $sekarang - 2);
        $akhir    = min($terakhir, $sekarang + 2);
        $btn      = 'join-item btn btn-sm border-slate-200 bg-white text-slate-600 hover:bg-slate-50';
    @endphp

    <div class="px-6 py-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
        <span class="text-xs text-slate-500">
            Menampilkan {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </span>

        @if ($paginator->hasPages())
            <nav class="join sm:ml-auto" aria-label="Paginasi">

                {{-- Sebelumnya --}}
                @if ($paginator->onFirstPage())
                    <button type="button" class="{{ $btn }}" disabled aria-label="Sebelumnya">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="{{ $btn }}" aria-label="Sebelumnya">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                @endif

                {{-- Halaman pertama + titik-titik --}}
                @if ($awal > 1)
                    <a href="{{ $paginator->url(1) }}" class="{{ $btn }}">1</a>
                    @if ($awal > 2)
                        <button type="button" class="{{ $btn }}" disabled>&hellip;</button>
                    @endif
                @endif

                {{-- Jendela halaman di sekitar halaman aktif --}}
                @for ($i = $awal; $i <= $akhir; $i++)
                    @if ($i === $sekarang)
                        <span class="join-item btn btn-sm border-[#2d4499] bg-[#2d4499] text-white" aria-current="page">{{ $i }}</span>
                    @else
                        <a href="{{ $paginator->url($i) }}" class="{{ $btn }}">{{ $i }}</a>
                    @endif
                @endfor

                {{-- Titik-titik + halaman terakhir --}}
                @if ($akhir < $terakhir)
                    @if ($akhir < $terakhir - 1)
                        <button type="button" class="{{ $btn }}" disabled>&hellip;</button>
                    @endif
                    <a href="{{ $paginator->url($terakhir) }}" class="{{ $btn }}">{{ $terakhir }}</a>
                @endif

                {{-- Berikutnya --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="{{ $btn }}" aria-label="Berikutnya">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                @else
                    <button type="button" class="{{ $btn }}" disabled aria-label="Berikutnya">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                @endif

            </nav>
        @endif
    </div>
@endif