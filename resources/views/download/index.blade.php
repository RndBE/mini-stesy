@extends('layouts.app')

@section('content')
    <div class="space-y-3">
        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Unduh Aplikasi</h1>
                <p class="text-sm text-slate-500">Download aplikasi mobile untuk Android dan iOS</p>
            </div>
        </div>

        <div class="w-full md:w-1/2">
            <div class="space-y-2">
                <!-- Android Card -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow p-4">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-xl bg-slate-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-700" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M17.6 9.48l1.84-3.18c.16-.31.04-.69-.26-.85-.29-.15-.65-.06-.83.22l-1.88 3.24a11.5 11.5 0 00-8.94 0L5.65 5.67c-.19-.28-.54-.37-.83-.22-.3.16-.42.54-.26.85l1.84 3.18C4.8 11.1 3.5 13.76 3.5 16.5h17c0-2.74-1.3-5.4-2.9-7.02M7 14.5c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1m10 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1" />
                            </svg>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="font-semibold text-slate-900">Aplikasi Android</div>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">V
                                    {{ $downloads['android']['version'] }}</span>
                            </div>

                            <div class="mt-3 flex items-center gap-3">
                                <input type="text" value="{{ $downloads['android']['url'] }}"
                                    class="w-full h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
                                    readonly />

                                <a href="{{ $downloads['android']['url'] }}"
                                    class="shrink-0 h-11 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v10m0 0l4-4m-4 4l-4-4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 17v3h16v-3" />
                                    </svg>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- iOS Card -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow p-4">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-xl bg-slate-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-700" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                            </svg>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="font-semibold text-slate-900">Aplikasi iOS</div>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">V
                                    {{ $downloads['ios']['version'] }}</span>
                            </div>

                            <div class="mt-3 flex items-center gap-3">
                                <input type="text" value="{{ $downloads['ios']['url'] }}"
                                    class="w-full h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
                                    readonly />

                                <a href="{{ $downloads['ios']['url'] }}"
                                    class="shrink-0 h-11 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v10m0 0l4-4m-4 4l-4-4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 17v3h16v-3" />
                                    </svg>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="max-w-4xl">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Catatan Penting:</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-700">
                                    <li>Pastikan perangkat Anda terhubung ke internet saat mengunduh</li>
                                    <li>Untuk Android, izinkan instalasi dari sumber tidak dikenal jika diminta</li>
                                    <li>Hubungi administrator jika mengalami kendala saat instalasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
