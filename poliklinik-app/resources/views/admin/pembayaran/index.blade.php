<x-layouts.app title="Verifikasi Pembayaran">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Verifikasi Pembayaran</h2>
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
                                <td class="px-6 py-4 text-slate-500">
                                    Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge {{ $periksa->status_bayar_badge }}">{{ $periksa->status_bayar_label }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @if ($periksa->bukti_bayar)
                                            <a href="{{ route('bukti-bayar.show', $periksa->id) }}" target="_blank"
                                               class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-image"></i> Bukti
                                            </a>
                                        @endif

                                        @if ($periksa->status_bayar === \App\Models\Periksa::BAYAR_MENUNGGU)
                                            <form method="POST" action="{{ route('pembayaran.verifikasi', $periksa->id) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm bg-green-500 hover:bg-green-600 text-white border-none rounded-lg px-4">
                                                    <i class="fas fa-check"></i> Verifikasi (Lunas)
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-14 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <span>Belum ada data pembayaran</span>
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
