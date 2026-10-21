<x-layouts.app title="Riwayat Pemeriksaan">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Pemeriksaan</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Poli / Dokter</th>
                            <th class="px-6 py-4">Biaya</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayat as $periksa)
                            @php $dokter = $periksa->daftarPoli->jadwalPeriksa->dokter; @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $periksa->tgl_periksa->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $dokter?->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $dokter?->poli?->nama_poli ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge {{ $periksa->status_bayar_badge }}">{{ $periksa->status_bayar_label }}</span>
                                    @if ($periksa->catatan_penolakan && $periksa->status_bayar === \App\Models\Periksa::BAYAR_BELUM)
                                        <div class="text-xs text-red-500 mt-1">Bukti ditolak, unggah ulang</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($periksa->status_bayar === \App\Models\Periksa::BAYAR_BELUM)
                                        <button type="button"
                                                onclick="document.getElementById('modal-bayar-{{ $periksa->id }}').showModal()"
                                                class="btn btn-sm bg-blue-500 hover:bg-blue-600 text-white border-none rounded-lg">
                                            <i class="fas fa-wallet"></i> Bayar
                                        </button>
                                    @else
                                        <button type="button"
                                                onclick="document.getElementById('modal-bayar-{{ $periksa->id }}').showModal()"
                                                class="btn btn-sm btn-ghost text-indigo-600 hover:text-indigo-800 rounded-lg">
                                            <i class="fas fa-receipt"></i> Detail
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-14 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <span>Belum ada riwayat pemeriksaan</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= POP-UP PEMBAYARAN (satu per pemeriksaan) ================= --}}
    @foreach ($riwayat as $periksa)
        @php
            $dokter    = $periksa->daftarPoli->jadwalPeriksa->dokter;
            $belumBayar = $periksa->status_bayar === \App\Models\Periksa::BAYAR_BELUM;
            // Error validasi upload hanya ditampilkan di modal milik pemeriksaan yang bersangkutan
            $adaError  = $errors->any() && (string) old('periksa_id') === (string) $periksa->id;
        @endphp

        <dialog id="modal-bayar-{{ $periksa->id }}" class="modal">
            <div class="modal-box rounded-2xl">

                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3" aria-label="Tutup">
                        <i class="fas fa-xmark"></i>
                    </button>
                </form>

                <h3 class="text-lg font-bold text-slate-800 mb-1">
                    {{ $belumBayar ? 'Pembayaran Pemeriksaan' : 'Detail Pembayaran' }}
                </h3>
                <p class="text-sm text-slate-400 mb-4">
                    {{ $periksa->tgl_periksa->format('d/m/Y H:i') }}
                    &middot; {{ $dokter?->nama ?? '-' }} ({{ $dokter?->poli?->nama_poli ?? '-' }})
                </p>

                {{-- Rincian tagihan --}}
<div class="rounded-xl border border-slate-200 overflow-hidden mb-3">
    @include('components.partials.rincian-biaya', ['periksa' => $periksa, 'compact' => true])
</div>
<div class="flex justify-end mb-4">
    <span class="badge {{ $periksa->status_bayar_badge }}">{{ $periksa->status_bayar_label }}</span>
