<header class="border-b border-stone-800 bg-stone-950/95 px-4 py-4 md:px-8 md:py-5 sticky top-0 z-20">
    <div class="max-w-7xl mx-auto flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-amber-400 tracking-[0.28em] uppercase text-xs">Admin Dashboard</p>
            <h1 class="font-serif text-2xl md:text-3xl mt-2">{{ $title ?? 'Admin' }}</h1>
            @isset($subtitle)
                <p class="text-stone-400 text-sm mt-1">{{ $subtitle }}</p>
            @endisset
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <nav class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="rounded-full border px-4 py-2 {{ request()->routeIs('admin.dashboard', 'admin.dashboard.index') ? 'border-amber-400 bg-amber-400 text-stone-950 font-semibold' : 'border-stone-700 text-stone-300 hover:border-amber-400' }}">Dashboard</a>
                <a href="{{ route('admin.payout.index') }}" class="rounded-full border px-4 py-2 {{ request()->routeIs('admin.payout.*', 'admin.payouts.*') ? 'border-amber-400 bg-amber-400 text-stone-950 font-semibold' : 'border-stone-700 text-stone-300 hover:border-amber-400' }}">Pencairan Gift</a>
                <a href="{{ route('admin.password.edit') }}" class="rounded-full border px-4 py-2 {{ request()->routeIs('admin.password.*') ? 'border-amber-400 bg-amber-400 text-stone-950 font-semibold' : 'border-stone-700 text-stone-300 hover:border-amber-400' }}">Ubah Password</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="text-stone-300 border border-stone-700 rounded-xl px-4 py-2 hover:border-amber-500 hover:text-amber-200">Keluar</button>
            </form>
        </div>
    </div>
</header>
