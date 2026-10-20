<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /** What deleting the account would remove, shown before the owner confirms. */
    public function deletionSummary(Request $request, AccountDeletionService $deletion): JsonResponse
    {
        return response()->json(['data' => $deletion->summary($request->user())]);
    }

    public function destroy(Request $request, AccountDeletionService $deletion): JsonResponse
    {
        $request->validate(['confirm' => ['required', 'in:HAPUS']], [
            'confirm.in' => 'Ketik HAPUS untuk mengonfirmasi penghapusan akun.',
            'confirm.required' => 'Ketik HAPUS untuk mengonfirmasi penghapusan akun.',
        ]);

        $summary = $deletion->summary($request->user());
        if ($summary['unpaid_balance'] > 0 && ! $request->boolean('forfeit_balance')) {
            throw ValidationException::withMessages([
                'forfeit_balance' => 'Masih ada saldo tanda kasih Rp'.number_format($summary['unpaid_balance'], 0, ',', '.').' yang belum dicairkan. Setujui bahwa saldo itu hangus, atau cairkan dulu.',
            ]);
        }

        $deletion->delete($request->user());

        return response()->json(['message' => 'Akun Anda sudah dihapus.']);
    }
}
