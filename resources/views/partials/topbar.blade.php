<header class="flex items-center justify-between border-b border-slate-200 bg-white p-4">
    {{-- <div class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">
        @yield('page_title', 'Beranda')
    </div> --}}
    <div class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900" x-text="pageTitle">
    </div>
    <div class="flex items-center gap-3 sm:gap-6">
        <div class="text-right hidden sm:block">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                TELEMETRI BBWS 3
            </div>
            <div class="text-sm font-semibold text-slate-800">
                {{ auth()->user()->nama ?? 'User' }}
            </div>
        </div>

        <div class="relative" x-data="{ open: false }">
            <button type="button"
                class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-3 py-2 hover:bg-slate-50"
                @click="open = !open" @keydown.escape.window="open = false">
                <img src="{{ asset('images/logo-avatar.png') }}" class="h-8 w-8 rounded-full object-cover"
                    alt="Avatar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6" />
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition @click.outside="open = false"
                class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                @permission('view_profile')
                    <a href="{{ route('profile.edit') }}"
                        class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">Profile</a>
                    <a href="{{ route('profile.edit') }}"
                        class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">Settings</a>
                @endpermission
                <a href="#" class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">Status</a>
                <div class="h-px bg-slate-200"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full px-4 py-3 text-left text-sm text-slate-700 hover:bg-slate-50">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
