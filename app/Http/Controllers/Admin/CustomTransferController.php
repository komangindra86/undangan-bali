<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftPayoutRequest;
use App\Models\Invitation;
use App\Services\GiftPayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Transfers of gift money the admin made to a custom order's customer. The customer has no account to request a
 * payout, so the admin records each transfer after making it, with a photo of the receipt.
 */
class CustomTransferController extends Controller
{
    public function store(Request $request, Invitation $invitation, GiftPayoutService $payouts): RedirectResponse
    {
        $this->ensureCustomOrder($request, $invitation);
        abort_if($invitation->isTakenDown(), 422, 'Undangan ini diturunkan, jadi transfer tidak dapat dicatat.');

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'transferred_on' => ['required', 'date', 'before_or_equal:today'],
            'bank_name' => ['required', 'string', 'max:80', 'not_regex:/[<>]/'],
            'account_number' => ['required', 'string', 'max:50', 'regex:/^[0-9 .-]+$/'],
            'account_holder_name' => ['required', 'string', 'max:120', 'not_regex:/[<>]/'],
            'transfer_reference' => ['nullable', 'string', 'max:120', 'not_regex:/[<>]/'],
            'proof' => ['required', 'image', 'max:8192'],
        ], [
            'account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
        ], [
            'amount' => 'nominal',
            'transferred_on' => 'tanggal transfer',
            'bank_name' => 'bank',
            'account_number' => 'nomor rekening',
            'account_holder_name' => 'nama pemilik rekening',
            'transfer_reference' => 'nomor referensi',
            'proof' => 'foto bukti transfer',
        ]);

        $proofPath = $request->file('proof')->store('invitations/transfers', 'public');
        try {
            $payout = $payouts->recordTransfer(
                $request->user('web'),
                $invitation,
                $data,
                (int) $data['amount'],
                Carbon::parse($data['transferred_on'], 'Asia/Makassar')->setTimeFrom(now()),
                $proofPath,
                $data['transfer_reference'] ?? null,
            );
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($proofPath);
            throw $exception;
        }

        return redirect()->route('admin.custom.edit', $invitation)
            ->with('message', 'Transfer Rp'.number_format($payout->net_amount, 0, ',', '.').' tercatat dan tampil di halaman pantau pelanggan.')
            ->with('transfer_whatsapp', $this->whatsappLink($invitation, $payout));
    }

    public function destroy(Request $request, Invitation $invitation, GiftPayoutRequest $payout): RedirectResponse
    {
        $this->ensureCustomOrder($request, $invitation);
        // Only transfers recorded here can be taken back; payouts requested in the app go through the payout page.
        abort_unless($payout->invitation_id === $invitation->id && $payout->payout_account_id === null && $payout->proof_path, 404);

        DB::transaction(function () use ($payout) {
            $payout->items()->delete();
            $payout->delete();
        });
        Storage::disk('public')->delete($payout->proof_path);

        return redirect()->route('admin.custom.edit', $invitation)
            ->with('message', 'Catatan transfer dihapus; nominalnya kembali menjadi saldo yang belum ditransfer.');
    }

    /** A ready-to-send WhatsApp message telling the customer about the transfer, to their number when known. */
    private function whatsappLink(Invitation $invitation, GiftPayoutRequest $payout): string
    {
        $order = $invitation->order_details ?? [];
        $digits = preg_replace('/\D/', '', (string) ($order['customer_whatsapp'] ?? ''));
        $number = match (true) {
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
        $greeting = filled($order['customer_name'] ?? null) ? 'Halo '.$order['customer_name'].', ' : 'Halo, ';
        $text = $greeting.'tanda kasih untuk undangan '.$invitation->display_name.' sebesar Rp'.number_format($payout->net_amount, 0, ',', '.')
            .' sudah kami transfer ke '.$payout->bank_name.' '.$payout->masked_account_number.'.'
            .($invitation->monitor_token ? ' Rincian dan bukti transfer: '.route('gifts.monitor', $invitation->monitor_token) : '');

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }

    private function ensureCustomOrder(Request $request, Invitation $invitation): void
    {
        abort_unless($request->user('web')?->isAdmin(), 403);
        abort_unless($invitation->user?->isAdmin() && $invitation->invitation_type === 'wedding', 404);
    }
}
