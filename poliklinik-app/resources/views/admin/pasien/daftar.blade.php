<x-layouts.app title="Pendaftaran Periksa">

    <div class="max-w-xl">

        {{-- ============================================================
             BLOK PEMBLOKIR: tampil jika pasien punya antrian aktif
        ============================================================ --}}
        @if($antrianAktif)
            @php
                $j = $antrianAktif->jadwalPeriksa;
            @endphp
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

        @else

            {{-- ============================================================
                 FORM PENDAFTARAN (hanya muncul jika tidak ada antrian aktif)
            ============================================================ --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl">
                <div class="card-body">
                    <h1 class="text-xl font-bold text-[#1e2d6b] mb-4">
                        <i class="fas fa-clipboard-list mr-2 text-indigo-400"></i>
                        Pendaftaran Periksa
                    </h1>

                    @if ($errors->any())
                        <div class="alert alert-error mb-4 rounded-xl text-sm">
                            <i class="fas fa-circle-xmark"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form action="{{ route('pasien.daftar.submit') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label class="label"><span class="label-text font-semibold">No. Rekam Medis</span></label>
                            <input type="text"
                                   value="{{ $user->no_rm ?? '-' }}"
                                   class="input input-bordered w-full bg-slate-50 text-slate-500"
                                   readonly>
                        </div>

                        <div>
                            <label class="label"><span class="label-text font-semibold">Poli</span></label>
                            <select name="id_poli" id="selectPoli" class="select select-bordered w-full" required>
                                <option value="" disabled @selected(!old('id_poli'))>Pilih poli</option>
                                @foreach ($polis as $poli)
                                    <option value="{{ $poli->id }}"
                                            @selected((string) old('id_poli') === (string) $poli->id)>
                                        {{ $poli->nama_poli }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label"><span class="label-text font-semibold">Jadwal Dokter</span></label>
                            <select name="id_jadwal" id="selectJadwal" class="select select-bordered w-full" required>
                                <option value="" disabled @selected(!old('id_jadwal'))>Pilih jadwal</option>
                                @foreach ($jadwals as $jadwal)
                                    <option value="{{ $jadwal->id }}"
                                            data-poli="{{ $jadwal->dokter?->id_poli }}"
                                            @selected((string) old('id_jadwal') === (string) $jadwal->id)>
                                        {{ $jadwal->dokter?->nama ?? '-' }}
                                        — {{ $jadwal->hari }},
                                        {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label"><span class="label-text font-semibold">Keluhan</span></label>
                            <textarea name="keluhan"
                                      rows="3"
                                      class="textarea textarea-bordered w-full"
                                      placeholder="Tuliskan keluhan Anda..."
                                      required>{{ old('keluhan') }}</textarea>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button type="submit"
                                    class="btn text-white flex-1"
                                    style="background: linear-gradient(135deg,#1e2d6b,#2d4499);">
                                <i class="fas fa-paper-plane mr-1"></i> Daftar Sekarang
                            </button>
                            <a href="{{ route('pasien.dashboard') }}" class="btn btn-ghost">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

        @endif

    </div>

    @push('scripts')
    <script>
        (function () {
            const poli   = document.getElementById('selectPoli');
            const jadwal = document.getElementById('selectJadwal');
            if (!poli || !jadwal) return;

            // Tampilkan hanya jadwal dokter di poli yang dipilih
            function filterJadwal() {
                let pilihanMasihValid = false;
                jadwal.querySelectorAll('option[data-poli]').forEach(function (opt) {
                    const cocok = opt.dataset.poli === poli.value;
                    opt.hidden   = !cocok;
                    opt.disabled = !cocok;
                    if (cocok && opt.value === jadwal.value) pilihanMasihValid = true;
                });
                if (!pilihanMasihValid) jadwal.value = '';
            }

            poli.addEventListener('change', filterJadwal);
            filterJadwal();
        })();
    </script>
    @endpush

</x-layouts.app>