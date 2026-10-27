<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poli;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AdminPoliController extends Controller
{
    public function index()
    {
        $polis = Poli::orderBy('id')->paginate(10)->withQueryString();
        return view('admin.polis.index', compact('polis'));
    }

    public function create()
    {
        return view('admin.polis.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_poli' => 'required|string|max:25',
            'keterangan' => 'nullable',
        ]);

        Poli::create($validated);
        return redirect()->route('polis.index')->with('success', 'Poli berhasil di tambahkan')->with('type', 'success');
    }

    public function edit(string $id)
    {
        $poli = Poli::findOrFail($id);
        return view('admin.polis.edit', compact('poli'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_poli' => 'required|string|max:25',
            'keterangan' => 'nullable',
        ]);

        $poli = Poli::findOrFail($id);
        $poli->update($validated);
        return redirect()->route('polis.index')->with('success', 'Polis berhasil di update');
    }

    public function destroy(string $id)
    {
        $poli = Poli::findOrFail($id);

        try {
            $poli->delete();
        } catch (QueryException $e) {
            // 23000 = pelanggaran foreign key (poli masih dipakai dokter)
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }

            return redirect()->route('polis.index')
                ->with('error', 'Poli tidak dapat dihapus karena masih dipakai oleh dokter.');
        }

        return redirect()->route('polis.index')->with('success', 'Polis Berhasil di hapus !');
    }
}