<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password'      => ['required', 'string', 'min:6', 'confirmed', 'different:password_lama'],
        ], [
            'password_lama.required'         => 'Password lama wajib diisi.',
            'password_lama.current_password' => 'Password lama tidak sesuai.',
            'password.required'              => 'Password baru wajib diisi.',
            'password.min'                   => 'Password baru minimal 6 karakter.',
            'password.confirmed'             => 'Konfirmasi password baru tidak cocok.',
            'password.different'             => 'Password baru harus berbeda dari password lama.',
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return back()->with('success', 'Password berhasil diganti.');
    }
}