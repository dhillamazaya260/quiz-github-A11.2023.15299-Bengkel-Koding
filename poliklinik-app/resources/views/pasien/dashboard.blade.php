<x-layouts.app title="Dashboard Pasien">
    <div class="card bg-base-100 shadow-sm rounded-2xl">
        <div class="card-body">
            <h1 class="text-2xl font-bold text-[#1e2d6b]">Selamat datang, {{ auth()->user()->nama }}!</h1>
            <p class="text-slate-500">Ini dashboard Pasien.</p>
        </div>
    </div>
</x-layouts.app>