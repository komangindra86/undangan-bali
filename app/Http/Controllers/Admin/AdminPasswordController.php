<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminPasswordController extends Controller
{
    public function edit(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('admin.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers(),
                'different:current_password',
            ],
        ], [
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ], [
            'current_password' => 'password lama',
            'password' => 'password baru',
        ]);

        $request->user('web')->update([
            'password' => Hash::make($data['password']),
        ]);

        $request->session()->regenerate();

        return back()->with('message', 'Password admin berhasil diperbarui.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
    }
}
