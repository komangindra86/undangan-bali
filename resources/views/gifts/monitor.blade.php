@php
    $whatsapp = preg_replace('/\D+/', '', (string) config('services.custom_invitation.whatsapp_number'));
    $rupiah = fn (int $amount) => 'Rp'.number_format($amount, 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>Tanda Kasih Masuk - {{ $invitation->display_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #150f0b; color: #f7ead2; font-family: Arial, sans-serif; margin: 0; min-height: 100vh; padding: 24px 16px 48px; }
        .card { background: linear-gradient(145deg, #251913, #17100d); border: 1px solid #9d7444; border-radius: 28px; box-shadow: 0 24px 80px #0008; margin: 0 auto; max-width: 560px; padding: 28px 22px; }
        .label { color: #d5a75e; font-size: 11px; letter-spacing: .32em; margin: 0 0 12px; text-transform: uppercase; }
        h1 { font-family: Georgia, serif; font-size: 28px; font-weight: 400; line-height: 1.25; margin: 0; }
        p { color: #bba98f; font-size: 15px; line-height: 1.7; margin: 12px 0 0; }
        .summary { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; margin: 22px 0 0; }
        .summary div { background: #120c09; border: 1px solid #4a382a; border-radius: 18px; padding: 16px; }
        .summary span { color: #a2927a; display: block; font-size: 12px; }
        .summary strong { color: #f3d9a4; display: block; font-family: Georgia, serif; font-size: clamp(17px, 5.2vw, 24px); font-weight: 400; margin-top: 6px; white-space: nowrap; }
        ul { list-style: none; margin: 24px 0 0; padding: 0; }
        li { border-top: 1px solid #3a2b20; padding: 16px 0; }
        .row { align-items: baseline; display: flex; gap: 12px; justify-content: space-between; }
        .row strong { font-size: 16px; overflow-wrap: anywhere; }
        .row span { color: #f3d9a4; flex: none; font-size: 16px; }
        .when { color: #806f5c; font-size: 12px; margin: 4px 0 0; }
        .message { color: #d8c2a0; font-size: 14px; font-style: italic; line-height: 1.6; margin: 8px 0 0; overflow-wrap: anywhere; }
        .note { background: #120c09; border: 1px dashed #5d4733; border-radius: 16px; font-size: 13px; margin: 24px 0 0; padding: 14px 16px; }
        h2 { color: #e9d3ab; font-family: Georgia, serif; font-size: 20px; font-weight: 400; margin: 30px 0 0; }
        .transfer { align-items: center; display: grid; gap: 12px; grid-template-columns: 1fr auto; }
        .transfer img { border: 1px solid #4a382a; border-radius: 12px; height: 56px; object-fit: cover; width: 56px; }
        .detail { color: #a2927a; font-size: 12px; line-height: 1.6; margin: 4px 0 0; }
        .button { background: #d5a75e; border-radius: 999px; color: #1b120c; display: inline-block; font-size: 14px; font-weight: 700; margin: 14px 0 0; padding: 12px 22px; text-decoration: none; }
        @media (max-width: 360px) { .summary { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="card">
        <p class="label">Tanda Kasih Masuk</p>
        <h1>{{ $invitation->display_name }}</h1>
        <p>Daftar tanda kasih dari tamu yang pembayarannya sudah berhasil. Muat ulang halaman ini untuk melihat yang terbaru.</p>

        <div class="summary">
            <div><span>Total diterima</span><strong>{{ $rupiah($total) }}</strong></div>
            <div><span>Jumlah pengirim</span><strong>{{ $gifts->count() }}</strong></div>
        </div>

        @if ($gifts->isEmpty())
            <p class="note">Belum ada tanda kasih yang masuk.</p>
        @else
            <ul>
                @foreach ($gifts as $gift)
                    <li>
                        <div class="row"><strong>{{ $gift->guest_name }}</strong><span>{{ $rupiah((int) $gift->gift_amount) }}</span></div>
                        <p class="when">{{ $gift->paid_at?->timezone('Asia/Makassar')->translatedFormat('d F Y, H:i') }} WITA</p>
                        @if ($gift->message)
                            <p class="message">"{{ $gift->message }}"</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <h2>Transfer ke rekening Anda</h2>
        <div class="summary">
            <div><span>Sudah ditransfer</span><strong>{{ $rupiah((int) $summary['paid_out']) }}</strong></div>
            <div><span>Belum ditransfer</span><strong>{{ $rupiah((int) $summary['available_balance']) }}</strong></div>
        </div>
        <p class="detail">{{ $summary['payout_fee_description'] }} Nominal "belum ditransfer" masih sebelum dipotong biaya.</p>
        @if ($transfers->isEmpty())
            <p class="note">Belum ada transfer ke rekening Anda.</p>
        @else
            <ul>
                @foreach ($transfers as $transfer)
                    <li class="transfer">
                        <div>
                            <div class="row"><strong>{{ $transfer->paid_at?->timezone('Asia/Makassar')->translatedFormat('d F Y') }}</strong><span>{{ $rupiah((int) $transfer->net_amount) }}</span></div>
                            <p class="detail">Ke {{ $transfer->bank_name }} {{ $transfer->masked_account_number }} a/n {{ $transfer->account_holder_name }}. Dari {{ $rupiah((int) $transfer->amount) }}, biaya platform {{ $rupiah((int) $transfer->platform_fee) }}.</p>
                        </div>
                        @if ($transfer->proof_path)
                            <a href="{{ Storage::url($transfer->proof_path) }}" target="_blank" rel="noopener" aria-label="Lihat bukti transfer"><img src="{{ Storage::url($transfer->proof_path) }}" alt="Bukti transfer"></a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="note">
            Dana diteruskan oleh admin Undangan Bali Santih ke rekening yang Anda berikan saat memesan.
            Simpan link halaman ini untuk Anda sendiri; siapa pun yang memilikinya dapat melihat daftar ini.
        </p>
        @if ($whatsapp)
            <a class="button" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">Hubungi admin via WhatsApp</a>
        @endif
    </main>
</body>
</html>
