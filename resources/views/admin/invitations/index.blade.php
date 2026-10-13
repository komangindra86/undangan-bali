<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Undangan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Undangan',
        'subtitle' => 'Cari undangan, periksa isinya, lalu turunkan undangan palsu atau yang menyalahgunakan layanan.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif

        @if ($errors->any())
            <p class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">{{ $errors->first() }}</p>
        @endif

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-5 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" action="{{ route('admin.invitations.index') }}" class="flex flex-1 gap-2 max-w-xl">
                <input type="hidden" name="filter" value="{{ $activeFilter }}">
                <input name="q" value="{{ $search }}" placeholder="Cari nama, link, tempat, atau email pemilik" class="flex-1 bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                <button class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-4 py-3 hover:bg-amber-300">Cari</button>
            </form>
            <nav class="flex flex-wrap gap-2 text-sm">
                @foreach (['all' => 'Semua', 'published' => 'Tayang', 'reported' => 'Dilaporkan', 'taken_down' => 'Diturunkan ('.$takenDownCount.')'] as $value => $label)
                    <a
                        href="{{ route('admin.invitations.index', array_filter(['filter' => $value, 'q' => $search])) }}"
                        class="rounded-full border px-4 py-2 {{ $activeFilter === $value ? 'border-amber-400 bg-amber-400 text-stone-950 font-semibold' : 'border-stone-700 text-stone-300 hover:border-amber-400' }}"
                    >{{ $label }}</a>
                @endforeach
            </nav>
        </section>

        <section class="space-y-5">
            @forelse ($invitations as $invitation)
                @php
                    $takenDown = $invitation->isTakenDown();
                    $statusLabel = $takenDown ? 'Diturunkan' : (['draft' => 'Draft', 'published' => 'Tayang', 'archived' => 'Diarsipkan'][$invitation->status] ?? $invitation->status);
                    $statusClass = $takenDown
                        ? 'bg-red-500/15 text-red-200 border-red-500/40'
                        : ($invitation->status === 'published' ? 'bg-emerald-500/15 text-emerald-200 border-emerald-500/40' : 'bg-stone-800 text-stone-300 border-stone-700');
                @endphp
                <article class="bg-stone-900 border {{ $takenDown ? 'border-red-500/40' : 'border-stone-800' }} rounded-3xl overflow-hidden">
                    <div class="p-4 md:p-6 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-lg">{{ $invitation->display_name }}</p>
                                <span class="text-stone-500">#{{ $invitation->id }}</span>
                                <span class="rounded-full border px-3 py-1 text-xs uppercase tracking-wider {{ $statusClass }}">{{ $statusLabel }}</span>
                                @if ($invitation->open_reports_count)
                                    <a href="{{ route('admin.reports.index') }}" class="rounded-full border border-amber-500/40 bg-amber-500/15 text-amber-200 px-3 py-1 text-xs">{{ $invitation->open_reports_count }} laporan menunggu</a>
                                @endif
                            </div>
                            <p class="text-stone-400 text-sm mt-2">
                                {{ $invitation->event_type ?? '-' }}
                                <span class="text-stone-600 mx-2">|</span>
                                Acara {{ $invitation->event_date?->format('d/m/Y') ?? '-' }}
                                <span class="text-stone-600 mx-2">|</span>
                                {{ $invitation->venue_name ?? 'Tempat belum diisi' }}
                            </p>
                            <p class="text-stone-400 text-sm mt-1">
                                Pemilik: {{ $invitation->user?->name ?? '-' }} {{ $invitation->user ? '('.$invitation->user->email.')' : '' }}
                            </p>
                            <p class="text-stone-500 text-xs mt-2">
                                Dibuat {{ $invitation->created_at?->format('d/m/Y H:i') }}
                                <span class="text-stone-700 mx-2">|</span>
                                {{ $invitation->template?->name ?? 'Template tidak tersedia' }}
                            </p>
                        </div>
                        <div class="lg:text-right text-sm">
                            @if ($invitation->public_url)
                                <a href="{{ $invitation->public_url }}" target="_blank" rel="noopener" class="text-amber-300 hover:underline">Buka undangan</a>
                                <p class="text-stone-500 text-xs mt-1 break-all">{{ $invitation->slug }}</p>
                            @endif
                            <p class="text-stone-400 mt-2">
                                Gift {{ $invitation->giftSetting?->is_active ? 'aktif' : 'tidak aktif' }}
                                @if ($invitation->gift_paid_total)
                                    <span class="text-amber-200">- masuk Rp{{ number_format($invitation->gift_paid_total, 0, ',', '.') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-stone-800 p-4 md:p-6">
                        @if ($takenDown)
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <p class="text-sm text-red-200">
                                    Diturunkan {{ $invitation->taken_down_at->format('d/m/Y H:i') }}
                                    @if ($invitation->takedown_note) - {{ $invitation->takedown_note }} @endif
                                </p>
                                <form method="POST" action="{{ route('admin.invitations.restore', $invitation) }}">
                                    @csrf
                                    @method('PUT')
                                    <button class="border border-stone-600 text-stone-200 rounded-xl px-4 py-3 hover:border-amber-400">Pulihkan undangan</button>
                                </form>
                            </div>
                        @else
                            <form
                                method="POST"
                                action="{{ route('admin.invitations.take-down', $invitation) }}"
                                class="grid lg:grid-cols-[1fr_220px] gap-3"
                                onsubmit="return confirm('Turunkan undangan ini? Link publik, halaman gift, dan feed-nya akan mati.')"
                            >
                                @csrf
                                @method('PUT')
                                <input name="takedown_note" required maxlength="500" placeholder="Alasan menurunkan (wajib), contoh: undangan palsu, dilaporkan keluarga" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                                <button class="bg-red-500/90 text-white font-semibold rounded-xl px-4 py-3 hover:bg-red-500">Turunkan undangan</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="bg-stone-900 border border-stone-800 rounded-3xl p-6 text-stone-400">Tidak ada undangan pada pencarian atau filter ini.</p>
            @endforelse
        </section>

        <div class="mt-6">{{ $invitations->links() }}</div>
    </main>
</body>
</html>
