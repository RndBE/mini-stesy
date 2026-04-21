<header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-2">
    <div class="flex items-center gap-2">
        @auth
            <button type="button" onclick="toggleMainSidebar()" aria-label="Open sidebar"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        @endauth
        <div class="text-xl sm:text-xl font-extrabold tracking-tight text-slate-900" x-text="pageTitle"></div>
    </div>
    <div class="flex items-center gap-3 sm:gap-4">
        @php
            $currentUser = auth()->user();
            $namaUser = $currentUser?->nama ?? ($currentUser?->username ?? 'User');
            $instansi = $currentUser?->instansi;
            $namaInstansi = $instansi?->nama ?? '-';
            $avatarUrl = asset('images/logo-avatar.png');

            $logoValue = $instansi?->logo;
            if (!empty($logoValue)) {
                $logoValue = ltrim((string) $logoValue, '/');

                if (filter_var($logoValue, FILTER_VALIDATE_URL)) {
                    $avatarUrl = $logoValue;
                } else {
                    $candidates = [
                        'storage/' . $logoValue,
                        $logoValue,
                        'logo_instansi/' . basename($logoValue),
                    ];

                    foreach ($candidates as $path) {
                        if (file_exists(public_path($path))) {
                            $avatarUrl = asset($path);
                            break;
                        }
                    }
                }
            }
        @endphp

        @auth
            <div class="text-right hidden sm:block">
                <div class="text-[11px] font-semibold tracking-wider text-slate-500">
                    {{ $namaUser }}
                </div>
                <div class="text-sm font-semibold text-slate-800">
                    {{ $namaInstansi }}
                </div>
            </div>

            <div id="topbarLogoWrapper" class="relative z-[200]" x-data="{ open: false }">
                <button type="button" class="flex items-center gap-3 py-2 px-2 rounded hover:bg-slate-50"
                    @click="open = !open" @keydown.escape.window="open = false">
                    <img src="{{ $avatarUrl }}" class="h-8 rounded object-cover" alt="{{ $namaUser }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition @click.outside="open = false"
                    class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">

                    <a href="{{ route('profile.password') }}"
                        class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">Ubah Kata Sandi</a>
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
        @endauth
    </div>
</header>
