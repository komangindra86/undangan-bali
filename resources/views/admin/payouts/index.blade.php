<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pencairan Wedding Gift - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Pencairan Wedding Gift',
        'subtitle' => 'Cek rekening tujuan, proses transfer manual, lalu tandai status pencairan.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif

        @if ($errors->any())
            <div class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">
                <p class="font-semibold">Status belum tersimpan</p>
                <ul class="list-disc list-inside text-sm mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <article class="bg-stone-900 border border-amber-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Perlu diproses</p>
                <p class="text-amber-300 font-serif text-3xl mt-2">Rp{{ number_format($summary['pending_amount'], 0, ',', '.') }}</p>
                <p class="text-stone-500 text-sm mt-2">{{ $summary['pending_count'] }} pengajuan aktif</p>
            </article>
            <article class="bg-stone-900 border border-emerald-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Sudah ditransfer</p>
                <p class="text-emerald-300 font-serif text-3xl mt-2">Rp{{ number_format($summary['paid'], 0, ',', '.') }}</p>
                <p class="text-stone-500 text-sm mt-2">{{ $summary['paid_count'] }} pengajuan selesai</p>
            </article>
            <article class="bg-stone-900 border border-sky-500/30 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Pengajuan hari ini</p>
                <p class="text-sky-300 font-serif text-3xl mt-2">Rp{{ number_format($summary['today_amount'], 0, ',', '.') }}</p>
                <p class="text-stone-500 text-sm mt-2">{{ $summary['today_count'] }} pengajuan baru</p>
            </article>
            <article class="bg-stone-900 border border-stone-800 rounded-3xl p-5">
                <p class="text-stone-400 text-sm">Total pengajuan</p>
                <p class="text-stone-100 font-serif text-3xl mt-2">{{ $summary['requests'] }}</p>
                <p class="text-stone-500 text-sm mt-2">Semua status</p>
            </article>
        </section>

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-5 mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="font-serif text-xl">Antrian pencairan</h2>
                    <p class="text-stone-400 text-sm mt-1">
                        Nomor rekening ada di kartu tiap pengajuan, bagian <span class="text-amber-200">Rekening tujuan transfer</span>.
                    </p>
                </div>
                <nav class="flex flex-wrap gap-2 text-sm">
                    @php
                        $filters = [
                            '' => 'Semua',
                            'pending' => 'Pending',
                            'approved' => 'Terverifikasi',
                            'processing' => 'Diproses',
                            'paid' => 'Dibayar',
                            'rejected' => 'Ditolak',
                        ];
                    @endphp
                    @foreach ($filters as $value => $label)
                        <a
                            href="{{ route('admin.payouts.index', array_filter(['status' => $value])) }}"
                            class="rounded-full border px-4 py-2 {{ ($activeStatus ?: '') === $value ? 'border-amber-400 bg-amber-400 text-stone-950 font-semibold' : 'border-stone-700 text-stone-300 hover:border-amber-400' }}"
                        >{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
        </section>

        <section class="space-y-5">
            @forelse ($payouts as $payout)
                @php
                    $isFinal = in_array($payout->status, ['paid', 'rejected'], true);
                    $account = $payout->payoutAccount;
                    $accountChanged = $account && (
                        $account->bank_code !== $payout->bank_code
                        || $account->bank_name !== $payout->bank_name
                        || $account->account_number !== $payout->account_number
                        || $account->account_holder_name !== $payout->account_holder_name
                    );
                    $statusClass = [
                        'pending' => 'bg-amber-500/15 text-amber-200 border-amber-500/40',
                        'approved' => 'bg-sky-500/15 text-sky-200 border-sky-500/40',
                        'processing' => 'bg-violet-500/15 text-violet-200 border-violet-500/40',
                        'paid' => 'bg-emerald-500/15 text-emerald-200 border-emerald-500/40',
                        'rejected' => 'bg-red-500/15 text-red-200 border-red-500/40',
                    ][$payout->status] ?? 'bg-stone-800 text-stone-300 border-stone-700';
                @endphp

                <article class="bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden">
                    <div class="p-4 md:p-6 border-b border-stone-800 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-lg">{{ $payout->user->name }}</p>
                                <span class="text-stone-500">#{{ $payout->id }}</span>
                                <span class="rounded-full border px-3 py-1 text-xs uppercase tracking-wider {{ $statusClass }}">{{ $payout->status }}</span>
                            </div>
                            <p class="text-stone-400 text-sm mt-2">
                                {{ $payout->invitation?->groom_nickname ?? 'Mempelai' }} &amp; {{ $payout->invitation?->bride_nickname ?? 'Pasangan' }}
                                <span class="text-stone-600 mx-2">|</span>
                                {{ $payout->requested_at?->format('d/m/Y H:i') ?? '-' }}
                            </p>
                            <p class="text-stone-500 text-sm mt-1">{{ $payout->user->email }}</p>
                        </div>
                        <div class="lg:text-right">
                            <p class="text-stone-400 text-sm">Nominal dicairkan</p>
                            <p class="text-amber-300 font-serif text-3xl">Rp{{ number_format($payout->amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="grid xl:grid-cols-[1.1fr_0.9fr] gap-4 p-4 md:p-6">
                        <section class="bg-stone-950 border border-amber-500/30 rounded-2xl p-4">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="text-amber-300 font-semibold">Rekening tujuan transfer</p>
                                    <p class="text-stone-400 text-sm mt-1">Snapshot rekening saat pasangan mengajukan pencairan.</p>
                                </div>
                                @if ($account?->is_verified)
                                    <span class="rounded-full bg-emerald-500/15 text-emerald-200 border border-emerald-500/40 px-3 py-1 text-xs">Rekening terverifikasi</span>
                                @else
                                    <span class="rounded-full bg-amber-500/15 text-amber-200 border border-amber-500/40 px-3 py-1 text-xs">Perlu verifikasi</span>
                                @endif
                            </div>

                            <dl class="grid sm:grid-cols-2 gap-3 mt-4">
                                <div class="rounded-xl bg-stone-900 p-3">
                                    <dt class="text-stone-500 text-xs uppercase tracking-wider">Bank</dt>
                                    <dd class="font-semibold mt-1">{{ $payout->bank_name }} ({{ $payout->bank_code }})</dd>
                                </div>
                                <div class="rounded-xl bg-stone-900 p-3">
                                    <dt class="text-stone-500 text-xs uppercase tracking-wider">Nomor rekening</dt>
                                    <dd class="font-mono text-lg text-amber-100 mt-1 break-all">{{ $payout->account_number }}</dd>
                                </div>
                                <div class="rounded-xl bg-stone-900 p-3 sm:col-span-2">
                                    <dt class="text-stone-500 text-xs uppercase tracking-wider">Nama pemilik rekening</dt>
                                    <dd class="font-semibold text-lg mt-1">{{ $payout->account_holder_name }}</dd>
                                </div>
                            </dl>

                            @if ($accountChanged)
                                <div class="mt-4 rounded-xl border border-red-500/40 bg-red-950/40 p-3 text-sm text-red-100">
                                    <p class="font-semibold">Rekening akun saat ini berbeda dari rekening saat pengajuan.</p>
                                    <p class="mt-1">Gunakan snapshot di atas untuk transfer pengajuan ini, atau tolak agar pasangan mengajukan ulang.</p>
                                    <p class="mt-2 text-red-200">
                                        Saat ini: {{ $account->bank_name }} ({{ $account->bank_code }}) - {{ $account->account_number }} a/n {{ $account->account_holder_name }}
                                    </p>
                                </div>
                            @endif

                            @if ($payout->transfer_reference)
                                <p class="mt-4 text-emerald-300 text-sm">Referensi transfer: <span class="font-semibold">{{ $payout->transfer_reference }}</span></p>
                            @endif
                        </section>

                        <section class="bg-stone-950 border border-stone-800 rounded-2xl p-4">
                            <p class="text-stone-200 font-semibold">Rincian gift yang dicairkan</p>
                            <p class="text-stone-500 text-sm mt-1">{{ $payout->items->count() }} transaksi gift masuk ke pengajuan ini.</p>
                            <div class="mt-4 space-y-2 max-h-52 overflow-y-auto pr-1">
                                @forelse ($payout->items as $item)
                                    <div class="rounded-xl bg-stone-900 p-3 text-sm">
                                        <div class="flex justify-between gap-3">
                                            <p class="text-stone-200">{{ $item->weddingGift?->guest_name ?? 'Tamu' }}</p>
                                            <p class="text-amber-200">Rp{{ number_format($item->amount, 0, ',', '.') }}</p>
                                        </div>
                                        <p class="text-stone-500 mt-1">{{ $item->weddingGift?->paid_at?->format('d/m/Y H:i') ?? 'Tanggal bayar tidak tersedia' }}</p>
                                    </div>
                                @empty
                                    <p class="text-stone-500 text-sm">Rincian gift tidak tersedia.</p>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    <div class="border-t border-stone-800 p-4 md:p-6">
                        @if (! $isFinal)
                            <form method="POST" action="{{ route('admin.payouts.update', $payout) }}" class="grid lg:grid-cols-[220px_1fr_1fr_180px] gap-3">
                                @csrf
                                @method('PUT')
                                <select name="status" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                                    <option value="approved" @selected($payout->status === 'approved')>Rekening terverifikasi</option>
                                    <option value="processing" @selected($payout->status === 'processing')>Sedang ditransfer</option>
                                    <option value="paid">Selesai dibayar</option>
                                    <option value="rejected">Tolak & kembalikan saldo</option>
                                </select>
                                <input name="transfer_reference" value="{{ old('transfer_reference', $payout->transfer_reference) }}" placeholder="Referensi transfer (wajib jika selesai)" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                                <input name="admin_note" value="{{ old('admin_note', $payout->admin_note) }}" placeholder="Catatan admin" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                                <button class="bg-amber-500 text-stone-950 rounded-xl font-semibold px-4 py-3 hover:bg-amber-400">Simpan Status</button>
                            </form>
                        @else
                            <div class="rounded-xl bg-stone-950 border border-stone-800 p-4 text-sm">
                                <p class="text-stone-300">{{ $payout->admin_note ?: 'Pengajuan sudah final.' }}</p>
                                @if ($payout->paid_at)
                                    <p class="text-emerald-300 mt-2">Dibayar pada {{ $payout->paid_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="bg-stone-900 border border-stone-800 rounded-3xl text-center py-16 px-4">
                    <p class="font-serif text-2xl text-stone-200">Belum ada permintaan pencairan</p>
                    <p class="text-stone-500 mt-2">Jika pasangan mengajukan pencairan dari aplikasi, detail rekening akan muncul di sini.</p>
                </div>
            @endforelse
        </section>

        <div class="mt-8">{{ $payouts->withQueryString()->links() }}</div>
    </main>
</body>
</html>
