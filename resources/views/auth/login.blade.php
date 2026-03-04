@extends('layouts.auth')

@section('content')
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-white">
        <section class="relative overflow-hidden px-6 py-10 sm:px-12 lg:col-span-8">

            <div class="relative z-10 flex h-full flex-col bg-blue items-center">


                <div class="mt-10 flex flex-1 items-center justify-center">
                    <img src="{{ asset('images/login_page.svg') }}" alt="Dashboard illustration"
                        class="w-full max-w-xl rounded-3xl " />
                </div>

                <div class="mt-8 max-w-7xl">
                    <h2 class="text-3xl sm:text-4xl font-semibold text-[#303481] italic">
                        Semua Kendali di <span class="underline decoration-[#FF3333] decoration-4 underline-offset-4">Satu
                            Aplikasi</span>
                    </h2>
                    <div class="max-w-3xl">
                        <p class="mt-4 text-base sm:text-lg text-[#303481]">
                            Monitoring berbagai data secara
                            <span class="inline-block rounded-xl bg-[#F8E3B3] px-2  font-medium  ">real-time,</span>
                            hanya
                            <span class="inline-block rounded-xl bg-[#F8E3B3] px-2  font-medium ">lewat ujung jari</span>.
                        </p>
                    </div>
                    <div class="mt-6 flex items-center justify-between text-xs text-slate-500">
                        <span>&copy; Beacon Engineering 2026</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex flex-col items-center justify-center bg-slate-50 px-6 py-12 sm:px-12 lg:col-span-4">
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <h1 class="text-3xl font-semibold text-indigo-900">Masuk ke Akun</h1>
                    <p class="mt-2 text-sm text-slate-500">Masukkan detail akun Anda untuk melanjutkan.</p>
                </div>

                <x-auth-session-status class="mb-4 text-sm text-slate-600" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="text-sm font-semibold text-indigo-900">Nama Pengguna</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required
                            autofocus autocomplete="username"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            placeholder="Masukkan Nama Pengguna" />
                        @error('username')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="password" class="text-sm font-semibold text-indigo-900">Kata Sandi</label>
                        <div class="relative mt-2">
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                                autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-slate-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                placeholder="Masukkan Kata Sandi" />
                            <button type="button"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-indigo-700"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                :aria-pressed="showPassword.toString()">
                                <svg x-show="!showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.4 0-10-7-10-7a21.8 21.8 0 0 1 5.06-6.94">
                                    </path>
                                    <path d="M1 1l22 22"></path>
                                    <path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"></path>
                                    <path d="M14.12 14.12A3 3 0 0 0 9.88 9.88"></path>
                                    <path d="M5.06 5.06A10.94 10.94 0 0 1 12 5c6.4 0 10 7 10 7a21.8 21.8 0 0 1-4.22 5.06">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600">
                            <input id="remember_me" type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                name="remember">
                            Ingat saya
                        </label>
                        @if (Route::has('password.request'))
                            <a class="font-medium text-indigo-600 hover:text-indigo-800"
                                href="{{ route('password.request') }}">
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="mt-2 w-full rounded-xl bg-indigo-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        Masuk
                    </button>
                </form>

                <div class="mt-8 flex w-full justify-center items-center gap-4">
                    <img src="{{ asset('images/beacon-logo.png') }}" alt="Beacon Logo" class="h-8 object-contain" />
                    <img src="{{ asset('images/mini_stesy.png') }}" alt="Mini Stesy" class="h-7 object-contain" />
                </div>
            </div>
        </section>
    </div>
@endsection
