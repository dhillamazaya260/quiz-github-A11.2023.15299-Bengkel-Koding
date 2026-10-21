<x-layouts.app title="Profil Saya">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Profil Saya</h2>
    </div>

    <div class="w-full flex flex-col gap-5 max-w-2xl">

        {{-- Informasi akun --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Informasi Akun</h3>
                </div>
                <div class="px-6 py-5 flex flex-col gap-3 text-sm">
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Nama</span>
                        <span class="text-slate-800 font-semibold">{{ $user->nama }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Email</span>
                        <span class="text-slate-800">{{ $user->email }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-36 text-slate-500 font-medium">Role</span>
                        <span class="text-slate-800 capitalize">{{ $user->role }}</span>
                    </div>
                    @if ($user->role === 'pasien' && $user->no_rm)
                        <div class="flex gap-2">
                            <span class="w-36 text-slate-500 font-medium">No. Rekam Medis</span>
                            <span class="text-slate-800">{{ $user->no_rm }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Ganti password --}}
        <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Ganti Password</h3>
                </div>

                <form method="POST" action="{{ route($user->role . '.profile.password') }}"
                      class="px-6 py-5 flex flex-col gap-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="text-sm font-semibold text-slate-700 block mb-2" for="password_lama">Password lama</label>
                        <input type="password" id="password_lama" name="password_lama" required
                               autocomplete="current-password"
                               class="input input-bordered w-full @error('password_lama') input-error @enderror">
                        @error('password_lama')
                            <p class="text-xs text-red-500 mt-1"><i class="fas fa-circle-xmark"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700 block mb-2" for="password">Password baru</label>
                        <input type="password" id="password" name="password" required minlength="8"
                               autocomplete="new-password"
                               class="input input-bordered w-full @error('password') input-error @enderror">
                        @error('password')
                            <p class="text-xs text-red-500 mt-1"><i class="fas fa-circle-xmark"></i> {{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-400 mt-1">Minimal 8 karakter.</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700 block mb-2" for="password_confirmation">Konfirmasi password baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               autocomplete="new-password" class="input input-bordered w-full">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="btn bg-blue-500 hover:bg-blue-600 text-white border-none rounded-lg">
                            <i class="fas fa-key"></i> Simpan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</x-layouts.app>