<?php

namespace App\Http\Controllers;

use App\Models\AccountDeletionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The web page Google Play links to for deleting an account: it explains the in-app way and takes a request from
 * anyone who cannot open the app, for an admin to handle.
 */
class AccountDeletionRequestController extends Controller
{
    public function create(): View
    {
        return view('account-deletion');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'note' => ['nullable', 'string', 'max:500', 'not_regex:/[<>]/'],
        ], [], ['email' => 'email', 'note' => 'catatan']);

        if (! $request->filled('website')) {
            AccountDeletionRequest::create([
                'email' => strtolower(trim($data['email'])),
                'note' => $data['note'] ?? null,
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('account-deletion')->with('requested', true);
    }
}
