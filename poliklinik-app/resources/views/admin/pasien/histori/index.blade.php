<x-layouts.app title="Riwayat Pendaftaran Poli">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Pendaftaran Poli</h2>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <tr class="border-b border-slate-200">
                            <th class="px-5 py-4 whitespace-nowrap">Tanggal Daftar</th>
                            <th class="px-5 py-4 whitespace-nowrap">Poli / Dokter</th>
                            <th class="px-5 py-4 whitespace-nowrap">Jadwal</th>
                            <th class="px-5 py-4 whitespace-nowrap text-center">No. Antrian</th>
                            <th class="px-5 py-4 whitespace-nowrap">Status</th>
                            <th class="px-5 py-4 whitespace-nowrap text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($riwayat as $daftar)
                            @php
                                $jadwal = $daftar->jadwalPeriksa;
                                $dokter = $jadwal?->dokter;
                                $sudahDiperiksa = $daftar->periksa !== null;
                            @endphp
                            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition">

                                {{-- Tanggal & jam dipisah dua baris agar tidak terpotong --}}
                                <td class="px-5 py-4 align-middle whitespace-nowrap">
                                    <div class="text-slate-700">{{ $daftar->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $daftar->created_at->format('H:i') }}</div>
                                </td>

                                <td class="px-5 py-4 align-middle">
                                    <div class="font-semibold text-slate-800">{{ $dokter?->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $dokter?->poli?->nama_poli ?? '-' }}</div>
                                </td>

                                {{-- Hari + tanggal di baris atas, jam di baris bawah --}}
                                <td class="px-5 py-4 align-middle whitespace-nowrap">
                                    <div class="text-slate-700">
                                        {{ $jadwal?->hari ?? '-' }}{{ $daftar->tanggal_jadwal ? ', ' . $daftar->tanggal_jadwal->format('d/m/Y') : '' }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        {{ substr($jadwal?->jam_mulai ?? '', 0, 5) }}&ndash;{{ substr($jadwal?->jam_selesai ?? '', 0, 5) }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-middle text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2.75rem] h-8 px-2 rounded-lg
                                                 bg-slate-100 text-slate-700 font-bold tabular-nums">
                                        {{ str_pad($daftar->no_antrian, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 align-middle">
                                    @if ($sudahDiperiksa)
                                        <span class="badge badge-soft badge-success whitespace-nowrap gap-1">
                                            <i class="fas fa-circle-check text-xs"></i> Sudah diperiksa
                                        </span>
                                    @else
                                        <span class="badge badge-soft badge-warning whitespace-nowrap gap-1">
                                            <i class="fas fa-hourglass-half text-xs"></i> Menunggu
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 align-middle text-right">
                                    @if ($sudahDiperiksa)
                                        <a href="{{ route('pasien.histori.show', $daftar->id) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap
                                                  text-xs font-semibold text-indigo-600 bg-indigo-50
                                                  hover:bg-indigo-100 transition">
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

            @include('components.partials.paginasi', ['paginator' => $riwayat])
        </div>
    </div>

</x-layouts.app>