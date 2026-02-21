<!doctype html>
<html lang="en">
@include('partials.head')

<body class="bg-slate-50" x-data="{ pageTitle: '{{ $title ?? 'Beranda' }}' }">
    <style>
        @media (max-width: 768px) {
            #mainContent {
                margin-left: 0 !important;
            }
        }
    </style>
    <div class="min-h-screen flex">
        @include('partials.sidebar')
        <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-black/50 md:hidden"></div>

        <div id="mainContent" class="flex min-h-screen w-full flex-col transition-all duration-300"
            style="margin-left: 16rem; width: calc(100% - 16rem);">
            @include('partials.topbar')

            {{-- <main class="mx-auto w-full max-w-[1300px] flex-1 px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
            </main> --}}
            @php
                $contentPaddingClass = request()->routeIs('peta.*') ? '' : 'p-4';
                $showFooter = !request()->routeIs('peta.lokasi');
            @endphp
            <main class="flex-1 min-h-[calc(100vh-5rem)] min-w-0">
                <div class=" bg-white min-h-[calc(100vh-5rem)] min-w-0 overflow-x-hidden">
                    <div class="w-full max-w-full {{ $contentPaddingClass }}">
                        @yield('content')
                    </div>
                </div>
            </main>

            @if ($showFooter)
                @include('partials.footer')
            @endif
        </div>
    </div>
    @include('partials.script')
    @stack('scripts')
</body>

</html>
