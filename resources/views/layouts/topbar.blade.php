<header class="border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden"
                @click="sidebarOpen = true"
            >
                <span class="sr-only">Buka menu</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Admin Panel</p>
                <h2 class="text-lg font-semibold text-slate-900">{{ $header ?? 'Dashboard' }}</h2>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('profile.edit') }}"
                class="hidden rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:inline-flex"
            >
                Profil
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Logout
                </button>
            </form>
        </div>
    </div>
</header>
