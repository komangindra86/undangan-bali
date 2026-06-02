<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pencairan Wedding Gift - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    <header class="border-b border-stone-800 px-6 py-5 flex justify-between items-center">
        <div>
            <p class="text-amber-400 tracking-[0.28em] uppercase text-xs">Undangan Pernikahan Bali</p>
            <h1 class="font-serif text-2xl mt-2">Pencairan Wedding Gift</h1>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="text-stone-300 border border-stone-700 rounded-lg px-4 py-2">Keluar</button>
        </form>
    </header>
    <main class="max-w-7xl mx-auto p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-xl p-4 mb-6">{{ session('message') }}</p>
        @endif
        <section class="grid md:grid-cols-3 gap-4 mb-8">
            <article class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
                <p class="text-stone-400 text-sm">Menunggu / diproses</p>
                <p class="text-amber-300 font-serif text-3xl mt-2">Rp{{ number_format($summary['pending'], 0, ',', '.') }}</p>
            </article>
            <article class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
                <p class="text-stone-400 text-sm">Sudah ditransfer</p>
                <p class="text-emerald-300 font-serif text-3xl mt-2">Rp{{ number_format($summary['paid'], 0, ',', '.') }}</p>
            </article>
            <article class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
                <p class="text-stone-400 text-sm">Jumlah pengajuan</p>
                <p class="text-stone-100 font-serif text-3xl mt-2">{{ $summary['requests'] }}</p>
            </article>
        </section>
        <section class="space-y-4">
            @forelse ($payouts as $payout)
                <article class="bg-stone-900 border border-stone-800 rounded-2xl p-5">
                    <div class="flex flex-wrap justify-between gap-4">
                        <div>
                            <p class="font-semibold text-lg">{{ $payout->user->name }} <span class="text-stone-400 text-sm font-normal">#{{ $payout->id }}</span></p>
                            <p class="text-stone-400 text-sm mt-1">{{ $payout->invitation->groom_nickname }} &amp; {{ $payout->invitation->bride_nickname }} | {{ $payout->requested_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-amber-300 font-serif text-2xl">Rp{{ number_format($payout->amount, 0, ',', '.') }}</p>
                            <p class="uppercase text-xs tracking-widest text-stone-400 mt-1">{{ $payout->status }}</p>
                        </div>
                    </div>
                    <div class="bg-stone-800 rounded-xl p-4 my-5 text-sm">
                        <p class="text-stone-400 mb-1">Transfer ke rekening tersimpan saat pengajuan</p>
                        <p class="font-semibold">{{ $payout->bank_name }} ({{ $payout->bank_code }}) - {{ $payout->account_number }}</p>
                        <p class="text-stone-300">{{ $payout->account_holder_name }}</p>
                        @if ($payout->transfer_reference)
                            <p class="text-emerald-300 mt-2">Referensi transfer: {{ $payout->transfer_reference }}</p>
                        @endif
                    </div>
                    @if (! in_array($payout->status, ['paid', 'rejected']))
                        <form method="POST" action="{{ route('admin.payouts.update', $payout) }}" class="grid md:grid-cols-4 gap-3">
                            @csrf
                            @method('PUT')
                            <select name="status" class="bg-stone-800 border border-stone-700 rounded-xl px-3 py-3">
                                <option value="approved">Rekening terverifikasi</option>
                                <option value="processing">Sedang ditransfer</option>
                                <option value="paid">Selesai dibayar</option>
                                <option value="rejected">Tolak & kembalikan saldo</option>
                            </select>
                            <input name="transfer_reference" placeholder="Referensi transfer (wajib jika selesai)" class="bg-stone-800 border border-stone-700 rounded-xl px-3 py-3 md:col-span-1">
                            <input name="admin_note" placeholder="Catatan admin" class="bg-stone-800 border border-stone-700 rounded-xl px-3 py-3">
                            <button class="bg-amber-500 text-stone-950 rounded-xl font-semibold px-4 py-3">Simpan Status</button>
                        </form>
                    @else
                        <p class="text-stone-400 text-sm">{{ $payout->admin_note ?: 'Pengajuan sudah final.' }}</p>
                    @endif
                </article>
            @empty
                <p class="text-stone-400 text-center py-16">Belum ada permintaan pencairan Wedding Gift.</p>
            @endforelse
        </section>
        <div class="mt-8">{{ $payouts->links() }}</div>
    </main>
</body>
</html>
