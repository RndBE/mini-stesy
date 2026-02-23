@extends('layouts.app')

@section('content')
    <div class="w-full px-3 py-4 sm:px-4">
        <div class="mr-auto w-full rounded-xl border border-slate-300 bg-white p-4 shadow-none sm:p-6"
            style="max-width: 760px;" x-data="{
            showCurrent: false,
            showNew: false,
            showConfirm: false
        }">
            <h1 class="text-lg font-extrabold tracking-tight text-slate-900 sm:text-xl">Ubah Kata Sandi Anda.</h1>
            <p class="mt-1.5 text-[11px] text-slate-700 sm:text-xs">Pastikan kata sandi baru Anda kuat dan belum pernah digunakan sebelumnya.
            </p>

            @if (session('status') === 'password-updated')
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    Kata sandi berhasil diperbarui.
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-2 block text-base font-bold text-slate-900">Kata
                        Sandi Saat Ini</label>
                    <div class="relative">
                        <input id="current_password" name="current_password" :type="showCurrent ? 'text' : 'password'"
                            autocomplete="current-password"
                            class="h-12 w-full rounded-lg border border-slate-300 bg-white px-3.5 pr-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required>
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                            @click="showCurrent = !showCurrent" :aria-label="showCurrent ? 'Sembunyikan password' : 'Lihat password'">
                            <svg x-show="!showCurrent" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                            </svg>
                            <svg x-show="showCurrent" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.06 10.06 0 012.042-3.368m3.1-2.62A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.96 9.96 0 01-4.293 5.116M15 12a3 3 0 00-4.122-2.793M9.88 14.12A3 3 0 0014.12 9.88" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @if ($errors->updatePassword->has('current_password'))
                        <p class="mt-2 text-xs font-medium text-red-600">
                            {{ $errors->updatePassword->first('current_password') }}
                        </p>
                    @endif
                </div>

                <div>
                    <label for="password" class="mb-2 block text-base font-bold text-slate-900">Kata Sandi
                        Baru</label>
                    <div class="relative">
                        <input id="password" name="password" :type="showNew ? 'text' : 'password'"
                            autocomplete="new-password" placeholder="Masukkan Kata Sandi Baru"
                            class="h-12 w-full rounded-lg border border-slate-300 bg-white px-3.5 pr-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required>
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                            @click="showNew = !showNew" :aria-label="showNew ? 'Sembunyikan password' : 'Lihat password'">
                            <svg x-show="!showNew" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                            </svg>
                            <svg x-show="showNew" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.06 10.06 0 012.042-3.368m3.1-2.62A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.96 9.96 0 01-4.293 5.116M15 12a3 3 0 00-4.122-2.793M9.88 14.12A3 3 0 0014.12 9.88" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-1.5 flex items-center gap-2 text-[11px] text-slate-700 sm:text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-900" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10A8 8 0 114 4.932V4a1 1 0 112 0v.101A8 8 0 0118 10zM9 9a1 1 0 012 0v5a1 1 0 11-2 0V9zm1-4a1.25 1.25 0 100 2.5A1.25 1.25 0 0010 5z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Minimal 8 karakter. Gunakan kombinasi huruf dan angka.</span>
                    </div>
                    @if ($errors->updatePassword->has('password'))
                        <p class="mt-2 text-xs font-medium text-red-600">
                            {{ $errors->updatePassword->first('password') }}
                        </p>
                    @endif
                </div>

                <div>
                    <label for="password_confirmation"
                        class="mb-2 block text-base font-bold text-slate-900">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation"
                            :type="showConfirm ? 'text' : 'password'" autocomplete="new-password"
                            placeholder="Ulangi Kata Sandi Baru"
                            class="h-12 w-full rounded-lg border border-slate-300 bg-white px-3.5 pr-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            required>
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                            @click="showConfirm = !showConfirm"
                            :aria-label="showConfirm ? 'Sembunyikan password' : 'Lihat password'">
                            <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                            </svg>
                            <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.06 10.06 0 012.042-3.368m3.1-2.62A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.96 9.96 0 01-4.293 5.116M15 12a3 3 0 00-4.122-2.793M9.88 14.12A3 3 0 0014.12 9.88" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @if ($errors->updatePassword->has('password_confirmation'))
                        <p class="mt-2 text-xs font-medium text-red-600">
                            {{ $errors->updatePassword->first('password_confirmation') }}
                        </p>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-3 pt-6 sm:grid-cols-2">
                    <button type="button" onclick="window.history.back()"
                        class="h-12 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-12 rounded-lg bg-indigo-800 text-sm font-semibold text-white transition hover:bg-indigo-900">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
