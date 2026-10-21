<x-layouts.app title="Detail Pemeriksaan">

    @php
        $jadwal  = $daftar->jadwalPeriksa;
        $dokter  = $jadwal?->dokter;
        $periksa = $daftar->periksa;
    @endphp

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pasien.histori.index') }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Detail Pemeriksaan</h2>
    </div>

    <div class="w-full flex flex-col gap-5">

        {{-- Informasi pendaftaran --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Informasi Pendaftaran</h3>
                    @if ($periksa)
                        <span class="badge badge-success">Sudah diperiksa</span>
                    @else
                        <span class="badge badge-warning">Menunggu</span>
                    @endif
                </div>
                <div class="px-6 py-5 flex flex-col gap-3 text-sm">
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Tanggal Daftar</span>
                        <span class="text-slate-800">{{ $daftar->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Poli</span>
                        <span class="text-slate-800">{{ $dokter?->poli?->nama_poli ?? '-' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Dokter</span>
                        <span class="text-slate-800">{{ $dokter?->nama ?? '-' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Jadwal</span>
                        <span class="text-slate-800">
                            {{ $jadwal?->hari ?? '-' }}{{ $daftar->tanggal_jadwal ? ', ' . $daftar->tanggal_jadwal->format('d/m/Y') : '' }},
                            {{ substr($jadwal?->jam_mulai ?? '', 0, 5) }}&ndash;{{ substr($jadwal?->jam_selesai ?? '', 0, 5) }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">No. Antrian</span>
                        <span class="text-slate-800 font-semibold">{{ str_pad($daftar->no_antrian, 3, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Keluhan</span>
                        <span class="text-slate-800">{{ $daftar->keluhan }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($periksa)
            {{-- Hasil pemeriksaan --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="font-bold text-slate-800">Hasil Pemeriksaan</h3>
                    </div>
                    <div class="px-6 py-5 flex flex-col gap-3 text-sm">
                        <div class="flex gap-2">
                            <span class="w-36 text-slate-500 font-medium">Tanggal Periksa</span>
                            <span class="text-slate-800">{{ $periksa->tgl_periksa->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex gap-2">
                            <span class="w-36 text-slate-500 font-medium">Catatan Dokter</span>
                            <span class="text-slate-800">{{ $periksa->catatan ?: 'Tidak ada catatan' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Obat --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="font-bold text-slate-800">Obat yang Diresepkan</h3>
                    </div>
                    @if ($periksa->detailPeriksas->count() > 0)
                        <table class="table table-zebra w-full">
                            <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">#</th>
                                    <th class="px-6 py-4">Nama Obat</th>
                                    <th class="px-6 py-4">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($periksa->detailPeriksas as $i => $detail)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 text-slate-500">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4 font-semibold text-slate-800">{{ $detail->obat->nama_obat }}</td>
                                        <td class="px-6 py-4 text-slate-500">Rp {{ number_format($detail->obat->harga, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="px-6 py-5 text-sm text-slate-400">Tidak ada obat yang diresepkan</div>
                    @endif
                </div>
            </div>

            {{-- Total biaya + status bayar --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body px-6 py-5 flex flex-row items-center justify-between flex-wrap gap-3">
                    <div>
                        <div class="font-bold text-slate-700">Total Biaya Periksa</div>
                        <span class="badge {{ $periksa->status_bayar_badge }} mt-1">{{ $periksa->status_bayar_label }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-2xl font-bold text-[#2d4499]">
                            Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                        </span>
                        <a href="{{ route('pasien.riwayat') }}"
                           class="btn btn-sm bg-blue-500 hover:bg-blue-600 text-white border-none rounded-lg">
                            <i class="fas fa-wallet"></i> Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body px-6 py-8 text-center text-slate-400 text-sm">
                    <i class="fas fa-hourglass-half text-2xl mb-2"></i>
                    Pemeriksaan belum dilakukan. Hasil pemeriksaan akan muncul di sini setelah dokter selesai memeriksa.
                </div>
            </div>
        @endif

    </div>

</x-layouts.app>