<!doctype html>
<html lang="en">
@include('partials.head')

<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="min-h-screen">
        @yield('content')
    </main>
    @include('partials.script')
    @stack('scripts')
</body>

</html>
