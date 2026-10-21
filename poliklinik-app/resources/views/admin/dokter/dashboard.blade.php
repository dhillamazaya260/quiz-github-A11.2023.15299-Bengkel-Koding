@php
    use App\Models\Periksa;

    $rp = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    // Format singkat untuk label grafik: 1.250.000 -> 1,3 jt
    $singkat = function ($n) {
        $n = (int) $n;
        if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',') . ' jt';
        if ($n >= 1000)    return round($n / 1000) . ' rb';
        return (string) $n;
    };

    $maksBulan = max(1, (int) $pendapatanBulanan->max('total'));
    $maksPoli  = max(1, (int) $perPoli->max('total'));
    $totalBayar = max(1, $pembayaran['lunas'] + $pembayaran['menunggu'] + $pembayaran['belum']);
@endphp

<x-layouts.app title="Dashboard Admin">

    {{-- ================= SAMBUTAN ================= --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#1e2d6b]">Selamat datang, {{ auth()->user()->nama }}!</h1>
        <p class="text-slate-500 text-sm">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>

    {{-- ================= PERINGATAN: PERLU VERIFIKASI ================= --}}
    @if ($pembayaran['menunggu'] > 0)
        <div class="alert bg-amber-50 border border-amber-200 text-amber-900 rounded-xl mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-bell text-amber-500"></i>
                <span>
                    <strong>{{ $pembayaran['menunggu'] }} pembayaran</strong>
                    ({{ $rp($pembayaran['nominal_menunggu']) }}) menunggu verifikasi Anda.
                </span>
            </div>
            <a href="{{ route('pembayaran.index', ['status' => Periksa::BAYAR_MENUNGGU]) }}"
               class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white border-none rounded-lg shrink-0">
                Verifikasi sekarang
            </a>
        </div>
    @endif

    {{-- ================= KARTU KEUANGAN ================= --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-4">
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pendapatan</span>
                    <span class="w-9 h-9 rounded-xl bg-green-100 text-green-600 flex items-center justify-center"><i class="fas fa-sack-dollar"></i></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $rp($pembayaran['pendapatan']) }}</div>
                <div class="text-xs text-slate-400">Dari {{ $pembayaran['lunas'] }} pembayaran lunas</div>
            </div>
        </div>

        <a href="{{ route('pembayaran.index', ['status' => Periksa::BAYAR_MENUNGGU]) }}"
           class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 hover:border-amber-300 transition">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Menunggu Verifikasi</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-hourglass-half"></i></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $pembayaran['menunggu'] }}</div>
                <div class="text-xs text-slate-400">{{ $rp($pembayaran['nominal_menunggu']) }}</div>
            </div>
        </a>

        <a href="{{ route('pembayaran.index', ['status' => Periksa::BAYAR_BELUM]) }}"
           class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 hover:border-amber-300 transition">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Belum Dibayar</span>
                    <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fas fa-file-invoice"></i></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $pembayaran['belum'] }}</div>
                <div class="text-xs text-slate-400">Tagihan {{ $rp($pembayaran['nominal_belum']) }}</div>
            </div>
        </a>

        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Antrian Aktif</span>
                    <span class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fas fa-people-line"></i></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $aktivitas['antrian_aktif'] }}</div>
                <div class="text-xs text-slate-400">Pasien belum diperiksa</div>
            </div>
        </div>
    </div>

    {{-- ================= KARTU DATA MASTER ================= --}}
    <div class="grid gap-4 grid-cols-2 xl:grid-cols-4 mb-6">
        @foreach ([
            ['Dokter', $stat['dokter'], 'fa-user-doctor', 'dokter.index'],
            ['Pasien', $stat['pasien'], 'fa-users', 'pasien.index'],
            ['Poli', $stat['poli'], 'fa-hospital', 'polis.index'],
            ['Obat', $stat['obat'], 'fa-pills', 'obat.index'],
        ] as [$label, $jumlah, $ikon, $rute])
            <a href="{{ route($rute) }}"
               class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 hover:border-indigo-300 transition">
                <div class="card-body p-4 flex-row items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-[#1e2d6b]/10 text-[#2d4499] flex items-center justify-center shrink-0">
                        <i class="fas {{ $ikon }}"></i>
                    </span>
                    <div>
                        <div class="text-xl font-bold text-slate-800 leading-tight">{{ $jumlah }}</div>
                        <div class="text-xs text-slate-400">{{ $label }}</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3 mb-6">

        {{-- ================= GRAFIK PENDAPATAN ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 xl:col-span-2">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-slate-800">Pendapatan 6 Bulan Terakhir</h3>
                    <span class="text-xs text-slate-400">Berdasarkan tanggal verifikasi</span>
                </div>

                <div class="flex items-end gap-3 h-52 pt-6">
                    @foreach ($pendapatanBulanan as $bulan)
                        @php $tinggi = $bulan['total'] > 0 ? max(4, round($bulan['total'] / $maksBulan * 100)) : 0; @endphp
                        <div class="flex-1 h-full flex flex-col justify-end items-center gap-1" title="{{ $rp($bulan['total']) }}">
                            <span class="text-[11px] font-semibold text-slate-500">{{ $bulan['total'] > 0 ? $singkat($bulan['total']) : '' }}</span>
                            <div class="w-full max-w-14 rounded-t-lg bg-gradient-to-t from-[#1e2d6b] to-[#4f6bd8]"
                                 style="height: {{ $tinggi }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-3 border-t border-slate-200 pt-2">
                    @foreach ($pendapatanBulanan as $bulan)
                        <div class="flex-1 text-center text-xs text-slate-400">{{ $bulan['label'] }}</div>
                    @endforeach
                </div>

                @if ($pendapatanBulanan->sum('total') === 0)
                    <p class="text-sm text-slate-400 text-center">Belum ada pembayaran lunas pada periode ini.</p>
                @endif
            </div>
        </div>

        {{-- ================= STATUS PEMBAYARAN + AKTIVITAS HARI INI ================= --}}
        <div class="space-y-6">
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body">
                    <h3 class="font-bold text-slate-800">Aktivitas Hari Ini</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <div class="text-2xl font-bold text-[#2d4499]">{{ $aktivitas['daftar_hari_ini'] }}</div>
                            <div class="text-xs text-slate-400">Pendaftaran</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <div class="text-2xl font-bold text-[#2d4499]">{{ $aktivitas['periksa_hari_ini'] }}</div>
                            <div class="text-xs text-slate-400">Pemeriksaan</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body">
                    <h3 class="font-bold text-slate-800">Komposisi Pembayaran</h3>
                    <div class="flex h-3 rounded-full overflow-hidden bg-slate-100">
                        <div class="bg-green-500" style="width: {{ $pembayaran['lunas'] / $totalBayar * 100 }}%"></div>
                        <div class="bg-blue-500" style="width: {{ $pembayaran['menunggu'] / $totalBayar * 100 }}%"></div>
                        <div class="bg-amber-400" style="width: {{ $pembayaran['belum'] / $totalBayar * 100 }}%"></div>
                    </div>
                    <ul class="text-sm space-y-1.5 mt-1">
                        <li class="flex justify-between"><span><span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></span>Lunas</span><strong>{{ $pembayaran['lunas'] }}</strong></li>
                        <li class="flex justify-between"><span><span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 mr-2"></span>Menunggu verifikasi</span><strong>{{ $pembayaran['menunggu'] }}</strong></li>
                        <li class="flex justify-between"><span><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 mr-2"></span>Belum bayar</span><strong>{{ $pembayaran['belum'] }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">

        {{-- ================= PENDAFTARAN PER POLI ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-2">Pendaftaran per Poli</h3>
                @forelse ($perPoli as $poli)
                    <div class="mb-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-700">{{ $poli->nama_poli }}</span>
                            <span class="font-semibold text-slate-800">{{ $poli->total }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-[#2d4499] to-[#6b84e8]"
                                 style="width: {{ $poli->total / $maksPoli * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada data poli.</p>
                @endforelse
            </div>
        </div>

        {{-- ================= MENUNGGU VERIFIKASI ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Menunggu Verifikasi</h3>
                    <a href="{{ route('pembayaran.index', ['status' => Periksa::BAYAR_MENUNGGU]) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lihat semua</a>
                </div>

                @forelse ($menungguVerifikasi as $periksa)
                    <div class="px-6 py-3 flex items-center justify-between gap-3 border-b border-slate-100 last:border-0">
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-800 truncate">{{ $periksa->daftarPoli->pasien->nama }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $rp($periksa->biaya_periksa) }}
                                &middot; diunggah {{ $periksa->tgl_bayar?->locale('id')->diffForHumans() ?? '-' }}
                            </div>
                        </div>
                        <a href="{{ route('pembayaran.index', ['status' => Periksa::BAYAR_MENUNGGU, 'buka' => $periksa->id]) }}"
                           class="btn btn-sm bg-green-500 hover:bg-green-600 text-white border-none rounded-lg shrink-0">
                            Tinjau
                        </a>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-slate-400 text-sm">
                        <i class="fas fa-circle-check text-3xl text-green-400 block mb-2"></i>
                        Semua pembayaran sudah diproses
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</x-layouts.app>
