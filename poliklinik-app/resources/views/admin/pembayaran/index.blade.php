@php
    use App\Models\Periksa;

    $rp   = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $tabs = [
        'semua'                       => ['Semua', $jumlah->sum()],
        Periksa::BAYAR_MENUNGGU       => ['Menunggu Verifikasi', $jumlah[Periksa::BAYAR_MENUNGGU] ?? 0],
        Periksa::BAYAR_BELUM          => ['Belum Bayar', $jumlah[Periksa::BAYAR_BELUM] ?? 0],
        Periksa::BAYAR_LUNAS          => ['Lunas', $jumlah[Periksa::BAYAR_LUNAS] ?? 0],
    ];
@endphp

<x-layouts.app title="Verifikasi Pembayaran">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Verifikasi Pembayaran</h2>
    </div>

    {{-- ================= TAB FILTER ================= --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($tabs as $key => [$label, $total])
            <a href="{{ route('pembayaran.index', $key === 'semua' ? [] : ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold border transition
                      {{ $filter === $key ? 'bg-[#2d4499] text-white border-[#2d4499]' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full {{ $filter === $key ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ $total }}</span>
            </a>
        @endforeach
    </div>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Dokter</th>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Biaya</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembayarans as $periksa)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $periksa->daftarPoli->pasien->nama }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $periksa->daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $periksa->tgl_periksa->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $rp($periksa->biaya_periksa) }}</td>
                                <td class="px-6 py-4">
                                    <span class="badge {{ $periksa->status_bayar_badge }}">{{ $periksa->status_bayar_label }}</span>
                                    @if ($periksa->catatan_penolakan && $periksa->status_bayar === Periksa::BAYAR_BELUM)
                                        <div class="text-xs text-red-500 mt-1">Bukti ditolak sebelumnya</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($periksa->status_bayar === Periksa::BAYAR_MENUNGGU)
                                        <button type="button"
                                                onclick="document.getElementById('modal-bayar-{{ $periksa->id }}').showModal()"
                                                class="btn btn-sm bg-green-500 hover:bg-green-600 text-white border-none rounded-lg px-4">
                                            <i class="fas fa-magnifying-glass"></i> Tinjau &amp; Konfirmasi
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
                                <td colspan="6" class="text-center py-14 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <span>Tidak ada data pembayaran{{ $filter !== 'semua' ? ' pada filter ini' : '' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= MODAL TINJAU (satu per pembayaran) ================= --}}
    @foreach ($pembayarans as $periksa)
        @php
            $menunggu = $periksa->status_bayar === Periksa::BAYAR_MENUNGGU;
            $dokter   = $periksa->daftarPoli->jadwalPeriksa->dokter;
            $adaError = $errors->has('alasan') && (string) old('periksa_id') === (string) $periksa->id;
        @endphp

        <dialog id="modal-bayar-{{ $periksa->id }}" class="modal">
            <div class="modal-box rounded-2xl max-w-2xl">

                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3" aria-label="Tutup">
                        <i class="fas fa-xmark"></i>
                    </button>
                </form>

                <h3 class="text-lg font-bold text-slate-800 mb-1">
                    {{ $menunggu ? 'Tinjau Pembayaran' : 'Detail Pembayaran' }}
                </h3>
                <p class="text-sm text-slate-400 mb-4">
                    {{ $periksa->daftarPoli->pasien->nama }}
                    &middot; {{ $periksa->tgl_periksa->format('d/m/Y H:i') }}
                    &middot; {{ $dokter?->nama ?? '-' }} ({{ $dokter?->poli?->nama_poli ?? '-' }})
                </p>

                <div class="grid md:grid-cols-2 gap-4">
                    {{-- Rincian tagihan --}}
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Rincian Tagihan</div>
                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            @include('components.partials.rincian-biaya', ['periksa' => $periksa, 'compact' => true])
                        </div>
                    </div>

                    {{-- Bukti bayar --}}
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Bukti Pembayaran</div>
                        @if ($periksa->bukti_bayar)
                            <a href="{{ route('bukti-bayar.show', $periksa->id) }}" target="_blank" title="Buka ukuran penuh">
                                <img src="{{ route('bukti-bayar.show', $periksa->id) }}" alt="Bukti pembayaran" loading="lazy"
                                     class="w-full max-h-72 object-contain rounded-xl border bg-slate-50">
                            </a>
                            @if ($periksa->tgl_bayar)
                                <p class="text-xs text-slate-400 mt-2">Diunggah {{ $periksa->tgl_bayar->format('d/m/Y H:i') }}</p>
                            @endif
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 text-slate-400 text-sm text-center py-10">
                                <i class="fas fa-image text-2xl block mb-2"></i>
                                Belum ada bukti diunggah
                            </div>
                        @endif
                    </div>
                </div>

                @if ($periksa->tgl_verifikasi)
                    <p class="text-sm text-slate-500 mt-4">
                        <i class="fas fa-circle-check text-green-500"></i>
                        Dikonfirmasi {{ $periksa->tgl_verifikasi->format('d/m/Y H:i') }}
                    </p>
                @endif

                @if ($menunggu)
                    <div class="mt-5 pt-4 border-t border-slate-200 space-y-3">

                        {{-- Tolak --}}
                        <form method="POST" action="{{ route('pembayaran.tolak', $periksa->id) }}" class="space-y-2"
                              onsubmit="return confirm('Tolak bukti pembayaran ini? Pasien harus mengunggah ulang.')">
                            @csrf
                            <input type="hidden" name="periksa_id" value="{{ $periksa->id }}">
                            <label class="text-sm font-semibold text-slate-700 block" for="alasan-{{ $periksa->id }}">
                                Alasan penolakan <span class="font-normal text-slate-400">(hanya diisi jika menolak)</span>
                            </label>
                            <input type="text" id="alasan-{{ $periksa->id }}" name="alasan" maxlength="255"
                                   value="{{ $adaError ? old('alasan') : '' }}"
                                   placeholder="Mis. foto buram atau nominal tidak sesuai"
                                   class="input input-bordered w-full rounded-lg {{ $adaError ? 'input-error' : '' }}">
                            @if ($adaError)
                                <p class="text-xs text-red-500">{{ $errors->first('alasan') }}</p>
                            @endif
                            <button type="submit"
                                    class="btn btn-sm btn-outline btn-error rounded-lg">
                                <i class="fas fa-xmark"></i> Tolak Bukti
                            </button>
                        </form>

                        {{-- Konfirmasi --}}
                        <form method="POST" action="{{ route('pembayaran.verifikasi', $periksa->id) }}"
                              onsubmit="return confirm('Konfirmasi pembayaran {{ $rp($periksa->biaya_periksa) }} ini sebagai LUNAS?')">
                            @csrf
                            <button type="submit"
                                    class="btn w-full bg-green-500 hover:bg-green-600 text-white border-none rounded-lg">
                                <i class="fas fa-check"></i> Konfirmasi Lunas ({{ $rp($periksa->biaya_periksa) }})
                            </button>
                        </form>
                    </div>
                @else
                    <div class="modal-action mt-4">
                        <form method="dialog">
                            <button class="btn btn-ghost rounded-lg">Tutup</button>
                        </form>
                    </div>
                @endif
            </div>

            <form method="dialog" class="modal-backdrop"><button>tutup</button></form>
        </dialog>
    @endforeach

    @push('scripts')
        <script>
            // Buka modal otomatis: dari tombol "Tinjau" di dashboard (?buka=ID) atau saat validasi tolak gagal
            const bukaId = @json(old('periksa_id') ?: request()->query('buka'));
            if (bukaId) {
                document.getElementById('modal-bayar-' + parseInt(bukaId, 10))?.showModal();
            }
        </script>
    @endpush

</x-layouts.app>
