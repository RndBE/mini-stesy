<!doctype html>
<html lang="en">
@include('partials.head')

<body class="bg-slate-50" x-data="{ pageTitle: '{{ $title ?? 'Beranda' }}' }">
    <style>
        @media (max-width: 1023px) {
            #mainContent {
                margin-left: 0 !important;
            }
        }

        
        [role="dialog"].fixed {
            z-index: 500 !important;
        }
        
        body.overflow-hidden #topbarLogoWrapper {
            z-index: 20 !important;
        }
    </style>
    <div class="min-h-screen flex">
        @auth
            @include('partials.sidebar')
            <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-black/50 lg:hidden"></div>
        @endauth

        <div id="mainContent" class="flex min-h-screen w-full flex-col transition-all duration-300"
            style="{{ auth()->check() ? 'margin-left: 16rem; width: calc(100% - 16rem);' : 'margin-left: 0; width: 100%;' }}">
            @include('partials.topbar')
@php
                $contentPaddingClass = request()->routeIs('peta.*') || request()->routeIs('skema-irigasi.*') ? '' : 'p-4';
                $showFooter = !request()->routeIs('peta.lokasi') && !request()->routeIs('skema-irigasi.*');
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
    @include('partials.chatbot')
    @include('partials.script')
    @stack('scripts')
</body>

</html>
