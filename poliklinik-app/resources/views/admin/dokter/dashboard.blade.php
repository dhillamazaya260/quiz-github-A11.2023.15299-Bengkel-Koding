<x-layouts.app title="Dashboard Admin">

    @php
        $rp = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

        $kartu = [
            ['label' => 'Dokter', 'nilai' => $stat['dokter'], 'ikon' => 'fa-user-doctor', 'warna' => 'bg-indigo-100 text-indigo-600',   'url' => route('dokter.index')],
            ['label' => 'Pasien', 'nilai' => $stat['pasien'], 'ikon' => 'fa-users',       'warna' => 'bg-sky-100 text-sky-600',         'url' => route('pasien.index')],
            ['label' => 'Poli',   'nilai' => $stat['poli'],   'ikon' => 'fa-hospital',    'warna' => 'bg-emerald-100 text-emerald-600', 'url' => route('polis.index')],
            ['label' => 'Obat',   'nilai' => $stat['obat'],   'ikon' => 'fa-pills',       'warna' => 'bg-amber-100 text-amber-600',     'url' => route('obat.index')],
        ];

        $maksPoli  = max(1, (int) $perPoli->max('total'));
        $maksBulan = max(1, (int) $pendapatanBulanan->max('total'));
    @endphp

    {{-- ================= HERO ================= --}}
    <div class="relative overflow-hidden rounded-2xl mb-6"
         style="background: linear-gradient(135deg, #1e2d6b 0%, #2d4499 55%, #3b5fd4 100%);">
        <div class="absolute -top-12 -right-12 w-56 h-56 rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);"></div>
        <div class="relative px-7 py-6">
            <p class="text-indigo-200 text-sm font-medium mb-1">
                <i class="fas fa-calendar-day mr-1.5"></i>
                {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            </p>
            <h1 class="text-2xl font-bold text-white leading-tight">Selamat datang, {{ auth()->user()->nama }}!</h1>
            <p class="text-indigo-300 text-sm mt-1">Ringkasan data dan aktivitas poliklinik hari ini.</p>
        </div>
    </div>

    {{-- ================= PERINGATAN VERIFIKASI ================= --}}
    @if ($pembayaran['menunggu'] > 0)
        <div class="alert alert-info mb-6 rounded-xl shadow-sm">
            <i class="fas fa-bell"></i>
            <span>
                Ada <strong>{{ $pembayaran['menunggu'] }}</strong> pembayaran yang menunggu verifikasi Anda.
            </span>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm">Verifikasi sekarang</a>
        </div>
    @endif

    {{-- ================= KARTU DATA MASTER ================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ($kartu as $k)
            <a href="{{ $k['url'] }}"
               class="card bg-base-100 shadow-sm rounded-2xl hover:shadow-md transition">
                <div class="card-body p-5 flex-row items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg {{ $k['warna'] }}">
                        <i class="fas {{ $k['ikon'] }}"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $k['label'] }}</p>
                        <p class="text-3xl font-extrabold text-slate-800 leading-tight">{{ $k['nilai'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- ================= AKTIVITAS HARI INI ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fas fa-clipboard-list mr-1"></i> Pendaftaran Hari Ini
                </p>
                <p class="text-3xl font-extrabold text-[#1e2d6b]">{{ $aktivitas['daftar_hari_ini'] }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fas fa-notes-medical mr-1"></i> Pemeriksaan Hari Ini
                </p>
                <p class="text-3xl font-extrabold text-[#1e2d6b]">{{ $aktivitas['periksa_hari_ini'] }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fas fa-ticket mr-1"></i> Antrian Belum Diperiksa
                </p>
                <p class="text-3xl font-extrabold text-[#1e2d6b]">{{ $aktivitas['antrian_aktif'] }}</p>
            </div>
        </div>
    </div>

    {{-- ================= PEMBAYARAN ================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Belum Bayar</p>
                <p class="text-3xl font-extrabold text-amber-500">{{ $pembayaran['belum'] }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Menunggu Verifikasi</p>
                <p class="text-3xl font-extrabold text-sky-500">{{ $pembayaran['menunggu'] }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Lunas</p>
                <p class="text-3xl font-extrabold text-emerald-500">{{ $pembayaran['lunas'] }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Pendapatan</p>
                <p class="text-xl font-extrabold text-[#2d4499] leading-tight">{{ $rp($pembayaran['pendapatan']) }}</p>
                <p class="text-[11px] text-slate-400">dari pembayaran lunas</p>
            </div>
        </div>
    </div>

    {{-- ================= GRAFIK ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

        {{-- Pendapatan 6 bulan --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-6">
                <h2 class="font-bold text-slate-800 mb-1">Pendapatan 6 Bulan Terakhir</h2>
                <p class="text-xs text-slate-400 mb-4">Pembayaran lunas, dihitung per bulan verifikasi.</p>

                <div class="flex items-end gap-3 h-44">
                    @foreach ($pendapatanBulanan as $b)
                        @php $tinggi = $b['total'] > 0 ? max(4, round($b['total'] / $maksBulan * 100)) : 2; @endphp
                        <div class="flex-1 h-full flex flex-col justify-end items-center gap-1" title="{{ $rp($b['total']) }}">
                            <span class="text-[10px] text-slate-400">
                                {{ $b['total'] > 0 ? number_format($b['total'] / 1000, 0, ',', '.') . 'rb' : '' }}
                            </span>
                            <div class="w-full rounded-t-lg"
                                 style="height: {{ $tinggi }}%; background: linear-gradient(180deg, #3b5fd4, #1e2d6b);"></div>
                            <span class="text-[11px] text-slate-500 mt-1">{{ $b['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Pendaftaran per poli --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl">
            <div class="card-body p-6">
                <h2 class="font-bold text-slate-800 mb-1">Pendaftaran per Poli</h2>
                <p class="text-xs text-slate-400 mb-4">Total pendaftaran sejak awal.</p>

                @forelse ($perPoli as $p)
                    <div class="mb-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-semibold text-slate-700">{{ $p->nama_poli }}</span>
                            <span class="text-slate-500">{{ $p->total }}</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full"
                                 style="width: {{ round($p->total / $maksPoli * 100) }}%; background: linear-gradient(90deg, #6366f1, #3b5fd4);"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 py-6 text-center">Belum ada poli.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= MENUNGGU VERIFIKASI ================= --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl">
        <div class="card-body p-0">
            <div class="flex items-center justify-between px-6 py-4 border-b border-base-200">
                <h2 class="font-bold text-slate-800">Menunggu Verifikasi Pembayaran</h2>
                <a href="{{ route('pembayaran.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                    Lihat semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Pasien</th>
                            <th class="px-6 py-3">Dokter</th>
                            <th class="px-6 py-3">Diunggah</th>
                            <th class="px-6 py-3">Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($menungguVerifikasi as $periksa)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 font-semibold text-slate-800">
                                    {{ $periksa->daftarPoli->pasien->nama }}
                                </td>
                                <td class="px-6 py-3 text-slate-500">
                                    {{ $periksa->daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-slate-500">
                                    {{ $periksa->tgl_bayar?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-slate-500">{{ $rp($periksa->biaya_periksa) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-slate-400">
                                    <i class="fas fa-circle-check text-2xl mb-2 block text-emerald-300"></i>
                                    Tidak ada pembayaran yang menunggu verifikasi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>