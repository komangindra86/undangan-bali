<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PremiumTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('admin.templates.index', [
            'templates' => InvitationTemplate::where('is_premium', true)
                ->withCount('invitations')
                ->orderBy('invitation_type')
                ->orderBy('id')
                ->get(),
            'accounts' => User::where('can_use_premium_templates', true)
                ->withCount('invitations')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function grant(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', strtolower(trim($data['email'])))->where('role', 'user')->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Belum ada akun aplikasi dengan email itu. Daftar dulu di aplikasi, lalu tambahkan di sini.',
            ]);
        }

        $user->forceFill(['can_use_premium_templates' => true])->save();

        return back()->with('message', $user->name.' sekarang dapat memakai template premium di aplikasi.');
    }

    public function revoke(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin($request);

        $user->forceFill(['can_use_premium_templates' => false])->save();

        return back()->with('message', 'Akses template premium untuk '.$user->name.' dicabut. Undangan yang sudah tayang tidak berubah.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
    }
}
