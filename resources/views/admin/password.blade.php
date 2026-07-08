<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Password Admin - Undangan Bali</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @include('admin.partials.nav', [
        'title' => 'Ubah Password',
        'subtitle' => 'Gunakan password kuat dan jangan bagikan akses admin kepada siapa pun.',
    ])

    <main class="max-w-3xl mx-auto p-4 md:p-6">
        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif

        @if ($errors->any())
            <div class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">
                <p class="font-semibold">Password belum tersimpan</p>
                <ul class="list-disc list-inside text-sm mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-5 md:p-7">
            <h2 class="font-serif text-2xl">Ganti Password Admin</h2>
            <p class="text-stone-400 text-sm mt-2">Minimal 12 karakter, mengandung huruf besar, huruf kecil, dan angka.</p>

            <form method="POST" action="{{ route('admin.password.update') }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <label class="block">
                    <span class="text-stone-300 text-sm">Password lama</span>
                    <input name="current_password" type="password" required autocomplete="current-password" class="mt-2 bg-stone-950 border border-stone-700 rounded-xl px-4 py-3 w-full focus:border-amber-500 outline-none">
                </label>

                <label class="block">
                    <span class="text-stone-300 text-sm">Password baru</span>
                    <input name="password" type="password" required autocomplete="new-password" class="mt-2 bg-stone-950 border border-stone-700 rounded-xl px-4 py-3 w-full focus:border-amber-500 outline-none">
                </label>

                <label class="block">
                    <span class="text-stone-300 text-sm">Konfirmasi password baru</span>
                    <input name="password_confirmation" type="password" required autocomplete="new-password" class="mt-2 bg-stone-950 border border-stone-700 rounded-xl px-4 py-3 w-full focus:border-amber-500 outline-none">
                </label>

                <button class="bg-amber-500 text-stone-950 rounded-xl font-semibold px-5 py-3 hover:bg-amber-400 w-full md:w-auto">Simpan Password</button>
            </form>
        </section>

        <section class="bg-stone-900 border border-stone-800 rounded-3xl p-5 md:p-7 mt-5">
            <h2 class="font-serif text-xl">Tips Keamanan Admin</h2>
            <ul class="list-disc list-inside text-stone-400 text-sm leading-7 mt-3">
                <li>Pakai password unik yang tidak dipakai di akun lain.</li>
                <li>Simpan password di password manager, bukan di chat atau catatan umum.</li>
                <li>Ganti password setelah memberi akses sementara ke orang lain.</li>
                <li>Logout setelah memakai komputer publik atau perangkat milik orang lain.</li>
            </ul>
        </section>
    </main>
</body>
</html>
