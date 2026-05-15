@extends('layouts.app')

@section('content')
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

        <div class="p-2">
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-2">
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
</div>
@endsection
