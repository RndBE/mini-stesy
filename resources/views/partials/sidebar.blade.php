{{-- <aside class="fixed inset-y-0 left-0 hidden .w-[260px] border-r border-slate-200 bg-white lg:block"> --}}
<aside class="fixed top-0 left-0 z-40 w-64 h-full transition-transform -translate-x-full bg-white border-r sm:translate-x-0">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/beacon-logo.png') }}" alt="Beacon Engineering" class="h-full w-auto">
                <div
                    class="ml-auto flex items-center justify-center rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-500">
                    ☐
                </div>
            </div>
        </div>

        <nav class="px-3">
            <div class="space-y-1">
                <a href="{{ route('beranda') }}"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('beranda') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span>Beranda</span>
                    {{-- <span
                        class="text-[11px] {{ request()->routeIs('beranda') ? 'text-white/70' : 'text-slate-400' }}">/beranda</span> --}}
                </a>

                <a href="{{ route('peta.lokasi') }}"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('peta.lokasi') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span>Peta Lokasi</span>
                    {{-- <span
                        class="text-[11px] {{ request()->routeIs('peta.lokasi') ? 'text-white/70' : 'text-slate-400' }}">/peta</span> --}}
                </a>
            </div>
        </nav>

        <div class="mt-auto px-3 pb-5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Logout
                </button>
            </form>

            <div class="mt-4 px-3 text-[11px] text-slate-400">© Beacon Engineering {{ now()->year }}</div>
        </div>
    </div>
</aside>
