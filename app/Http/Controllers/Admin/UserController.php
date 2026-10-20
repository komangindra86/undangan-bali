<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * App users: find an account, change its email so an owner who signed up without a Google address can sign in with
 * Google, delete an account, and handle deletion requests sent from the website.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);
        $search = trim($request->string('q')->toString());

        return view('admin.users.index', [
            'users' => User::where('role', 'user')
                ->whereNull('account_deleted_at')
                ->when($search !== '', function ($query) use ($search) {
                    $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';
                    $query->where(fn ($query) => $query->where('email', 'like', $like)->orWhere('name', 'like', $like));
                })
                ->withCount(['invitations' => fn ($query) => $query->whereNull('owner_deleted_at')])
                ->latest()
                ->paginate(30)
                ->withQueryString(),
            'search' => $search,
            'requests' => AccountDeletionRequest::where('status', 'open')->with('user:id,name,email')->latest()->get(),
        ]);
    }

    public function updateEmail(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->ensureAppUser($user);
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], ['email.unique' => 'Email itu sudah dipakai akun lain.'], ['email' => 'email']);

        $old = $user->email;
        $user->forceFill(['email' => $data['email']])->save();

        return back()->with('message', 'Email '.$old.' diganti menjadi '.$user->email.'. Pemilik kini bisa masuk dengan Google memakai email itu.');
    }

    public function destroy(Request $request, User $user, AccountDeletionService $deletion): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->ensureAppUser($user);
        $request->validate(['confirm' => ['required', 'in:HAPUS']], ['confirm.*' => 'Ketik HAPUS untuk mengonfirmasi.']);

        $email = $user->email;
        $deletion->delete($user);
        AccountDeletionRequest::where('email', $email)->where('status', 'open')->update(['status' => 'done', 'handled_at' => now()]);

        return back()->with('message', 'Akun '.$email.' sudah dihapus.');
    }

    public function closeRequest(Request $request, AccountDeletionRequest $deletionRequest): RedirectResponse
    {
        $this->ensureAdmin($request);
        $deletionRequest->update(['status' => 'dismissed', 'handled_at' => now()]);

        return back()->with('message', 'Permintaan dari '.$deletionRequest->email.' ditandai selesai tanpa menghapus akun.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
    }

    private function ensureAppUser(User $user): void
    {
        abort_unless($user->role === 'user' && ! $user->account_deleted_at, 404);
    }
}
