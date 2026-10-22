<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ObatExport;
use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ObatController extends Controller
{
    public function index()
    {
        $obats = Obat::orderBy('nama_obat')->paginate(10)->withQueryString();

        return view('admin.obat.index', compact('obats'));
    }

    public function export()
    {
        return Excel::download(new ObatExport, 'data-obat-' . date('Ymd-His') . '.xlsx');
    }

    public function create()
    {
        return view('admin.obat.create');
    }

    public function store(Request $request)
    {
        Obat::create($this->validated($request));

        return redirect()->route('obat.index')->with('success', 'Data obat berhasil ditambahkan.');
    }

    public function edit(Obat $obat)
    {
        return view('admin.obat.edit', compact('obat'));
    }

    public function update(Request $request, Obat $obat)
    {
        $obat->update($this->validated($request));

        return redirect()->route('obat.index')->with('success', 'Data obat berhasil diperbarui.');
    }

    public function destroy(Obat $obat)
    {
        $obat->delete();

        return redirect()->route('obat.index')->with('success', 'Data obat berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama_obat' => ['required', 'string', 'max:255'],
            'kemasan'   => ['nullable', 'string', 'max:35'],
            'stok'      => ['nullable', 'integer', 'min:0'],
            'harga'     => ['required', 'integer', 'min:0'],
        ]);

        // Kolom stok NOT NULL (default 0): kalau tidak diisi, jangan ditimpa NULL.
        if (($data['stok'] ?? null) === null) {
            unset($data['stok']);
        }

        return $data;
    }
}