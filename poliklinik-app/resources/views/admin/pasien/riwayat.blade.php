<x-layouts.app title="Riwayat & Pembayaran">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat &amp; Pembayaran</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4 rounded-xl shadow-sm">
            <i class="fas fa-circle-xmark"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

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
                            <th class="px-6 py-4 text-right">Pembayaran</th>
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
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($periksa->status_bayar === \App\Models\Periksa::BAYAR_BELUM)
                                        <form method="POST" action="{{ route('pasien.bayar', $periksa->id) }}"
                                              enctype="multipart/form-data"
                                              class="flex items-center justify-end gap-2">
                                            @csrf
                                            <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png" required
                                                   class="file-input file-input-bordered file-input-sm w-48">
                                            <button type="submit"
                                                    class="btn btn-sm bg-blue-500 hover:bg-blue-600 text-white border-none rounded-lg">
                                                <i class="fas fa-upload"></i> Upload
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('bukti-bayar.show', $periksa->id) }}" target="_blank"
                                           class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-image"></i> Lihat bukti
                                        </a>
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

</x-layouts.app>
