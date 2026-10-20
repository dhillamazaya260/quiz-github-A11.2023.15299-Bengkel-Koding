<x-layouts.app title="Pendaftaran Periksa">
    <div class="card bg-base-100 shadow-sm rounded-2xl max-w-xl">
        <div class="card-body">
            <h1 class="text-xl font-bold text-[#1e2d6b] mb-4">Pendaftaran Periksa</h1>

            @if ($errors->any())
                <div class="alert alert-error mb-4 rounded-xl text-sm">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('pasien.daftar.submit') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="label"><span class="label-text font-semibold">No. Rekam Medis</span></label>
                    <input type="text" value="{{ $user->no_rm ?? '-' }}" class="input input-bordered w-full bg-slate-50" readonly>
                </div>

                <div>
                    <label class="label"><span class="label-text font-semibold">Poli</span></label>
                    <select name="id_poli" id="selectPoli" class="select select-bordered w-full" required>
                        <option value="" disabled @selected(! old('id_poli'))>Pilih poli</option>
                        @foreach ($polis as $poli)
                            <option value="{{ $poli->id }}" @selected((string) old('id_poli') === (string) $poli->id)>{{ $poli->nama_poli }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label"><span class="label-text font-semibold">Jadwal Dokter</span></label>
                    <select name="id_jadwal" id="selectJadwal" class="select select-bordered w-full" required>
                        <option value="" disabled @selected(! old('id_jadwal'))>Pilih jadwal</option>
                        @foreach ($jadwals as $jadwal)
                            <option value="{{ $jadwal->id }}"
                                    data-poli="{{ $jadwal->dokter?->id_poli }}"
                                    @selected((string) old('id_jadwal') === (string) $jadwal->id)>
                                {{ $jadwal->dokter?->nama ?? '-' }} - {{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai, 0, 5) }}-{{ substr($jadwal->jam_selesai, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label"><span class="label-text font-semibold">Keluhan</span></label>
                    <textarea name="keluhan" rows="3" class="textarea textarea-bordered w-full" placeholder="Tuliskan keluhan Anda..." required>{{ old('keluhan') }}</textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn bg-[#2d4499] text-white">Daftar</button>
                    <a href="{{ route('pasien.dashboard') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const poli = document.getElementById('selectPoli');
            const jadwal = document.getElementById('selectJadwal');

            // Tampilkan hanya jadwal dokter di poli yang dipilih
            function filterJadwal() {
                let pilihanMasihValid = false;

                jadwal.querySelectorAll('option[data-poli]').forEach((opt) => {
                    const cocok = opt.dataset.poli === poli.value;
                    opt.hidden = !cocok;
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