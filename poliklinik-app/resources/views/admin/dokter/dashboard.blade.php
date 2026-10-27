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

    <div class="grid gap-4 lg:grid-cols-5">

        {{-- ================= JADWAL PRAKTIK ================= --}}
        @php
            $urutHari    = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $perHari     = $jadwal->groupBy('hari');
            $jamSekarang = now()->format('H:i');
            $idxHariIni  = (int) array_search($hariIni, $urutHari);

            $sesiHariIni     = $perHari->get($hariIni, collect());
            $menungguHariIni = (int) $sesiHariIni->sum('menunggu_count');

            // Sesi praktik terdekat yang belum selesai (mulai dari hari ini)
            $sesiBerikutnya = null;
            for ($i = 0; $i < 7 && ! $sesiBerikutnya; $i++) {
                $namaHari = $urutHari[($idxHariIni + $i) % 7];
                foreach ($perHari->get($namaHari, collect()) as $sesi) {
                    if ($i === 0 && substr($sesi->jam_selesai, 0, 5) <= $jamSekarang) {
                        continue; // sesi hari ini yang sudah lewat
                    }
                    $sesiBerikutnya = ['hari' => $namaHari, 'sesi' => $sesi, 'hari_ini' => $i === 0];
                    break;
                }
            }

            if (! $sesiBerikutnya) {
                $ringkasan = $jadwal->isEmpty() ? 'Belum ada jadwal praktik' : ($sesiHariIni->isNotEmpty() ? 'Praktik hari ini sudah selesai' : 'Tidak ada praktik hari ini');
                $ringkasanWarna = 'bg-slate-100 text-slate-600';
            } elseif ($sesiBerikutnya['hari_ini'] && substr($sesiBerikutnya['sesi']->jam_mulai, 0, 5) <= $jamSekarang) {
                $ringkasan = 'Sedang praktik sampai ' . substr($sesiBerikutnya['sesi']->jam_selesai, 0, 5);
                $ringkasanWarna = 'bg-emerald-100 text-emerald-700';
            } elseif ($sesiBerikutnya['hari_ini']) {
                $ringkasan = 'Praktik hari ini mulai ' . substr($sesiBerikutnya['sesi']->jam_mulai, 0, 5);
                $ringkasanWarna = 'bg-amber-100 text-amber-700';
            } else {
                $ringkasan = 'Praktik berikutnya: ' . $sesiBerikutnya['hari'] . ', ' . substr($sesiBerikutnya['sesi']->jam_mulai, 0, 5);
                $ringkasanWarna = 'bg-indigo-100 text-indigo-700';
            }
        @endphp

        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 lg:col-span-3">
            <div class="card-body p-0">

                {{-- Header: ringkasan + aksi cepat --}}
                <div class="px-6 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-800">Jadwal Praktik Mingguan</h3>
                        <span class="inline-flex items-center gap-1.5 mt-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $ringkasanWarna }}">
                            <i class="fas fa-clock text-[10px]"></i> {{ $ringkasan }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('jadwal-periksa.create') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition">
                            <i class="fas fa-plus text-[10px]"></i> Tambah
                        </a>
                        <a href="{{ route('jadwal-periksa.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                            Kelola
                        </a>
                    </div>
                </div>

                @if ($jadwal->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center">
                            <i class="fas fa-calendar-plus text-lg"></i>
                        </div>
                        <p class="font-semibold text-slate-700">Belum ada jadwal praktik</p>
                        <p class="text-sm text-slate-400 mt-1 mb-4">Tambahkan jadwal agar pasien bisa mendaftar ke poli Anda.</p>
                        <a href="{{ route('jadwal-periksa.create') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition">
                            <i class="fas fa-plus text-xs"></i> Tambah Jadwal Pertama
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($urutHari as $namaHari)
                            @php
                                $sesiHari      = $perHari->get($namaHari, collect());
                                $adalahHariIni = $namaHari === $hariIni;
                            @endphp

                            <li class="px-5 py-3 grid grid-cols-[6.5rem_1fr] gap-x-3 items-start border-l-4
                                       {{ $adalahHariIni ? 'bg-indigo-50/70 border-indigo-500' : 'border-transparent' }}">

                                {{-- Kolom hari: baris 1 = nama hari, baris 2 = badge "Hari Ini" (tinggi tetap agar sejajar) --}}
                                <div>
                                    <div class="h-5 leading-5 font-semibold {{ $sesiHari->isEmpty() ? 'text-slate-400' : 'text-slate-800' }}">{{ $namaHari }}</div>
                                    <div class="h-6 flex items-center">
                                        @if ($adalahHariIni)
                                            <span class="text-[10px] font-bold leading-none text-indigo-700 bg-indigo-100 px-2 py-1 rounded-full">Hari Ini</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Kolom sesi: setiap sesi selalu 2 baris (jam / status) --}}
                                <div class="min-w-0 divide-y divide-slate-100/80">
                                    @forelse ($sesiHari as $j)
                                        @php
                                            $mulai   = substr($j->jam_mulai, 0, 5);
                                            $selesai = substr($j->jam_selesai, 0, 5);

                                            if (! $adalahHariIni) {
                                                $status = null;
                                            } elseif ($jamSekarang >= $selesai) {
                                                $status = ['Selesai', 'bg-slate-100 text-slate-500', false];
                                            } elseif ($jamSekarang >= $mulai) {
                                                $status = ['Berlangsung', 'bg-emerald-100 text-emerald-700', true];
                                            } else {
                                                $status = ['Belum mulai', 'bg-amber-100 text-amber-700', false];
                                            }
                                        @endphp

                                        <div class="flex items-center justify-between gap-3 {{ $loop->first ? '' : 'pt-2 mt-2' }}">

                                            {{-- Kiri: jam (baris 1) + status & antrian (baris 2) --}}
                                            <div class="min-w-0">
                                                <div class="h-5 leading-5 flex items-center gap-1.5 font-medium text-slate-700 whitespace-nowrap">
                                                    <i class="fas fa-clock text-slate-400 text-xs w-3.5"></i>
                                                    {{ $mulai }} – {{ $selesai }}
                                                </div>
                                                <div class="h-6 flex items-center gap-1.5 whitespace-nowrap">
                                                    @if ($status)
                                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2 py-1 rounded-full leading-none {{ $status[1] }}">
                                                            @if ($status[2])
                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                            @endif
                                                            {{ $status[0] }}
                                                        </span>
                                                    @endif

                                                    @if ($j->menunggu_count > 0)
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-full leading-none">
                                                            <i class="fas fa-users text-[10px]"></i> {{ $j->menunggu_count }} menunggu
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-slate-400">Tidak ada antrian</span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Kanan: aksi (lebar pensil tetap supaya sejajar antar baris) --}}
                                            <div class="flex items-center gap-1 shrink-0">
                                                @if ($adalahHariIni && $j->menunggu_count > 0)
                                                    <a href="{{ route('periksa-pasien.index') }}"
                                                       class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold whitespace-nowrap transition">
                                                        <i class="fas fa-bullhorn text-[10px]"></i> Lihat antrian
                                                    </a>
                                                @endif
                                                <a href="{{ route('jadwal-periksa.edit', $j->id) }}" title="Ubah jadwal {{ $namaHari }} {{ $mulai }}"
                                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div>
                                            <div class="h-5 leading-5 text-slate-300">Libur</div>
                                            <div class="h-6"></div>
                                        </div>
                                    @endforelse
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- ================= PASIEN TERAKHIR DIPERIKSA ================= --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 lg:col-span-2 self-start">
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