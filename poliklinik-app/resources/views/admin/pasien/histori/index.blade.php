<x-layouts.app title="Riwayat Pendaftaran Poli">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Pendaftaran Poli</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Tanggal Daftar</th>
                            <th class="px-6 py-4">Poli / Dokter</th>
                            <th class="px-6 py-4">Jadwal</th>
                            <th class="px-6 py-4">No. Antrian</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayat as $daftar)
                            @php
                                $jadwal = $daftar->jadwalPeriksa;
                                $dokter = $jadwal?->dokter;
                                $sudahDiperiksa = $daftar->periksa !== null;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $daftar->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $dokter?->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $dokter?->poli?->nama_poli ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $jadwal?->hari ?? '-' }},
                                    {{ substr($jadwal?->jam_mulai ?? '', 0, 5) }}&ndash;{{ substr($jadwal?->jam_selesai ?? '', 0, 5) }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    {{ str_pad($daftar->no_antrian, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($sudahDiperiksa)
                                        <span class="badge badge-success">Sudah diperiksa</span>
                                    @else
                                        <span class="badge badge-warning">Menunggu</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
    @if ($sudahDiperiksa)
        <a href="{{ route('pasien.histori.show', $daftar->id) }}"
           class="btn btn-sm btn-ghost text-indigo-600 hover:text-indigo-800 rounded-lg">
            <i class="fas fa-eye"></i> Detail
        </a>
    @else
        <span class="text-slate-300">&mdash;</span>
    @endif
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-14 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <span>Belum ada riwayat pendaftaran poli</span>
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