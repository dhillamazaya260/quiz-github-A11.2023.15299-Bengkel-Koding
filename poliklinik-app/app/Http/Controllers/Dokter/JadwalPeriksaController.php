<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index()
    {
        $jadwals = JadwalPeriksa::where('id_dokter', Auth::id())
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get();

        return view('admin.dokter.jadwal-periksa.index', compact('jadwals'));
    }

    public function create()
    {
        return view('admin.dokter.jadwal-periksa.create', ['hariList' => self::HARI]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($this->bentrok($data)) {
            return back()->withErrors(['jam_mulai' => 'Jadwal bentrok dengan jadwal lain di hari yang sama.'])->withInput();
        }

        JadwalPeriksa::create($data + ['id_dokter' => Auth::id()]);

        return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil ditambahkan.');
    }

    public function edit(JadwalPeriksa $jadwal_periksa)
    {
        $this->pastikanMilikSendiri($jadwal_periksa);

        return view('admin.dokter.jadwal-periksa.edit', [
            'jadwal' => $jadwal_periksa,
            'hariList' => self::HARI,
        ]);
    }

    public function update(Request $request, JadwalPeriksa $jadwal_periksa)
    {
        $this->pastikanMilikSendiri($jadwal_periksa);
        $data = $this->validated($request);

        if ($this->bentrok($data, $jadwal_periksa->id)) {
            return back()->withErrors(['jam_mulai' => 'Jadwal bentrok dengan jadwal lain di hari yang sama.'])->withInput();
        }

        $jadwal_periksa->update($data);

        return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil diperbarui.');
    }

    public function destroy(JadwalPeriksa $jadwal_periksa)
    {
        $this->pastikanMilikSendiri($jadwal_periksa);
        $jadwal_periksa->delete();

        return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'hari'        => ['required', 'in:' . implode(',', self::HARI)],
            'jam_mulai'   => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);
    }

    /** Cek apakah jam bertumpuk dengan jadwal lain milik dokter ini di hari yang sama. */
    private function bentrok(array $data, ?int $abaikanId = null): bool
    {
        return JadwalPeriksa::where('id_dokter', Auth::id())
            ->where('hari', $data['hari'])
            ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId))
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            ->exists();
    }

    private function pastikanMilikSendiri(JadwalPeriksa $jadwal): void
    {
        abort_if($jadwal->id_dokter !== Auth::id(), 403, 'Ini bukan jadwal Anda.');
    }
}