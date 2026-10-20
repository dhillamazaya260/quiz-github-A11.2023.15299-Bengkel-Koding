<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->intended(route('admin.dashboard')),
                'dokter' => redirect()->intended(route('dokter.dashboard')),
                default => redirect()->intended(route('pasien.dashboard')),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_ktp' => ['required', 'numeric', 'unique:users,no_ktp'],
            'no_hp' => ['required', 'numeric'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Format no_rm: YYYYMM-XXX (XXX = nomor urut pasien pada bulan tersebut)
        $prefix = date('Ym');
        $lastNoRm = User::where('role', 'pasien')
            ->where('no_rm', 'like', $prefix . '-%')
            ->orderBy('no_rm', 'desc')
            ->value('no_rm');

        $urut = $lastNoRm ? ((int) explode('-', $lastNoRm)[1]) + 1 : 1;
        $noRm = $prefix . '-' . str_pad((string) $urut, 3, '0', STR_PAD_LEFT);

        User::create([
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'no_ktp' => $validated['no_ktp'],
            'no_hp' => $validated['no_hp'],
            'no_rm' => $noRm,
            'role' => 'pasien',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}