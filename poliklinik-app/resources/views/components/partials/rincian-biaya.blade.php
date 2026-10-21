@php
    $compact   = $compact ?? false;
    $judul     = $judul ?? 'Rincian Biaya';
    $details   = $periksa->detailPeriksas;
    $totalObat = (int) $details->sum('harga');
    $biayaJasa = (int) $periksa->biaya_jasa;
    $rp        = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
@endphp

@unless ($compact)
<div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
    <div class="card-body p-0">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="font-bold text-slate-800">{{ $judul }}</h3>
        </div>
@endunless

        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Item</th>
                        <th class="px-6 py-3 text-right">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-6 py-3 font-semibold text-slate-800">Jasa Dokter</td>
                        <td class="px-6 py-3 text-right text-slate-700">{{ $rp($biayaJasa) }}</td>
                    </tr>

                    @forelse ($details as $detail)
                        <tr>
                            <td class="px-6 py-3 text-slate-700">
                                {{ $detail->obat?->nama_obat ?? 'Obat (sudah dihapus)' }}
                                @if ($detail->obat?->kemasan)
                                    <span class="text-slate-400">({{ $detail->obat->kemasan }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right text-slate-700">{{ $rp($detail->harga) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-3 text-slate-400">Tidak ada obat yang diresepkan</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="text-sm">
                    <tr class="border-t border-slate-200">
                        <td class="px-6 py-3 text-slate-500">Subtotal Obat ({{ $details->count() }} item)</td>
                        <td class="px-6 py-3 text-right text-slate-500">{{ $rp($totalObat) }}</td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td class="px-6 py-4 font-bold text-slate-800">Total Biaya</td>
                        <td class="px-6 py-4 text-right text-xl font-bold text-[#2d4499]">{{ $rp($periksa->biaya_periksa) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

@unless ($compact)
    </div>
</div>
@endunless