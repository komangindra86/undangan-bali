<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - Undangan Bali</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Ringkasan Aplikasi',
        'subtitle' => 'Pantau pengguna, undangan live, undangan lewat, view, gift, dan pencairan.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        <section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Pengguna pasangan</p>
                <p class="font-serif text-4xl mt-2">{{ number_format($summary['users']) }}</p>
                <p class="text-stone-500 text-sm mt-2">{{ number_format($summary['admins']) }} admin</p>
            </article>
            <article class="bg-stone-900 border border-amber-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Total undangan</p>
                <p class="text-amber-300 font-serif text-4xl mt-2">{{ number_format($summary['invitations']) }}</p>
                <p class="text-stone-500 text-sm mt-2">{{ number_format($summary['drafts']) }} draft</p>
            </article>
            <article class="bg-stone-900 border border-emerald-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Undangan live</p>
                <p class="text-emerald-300 font-serif text-4xl mt-2">{{ number_format($summary['live']) }}</p>
                <p class="text-stone-500 text-sm mt-2">Published dan tanggal acara belum lewat</p>
            </article>
            <article class="bg-stone-900 border border-red-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Undangan sudah lewat</p>
                <p class="text-red-300 font-serif text-4xl mt-2">{{ number_format($summary['expired']) }}</p>
                <p class="text-stone-500 text-sm mt-2">Published dengan tanggal acara lampau</p>
            </article>
        </section>

        <section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-4">
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Published total</p>
                <p class="font-serif text-3xl mt-2">{{ number_format($summary['published']) }}</p>
            </article>
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Total view undangan</p>
                <p class="font-serif text-3xl mt-2">{{ number_format($summary['views']) }}</p>
            </article>
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Gift paid pasangan</p>
                <p class="font-serif text-3xl mt-2">Rp{{ number_format($summary['gift_paid'], 0, ',', '.') }}</p>
            </article>
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Pencairan aktif</p>
                <p class="font-serif text-3xl mt-2">Rp{{ number_format($summary['payout_pending'], 0, ',', '.') }}</p>
                <a href="{{ route('admin.payout.index') }}" class="inline-block text-amber-300 text-sm mt-3">Buka pencairan</a>
            </article>
        </section>

        <section class="grid xl:grid-cols-[1.1fr_0.9fr] gap-5 mt-6">
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-serif text-xl">Undangan terbaru</h2>
                        <p class="text-stone-500 text-sm mt-1">Status, tanggal acara, dan pemilik undangan.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-stone-500 border-b border-stone-800">
                            <tr>
                                <th class="py-3 pr-4">Pasangan</th>
                                <th class="py-3 pr-4">User</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-800">
                            @forelse ($latestInvitations as $invitation)
                                <tr>
                                    <td class="py-3 pr-4">
                                        @php
                                            $coupleName = ($invitation->groom_nickname ?: 'Mempelai').' & '.($invitation->bride_nickname ?: 'Pasangan');
                                            $canOpenLive = $invitation->status === 'published' && $invitation->public_url;
                                        @endphp

                                        @if ($canOpenLive)
                                            <a
                                                href="{{ $invitation->public_url }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="font-semibold text-amber-100 hover:text-amber-300 underline decoration-amber-500/40 underline-offset-4"
                                            >{{ $coupleName }}</a>
                                            <p class="text-amber-400 text-xs mt-1">Klik untuk melihat undangan live</p>
                                        @else
                                            <p class="font-semibold">{{ $coupleName }}</p>
                                        @endif
                                        <p class="text-stone-500">{{ $invitation->template?->name ?? 'Template tidak tersedia' }}</p>
                                    </td>
                                    <td class="py-3 pr-4">{{ $invitation->user?->name ?? '-' }}</td>
                                    <td class="py-3 pr-4">
                                        @if ($canOpenLive)
                                            <a
                                                href="{{ $invitation->public_url }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="inline-flex rounded-full border border-emerald-500/50 bg-emerald-500/10 px-3 py-1 text-xs uppercase text-emerald-200 hover:border-emerald-300"
                                            >Published</a>
                                        @else
                                            <span class="rounded-full border border-stone-700 px-3 py-1 text-xs uppercase">{{ $invitation->status }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">{{ $invitation->event_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-stone-500">Belum ada undangan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <h2 class="font-serif text-xl">Pengguna terbaru</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($latestUsers as $user)
                        <div class="rounded-2xl bg-stone-950 border border-stone-800 p-4">
                            <p class="font-semibold">{{ $user->name }}</p>
                            <p class="text-stone-500 text-sm">{{ $user->email }}</p>
                            <p class="text-stone-600 text-xs mt-2">Daftar {{ $user->created_at?->format('d/m/Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-stone-500 text-sm">Belum ada user pasangan.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid xl:grid-cols-2 gap-5 mt-6">
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <h2 class="font-serif text-xl">Template populer</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($popularTemplates as $template)
                        <div class="flex items-center justify-between rounded-2xl bg-stone-950 border border-stone-800 p-4">
                            <div>
                                <p class="font-semibold">{{ $template->name }}</p>
                                <p class="text-stone-500 text-sm">{{ $template->slug }}</p>
                            </div>
                            <p class="text-amber-300 font-serif text-2xl">{{ number_format($template->usage_count) }}</p>
                        </div>
                    @empty
                        <p class="text-stone-500 text-sm">Belum ada template.</p>
                    @endforelse
                </div>
            </article>

            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-serif text-xl">Antrian pencairan</h2>
                        <p class="text-stone-500 text-sm mt-1">Pengajuan aktif yang perlu diproses.</p>
                    </div>
                    <a href="{{ route('admin.payout.index') }}" class="text-amber-300 text-sm">Lihat semua</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($payoutQueue as $payout)
                        <div class="rounded-2xl bg-stone-950 border border-stone-800 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold">{{ $payout->user?->name ?? '-' }}</p>
                                <p class="text-amber-300">Rp{{ number_format($payout->amount, 0, ',', '.') }}</p>
                            </div>
                            <p class="text-stone-500 text-sm mt-1">{{ $payout->bank_name }} - {{ $payout->account_number }}</p>
                            <p class="text-stone-600 text-xs mt-2">{{ strtoupper($payout->status) }} | {{ $payout->requested_at?->format('d/m/Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-stone-500 text-sm">Belum ada antrian pencairan aktif.</p>
                    @endforelse
                </div>
            </article>
        </section>
    </main>
</body>
</html>
