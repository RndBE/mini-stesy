@extends('layouts.app')

@section('content')
<style>[x-cloak]{display:none!important}</style>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-slate-800">Pengaturan Sistem</h2>
    </div>

    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-center gap-3">
            {{-- <img src="{{ asset('icons/check_circle.svg') }}" class="h-5 w-5 text-emerald-500" alt="Success"> --}}
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Maintenance Mode (Mode Perbaikan)</h3>
            <p class="mt-1 text-sm text-slate-500">Aktifkan mode ini untuk memblokir login aplikasi mobile sementara server diperbaiki.</p>
        </div>

        <div class="px-6 py-3">
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-3">
                @csrf

                <!-- Toggle Switch -->
                <div class="flex items-center gap-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700">Aktifkan Maintenance Mode</span>
                    </label>
                </div>

                <!-- Pesan Maintenance -->
                <div>
                    <label for="maintenance_message" class="block text-sm font-medium text-slate-700">Pesan Maintenance</label>
                    <p class="text-xs text-slate-500 mb-2">Pesan ini akan ditampilkan pada layar pop-up di aplikasi mobile pengguna, dan akan dikirimkan sebagai Notifikasi Broadcast.</p>
                    <textarea name="maintenance_message" id="maintenance_message" rows="3"
                        class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="Contoh: Server sedang dalam perbaikan. Harap tunggu hingga pukul 12:00.">{{ old('maintenance_message', $settings['maintenance_message'] ?? '') }}</textarea>
                </div>

                <!-- Versi Aplikasi Mobile -->
                <div class="pt-6 border-t border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Pembaruan Aplikasi Mobile</h3>

                    <div class="space-y-6">
                        <!-- Versi Aplikasi -->
                        <div>
                            <label for="latest_app_version" class="block text-sm font-medium text-slate-700">Versi Aplikasi Terbaru</label>
                            <p class="text-xs text-slate-500 mb-2">Masukkan versi terbaru aplikasi (misal: 1.0.0). Pengguna dengan versi di bawah ini akan mendapatkan peringatan.</p>
                            <input type="text" name="latest_app_version" id="latest_app_version"
                                class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                value="{{ old('latest_app_version', $settings['latest_app_version'] ?? '1.0.0') }}" placeholder="1.0.0">
                        </div>

                        <!-- URL Update -->
                        <div>
                            <label for="update_url" class="block text-sm font-medium text-slate-700">Tautan Unduhan (Play Store / Website)</label>
                            <p class="text-xs text-slate-500 mb-2">Tautan ini akan terbuka otomatis saat pengguna menekan tombol Update.</p>
                            <input type="text" name="update_url" id="update_url"
                                class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                value="{{ old('update_url', $settings['update_url'] ?? 'https://play.google.com/store/apps/details?id=com.ministesy.app') }}" placeholder="https://play.google.com/...">
                        </div>

                        <!-- Toggle Force Update -->
                        <div class="flex items-start gap-4">
                            <label class="relative inline-flex items-center cursor-pointer mt-1">
                                <input type="checkbox" name="force_update" value="1" class="sr-only peer" {{ ($settings['force_update'] ?? false) ? 'checked' : '' }}>
                                <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-600"></div>
                            </label>
                            <div>
                                <span class="text-sm font-medium text-slate-700">Wajibkan Pembaruan (Force Update)</span>
                                <p class="text-xs text-slate-500 mt-1">Jika diaktifkan, layar aplikasi akan dikunci dan pengguna wajib memperbarui untuk bisa menggunakannya.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{-- <img src="{{ asset('icons/save_line.svg') }}" class="h-4 w-4 brightness-0 invert" alt="Save"> --}}
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ Unduh Aplikasi (Halaman Download Web) ============ --}}
    @php
        $apkSize = $settings['download_android_apk_size'] ?? null;
        $apkSizeLabel = $apkSize ? number_format($apkSize / 1048576, 2) . ' MB' : null;
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Unduh Aplikasi</h3>
            <p class="mt-1 text-sm text-slate-500">Atur file APK atau tautan store yang tampil di halaman <a href="{{ route('download.index') }}" class="text-blue-600 hover:underline">Unduh Aplikasi</a>.</p>
        </div>

        <div class="px-6 py-4">
            <form action="{{ route('settings.download.update') }}" method="POST" enctype="multipart/form-data"
                class="space-y-6" x-data="{ androidMode: '{{ old('download_android_mode', $settings['download_android_mode'] ?? 'apk') }}' }">
                @csrf

                {{-- ANDROID --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Android</h4>

                    {{-- Pilih sumber --}}
                    <div class="flex flex-wrap gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="download_android_mode" value="apk" class="peer sr-only" x-model="androidMode">
                            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700">Upload APK</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="download_android_mode" value="playstore" class="peer sr-only" x-model="androidMode">
                            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700">Link Play Store</span>
                        </label>
                    </div>

                    {{-- Mode: Upload APK --}}
                    <div x-show="androidMode === 'apk'" x-cloak class="space-y-2">
                        @if (!empty($settings['download_android_apk_path']))
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <div>
                                    <p class="font-medium text-slate-700">{{ $settings['download_android_apk_name'] ?? 'APK terpasang' }}</p>
                                    <p class="text-xs text-slate-500">
                                        @if ($apkSizeLabel){{ $apkSizeLabel }} · @endif
                                        Diunggah {{ $settings['download_android_apk_uploaded_at'] ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        <label for="download_android_apk" class="block text-sm font-medium text-slate-700">
                            {{ !empty($settings['download_android_apk_path']) ? 'Ganti File APK' : 'Unggah File APK' }}
                        </label>
                        <input type="file" name="download_android_apk" id="download_android_apk" accept=".apk"
                            class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-500">
                        <p class="text-xs text-slate-500">Format .apk, maksimal 200 MB.</p>
                        @error('download_android_apk')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Mode: Play Store --}}
                    <div x-show="androidMode === 'playstore'" x-cloak>
                        <label for="download_android_playstore_url" class="block text-sm font-medium text-slate-700">Tautan Play Store</label>
                        <input type="url" name="download_android_playstore_url" id="download_android_playstore_url"
                            class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            value="{{ old('download_android_playstore_url', $settings['download_android_playstore_url'] ?? '') }}"
                            placeholder="https://play.google.com/store/apps/details?id=...">
                        @error('download_android_playstore_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Versi Android --}}
                    <div>
                        <label for="download_android_version" class="block text-sm font-medium text-slate-700">Versi Android</label>
                        <input type="text" name="download_android_version" id="download_android_version"
                            class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            value="{{ old('download_android_version', $settings['download_android_version'] ?? '') }}" placeholder="1.2.0">
                    </div>
                </div>

                {{-- iOS --}}
                <div class="space-y-4 border-t border-slate-200 pt-6">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">iOS</h4>
                    <div>
                        <label for="download_ios_url" class="block text-sm font-medium text-slate-700">Tautan App Store</label>
                        <input type="url" name="download_ios_url" id="download_ios_url"
                            class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            value="{{ old('download_ios_url', $settings['download_ios_url'] ?? '') }}"
                            placeholder="https://apps.apple.com/id/app/...">
                        @error('download_ios_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="download_ios_version" class="block text-sm font-medium text-slate-700">Versi iOS</label>
                        <input type="text" name="download_ios_version" id="download_ios_version"
                            class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            value="{{ old('download_ios_version', $settings['download_ios_version'] ?? '') }}" placeholder="1.3.6">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan Unduhan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