</div>

                @if ($belumBayar)
                    {{-- ===== Form upload bukti bayar ===== --}}
                    @if ($periksa->catatan_penolakan)
                        <div class="alert alert-warning mb-4 rounded-xl text-sm">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span>
                                <strong>Bukti sebelumnya ditolak admin.</strong><br>
                                {{ $periksa->catatan_penolakan }}<br>
                                Silakan unggah ulang bukti pembayaran yang benar.
                            </span>
                        </div>
                    @endif
                    @if ($adaError)
                        <div class="alert alert-error mb-4 rounded-xl text-sm">
                            <i class="fas fa-circle-xmark"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pasien.bayar', $periksa->id) }}"
                          enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="periksa_id" value="{{ $periksa->id }}">

                        <div>
                            <label class="text-sm font-semibold text-slate-700 block mb-2"
                                   for="bukti-{{ $periksa->id }}">
                                Unggah bukti pembayaran
                            </label>
                            <input type="file" id="bukti-{{ $periksa->id }}" name="bukti_bayar"
                                   accept=".jpg,.jpeg,.png" required
                                   onchange="previewBukti(this, {{ $periksa->id }})"
                                   class="file-input file-input-bordered w-full">
                            <p class="text-xs text-slate-400 mt-1">Format JPG/PNG, maksimal 2 MB. Bukti hanya bisa diunggah sekali.</p>
                        </div>

                        <img id="preview-{{ $periksa->id }}" alt="Pratinjau bukti pembayaran"
                             class="hidden w-full max-h-64 object-contain rounded-xl border bg-slate-50">

                        <div class="modal-action mt-2">
                            <button type="button"
                                    onclick="document.getElementById('modal-bayar-{{ $periksa->id }}').close()"
                                    class="btn btn-ghost rounded-lg">Batal</button>
                            <button type="submit"
                                    class="btn bg-blue-500 hover:bg-blue-600 text-white border-none rounded-lg">
                                <i class="fas fa-upload"></i> Kirim Bukti
                            </button>
                        </div>
                    </form>
                @else
                    {{-- ===== Detail: sudah diunggah / lunas ===== --}}
                    <dl class="text-sm space-y-2 mb-4">
                        @if ($periksa->tgl_bayar)
                            <div class="flex justify-between">
                                <dt class="text-slate-400">Diunggah</dt>
                                <dd class="text-slate-700">{{ $periksa->tgl_bayar->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                        @if ($periksa->tgl_verifikasi)
                            <div class="flex justify-between">
                                <dt class="text-slate-400">Diverifikasi admin</dt>
                                <dd class="text-slate-700">{{ $periksa->tgl_verifikasi->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($periksa->bukti_bayar)
                        <a href="{{ route('bukti-bayar.show', $periksa->id) }}" target="_blank" title="Buka ukuran penuh">
                            <img src="{{ route('bukti-bayar.show', $periksa->id) }}" alt="Bukti pembayaran" loading="lazy"
                                 class="w-full max-h-72 object-contain rounded-xl border bg-slate-50">
                        </a>
                    @endif

                    @if ($periksa->status_bayar === \App\Models\Periksa::BAYAR_MENUNGGU)
                        <p class="text-xs text-slate-400 mt-3">
                            Bukti sudah diterima dan sedang menunggu verifikasi admin.
                        </p>
                    @endif

                    <div class="modal-action mt-4">
                        <form method="dialog">
                            <button class="btn btn-ghost rounded-lg">Tutup</button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- klik area gelap = tutup --}}
            <form method="dialog" class="modal-backdrop"><button>tutup</button></form>
        </dialog>
    @endforeach

    @push('scripts')
        <script>
            const MAKS_UKURAN = 2 * 1024 * 1024; // 2 MB, sama dengan validasi server

            function previewBukti(input, id) {
                const img = document.getElementById('preview-' + id);
                const file = input.files && input.files[0];

                if (!file) {
                    img.classList.add('hidden');
                    img.removeAttribute('src');
                    return;
                }

                if (file.size > MAKS_UKURAN) {
                    alert('Ukuran bukti pembayaran maksimal 2 MB.');
                    input.value = '';
                    img.classList.add('hidden');
                    img.removeAttribute('src');
                    return;
                }

                img.src = URL.createObjectURL(file);
                img.classList.remove('hidden');
            }

            // Jika upload gagal validasi, buka kembali modal pemeriksaan tersebut agar pesan error terlihat
            @if ($errors->any() && old('periksa_id'))
                document.getElementById('modal-bayar-{{ (int) old('periksa_id') }}')?.showModal();
            @endif
        </script>
    @endpush

</x-layouts.app>