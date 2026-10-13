<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Template Premium - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Template Premium',
        'subtitle' => 'Desain untuk pesanan custom. Tidak tampil di aplikasi kecuali pada akun yang diberi akses di bawah.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif

        @if ($errors->any())
            <p class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">{{ $errors->first() }}</p>
        @endif

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-6 mb-6">
            <h2 class="font-serif text-xl">Akun yang boleh memakai template premium</h2>
            <p class="text-stone-400 text-sm mt-1">Masukkan email akun aplikasi yang Anda pakai untuk mengerjakan pesanan. Template premium akan muncul di daftar template akun itu.</p>

            <form method="POST" action="{{ route('admin.templates.grant') }}" class="grid sm:grid-cols-[1fr_200px] gap-3 mt-4 max-w-2xl">
                @csrf
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="email akun aplikasi" class="bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                <button class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-4 py-3 hover:bg-amber-300">Beri akses</button>
            </form>

            <div class="mt-5 space-y-3">
                @forelse ($accounts as $account)
                    <div class="rounded-2xl bg-stone-950 border border-stone-800 p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold">{{ $account->name }}</p>
                            <p class="text-stone-500 text-sm">{{ $account->email }} <span class="text-stone-700 mx-2">|</span> {{ $account->invitations_count }} undangan</p>
                        </div>
                        <form method="POST" action="{{ route('admin.templates.revoke', $account) }}">
                            @csrf
                            @method('PUT')
                            <button class="border border-stone-600 text-stone-200 rounded-xl px-4 py-2 hover:border-red-400 hover:text-red-200">Cabut akses</button>
                        </form>
                    </div>
                @empty
                    <p class="text-stone-500 text-sm">Belum ada akun yang diberi akses.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-6">
            <h2 class="font-serif text-xl">Daftar template premium</h2>
            <p class="text-stone-400 text-sm mt-1">Link contoh bisa dikirim ke calon pelanggan lewat WhatsApp; isinya data contoh, bukan undangan sungguhan.</p>

            <div class="mt-4 grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($templates as $template)
                    @php($previewUrl = route('templates.preview', $template))
                    <article class="rounded-2xl bg-stone-950 border border-stone-800 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-lg">{{ $template->name }}</p>
                                <p class="text-stone-500 text-sm">{{ $template->invitation_type }} <span class="text-stone-700 mx-2">|</span> dipakai {{ $template->invitations_count }} undangan</p>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs {{ $template->is_active ? 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200' : 'border-stone-700 text-stone-400' }}">{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="inline-block mt-3 text-amber-300 hover:underline">Buka contoh</a>
                        <input readonly value="{{ $previewUrl }}" onclick="this.select()" class="mt-3 w-full bg-stone-900 border border-stone-800 rounded-xl px-3 py-2 text-xs text-stone-300">
                    </article>
                @empty
                    <p class="text-stone-500 text-sm">Belum ada template premium.</p>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
