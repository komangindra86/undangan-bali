<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengguna - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Pengguna',
        'subtitle' => 'Cari akun aplikasi, ganti email agar pemilik bisa masuk dengan Google, dan tangani permintaan hapus akun.',
    ])

    <main class="max-w-7xl mx-auto p-4 md:p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif
        @if ($errors->any())
            <p class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">{{ $errors->first() }}</p>
        @endif

        @if ($requests->isNotEmpty())
            <section class="bg-stone-900 border border-amber-500/40 rounded-3xl p-4 md:p-6 mb-6">
                <h2 class="font-serif text-xl">Permintaan hapus akun dari website ({{ $requests->count() }})</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($requests as $deletionRequest)
                        <div class="rounded-2xl bg-stone-950 border border-stone-800 p-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="font-semibold">{{ $deletionRequest->email }}</p>
                                <p class="text-stone-500 text-sm">{{ $deletionRequest->created_at->format('d/m/Y H:i') }} · {{ $deletionRequest->user ? 'Akun ditemukan: '.$deletionRequest->user->name : 'Tidak ada akun dengan email ini' }}</p>
                                @if ($deletionRequest->note)<p class="text-stone-400 text-sm mt-1">{{ $deletionRequest->note }}</p>@endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if ($deletionRequest->user)
                                    <a href="{{ route('admin.users.index', ['q' => $deletionRequest->email]) }}" class="border border-stone-700 rounded-xl px-4 py-2 text-sm hover:border-amber-400">Lihat akun</a>
                                @endif
                                <form method="POST" action="{{ route('admin.users.requests.close', $deletionRequest) }}">
                                    @csrf
                                    @method('PUT')
                                    <button class="border border-stone-700 rounded-xl px-4 py-2 text-sm hover:border-amber-400">Tandai selesai</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2 max-w-xl mb-6">
            <input name="q" value="{{ $search }}" placeholder="Cari nama atau email" class="flex-1 bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
            <button class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-4 py-3 hover:bg-amber-300">Cari</button>
        </form>

        <section class="space-y-4">
            @forelse ($users as $user)
                <article class="bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-6">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                        <p class="font-semibold text-lg">{{ $user->name }} <span class="text-stone-500 text-sm font-normal">#{{ $user->id }}</span></p>
                        <p class="text-stone-500 text-sm">Daftar {{ $user->created_at?->format('d/m/Y') }} · {{ $user->invitations_count }} undangan</p>
                    </div>
                    <div class="grid lg:grid-cols-[1fr_auto] gap-4 mt-4">
                        <form method="POST" action="{{ route('admin.users.email', $user) }}" class="flex flex-col gap-2 sm:flex-row">
                            @csrf
                            @method('PUT')
                            <input type="email" name="email" required value="{{ $user->email }}" aria-label="Email {{ $user->name }}" class="flex-1 bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                            <button class="border border-stone-600 rounded-xl px-4 py-3 hover:border-amber-400">Simpan email</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="flex gap-2" onsubmit="return confirm('Hapus akun {{ addslashes($user->email) }}? Undangan, foto, dan saldo yang belum dicairkan ikut hilang dan tidak bisa dikembalikan.')">
                            @csrf
                            @method('DELETE')
                            <input name="confirm" required placeholder="Ketik HAPUS" aria-label="Konfirmasi hapus akun {{ $user->name }}" class="w-32 bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100">
                            <button class="border border-red-500/50 text-red-200 rounded-xl px-4 py-3 hover:bg-red-500/15">Hapus akun</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="bg-stone-900 border border-stone-800 rounded-3xl p-6 text-stone-400">Tidak ada akun yang cocok.</p>
            @endforelse
        </section>

        <div class="mt-6">{{ $users->links() }}</div>
    </main>
</body>
</html>
