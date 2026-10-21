<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
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

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        // Validasi sudah dijalankan oleh UpdatePasswordRequest (sisi server)
        $request->user()->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return back()->with('success', 'Password berhasil diganti.');
    }
}