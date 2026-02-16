{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html> --}}
<!doctype html>
<html lang="en">
@include('partials.head')

<body class="bg-slate-50" x-data="{ pageTitle: '{{ $title ?? 'Beranda' }}' }">
    <div class="min-h-screen flex">
        @include('partials.sidebar')

        <div id="mainContent" class="flex min-h-screen w-full flex-col transition-all duration-300"
            style="padding-left: 16rem;">
            @include('partials.topbar')

            {{-- <main class="mx-auto w-full max-w-[1300px] flex-1 px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
            </main> --}}
            <main class="flex-1 min-h-[calc(100vh-5rem)] min-w-0">
                <div class="p-4 sm:p-6 bg-white min-h-[calc(100vh-5rem)] min-w-0 overflow-x-hidden">
                    <div class="w-full max-w-full">
                        @yield('content')
                    </div>
                </div>
            </main>

            @include('partials.footer')
        </div>
    </div>
    @include('partials.script')
    @stack('scripts')
</body>

</html>
