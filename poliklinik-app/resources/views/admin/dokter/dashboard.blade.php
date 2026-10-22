<x-layouts.app title="Dashboard Dokter">

    {{-- ================= SAMBUTAN ================= --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#1e2d6b]">Selamat datang, {{ $dokter->nama }}!</h1>
        <p class="text-slate-500 text-sm">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>

    {{-- ================= PASIEN BERIKUTNYA ================= --}}
    @if ($berikutnya)
        <div class="alert bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-xl mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-user-clock text-indigo-500"></i>
                <span>
                    Pasien berikutnya: <strong>{{ $berikutnya->pasien->nama ?? '-' }}</strong>
                    (antrian #{{ $berikutnya->no_antrian }})
                </span>
            </div>
            @if ($berikutnya->dilayani_pada)
                <a href="{{ route('periksa-pasien.create', $berikutnya->id) }}"
                   class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-lg shrink-0">
                    Resepkan obat
                </a>
            @else
                <form action="{{ route('periksa-pasien.layani', $berikutnya->id) }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit"
                            class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white border-none rounded-lg">
                        Layani sekarang
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- ================= KARTU STATISTIK ================= --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        @php
            $kartu = [
                ['Menunggu Hari Ini', $stat['menunggu_hari_ini'], 'fa-hourglass-half', 'bg-amber-100 text-amber-600',  'Pasien belum diperiksa (' . $hariIni . ')'],
                ['Total Menunggu',    $stat['menunggu_total'],    'fa-users',          'bg-blue-100 text-blue-600',    'Semua jadwal Anda'],
                ['Diperiksa Hari Ini', $stat['periksa_hari_ini'], 'fa-stethoscope',    'bg-green-100 text-green-600',  'Pemeriksaan selesai hari ini'],
                ['Total Diperiksa',   $stat['periksa_total'],     'fa-clipboard-check', 'bg-indigo-100 text-indigo-600', 'Sejak awal'],
            ];
        @endphp
        @foreach ($kartu as [$label, $nilai, $ikon, $warna, $ket])
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</span>
                        <span class="w-9 h-9 rounded-xl {{ $warna }} flex items-center justify-center"><i class="fas {{ $ikon }}"></i></span>
                    </div>
                    <div class="text-2xl font-bold text-slate-800">{{ $nilai }}</div>
                    <div class="text-xs text-slate-400">{{ $ket }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-3 mb-6">

        {{-- ================= ANTRIAN HARI INI ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 lg:col-span-2">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Antrian Hari Ini ({{ $hariIni }})</h3>
                    <a href="{{ route('periksa-pasien.index') }}" class="text-sm text-indigo-600 hover:underline">Lihat semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">No</th>
                                <th class="px-6 py-3">Pasien</th>
                                <th class="px-6 py-3">Keluhan</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($antrianHariIni as $antri)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-semibold">{{ $antri->no_antrian }}</td>
                                    <td class="px-6 py-3">{{ $antri->pasien->nama ?? '-' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $antri->keluhan }}</td>
                                    <td class="px-6 py-3 text-right">
                                        @if ($antri->dilayani_pada)
                                            <a href="{{ route('periksa-pasien.create', $antri->id) }}"
                                               class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-lg">
                                                Resepkan Obat
                                            </a>
                                        @else
                                            <form action="{{ route('periksa-pasien.layani', $antri->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white border-none rounded-lg">
                                                    Layani
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-slate-400">Tidak ada antrian hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('components.partials.paginasi', ['paginator' => $antrianHariIni])
            </div>
        </div>

        {{-- ================= STATUS PEMBAYARAN ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-6">
                <h3 class="font-bold text-slate-800 mb-3">Status Pembayaran Pasien</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center justify-between">
                        <span class="badge badge-warning">Belum Bayar</span>
                        <span class="font-bold">{{ $pembayaran['belum'] }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="badge badge-info">Menunggu Verifikasi</span>
                        <span class="font-bold">{{ $pembayaran['menunggu'] }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="badge badge-success">Lunas</span>
                        <span class="font-bold">{{ $pembayaran['lunas'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">

        {{-- ================= JADWAL PRAKTIK ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Jadwal Praktik</h3>
                    <a href="{{ route('jadwal-periksa.index') }}" class="text-sm text-indigo-600 hover:underline">Kelola</a>
                </div>
                <ul class="divide-y divide-slate-100 text-sm">
                    @forelse ($jadwal as $j)
                        <li class="px-6 py-3 flex items-center justify-between {{ $j->hari === $hariIni ? 'bg-indigo-50' : '' }}">
                            <span>
                                <span class="font-semibold">{{ $j->hari }}</span>
                                <span class="text-slate-500">{{ substr($j->jam_mulai, 0, 5) }} – {{ substr($j->jam_selesai, 0, 5) }}</span>
                            </span>
                            <span class="text-xs text-slate-500">{{ $j->menunggu_count }} menunggu</span>
                        </li>
                    @empty
                        <li class="px-6 py-10 text-center text-slate-400">Belum ada jadwal periksa.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- ================= PASIEN TERAKHIR DIPERIKSA ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Terakhir Diperiksa</h3>
                    <a href="{{ route('riwayat-pasien.index') }}" class="text-sm text-indigo-600 hover:underline">Riwayat</a>
                </div>
                <ul class="divide-y divide-slate-100 text-sm">
                    @forelse ($terakhirDiperiksa as $p)
                        <li class="px-6 py-3 flex items-center justify-between">
                            <span class="font-semibold">{{ $p->daftarPoli->pasien->nama ?? '-' }}</span>
                            <span class="text-xs text-slate-500">{{ $p->tgl_periksa?->format('d/m/Y') }}</span>
                        </li>
                    @empty
                        <li class="px-6 py-10 text-center text-slate-400">Belum ada pemeriksaan.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</x-layouts.app>