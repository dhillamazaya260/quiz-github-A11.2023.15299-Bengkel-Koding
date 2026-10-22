<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PasienExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class PasienController extends Controller
{
    public function index()
    {
        $pasiens = User::where('role', 'pasien')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pasien.index', compact('pasiens'));
    }

    public function export()
    {
        return Excel::download(new PasienExport, 'data-pasien-' . date('Ymd-His') . '.xlsx');
    }

    public function create()
    {
        return view('admin.pasien.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_ktp' => 'required|string|max:16|unique:users,no_ktp',
            'no_hp' => 'required|string|max:15',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'no_ktp' => $request->no_ktp,
            'no_hp' => $request->no_hp,
            'no_rm' => $this->generateNoRm(),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pasien',
        ]);

        return redirect()->route('pasien.index')->with('message', 'Data Pasien berhasil ditambah')->with('type', 'success');
    }

    public function edit(User $pasien)
    {
        return view('admin.pasien.edit', compact('pasien'));
    }

    public function update(Request $request, User $pasien)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_ktp' => 'required|string|max:16|unique:users,no_ktp,' . $pasien->id,
            'no_hp' => 'required|string|max:15',
            'email' => 'required|string|email|unique:users,email,' . $pasien->id,
            // Kosong = password tidak diubah (sama seperti form edit dokter)
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'no_ktp' => $request->no_ktp,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $pasien->update($updateData);

        return redirect()
            ->route('pasien.index')
            ->with('message', 'Data Pasien berhasil diubah')
            ->with('type', 'success');
    }

    public function destroy(User $pasien)
    {
        $pasien->delete();
        return redirect()
            ->route('pasien.index')
            ->with('message', 'Data Pasien berhasil dihapus')
            ->with('type', 'success');
    }

    /** Format no_rm: YYYYMM-XXX (XXX = nomor urut pasien pada bulan tersebut), sama dengan form register. */
    private function generateNoRm(): string
    {
        $prefix = date('Ym');
        $lastNoRm = User::where('role', 'pasien')
            ->where('no_rm', 'like', $prefix . '-%')
            ->orderBy('no_rm', 'desc')
            ->value('no_rm');

        $urut = $lastNoRm ? ((int) explode('-', $lastNoRm)[1]) + 1 : 1;

        return $prefix . '-' . str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }
}