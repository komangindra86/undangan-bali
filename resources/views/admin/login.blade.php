<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Undangan Bali</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen flex items-center justify-center px-6">
    <form method="POST" action="{{ route('admin.login.store') }}" class="bg-stone-900 border border-stone-800 rounded-3xl p-8 w-full max-w-md">
        @csrf
        <p class="text-amber-400 tracking-[0.28em] uppercase text-xs">Admin Dashboard</p>
        <h1 class="font-serif text-3xl mt-4 mb-8">Masuk untuk memproses pencairan</h1>
        @if ($errors->any())
            <p class="bg-red-950 border border-red-800 text-red-200 rounded-xl p-3 text-sm mb-5">{{ $errors->first() }}</p>
        @endif
        <label class="block text-sm text-stone-300 mb-2">Email</label>
        <input name="email" type="email" value="{{ old('email') }}" required class="bg-stone-800 border border-stone-700 rounded-xl px-4 py-3 w-full mb-5">
        <label class="block text-sm text-stone-300 mb-2">Password</label>
        <input name="password" type="password" required class="bg-stone-800 border border-stone-700 rounded-xl px-4 py-3 w-full mb-7">
        <button class="bg-amber-500 hover:bg-amber-400 text-stone-950 font-semibold rounded-xl py-3 w-full">Masuk Admin</button>
    </form>
</body>
</html>
