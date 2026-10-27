<x-layouts.app title="Pendaftaran Periksa">

    {{-- ============================================================
         BLOK PEMBLOKIR: tampil jika pasien punya antrian aktif
    ============================================================ --}}
    @if($antrianAktif)
        @php
            $j = $antrianAktif->jadwalPeriksa;
        @endphp
        <div class="max-w-xl">
            <div class="rounded-2xl overflow-hidden shadow-sm mb-6"
                 style="background: linear-gradient(135deg,#1e2d6b,#2d4499); border: 1px solid rgba(99,102,241,0.4);">
                <div class="p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(255,255,255,0.15);">
                            <i class="fas fa-circle-exclamation text-amber-300 text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base">Anda Masih Memiliki Antrian Aktif</h3>
                            <p class="text-indigo-200 text-xs mt-0.5">
                                Pendaftaran baru tidak dapat dilakukan hingga pemeriksaan selesai.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.08);">
                            <p class="text-indigo-300 text-[10px] uppercase tracking-wide font-medium mb-0.5">Poli</p>
                            <p class="text-white font-semibold text-sm">{{ $j?->dokter?->poli?->nama_poli ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.08);">
                            <p class="text-indigo-300 text-[10px] uppercase tracking-wide font-medium mb-0.5">Dokter</p>
                            <p class="text-white font-semibold text-sm">{{ $j?->dokter?->nama ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.08);">
                            <p class="text-indigo-300 text-[10px] uppercase tracking-wide font-medium mb-0.5">Jadwal</p>
                            <p class="text-white font-semibold text-sm">{{ $j?->hari }}, {{ substr($j?->jam_mulai, 0, 5) }}–{{ substr($j?->jam_selesai, 0, 5) }}</p>
                        </div>
                        <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.08);">
                            <p class="text-indigo-300 text-[10px] uppercase tracking-wide font-medium mb-0.5">No. Antrian Saya</p>
                            <p class="text-white font-extrabold text-xl">{{ str_pad($antrianAktif->no_antrian, 3, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>

                    <a href="{{ route('pasien.dashboard') }}"
                       class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold transition-all"
                       style="background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.25);"
                       onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>

    @else

        {{-- ============================================================
             FORM PENDAFTARAN (hanya muncul jika tidak ada antrian aktif)
        ============================================================ --}}
        @php
            $hariIniNama = now()->locale('id')->isoFormat('dddd');
            $urutanHari  = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

            // Ikon & warna tiap poli (sama dengan dashboard pasien)
            $poliStyle = function (string $nama): array {
                $n = strtolower($nama);
                $peta = [
                    'tht'       => ['fa-ear-listen',      '#fef3c7,#fde68a', '#b45309'],
                    'kandungan' => ['fa-person-pregnant', '#fce7f3,#fbcfe8', '#be185d'],
                    'mata'      => ['fa-eye',             '#d1fae5,#a7f3d0', '#047857'],
                    'gigi'      => ['fa-tooth',           '#cffafe,#a5f3fc', '#0e7490'],
                    'anak'      => ['fa-baby',            '#ffedd5,#fed7aa', '#c2410c'],
                    'jantung'   => ['fa-heart-pulse',     '#fee2e2,#fecaca', '#b91c1c'],
                    'saraf'     => ['fa-brain',           '#ede9fe,#ddd6fe', '#6d28d9'],
                    'tulang'    => ['fa-bone',            '#f1f5f9,#e2e8f0', '#475569'],
                    'paru'      => ['fa-lungs',           '#e0f2fe,#bae6fd', '#0369a1'],
                    'umum'      => ['fa-hospital',        '#e0e7ff,#c7d2fe', '#4338ca'],
                ];
                foreach ($peta as $kunci => $gaya) {
                    if (str_contains($n, $kunci)) return $gaya;
                }
                return ['fa-stethoscope', '#e0e7ff,#c7d2fe', '#4338ca'];
            };

            // Jadwal: dokter (A–Z) -> hari (Senin–Minggu) -> jam mulai, dikelompokkan per dokter
            $jadwalPerDokter = $jadwals
                ->sortBy([
                    fn ($a, $b) => strcasecmp($a->dokter?->nama ?? '', $b->dokter?->nama ?? ''),
                    fn ($a, $b) => ($urutanHari[$a->hari] ?? 9) <=> ($urutanHari[$b->hari] ?? 9),
                    fn ($a, $b) => strcmp((string) $a->jam_mulai, (string) $b->jam_mulai),
                ])
                ->groupBy('id_dokter');
        @endphp

        <div class="max-w-5xl">

            <div class="mb-5">
                <h1 class="text-xl font-bold text-[#1e2d6b]">
                    <i class="fas fa-clipboard-list mr-2 text-indigo-400"></i>
                    Pendaftaran Periksa
                </h1>
                <p class="text-sm text-slate-500 mt-1">Pilih poli, pilih jadwal dokter, lalu tuliskan keluhan Anda.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error mb-4 rounded-xl text-sm">
                    <i class="fas fa-circle-xmark"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form id="formDaftar" action="{{ route('pasien.daftar.submit') }}" method="POST"
                  class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                @csrf

                {{-- ================= KOLOM KIRI: LANGKAH 1-3 ================= --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- ---------- LANGKAH 1: POLI ---------- --}}
                    <section class="card bg-base-100 shadow-sm rounded-2xl">
                        <div class="card-body gap-4">
                            <h2 class="flex items-center gap-2 font-bold text-slate-800">
                                <span class="w-6 h-6 rounded-full bg-[#2d4499] text-white text-xs font-bold flex items-center justify-center">1</span>
                                Pilih Poli
                            </h2>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($polis as $poli)
                                    @php
                                        $ps = $poliStyle($poli->nama_poli);
                                        $jadwalPoli = $jadwals->filter(fn ($x) => (int) $x->dokter?->id_poli === (int) $poli->id);
                                        $praktikHariIni = $jadwalPoli->contains(fn ($x) => $x->hari === $hariIniNama);
                                    @endphp
                                    <label class="relative cursor-pointer" title="{{ $poli->keterangan }}">
                                        <input type="radio" name="id_poli" value="{{ $poli->id }}"
                                               data-nama="{{ $poli->nama_poli }}"
                                               class="peer sr-only"
                                               @checked((string) old('id_poli') === (string) $poli->id)>
                                        <div class="h-full rounded-2xl border border-slate-200 bg-white p-3.5 flex flex-col items-center text-center gap-1.5 transition
                                                    hover:shadow-sm
                                                    peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:ring-2 peer-checked:ring-indigo-200
                                                    peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-300">
                                            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                                                 style="background: linear-gradient(135deg,{{ $ps[1] }});">
                                                <i class="fas {{ $ps[0] }} text-lg" style="color: {{ $ps[2] }};"></i>
                                            </div>
                                            <p class="font-semibold text-sm text-slate-700 leading-tight">{{ $poli->nama_poli }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $jadwalPoli->count() }} jadwal</p>
                                            @if ($praktikHariIni)
                                                <span class="text-[10px] font-bold text-green-600 bg-green-50 border border-green-200 rounded-full px-1.5 py-0.5">
                                                    Praktik hari ini
                                                </span>
                                            @endif
                                        </div>
                                        <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] items-center justify-center hidden peer-checked:flex">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            @error('id_poli')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    {{-- ---------- LANGKAH 2: JADWAL ---------- --}}
                    <section id="panelJadwal" class="card bg-base-100 shadow-sm rounded-2xl">
                        <div class="card-body gap-4">
                            <h2 class="flex items-center gap-2 font-bold text-slate-800">
                                <span class="w-6 h-6 rounded-full bg-[#2d4499] text-white text-xs font-bold flex items-center justify-center">2</span>
                                Pilih Jadwal Dokter
                                <span id="judulPoli" class="text-sm font-medium text-slate-400"></span>
                            </h2>

                            {{-- Belum pilih poli --}}
                            <div id="jadwalKosong" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-slate-400">
                                <i class="fas fa-hand-pointer text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">Pilih poli terlebih dahulu</p>
                                <p class="text-xs mt-0.5">Jadwal dokter akan muncul di sini.</p>
                            </div>

                            {{-- Poli dipilih tapi belum ada jadwal --}}
                            <div id="jadwalTanpa" class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-slate-400">
                                <i class="fas fa-calendar-xmark text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">Belum ada jadwal untuk poli ini</p>
                                <p class="text-xs mt-0.5">Silakan pilih poli lain.</p>
                            </div>

                            <div class="space-y-3">
                                @foreach ($jadwalPerDokter as $grup)
                                    @php
                                        $dokter    = $grup->first()->dokter;
                                        $namaDokter = $dokter?->nama ?? '-';
                                        $inisial   = strtoupper(mb_substr(preg_replace('/^(drg|dr)\.?\s*/i', '', $namaDokter), 0, 1));
                                    @endphp
                                    <div class="kartu-dokter hidden rounded-2xl border border-slate-200 p-4"
                                         data-poli="{{ $dokter?->id_poli }}">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                                                 style="background: linear-gradient(135deg,#6366f1,#4338ca);">
                                                {{ $inisial }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800 leading-tight">{{ $namaDokter }}</p>
                                                <p class="text-xs text-slate-400">{{ $dokter?->poli?->nama_poli ?? '-' }}</p>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($grup as $jadwal)
                                                @php
                                                    $hariIni = $jadwal->hari === $hariIniNama;
                                                    $jam     = substr($jadwal->jam_mulai, 0, 5) . '–' . substr($jadwal->jam_selesai, 0, 5);
                                                @endphp
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="id_jadwal" value="{{ $jadwal->id }}"
                                                           data-poli="{{ $dokter?->id_poli }}"
                                                           data-dokter="{{ $namaDokter }}"
                                                           data-hari="{{ $jadwal->hari }}"
                                                           data-jam="{{ $jam }}"
                                                           data-hariini="{{ $hariIni ? 1 : 0 }}"
                                                           class="peer sr-only"
                                                           @checked((string) old('id_jadwal') === (string) $jadwal->id)>
                                                    <div class="min-w-[7.5rem] rounded-xl border px-3 py-2 text-center transition hover:shadow-sm
                                                                {{ $hariIni ? 'border-green-300 bg-green-50 text-green-800' : 'border-slate-200 bg-white text-slate-600' }}
                                                                peer-checked:border-[#2d4499] peer-checked:bg-[#2d4499] peer-checked:text-white peer-checked:shadow-md
                                                                peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-300">
                                                        <p class="text-xs font-bold leading-tight">{{ $jadwal->hari }}</p>
                                                        <p class="text-[11px] opacity-80">{{ $jam }}</p>
                                                        @if ($hariIni)
                                                            <p class="text-[10px] font-bold uppercase tracking-wide mt-0.5">Hari ini</p>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @error('id_jadwal')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    {{-- ---------- LANGKAH 3: KELUHAN ---------- --}}
                    <section class="card bg-base-100 shadow-sm rounded-2xl">
                        <div class="card-body gap-3">
                            <h2 class="flex items-center gap-2 font-bold text-slate-800">
                                <span class="w-6 h-6 rounded-full bg-[#2d4499] text-white text-xs font-bold flex items-center justify-center">3</span>
                                Tuliskan Keluhan
                            </h2>

                            <textarea name="keluhan"
                                      rows="4"
                                      class="textarea textarea-bordered w-full"
                                      placeholder="Contoh: demam sejak 2 hari, disertai batuk..."
                                      required>{{ old('keluhan') }}</textarea>
                            <div class="flex items-center justify-between">
                                @error('keluhan')
                                    <p class="text-xs text-error">{{ $message }}</p>
                                @else
                                    <span></span>
                                @enderror
                                <span id="hitungKeluhan" class="text-[11px] text-slate-400">0 karakter</span>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- ================= KOLOM KANAN: RINGKASAN ================= --}}
                <aside class="lg:col-span-1">
                    <div class="card bg-base-100 shadow-sm rounded-2xl lg:sticky lg:top-6">
                        <div class="card-body gap-4">
                            <h3 class="font-bold text-sm uppercase tracking-wide text-[#1e2d6b]">
                                <i class="fas fa-ticket mr-1.5 text-indigo-400"></i> Ringkasan
                            </h3>

                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-[11px] uppercase tracking-wide text-slate-400">No. Rekam Medis</dt>
                                    <dd class="font-semibold text-slate-700">{{ $user->no_rm ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] uppercase tracking-wide text-slate-400">Poli</dt>
                                    <dd id="rsPoli" class="text-slate-300">—</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] uppercase tracking-wide text-slate-400">Dokter</dt>
                                    <dd id="rsDokter" class="text-slate-300">—</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] uppercase tracking-wide text-slate-400">Jadwal</dt>
                                    <dd id="rsJadwal" class="text-slate-300">—</dd>
                                </div>
                            </dl>

                            <p id="ringkasKurang" class="text-xs text-amber-600 flex items-start gap-1.5">
                                <i class="fas fa-circle-info mt-0.5"></i>
                                <span></span>
                            </p>

                            <div class="space-y-2">
                                <button type="submit" id="btnDaftar" disabled
                                        class="btn text-white w-full disabled:opacity-50"
                                        style="background: linear-gradient(135deg,#1e2d6b,#2d4499);">
                                    <i class="fas fa-paper-plane mr-1"></i> Daftar Sekarang
                                </button>
                                <button type="button" id="btnBatal" class="btn btn-ghost w-full">Batal</button>
                            </div>

                            <p class="text-[11px] text-slate-400 text-center">
                                Nomor antrian diberikan otomatis setelah pendaftaran berhasil.
                            </p>
                        </div>
                    </div>
                </aside>

            </form>
        </div>

        {{-- ================= POP-UP KONFIRMASI BATAL ================= --}}
        <dialog id="modalBatal" class="modal">
            <div class="modal-box rounded-2xl text-center max-w-sm">
                <div class="mx-auto mb-3 w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-triangle-exclamation text-amber-500 text-3xl"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-800">Batalkan pendaftaran?</h3>
                <p id="pesanBatal" class="text-sm text-slate-500 mt-1">Anda akan kembali ke dashboard.</p>
                <div class="modal-action justify-center gap-2">
                    <button type="button" id="batalLanjut" class="btn btn-ghost rounded-xl">Lanjut mengisi</button>
                    <a href="{{ route('pasien.dashboard') }}" class="btn btn-error text-white rounded-xl">Ya, batalkan</a>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>tutup</button>
            </form>
        </dialog>

    @endif

    @push('scripts')
    <script>
        (function () {
            const form = document.getElementById('formDaftar');
            if (!form) return;

            const radiosJadwal = Array.from(form.querySelectorAll('input[name="id_jadwal"]'));
            const kartuDokter  = Array.from(form.querySelectorAll('.kartu-dokter'));
            const panelKosong  = document.getElementById('jadwalKosong');
            const panelTanpa   = document.getElementById('jadwalTanpa');
            const panelJadwal  = document.getElementById('panelJadwal');
            const judulPoli    = document.getElementById('judulPoli');
            const keluhan      = form.querySelector('textarea[name="keluhan"]');
            const hitung       = document.getElementById('hitungKeluhan');
            const btn          = document.getElementById('btnDaftar');
            const kurang       = document.getElementById('ringkasKurang');
            const labelBtn     = btn.innerHTML;

            const btnBatal     = document.getElementById('btnBatal');
            const modalBatal   = document.getElementById('modalBatal');
            const pesanBatal   = document.getElementById('pesanBatal');
            const batalLanjut  = document.getElementById('batalLanjut');

            const poliTerpilih   = () => form.querySelector('input[name="id_poli"]:checked');
            const jadwalTerpilih = () => form.querySelector('input[name="id_jadwal"]:checked');

            function isi(id, teks) {
                const el = document.getElementById(id);
                el.textContent = teks || '—';
                el.classList.toggle('text-slate-300', !teks);
                el.classList.toggle('text-slate-700', !!teks);
                el.classList.toggle('font-semibold', !!teks);
            }

            // Tampilkan hanya kartu dokter milik poli yang dipilih
            function tampilkanJadwal() {
                const p = poliTerpilih();
                let jumlah = 0;

                kartuDokter.forEach(function (k) {
                    const cocok = !!p && k.dataset.poli === p.value;
                    k.classList.toggle('hidden', !cocok);
                    if (cocok) jumlah++;
                });

                // Lepas pilihan jadwal yang bukan milik poli ini
                radiosJadwal.forEach(function (r) {
                    if (r.checked && (!p || r.dataset.poli !== p.value)) r.checked = false;
                });

                panelKosong.classList.toggle('hidden', !!p);
                panelTanpa.classList.toggle('hidden', !p || jumlah > 0);
                judulPoli.textContent = p ? '· ' + p.dataset.nama : '';

                // Hanya satu jadwal pada poli ini -> pilih otomatis
                if (p && !jadwalTerpilih()) {
                    const chip = radiosJadwal.filter(function (r) { return r.dataset.poli === p.value; });
                    if (chip.length === 1) chip[0].checked = true;
                }
            }

            // Ringkasan + status tombol daftar
            function perbarui() {
                const p = poliTerpilih();
                const j = jadwalTerpilih();
                const adaKeluhan = keluhan.value.trim().length > 0;

                isi('rsPoli', p ? p.dataset.nama : '');
                isi('rsDokter', j ? j.dataset.dokter : '');
                isi('rsJadwal', j
                    ? j.dataset.hari + (j.dataset.hariini === '1' ? ' (hari ini)' : '') + ', ' + j.dataset.jam
                    : '');

                hitung.textContent = keluhan.value.length + ' karakter';

                const belum = [];
                if (!p) belum.push('poli');
                if (!j) belum.push('jadwal dokter');
                if (!adaKeluhan) belum.push('keluhan');

                btn.disabled = belum.length > 0;
                kurang.classList.toggle('hidden', belum.length === 0);
                kurang.querySelector('span').textContent = belum.length ? 'Lengkapi dulu: ' + belum.join(', ') + '.' : '';
            }

            form.querySelectorAll('input[name="id_poli"]').forEach(function (r) {
                r.addEventListener('change', function () {
                    tampilkanJadwal();
                    perbarui();
                    // Di layar kecil, geser ke daftar jadwal supaya langsung terlihat
                    if (window.innerWidth < 1024) {
                        panelJadwal.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
            radiosJadwal.forEach(function (r) { r.addEventListener('change', perbarui); });
            keluhan.addEventListener('input', perbarui);

            // Tombol Batal: minta konfirmasi dulu
            btnBatal.addEventListener('click', function () {
                const adaIsi = !!poliTerpilih() || !!jadwalTerpilih() || keluhan.value.trim().length > 0;
                pesanBatal.textContent = adaIsi
                    ? 'Data yang sudah Anda isi tidak akan disimpan.'
                    : 'Anda akan kembali ke dashboard.';
                modalBatal.showModal();
            });
            batalLanjut.addEventListener('click', function () { modalBatal.close(); });

            // Cegah kirim ganda
            form.addEventListener('submit', function (e) {
                if (btn.disabled) { e.preventDefault(); return; }
                btn.disabled = true;
                btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Mendaftarkan...';
            });

            // Tombol "kembali" browser: kembalikan tampilan tombol
            window.addEventListener('pageshow', function () {
                btn.innerHTML = labelBtn;
                perbarui();
            });

            tampilkanJadwal();
            perbarui();
        })();
    </script>
    @endpush

</x-layouts.app>