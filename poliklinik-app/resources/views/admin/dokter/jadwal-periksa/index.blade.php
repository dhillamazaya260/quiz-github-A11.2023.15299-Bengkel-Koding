<x-layouts.app title="Jadwal Periksa">

    <div class="flex items-center justify-between mb-5">
        <h1 class="text-2xl font-bold text-slate-800">Jadwal Periksa</h1>

        <a href="{{ route('jadwal-periksa.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow transition">
            <i class="fas fa-plus text-xs"></i>
            Tambah Jadwal
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="px-5 py-3">Hari</th>
                        <th class="px-5 py-3">Jam Mulai</th>
                        <th class="px-5 py-3">Jam Selesai</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($jadwals as $jadwal)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3">
                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                                    {{ $jadwal->hari }}
                                </span>
                            </td>
                            <td class="px-5 py-3 font-semibold text-slate-700">{{ substr($jadwal->jam_mulai, 0, 5) }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-700">{{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('jadwal-periksa.edit', $jadwal) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition">
                                        <i class="fas fa-pen-to-square"></i> Edit
                                    </a>

                                    <form action="{{ route('jadwal-periksa.destroy', $jadwal) }}" method="POST"
                                          onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-red-500 hover:bg-red-600 text-white text-xs font-semibold transition">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada jadwal periksa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>