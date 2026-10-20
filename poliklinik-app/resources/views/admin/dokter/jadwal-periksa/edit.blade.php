<x-layouts.app title="Edit Jadwal Periksa">
    <div class="card bg-base-100 shadow-sm rounded-2xl max-w-xl">
        <div class="card-body">
            <h1 class="text-xl font-bold text-[#1e2d6b] mb-4">Edit Jadwal Periksa</h1>

            @if ($errors->any())
                <div class="alert alert-error mb-4 rounded-xl text-sm">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('jadwal-periksa.update', $jadwal) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="label"><span class="label-text font-semibold">Hari</span></label>
                    <select name="hari" class="select select-bordered w-full" required>
                        <option value="" disabled>Pilih hari</option>
                        @foreach ($hariList as $hari)
                            <option value="{{ $hari }}" @selected(old('hari', $jadwal->hari) === $hari)>{{ $hari }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label"><span class="label-text font-semibold">Jam Mulai</span></label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label"><span class="label-text font-semibold">Jam Selesai</span></label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}" class="input input-bordered w-full" required>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn bg-[#2d4499] text-white">Simpan</button>
                    <a href="{{ route('jadwal-periksa.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>