<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Gift - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Transaksi Gift',
        'subtitle' => 'Setiap pembayaran gift beserta undangan dan pemiliknya, untuk dicocokkan dengan catatan payment gateway.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        <section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <article class="bg-stone-900 border border-emerald-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Gift lunas</p>
                <p class="text-emerald-300 font-serif text-3xl mt-2">Rp{{ number_format($summary['paid_amount'], 0, ',', '.') }}</p>
                <p class="text-stone-500 text-sm mt-2">{{ $summary['paid_count'] }} transaksi</p>
            </article>
            <article class="bg-stone-900 border border-sky-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Lunas hari ini</p>
                <p class="text-sky-300 font-serif text-3xl mt-2">Rp{{ number_format($summary['paid_today'], 0, ',', '.') }}</p>
            </article>
            <article class="bg-stone-900 border border-amber-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Menunggu pembayaran</p>
                <p class="text-amber-300 font-serif text-3xl mt-2">{{ $summary['pending_count'] }}</p>
            </article>
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Cara mencocokkan</p>
                <p class="text-stone-300 text-sm mt-2">Di iPaymu, referensi gift berawalan <span class="font-mono text-amber-200">WGIFT-</span> dan pesanan landing berawalan <span class="font-mono text-amber-200">BS-</span>.</p>
            </article>
        </section>

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-5 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <nav class="flex flex-wrap gap-2 text-sm">
                @foreach (['paid' => 'Lunas', 'pending' => 'Menunggu', 'expired' => 'Kedaluwarsa', 'failure' => 'Gagal', 'refunded' => 'Refund', 'all' => 'Semua'] as $value => $label)
                    <a
                        href="{{ route('admin.gifts.index', array_filter(['status' => $value, 'q' => $search])) }}"
                        class="rounded-full border px-4 py-2 {{ $activeStatus === $value ? 'border-amber-400 bg-amber-400 text-stone-950 font-semibold' : 'border-stone-700 text-stone-300 hover:border-amber-400' }}"
                    >{{ $label }}</a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('admin.gifts.index') }}" class="flex gap-2">
                <input type="hidden" name="status" value="{{ $activeStatus }}">
                <input name="q" value="{{ $search }}" placeholder="Cari referensi, ID transaksi, tamu, undangan" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-2 text-stone-100 w-72 max-w-full">
                <button class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-4 py-2 hover:bg-amber-300">Cari</button>
            </form>
        </section>

        <section class="bg-stone-900 border border-stone-800 rounded-3xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-stone-400 border-b border-stone-800">
                    <tr>
                        <th class="p-4 font-medium">Waktu</th>
                        <th class="p-4 font-medium">Undangan dan pemilik</th>
                        <th class="p-4 font-medium">Tamu</th>
                        <th class="p-4 font-medium text-right">Nominal</th>
                        <th class="p-4 font-medium">Status</th>
                        <th class="p-4 font-medium">Referensi dan gateway</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gifts as $gift)
                        @php
                            $invitation = $gift->invitation;
                            $provider = match (true) {
                                str_starts_with((string) $gift->payment_type, 'ipaymu') => 'iPaymu',
                                str_starts_with((string) $gift->payment_type, 'xendit') => 'Xendit',
                                default => 'Midtrans',
                            };
                            $statusClass = [
                                'paid' => 'bg-emerald-500/15 text-emerald-200 border-emerald-500/40',
                                'pending' => 'bg-amber-500/15 text-amber-200 border-amber-500/40',
                                'refunded' => 'bg-red-500/15 text-red-200 border-red-500/40',
                            ][$gift->transaction_status] ?? 'bg-stone-800 text-stone-300 border-stone-700';
                        @endphp
                        <tr class="border-b border-stone-800/70 align-top">
                            <td class="p-4 whitespace-nowrap text-stone-300">
                                {{ ($gift->paid_at ?? $gift->created_at)?->format('d/m/Y H:i') }}
                                <p class="text-stone-500 text-xs">{{ $gift->paid_at ? 'dibayar' : 'dibuat' }}</p>
                            </td>
                            <td class="p-4">
                                <p class="font-semibold">{{ $invitation?->display_name ?? 'Undangan terhapus' }} <span class="text-stone-500 font-normal">#{{ $gift->invitation_id }}</span></p>
                                <p class="text-stone-400">{{ $invitation?->gift_label }}@if ($invitation?->slug) · <a class="text-amber-300 hover:underline" href="{{ route('invitations.public', $invitation->slug) }}" target="_blank" rel="noopener">/u/{{ $invitation->slug }}</a>@endif</p>
                                <p class="text-stone-500">{{ $invitation?->user?->name ?? 'Tanpa pemilik' }} {{ $invitation?->user ? '('.$invitation->user->email.')' : '' }}</p>
                            </td>
                            <td class="p-4">
                                <p>{{ $gift->guest_name }}</p>
                                @if ($gift->guest_phone)<p class="text-stone-500">{{ $gift->guest_phone }}</p>@endif
                            </td>
                            <td class="p-4 text-right whitespace-nowrap font-semibold text-amber-200">Rp{{ number_format($gift->gift_amount, 0, ',', '.') }}</td>
                            <td class="p-4"><span class="rounded-full border px-3 py-1 text-xs uppercase tracking-wider {{ $statusClass }}">{{ $gift->transaction_status }}</span></td>
                            <td class="p-4">
                                <p class="font-mono text-xs break-all">{{ $gift->order_id }}</p>
                                <p class="text-stone-500 text-xs mt-1">{{ $provider }}@if ($gift->midtrans_transaction_id) · ID {{ $gift->midtrans_transaction_id }}@endif</p>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-stone-400">Tidak ada transaksi pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="mt-6">{{ $gifts->links() }}</div>
    </main>
</body>
</html>
