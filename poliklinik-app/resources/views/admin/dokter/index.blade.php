<x-layouts.app title="Data Dokter">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-3 flex-wrap mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Data Dokter
        </h2>

        <a href="{{ route('dokter.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90
                  text-white rounded-xl text-sm font-semibold transition whitespace-nowrap">
            <i class="fas fa-plus text-sm"></i>
            Tambah Dokter
        </a>
    </div>

    {{-- Card Table --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table w-full">

                    {{-- Table Head --}}
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <tr class="border-b border-slate-200">
                            <th class="px-5 py-4 whitespace-nowrap">Dokter</th>
                            <th class="px-5 py-4 whitespace-nowrap">No. KTP</th>
                            <th class="px-5 py-4 whitespace-nowrap">No. HP</th>
                            <th class="px-5 py-4 whitespace-nowrap">Alamat</th>
                            <th class="px-5 py-4 whitespace-nowrap">Poli</th>
                            <th class="px-5 py-4 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody class="text-sm text-slate-700">

                        @forelse($dokters as $dokter)
                        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition">

                            {{-- Nama + email dalam satu kolom supaya tabel tidak terlalu lebar --}}
                            <td class="px-5 py-4 align-middle">
                                <div class="font-semibold text-slate-800">{{ $dokter->nama }}</div>
                                <div class="text-xs text-slate-400 truncate max-w-[240px]" title="{{ $dokter->email }}">
                                    {{ $dokter->email }}
                                </div>
                            </td>

                            <td class="px-5 py-4 align-middle whitespace-nowrap tabular-nums">
                                {{ $dokter->no_ktp ?? '-' }}
                            </td>

                            <td class="px-5 py-4 align-middle whitespace-nowrap tabular-nums">
                                {{ $dokter->no_hp ?? '-' }}
                            </td>

                            <td class="px-5 py-4 align-middle">
                                <div class="max-w-[220px] line-clamp-2" title="{{ $dokter->alamat }}">
                                    {{ $dokter->alamat ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4 align-middle">
                                <span class="inline-flex items-center whitespace-nowrap px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                    {{ $dokter->poli->nama_poli ?? 'Belum Dipilih' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 align-middle text-right">
                                <div class="inline-flex items-center gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('dokter.edit', $dokter->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-2 whitespace-nowrap
                                              bg-amber-500 hover:bg-amber-600
                                              text-white rounded-lg text-xs font-semibold transition">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                        Edit
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('dokter.destroy', $dokter->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus dokter ini?')"
                                            class="inline-flex items-center gap-1 px-3 py-2 whitespace-nowrap
                                                   bg-red-500 hover:bg-red-600
                                                   text-white rounded-lg text-xs font-semibold transition">
                                            <i class="fas fa-trash text-xs"></i>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-16 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                Belum ada data dokter
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>