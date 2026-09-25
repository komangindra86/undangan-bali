<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Konten - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Laporan Konten',
        'subtitle' => 'Tinjau laporan Moment dan komentar dari pengguna, lalu sembunyikan konten yang melanggar.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-5 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-serif text-xl">Antrian laporan</h2>
                <p class="text-stone-400 text-sm mt-1">{{ $openCount }} laporan menunggu ditinjau.</p>
            </div>
            <nav class="flex flex-wrap gap-2 text-sm">
                @foreach (['open' => 'Menunggu', 'resolved' => 'Ditindak', 'dismissed' => 'Diabaikan', 'all' => 'Semua'] as $value => $label)
                    <a
                        href="{{ route('admin.reports.index', ['status' => $value]) }}"
                        class="rounded-full border px-4 py-2 {{ $activeStatus === $value ? 'border-amber-400 bg-amber-400 text-stone-950 font-semibold' : 'border-stone-700 text-stone-300 hover:border-amber-400' }}"
                    >{{ $label }}</a>
                @endforeach
            </nav>
        </section>

        <section class="space-y-5">
            @forelse ($reports as $report)
                @php
                    $isComment = $report->comment_id !== null;
                    $publicUrl = $report->invitation?->slug ? route('invitations.public', $report->invitation->slug) : null;
                @endphp
                <article class="bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden">
                    <div class="p-4 md:p-6 border-b border-stone-800 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-red-500/40 bg-red-500/15 text-red-200 px-3 py-1 text-xs uppercase tracking-wider">{{ $report->reasonLabel() }}</span>
                                <span class="rounded-full border border-stone-700 px-3 py-1 text-xs text-stone-300">{{ $isComment ? 'Komentar' : 'Moment' }}</span>
                                <span class="text-stone-500">#{{ $report->id }}</span>
                            </div>
                            <p class="text-stone-400 text-sm mt-2">
                                Dilaporkan oleh {{ $report->reporter?->name ?? 'Pengguna terhapus' }}
                                <span class="text-stone-600 mx-2">|</span>
                                {{ $report->created_at->format('d/m/Y H:i') }}
                            </p>
                            <p class="text-stone-400 text-sm mt-1">
                                Pemilik konten: {{ $report->reportedUser?->name ?? '-' }} {{ $report->reportedUser ? '('.$report->reportedUser->email.')' : '' }}
                            </p>
                        </div>
                        <div class="lg:text-right text-sm">
                            <p class="text-stone-300">{{ $report->invitation?->display_name ?? 'Undangan terhapus' }}</p>
                            @if ($publicUrl)
                                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="text-amber-300 hover:underline">Buka undangan</a>
                            @endif
                            @if ($report->invitation?->is_hidden_from_feed)
                                <p class="text-stone-500 mt-1">Sudah tersembunyi dari feed</p>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 md:p-6 space-y-3">
                        @if ($isComment)
                            <div class="rounded-2xl bg-stone-950 border border-stone-800 p-4">
                                <p class="text-stone-500 text-xs uppercase tracking-wider">Isi komentar</p>
                                <p class="mt-2 whitespace-pre-line">{{ $report->comment?->body ?? 'Komentar tidak tersedia.' }}</p>
                                @if ($report->comment?->deleted_at)
                                    <p class="text-stone-500 text-sm mt-2">Komentar sudah disembunyikan.</p>
                                @endif
                            </div>
                        @endif
                        @if ($report->note)
                            <p class="text-sm text-stone-300"><span class="text-stone-500">Catatan pelapor:</span> {{ $report->note }}</p>
                        @endif
                        @if ($report->status !== 'open')
                            <p class="text-sm text-emerald-300">
                                {{ $report->status === 'resolved' ? 'Ditindak' : 'Diabaikan' }} {{ $report->resolved_at?->format('d/m/Y H:i') }}
                                @if ($report->admin_note) - {{ $report->admin_note }} @endif
                            </p>
                        @endif
                    </div>

                    @if ($report->status === 'open')
                        <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="border-t border-stone-800 p-4 md:p-6 grid lg:grid-cols-[260px_1fr_160px] gap-3">
                            @csrf
                            @method('PUT')
                            <select name="action" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                                @if ($isComment)
                                    <option value="hide_comment">Sembunyikan komentar</option>
                                @endif
                                <option value="hide_moment">Sembunyikan Moment dari feed</option>
                                <option value="dismiss">Abaikan, tidak melanggar</option>
                            </select>
                            <input name="admin_note" placeholder="Catatan admin (opsional)" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                            <button class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-4 py-3 hover:bg-amber-300">Simpan</button>
                        </form>
                    @endif
                </article>
            @empty
                <p class="bg-stone-900 border border-stone-800 rounded-3xl p-6 text-stone-400">Tidak ada laporan pada filter ini.</p>
            @endforelse
        </section>

        <div class="mt-6">{{ $reports->links() }}</div>
    </main>
</body>
</html>
