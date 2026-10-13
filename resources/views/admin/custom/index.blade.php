<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Custom - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Pesanan Custom',
        'subtitle' => 'Undangan yang Anda buatkan untuk pelanggan, termasuk dengan template premium yang tidak ada di aplikasi.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif

        @if ($errors->any())
            <p class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">{{ $errors->first() }}</p>
        @endif

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-6 mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-serif text-xl">Template premium</h2>
                    <p class="text-stone-400 text-sm mt-1">Link contoh berisi data contoh dan bisa dikirim ke calon pelanggan lewat WhatsApp.</p>
                </div>
                <a href="{{ route('admin.custom.create') }}" class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-5 py-3 text-center hover:bg-amber-300">Buat undangan pelanggan</a>
            </div>
            <div class="mt-4 grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($premiumTemplates as $template)
                    @php $previewUrl = route('templates.preview', $template); @endphp
                    <article class="rounded-2xl bg-stone-950 border border-stone-800 p-4">
                        <p class="font-semibold text-lg">{{ $template->name }}</p>
                        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="inline-block mt-1 text-amber-300 hover:underline">Buka contoh</a>
                        <input readonly value="{{ $previewUrl }}" onclick="this.select()" class="mt-3 w-full bg-stone-900 border border-stone-800 rounded-xl px-3 py-2 text-xs text-stone-300">
                    </article>
                @empty
                    <p class="text-stone-500 text-sm">Belum ada template premium.</p>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            @forelse ($invitations as $invitation)
                @php
                    $takenDown = $invitation->isTakenDown();
                    $live = $invitation->status === 'published' && ! $takenDown;
                    $order = $invitation->order_details ?? [];
                @endphp
                <article class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold text-lg">{{ $invitation->display_name }}</p>
                            <span class="rounded-full border px-3 py-1 text-xs uppercase tracking-wider {{ $live ? 'bg-emerald-500/15 text-emerald-200 border-emerald-500/40' : ($takenDown ? 'bg-red-500/15 text-red-200 border-red-500/40' : 'bg-stone-800 text-stone-300 border-stone-700') }}">{{ $takenDown ? 'Diturunkan' : ($live ? 'Tayang' : 'Draft') }}</span>
                            @if ($invitation->template?->is_premium)
                                <span class="rounded-full border border-amber-500/40 bg-amber-500/15 text-amber-200 px-3 py-1 text-xs">Premium</span>
                            @endif
                        </div>
                        <p class="text-stone-400 text-sm mt-2">
                            {{ $invitation->template?->name ?? 'Template tidak tersedia' }}
                            <span class="text-stone-600 mx-2">|</span>
                            {{ $invitation->event_type }} {{ $invitation->event_date?->format('d/m/Y') }}
                        </p>
                        <p class="text-stone-400 text-sm mt-1">
                            Pelanggan: {{ $order['customer_name'] ?? '-' }}{{ ! empty($order['customer_whatsapp']) ? ' ('.$order['customer_whatsapp'].')' : '' }}
                        </p>
                        @if ($invitation->giftSetting?->is_active || $invitation->gift_paid_total)
                            <p class="text-stone-400 text-sm mt-1">Tanda kasih {{ $invitation->giftSetting?->is_active ? 'aktif' : 'tidak aktif' }} <span class="text-amber-200">- masuk Rp{{ number_format((int) $invitation->gift_paid_total, 0, ',', '.') }}</span></p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm lg:justify-end">
                        @if ($live)
                            <a href="{{ $invitation->public_url }}" target="_blank" rel="noopener" class="border border-stone-700 rounded-xl px-4 py-2 hover:border-amber-400">Buka undangan</a>
                            <a href="{{ route('invitations.share', $invitation->slug) }}" target="_blank" rel="noopener" class="border border-stone-700 rounded-xl px-4 py-2 hover:border-amber-400">Halaman bagikan</a>
                        @endif
                        <a href="{{ route('admin.custom.edit', $invitation) }}" class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-4 py-2 hover:bg-amber-300">Ubah</a>
                        <form method="POST" action="{{ route('admin.invitations.destroy', $invitation) }}" onsubmit="return confirm('Hapus undangan {{ addslashes($invitation->display_name) }} secara permanen? Foto, musik, komentar, dan laporannya ikut terhapus dan tidak bisa dikembalikan.')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="from" value="custom">
                            <button class="border border-red-500/50 text-red-200 rounded-xl px-4 py-2 hover:bg-red-500/15">Hapus permanen</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="bg-stone-900 border border-stone-800 rounded-3xl p-6 text-stone-400">Belum ada pesanan custom. Mulai dari tombol "Buat undangan pelanggan".</p>
            @endforelse
        </section>

        <div class="mt-6">{{ $invitations->links() }}</div>
    </main>
</body>
</html>
