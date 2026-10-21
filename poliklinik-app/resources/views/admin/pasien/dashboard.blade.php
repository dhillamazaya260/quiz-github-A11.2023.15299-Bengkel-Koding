<x-layouts.app title="Dashboard Pasien">

    {{-- ============================================================
         HERO WELCOME STRIP
    ============================================================ --}}
    <div class="relative overflow-hidden rounded-2xl mb-6"
         style="background: linear-gradient(135deg, #1e2d6b 0%, #2d4499 55%, #3b5fd4 100%);">

        {{-- Decorative blobs --}}
        <div class="absolute -top-12 -right-12 w-56 h-56 rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);"></div>
        <div class="absolute -bottom-10 -left-10 w-44 h-44 rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(165,180,252,0.15) 0%, transparent 70%);"></div>

        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-7 py-6">
            <div>
                <p class="text-indigo-200 text-sm font-medium mb-1">
                    <i class="fas fa-calendar-day mr-1.5"></i>
                    {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </p>
                <h1 class="text-2xl font-bold text-white leading-tight">
                    Selamat datang, {{ $user->nama }}!
                </h1>
                <p class="text-indigo-300 text-sm mt-1">
                    No. Rekam Medis:
                    <span class="font-semibold text-white">{{ $user->no_rm ?? '-' }}</span>
                </p>
            </div>

            @if(!$bannerData)
                {{-- Hanya tampilkan tombol Daftar Poli jika tidak ada antrian aktif --}}
                <a href="{{ route('pasien.daftar') }}"
                   class="flex-shrink-0 flex items-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm transition-all duration-200"
                   style="background: rgba(255,255,255,0.15); color:#fff; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(6px);"
                   onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                   onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <i class="fas fa-plus-circle"></i> Daftar Poli
                </a>
            @endif
        </div>
    </div>

    {{-- ============================================================
         BANNER ANTRIAN AKTIF
         Hanya tampil jika pasien memiliki antrian yang belum diperiksa
    ============================================================ --}}
    @if($bannerData)

        <div id="bannerAntrian"
             class="relative overflow-hidden rounded-2xl shadow-xl mb-6"
             data-jadwal-id="{{ $bannerData['id_jadwal'] }}"
             data-antrian-saya="{{ $bannerData['antrian_saya'] }}"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e2d6b 40%, #2d4499 80%, #3b5fd4 100%);
                    border: 1px solid rgba(99,102,241,0.45);">

            {{-- Glow decoration --}}
            <div class="absolute top-0 right-0 w-80 h-80 rounded-full pointer-events-none"
                 style="background: radial-gradient(circle at 75% 25%, rgba(99,102,241,0.22) 0%, transparent 60%);"></div>

            {{-- LIVE badge --}}
            <div class="absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                 style="background: rgba(239,68,68,0.18); border: 1px solid rgba(239,68,68,0.5); color:#fca5a5;">
                <span class="w-2 h-2 rounded-full bg-red-400 animate-ping inline-block"></span>
                LIVE
            </div>

            <div class="relative p-6 sm:p-7">

                {{-- Label --}}
                <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-4">
                    <i class="fas fa-ticket mr-1.5"></i> Antrian Anda
                </p>

                {{-- Info: Poli · Dokter · Jadwal --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(99,102,241,0.25); border:1px solid rgba(99,102,241,0.35);">
                            <i class="fas fa-hospital text-indigo-300"></i>
                        </div>
                        <div>
                            <p class="text-indigo-400 text-[11px] font-medium uppercase tracking-wide">Poli</p>
                            <p class="text-white font-semibold text-sm leading-snug">{{ $bannerData['poli'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(99,102,241,0.25); border:1px solid rgba(99,102,241,0.35);">
                            <i class="fas fa-user-doctor text-indigo-300"></i>
                        </div>
                        <div>
                            <p class="text-indigo-400 text-[11px] font-medium uppercase tracking-wide">Dokter</p>
                            <p class="text-white font-semibold text-sm leading-snug">{{ $bannerData['dokter'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(99,102,241,0.25); border:1px solid rgba(99,102,241,0.35);">
                            <i class="fas fa-clock text-indigo-300"></i>
                        </div>
                        <div>
                            <p class="text-indigo-400 text-[11px] font-medium uppercase tracking-wide">Jadwal Periksa</p>
                            <p class="text-white font-semibold text-sm leading-snug">
                                {{ $bannerData['hari'] }},
                                {{ $bannerData['jam_mulai'] }}–{{ $bannerData['jam_selesai'] }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Nomor Antrian Cards --}}
                <div class="flex flex-col sm:flex-row items-stretch gap-4">

                    {{-- Nomor Antrian Saya --}}
                    <div class="flex-1 rounded-2xl p-5 text-center"
                         style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);">
                        <p class="text-indigo-300 text-xs font-medium mb-2 uppercase tracking-wide">
                            <i class="fas fa-ticket mr-1"></i> Nomor Antrian Anda
                        </p>
                        <p class="text-white font-black"
                           style="font-size: 3.5rem; line-height: 1; letter-spacing: -2px;">
                            {{ str_pad($bannerData['antrian_saya'], 3, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>

                    {{-- Divider --}}
                    <div class="flex items-center justify-center">
                        <div class="hidden sm:flex flex-col items-center gap-1">
                            <div class="w-0.5 h-8 rounded-full" style="background: rgba(165,180,252,0.3);"></div>
                            <i class="fas fa-angles-right text-indigo-400 text-lg"></i>
                            <div class="w-0.5 h-8 rounded-full" style="background: rgba(165,180,252,0.3);"></div>
                        </div>
                    </div>

                    {{-- Sedang Dilayani --}}
                    <div class="flex-1 rounded-2xl p-5 text-center relative overflow-hidden"
                         style="background: linear-gradient(135deg, rgba(99,102,241,0.28), rgba(59,95,212,0.28));
                                border: 1px solid rgba(99,102,241,0.55);">
                        {{-- pulse glow --}}
                        <div class="absolute inset-0 rounded-2xl animate-pulse opacity-15 pointer-events-none"
                             style="background: linear-gradient(135deg, #6366f1, #3b5fd4);"></div>
                        <p class="relative text-indigo-200 text-xs font-medium mb-2 uppercase tracking-wide">
                            <i class="fas fa-stethoscope mr-1"></i> Sedang Dilayani
                        </p>
                        <p id="bannerNomorDilayani"
                           class="relative text-white font-black"
                           style="font-size: 3.5rem; line-height: 1; letter-spacing: -2px;">
                            {{ $bannerData['nomor_dilayani'] > 0
                                ? str_pad($bannerData['nomor_dilayani'], 3, '0', STR_PAD_LEFT)
                                : '—' }}
                        </p>
                    </div>
                </div>

                {{-- Status pesan --}}
                @php
                    $selisihBanner = $bannerData['antrian_saya'] - $bannerData['nomor_dilayani'];
                @endphp
                <div id="bannerStatusMsg" class="mt-4 flex items-center gap-2 text-xs">
                    @if($selisihBanner <= 0)
                        <i class="fas fa-bell text-green-400 animate-bounce"></i>
                        <span class="text-green-300"><strong>Giliran Anda sudah tiba!</strong> Anda sedang dilayani dokter.</span>
                    @elseif($bannerData['nomor_dilayani'] == 0)
                        <i class="fas fa-hourglass-start text-yellow-400"></i>
                        <span class="text-yellow-300">Antrian belum dimulai, harap bersiap di lokasi.</span>
                    @elseif($selisihBanner <= 3)
                        <i class="fas fa-bell text-amber-400 animate-bounce"></i>
                        <span class="text-amber-300">Bersiap! Giliran Anda <strong>{{ $selisihBanner }}</strong> nomor lagi.</span>
                    @else
                        <i class="fas fa-couch text-indigo-300"></i>
                        <span class="text-indigo-300">Masih <strong>{{ $selisihBanner }}</strong> nomor antrian sebelum giliran Anda.</span>
                    @endif
                </div>

            </div>
        </div>

    @else

        {{-- Tidak ada antrian aktif --}}
        <div class="mb-6 rounded-2xl flex items-center gap-4 px-6 py-5"
             style="background: linear-gradient(90deg,#f0f9ff,#e0f2fe); border: 1px solid #bae6fd;">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:#e0f2fe;">
                <i class="fas fa-circle-info text-sky-500"></i>
            </div>
            <div>
                <p class="font-semibold text-sky-700 text-sm">Anda belum memiliki antrian aktif</p>
                <p class="text-sky-500 text-xs mt-0.5">
                    Klik
                    <a href="{{ route('pasien.daftar') }}" class="underline font-semibold text-sky-600">Daftar Poli</a>
                    untuk mendaftarkan diri ke jadwal poliklinik.
                </p>
            </div>
        </div>

    @endif

        {{-- ============================================================
         TABEL JADWAL POLIKLINIK & DOKTER
         Dengan pencarian, dropdown Poli & Hari, dan paginasi
    ============================================================ --}}
    @php
        $daftarPoli = $semuaJadwal->pluck('poli')->unique()->sort()->values();
        $urutanHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $daftarHari = collect($urutanHari)->filter(fn ($h) => $semuaJadwal->contains('hari', $h))->values();
        $hariIniNama = now()->locale('id')->isoFormat('dddd');
        $hariColors = [
            'Senin'  => ['#dbeafe', '#1d4ed8'],
            'Selasa' => ['#f0fdf4', '#15803d'],
            'Rabu'   => ['#fef9c3', '#a16207'],
            'Kamis'  => ['#fdf4ff', '#9333ea'],
            'Jumat'  => ['#fff7ed', '#c2410c'],
            'Sabtu'  => ['#f0fdfa', '#0f766e'],
            'Minggu' => ['#fef2f2', '#dc2626'],
        ];
    @endphp

    <div class="card bg-base-100 shadow-sm rounded-2xl">
        <div class="card-body p-0">

            {{-- Card header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 pt-5 pb-4 border-b border-base-200">
                <div>
                    <h2 class="text-base font-bold text-[#1e2d6b] flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center"
                              style="background: linear-gradient(135deg,#e0e7ff,#c7d2fe);">
                            <i class="fas fa-list-ol text-indigo-600 text-xs"></i>
                        </span>
                        Jadwal Poliklinik &amp; Dokter
                    </h2>
                </div>
                <div class="flex items-center gap-2 ml-9 sm:ml-0">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-xs text-slate-500 font-medium" id="lastUpdateLabel">Live</span>
                </div>
            </div>

            @if($semuaJadwal->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <i class="fas fa-calendar-xmark text-5xl mb-3 text-slate-200"></i>
                    <p class="font-semibold">Belum ada jadwal poliklinik</p>
                    <p class="text-xs mt-1">Hubungi admin untuk informasi lebih lanjut.</p>
                </div>
            @else

                {{-- Filter: pencarian + dropdown --}}
                <div class="px-6 py-4 border-b border-base-200 bg-slate-50/60">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-5 relative">
                            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <input id="cariJadwal" type="search" autocomplete="off"
                                   placeholder="Cari poli, dokter, atau hari..."
                                   class="input input-bordered w-full pl-9 h-10 text-sm">
                        </div>

                        <select id="filterPoli" class="select select-bordered md:col-span-3 w-full h-10 text-sm">
                            <option value="">Semua Poli</option>
                            @foreach($daftarPoli as $namaPoli)
                                <option value="{{ $namaPoli }}">{{ $namaPoli }}</option>
                            @endforeach
                        </select>

                        <select id="filterHari" class="select select-bordered md:col-span-3 w-full h-10 text-sm">
                            <option value="">Semua Hari</option>
                            @foreach($daftarHari as $h)
                                <option value="{{ $h }}">{{ $h }}{{ $h === $hariIniNama ? ' (hari ini)' : '' }}</option>
                            @endforeach
                        </select>

                        <button type="button" id="resetFilter"
                                class="btn btn-ghost md:col-span-1 h-10 min-h-0 text-xs text-slate-500">
                            <i class="fas fa-rotate-left"></i> Reset
                        </button>
                    </div>
                </div>

                {{-- Tabel --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="tabelJadwal">
                        <thead>
                            <tr class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-5 font-semibold text-center w-12">No</th>
                                <th class="py-3 px-5 font-semibold text-left">Poli</th>
                                <th class="py-3 px-5 font-semibold text-left">Dokter</th>
                                <th class="py-3 px-5 font-semibold text-left">Hari</th>
                                <th class="py-3 px-5 font-semibold text-left">Jam Periksa</th>
                                <th class="py-3 px-5 font-semibold text-center">Sedang Dilayani</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($semuaJadwal as $i => $jadwal)
                            @php
                                $isMyJadwal = $bannerData && $bannerData['id_jadwal'] == $jadwal['id'];
                                $hc = $hariColors[$jadwal['hari']] ?? ['#f1f5f9', '#475569'];
                            @endphp
                            <tr class="baris-jadwal border-t border-slate-100 transition-colors duration-150 group
                                       {{ $isMyJadwal ? 'bg-indigo-50/60' : 'hover:bg-slate-50/80' }}"
                                data-jadwal-id="{{ $jadwal['id'] }}"
                                data-poli="{{ $jadwal['poli'] }}"
                                data-dokter="{{ $jadwal['dokter'] }}"
                                data-hari="{{ $jadwal['hari'] }}">

                                {{-- No (diurutkan ulang oleh JS sesuai hasil filter) --}}
                                <td class="py-3.5 px-5 text-center">
                                    <span class="no-urut inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold
                                                 {{ $isMyJadwal
                                                    ? 'bg-indigo-100 text-indigo-700'
                                                    : 'bg-slate-100 text-slate-500' }}">
                                        {{ $i + 1 }}
                                    </span>
                                </td>

                                {{-- Poli --}}
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                             style="background: linear-gradient(135deg,#e0e7ff,#c7d2fe);">
                                            <i class="fas fa-hospital text-indigo-600 text-xs"></i>
                                        </div>
                                        <span class="font-semibold text-slate-700">{{ $jadwal['poli'] }}</span>
                                    </div>
                                </td>

                                {{-- Dokter --}}
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                                             style="background: linear-gradient(135deg,#6366f1,#4338ca);">
                                            {{ strtoupper(substr($jadwal['dokter'], 0, 1)) }}
                                        </div>
                                        <span class="text-slate-600">{{ $jadwal['dokter'] }}</span>
                                    </div>
                                </td>

                                {{-- Hari --}}
                                <td class="py-3.5 px-5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                          style="background:{{ $hc[0] }}; color:{{ $hc[1] }};">
                                        {{ $jadwal['hari'] }}
                                    </span>
                                    @if($jadwal['is_hari_ini'])
                                        <span class="ml-1 text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded-full border border-green-200">
                                            Hari Ini
                                        </span>
                                    @endif
                                </td>

                                {{-- Jam Periksa --}}
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-1.5 text-slate-600">
                                        <i class="fas fa-clock text-slate-400 text-xs"></i>
                                        <span class="font-medium">{{ $jadwal['jam_mulai'] }}</span>
                                        <span class="text-slate-400">–</span>
                                        <span class="font-medium">{{ $jadwal['jam_selesai'] }}</span>
                                    </div>
                                </td>

                                {{-- Sedang Dilayani --}}
                                <td class="py-3.5 px-5 text-center">
                                    @if($jadwal['is_hari_ini'])
                                        <span class="tabel-nomor-dilayani inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 rounded-xl font-extrabold text-lg"
                                              data-jadwal-id="{{ $jadwal['id'] }}"
                                              style="background: linear-gradient(135deg,#fef3c7,#fde68a); color:#92400e;">
                                            {{ $jadwal['nomor_dilayani'] > 0 ? $jadwal['nomor_dilayani'] : '—' }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 text-sm">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            {{-- Tampil jika tidak ada hasil filter --}}
                            <tr id="kosongJadwal" class="hidden">
                                <td colspan="6" class="py-14 text-center text-slate-400">
                                    <i class="fas fa-magnifying-glass text-3xl mb-2 text-slate-200"></i>
                                    <p class="font-semibold text-sm">Tidak ada jadwal yang cocok</p>
                                    <p class="text-xs mt-1">Coba ubah kata kunci atau klik Reset.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Footer: info + paginasi --}}
                <div class="px-6 py-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
                    <span id="infoJadwal" class="text-xs text-slate-500"></span>

                    <div class="flex items-center gap-2 sm:ml-auto">
                        <button type="button" id="prevJadwal" class="btn btn-sm btn-ghost border border-slate-200">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <span id="halJadwal" class="text-xs font-semibold text-slate-600 min-w-[3rem] text-center"></span>
                        <button type="button" id="nextJadwal" class="btn btn-sm btn-ghost border border-slate-200">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 pb-3 flex flex-wrap items-center gap-2">
                    <span class="inline-block w-3 h-3 rounded" style="background:linear-gradient(135deg,#fef3c7,#fde68a);"></span>
                    <span class="text-[11px] text-slate-400">Kolom "Sedang Dilayani" hanya aktif untuk jadwal hari ini</span>
                    @if($bannerData)
                    <span class="ml-auto inline-flex items-center gap-1.5 text-[11px] font-medium text-indigo-600">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Baris disorot = jadwal antrian Anda
                    </span>
                    @endif
                </div>

            @endif

        </div>
    </div>

        </div>
    </div>

    @push('scripts')
        <script>
    (function () {
        'use strict';

        const tbody = document.querySelector('#tabelJadwal tbody');
        if (!tbody) return;

        const rows     = Array.from(tbody.querySelectorAll('tr.baris-jadwal'));
        const cari     = document.getElementById('cariJadwal');
        const selPoli  = document.getElementById('filterPoli');
        const selHari  = document.getElementById('filterHari');
        const btnReset = document.getElementById('resetFilter');
        const kosong   = document.getElementById('kosongJadwal');
        const info     = document.getElementById('infoJadwal');
        const btnPrev  = document.getElementById('prevJadwal');
        const btnNext  = document.getElementById('nextJadwal');
        const lblHal   = document.getElementById('halJadwal');

        const PER_HALAMAN = 8;
        let halaman = 1;

        // Abaikan huruf besar/kecil dan aksen supaya pencarian lebih longgar
        const norm = (s) => (s || '').toString().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();

        rows.forEach((r) => {
            r._cari = norm(r.dataset.poli + ' ' + r.dataset.dokter + ' ' + r.dataset.hari);
        });

        function terfilter() {
            const kw = norm(cari.value);
            const poli = selPoli.value;
            const hari = selHari.value;

            return rows.filter((r) => {
                if (poli && r.dataset.poli !== poli) return false;
                if (hari && r.dataset.hari !== hari) return false;
                if (kw && !r._cari.includes(kw)) return false;
                return true;
            });
        }

        function render() {
            const hasil  = terfilter();
            const total  = hasil.length;
            const jumlahHal = Math.max(1, Math.ceil(total / PER_HALAMAN));
            if (halaman > jumlahHal) halaman = jumlahHal;

            const awal  = (halaman - 1) * PER_HALAMAN;
            const tampil = hasil.slice(awal, awal + PER_HALAMAN);
            const set = new Set(tampil);

            rows.forEach((r) => r.classList.toggle('hidden', !set.has(r)));
            tampil.forEach((r, i) => {
                const no = r.querySelector('.no-urut');
                if (no) no.textContent = awal + i + 1;
            });

            kosong.classList.toggle('hidden', total > 0);
            info.textContent = total === 0
                ? 'Tidak ada jadwal yang cocok'
                : 'Menampilkan ' + (awal + 1) + '–' + (awal + tampil.length) + ' dari ' + total + ' jadwal';
            lblHal.textContent = halaman + ' / ' + jumlahHal;
            btnPrev.disabled = halaman <= 1;
            btnNext.disabled = halaman >= jumlahHal;
        }

        function ubahFilter() { halaman = 1; render(); }

        cari.addEventListener('input', ubahFilter);
        selPoli.addEventListener('change', ubahFilter);
        selHari.addEventListener('change', ubahFilter);
        btnPrev.addEventListener('click', () => { if (halaman > 1) { halaman--; render(); } });
        btnNext.addEventListener('click', () => { halaman++; render(); });
        btnReset.addEventListener('click', () => {
            cari.value = ''; selPoli.value = ''; selHari.value = '';
            ubahFilter();
        });

        render();
    })();
    </script>
    <script>
    (function () {
        'use strict';

        const endpoint = '{{ route('pasien.antrian-status') }}';

        // Data antrian aktif pasien (null jika pasien tidak punya antrian aktif)
        const bannerEl    = document.getElementById('bannerAntrian');
        const myJadwalId  = bannerEl ? Number(bannerEl.dataset.jadwalId) : null;
        const antrianSaya = bannerEl ? Number(bannerEl.dataset.antrianSaya) : null;
        let selesai       = false; // true setelah pasien selesai diperiksa
        const judulAsli   = document.title;
        let sudahDiberitahu = false; // notifikasi "giliran tiba" hanya sekali

        /** Pad angka jadi 3 digit, 0 → '—' */
        function pad(n) {
            return (n && n > 0) ? String(n).padStart(3, '0') : '—';
        }

        /** HTML pesan status banner */
        function statusHTML(nd, sa) {
            const selisih = sa - nd;
            // Dokter sudah memanggil nomor Anda -> sedang dilayani
            if (selisih <= 0) {
                return '<i class="fas fa-bell animate-bounce" style="color:#4ade80;"></i>'
                     + '<span style="color:#86efac;"><strong>Giliran Anda sudah tiba!</strong> Anda sedang dilayani dokter.</span>';
            }
            if (!nd || nd === 0) {
                return '<i class="fas fa-hourglass-start" style="color:#facc15;"></i>'
                     + '<span style="color:#fde047;">Antrian belum dimulai, harap bersiap di lokasi.</span>';
            }
            if (selisih <= 3) {
                return '<i class="fas fa-bell" style="color:#fb923c;"></i>'
                     + '<span style="color:#fdba74;">Bersiap! Giliran Anda <strong>' + selisih + '</strong> nomor lagi.</span>';
            }
            return '<i class="fas fa-couch" style="color:#a5b4fc;"></i>'
                 + '<span style="color:#c7d2fe;">Masih <strong>' + selisih + '</strong> nomor antrian sebelum giliran Anda.</span>';
        }

        /** Update banner antrian aktif. banner === null berarti antrian sudah selesai diperiksa. */
        function updateBanner(banner) {
            const ndEl  = document.getElementById('bannerNomorDilayani');
            const msgEl = document.getElementById('bannerStatusMsg');
            if (!bannerEl || !msgEl) return;

            if (!banner) {
                // Pasien sudah diperiksa -> antrian tidak aktif lagi
                if (ndEl && antrianSaya) ndEl.textContent = pad(antrianSaya);
                msgEl.innerHTML = '<i class="fas fa-circle-check" style="color:#4ade80;"></i>'
                    + '<span style="color:#86efac;">Pemeriksaan Anda sudah selesai. '
                    + '<a href="{{ route('pasien.riwayat') }}" style="text-decoration:underline;font-weight:700;">Lihat riwayat &amp; pembayaran</a></span>';
                selesai = true;
                document.title = judulAsli;
                return;
            }

            if (ndEl)  ndEl.textContent = pad(banner.nomor_dilayani);
            msgEl.innerHTML = statusHTML(banner.nomor_dilayani, banner.antrian_saya);

            // Giliran tiba: ubah judul tab (terlihat walau tab di latar belakang) + getar di HP
            if (banner.antrian_saya - banner.nomor_dilayani <= 0 && !sudahDiberitahu) {
                sudahDiberitahu = true;
                document.title = '🔔 Giliran Anda tiba! - ' + judulAsli;
                if (navigator.vibrate) navigator.vibrate([300, 150, 300]);
            }
        }

        /** Update kolom "Sedang Dilayani" di tabel */
        function updateTabel(jadwals) {
            document.querySelectorAll('.tabel-nomor-dilayani').forEach(function (el) {
                const id = el.dataset.jadwalId;
                if (!jadwals[id]) return;
                const nd = jadwals[id].nomor_dilayani;
                el.textContent = (nd && nd > 0) ? nd : '—';
            });
        }

        /** Update timestamp label */
        function updateTimestamp() {
            const el = document.getElementById('lastUpdateLabel');
            if (!el) return;
            const t = new Date();
            el.textContent = 'Update '
                + String(t.getHours()).padStart(2,'0') + ':'
                + String(t.getMinutes()).padStart(2,'0') + ':'
                + String(t.getSeconds()).padStart(2,'0');
        }

        /**
         * Ambil kondisi terbaru dari server (sumber data yang benar).
         * Dipanggil saat WebSocket (re)connect, setelah event untuk jadwal pasien,
         * saat tab kembali aktif, dan sebagai cadangan jika WebSocket terputus.
         */
        function sinkron() {
            fetch(endpoint, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                updateBanner(data.banner);   // null = sudah selesai diperiksa
                updateTabel(data.jadwals);
                updateTimestamp();
            })
            .catch(function (err) {
                console.warn('[Antrian Poliklinik] Sinkron gagal:', err);
            });
        }

        const koneksi = window.Echo?.connector?.pusher?.connection;
        const tersambung = function () { return koneksi && koneksi.state === 'connected'; };

        if (!window.Echo) {
            console.warn('[Antrian Poliklinik] Laravel Echo belum dimuat (jalankan npm run dev / build).');
        } else {
            // Event dari Reverb: dokter baru selesai memeriksa satu pasien
            window.Echo.channel('antrian')
                .listen('.antrian.diperbarui', function (e) {
                    // Tabel hanya menampilkan nomor dilayani untuk jadwal hari ini
                    if (e.hari_ini) {
                        updateTabel({ [e.id_jadwal]: { nomor_dilayani: e.nomor_dilayani } });
                    }

                    // Banner: langsung tampilkan nomor baru, lalu konfirmasi ke server
                    // (server yang tahu apakah antrian pasien ini sudah selesai diperiksa)
                    if (!selesai && myJadwalId !== null && Number(e.id_jadwal) === myJadwalId) {
                        updateBanner({ nomor_dilayani: e.nomor_dilayani, antrian_saya: antrianSaya });
                        sinkron();
                    }

                    updateTimestamp();
                });

            // Sinkron saat tersambung (pertama kali maupun setelah putus)
            if (koneksi) koneksi.bind('connected', sinkron);
        }

        // Cadangan: bila WebSocket tidak tersambung (Reverb mati / terputus), cek tiap 5 detik.
        setInterval(function () {
            if (!selesai && bannerEl && !tersambung()) sinkron();
        }, 5000);

        // Saat tab kembali dibuka, langsung ambil kondisi terbaru.
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) sinkron();
        });
    })();
    </script>
    @endpush

</x-layouts.app>