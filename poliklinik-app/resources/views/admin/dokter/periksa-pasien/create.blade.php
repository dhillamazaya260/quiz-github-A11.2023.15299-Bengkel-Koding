@php
    $oldObat = collect(old('obat', []))->map(fn ($id) => (int) $id)->all();
@endphp

<x-layouts.app title="Periksa Pasien">

    <div class="mb-5">
        <h1 class="text-2xl font-bold text-slate-800">Periksa Pasien</h1>
    </div>

    @isset($daftarPoli)
    {{-- Info pasien --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-md p-5 mb-5 max-w-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Pasien</p>
                <p class="font-semibold text-slate-800">{{ $daftarPoli->pasien->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">No. Antrian</p>
                <p class="font-semibold text-slate-800">{{ $daftarPoli->no_antrian }}</p>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Keluhan</p>
                <p class="text-slate-700">{{ $daftarPoli->keluhan }}</p>
            </div>
        </div>
    </div>
    @endisset

    <div class="bg-white rounded-xl border border-slate-200 shadow-md p-6 max-w-2xl">

        @if ($errors->any())
            <div class="alert alert-error mb-4 rounded-xl text-sm">
                <i class="fas fa-circle-xmark"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('periksa-pasien.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="id_daftar_poli" value="{{ $daftarPoli->id ?? $id }}">

            {{-- Pilih Obat --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih Obat</label>
                <div class="flex gap-2">
                    <select id="selectObat" class="select select-bordered w-full">
                        <option value="">-- Pilih obat --</option>
                        @foreach ($obats as $obat)
                            <option value="{{ $obat->id }}"
                                    data-nama="{{ $obat->nama_obat }}"
                                    data-kemasan="{{ $obat->kemasan }}"
                                    data-harga="{{ $obat->harga }}">
                                {{ $obat->nama_obat }}@if ($obat->kemasan) ({{ $obat->kemasan }})@endif
                                - Rp {{ number_format($obat->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="btnTambah"
                            class="px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold whitespace-nowrap transition">
                        <i class="fas fa-plus text-xs"></i> Tambah
                    </button>
                </div>
            </div>

            {{-- Obat Terpilih --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Obat Terpilih</label>
                <ul id="listObat" class="border border-slate-200 rounded-lg divide-y divide-slate-100 min-h-[48px]"></ul>
                <p id="kosongObat" class="text-sm text-slate-400 mt-2">Belum ada obat yang dipilih.</p>
                <div id="inputObat"></div>
            </div>

            {{-- Total Harga --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Total Harga</label>
                <input type="text" id="totalHarga" value="Rp 0" readonly
                       class="input input-bordered w-full bg-slate-50 font-bold text-slate-800">
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Catatan <span class="font-normal text-slate-400">(opsional)</span>
                </label>
                <textarea name="catatan" rows="3" class="textarea textarea-bordered w-full"
                          placeholder="Tuliskan hasil pemeriksaan...">{{ old('catatan') }}</textarea>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn bg-[#2d4499] text-white">
                    <i class="fas fa-floppy-disk"></i> Simpan
                </button>
                <a href="{{ route('periksa-pasien.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            const select = document.getElementById('selectObat');
            const btnTambah = document.getElementById('btnTambah');
            const list = document.getElementById('listObat');
            const kosong = document.getElementById('kosongObat');
            const inputs = document.getElementById('inputObat');
            const totalEl = document.getElementById('totalHarga');

            // id => {nama, kemasan, harga}
            const terpilih = new Map();

            const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

            function render() {
                list.innerHTML = '';
                inputs.innerHTML = '';
                let total = 0;

                terpilih.forEach((o, id) => {
                    total += o.harga;

                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between px-4 py-2 text-sm';

                    const info = document.createElement('span');
                    info.textContent = o.nama + (o.kemasan ? ' (' + o.kemasan + ')' : '') + ' - ' + rupiah(o.harga);

                    const hapus = document.createElement('button');
                    hapus.type = 'button';
                    hapus.className = 'px-2.5 py-1 rounded-md bg-red-500 hover:bg-red-600 text-white text-xs font-semibold';
                    hapus.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                    hapus.addEventListener('click', () => { terpilih.delete(id); render(); });

                    li.append(info, hapus);
                    list.appendChild(li);

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'obat[]';
                    hidden.value = id;
                    inputs.appendChild(hidden);
                });

                kosong.style.display = terpilih.size ? 'none' : 'block';
                totalEl.value = rupiah(total);
            }

            function tambah(id) {
                const opt = select.querySelector('option[value="' + id + '"]');
                if (!opt || terpilih.has(String(id))) return;

                terpilih.set(String(id), {
                    nama: opt.dataset.nama,
                    kemasan: opt.dataset.kemasan,
                    harga: parseInt(opt.dataset.harga, 10) || 0,
                });
                render();
            }

            btnTambah.addEventListener('click', () => {
                if (!select.value) return;
                tambah(select.value);
                select.value = '';
            });

            // Pulihkan pilihan jika validasi gagal
            @json($oldObat).forEach(tambah);
            render();
        })();
    </script>
    @endpush

</x-layouts.app>