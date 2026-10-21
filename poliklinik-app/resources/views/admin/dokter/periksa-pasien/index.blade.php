<x-layouts.app title="Periksa Pasien">

    <div class="flex items-center justify-between mb-5">
        <h1 class="text-2xl font-bold text-slate-800">Daftar Periksa Pasien</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Nama Pasien</th>
                        <th class="px-5 py-3">Keluhan</th>
                        <th class="px-5 py-3 text-center">No. Antrian</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarPasien as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3 text-slate-500">{{ $item->id }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $item->pasien->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->keluhan }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                                    {{ $item->no_antrian }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end">
                                    @if ($item->periksa)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                            <i class="fas fa-circle-check"></i> Sudah Diperiksa
                                        </span>
                                    @else
                                        <a href="{{ route('periksa-pasien.create', $item->id) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition">
                                            <i class="fas fa-stethoscope"></i> Periksa
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada pasien yang mendaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            // Antrian baru masuk -> muat ulang tabel (setelah toast sempat terbaca)
            document.addEventListener('notifikasi:masuk', function (e) {
                if (e.detail && e.detail.jenis === 'antrian_baru') {
                    setTimeout(function () { window.location.reload(); }, 2500);
                }
            });
        </script>
    @endpush

</x-layouts.app>
