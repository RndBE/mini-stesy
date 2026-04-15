@extends('layouts.app')

@section('content')
<div x-data="settingLogger()" class="space-y-4">
<div class="rounded-xl overflow-hidden shadow-md" style="background: linear-gradient(135deg, #303481 0%, #1a1d5e 60%, #10134B 100%);">
        <div class="px-6 py-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
<div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center backdrop-blur-sm border border-white/20">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg font-bold text-white leading-tight">Pos AWLR Sungai Brantas</h1>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-400/30 text-blue-100 border border-blue-300/30">
                            AWLR Ã¢â‚¬â€œ Non JIAT
                        </span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-blue-100/80">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Jl. Ahmad Yani No. 12, Malang
                        </span>
                        <span class="text-blue-300/50">Ã‚Â·</span>
                        <span class="font-mono text-xs text-blue-200/70">ID: BCN-AWLR-0042</span>
                    </div>
                </div>
            </div>
<div class="flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                    </span>
                    <span class="text-sm font-semibold text-emerald-300">Online</span>
                </div>
                <div class="h-4 w-px bg-white/20 hidden sm:block"></div>
                <div class="text-xs text-blue-100/70">
                    <span class="text-blue-200/50">Last update</span><br>
                    <span class="font-semibold text-white/80">10 Mar 2026 Ã‚Â· 10:32:05</span>
                </div>
            </div>
<div class="flex items-center gap-2 flex-shrink-0">
<button
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-white border border-white/25 bg-white/10 hover:bg-white/20 transition-all duration-200 backdrop-blur-sm"
                    title="Sync data dari logger">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync
                </button>
<button
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-red-300 border border-red-400/30 bg-red-500/10 hover:bg-red-500/25 transition-all duration-200 backdrop-blur-sm"
                    title="Restart logger">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" />
                    </svg>
                    Restart
                </button>
<button
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-bold text-white bg-white/20 hover:bg-white/30 border border-white/30 transition-all duration-200 shadow-sm"
                    title="Simpan pengaturan">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Simpan
                </button>
            </div>
        </div>
    </div>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
<div class="bg-white rounded-xl border border-slate-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Baterai</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9l-5-6H9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v6h6" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">78<span class="text-sm font-semibold text-slate-400 ml-0.5">%</span></p>
            <div class="mt-2 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-400 rounded-full" style="width: 78%"></div>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">12.4 V</p>
        </div>
<div class="bg-white rounded-xl border border-slate-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sinyal</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3a4.237 4.237 0 00-6 0zm-4-4l2 2a7.074 7.074 0 0110 0l2-2C15.14 9.14 8.87 9.14 5 13z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">-73<span class="text-sm font-semibold text-slate-400 ml-0.5">dBm</span></p>
            <div class="mt-2 flex items-end gap-0.5 h-5">
                @foreach([3, 5, 7, 9, 11] as $h)
                    <div class="flex-1 rounded-sm {{ $loop->index < 4 ? 'bg-blue-400' : 'bg-slate-200' }}" style="height: {{ $h * 2 }}px"></div>
                @endforeach
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Kuat Ã‚Â· 4G</p>
        </div>
<div class="bg-white rounded-xl border border-slate-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sensor</span>
                <div class="w-7 h-7 rounded-lg bg-violet-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">3<span class="text-sm font-semibold text-slate-400 ml-0.5">/ 4</span></p>
            <div class="mt-2 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-violet-400 rounded-full" style="width: 75%"></div>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">1 sensor nonaktif</p>
        </div>
<div class="bg-white rounded-xl border border-slate-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Interval</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">15<span class="text-sm font-semibold text-slate-400 ml-0.5">mnt</span></p>
            <p class="mt-3 text-[11px] text-slate-400">Kirim data setiap 15 menit</p>
        </div>
<div class="bg-white rounded-xl border border-slate-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Firmware</span>
                <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
            </div>
            <p class="text-xl font-bold text-slate-900 font-mono">v2.4.1</p>
            <span class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3"/>
                </svg>
                Up to date
            </span>
        </div>
    </div>
<div class="flex gap-4 items-start">
<div class="flex-shrink-0 w-44 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-3 py-2.5 border-b border-slate-100">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pengaturan</p>
            </div>
            <nav class="p-2 space-y-0.5">
                @php
                    $tabs = [
                        ['key' => 'umum',        'label' => 'Umum',        'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['key' => 'sensor',      'label' => 'Sensor',      'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['key' => 'komunikasi',  'label' => 'Komunikasi',  'icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0'],
                        ['key' => 'alarm',       'label' => 'Alarm',       'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                        ['key' => 'daya',        'label' => 'Daya',        'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['key' => 'maintenance', 'label' => 'Maintenance', 'icon' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z'],
                    ];
                @endphp

                @foreach ($tabs as $tab)
                    <button @click="activeTab = '{{ $tab['key'] }}'"
                        :class="activeTab === '{{ $tab['key'] }}'
                            ? 'bg-[#303481] text-white'
                            : 'text-slate-600 hover:bg-slate-100'"
                        class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 text-left">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}" />
                        </svg>
                        <span>{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </nav>
        </div>
<div class="flex-1 min-w-0">
<div x-show="activeTab === 'umum'" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="w-1 h-5 rounded-full bg-[#303481]"></div>
                        <h2 class="text-base font-bold text-slate-900">Pengaturan Umum</h2>
                    </div>
                    <div class="px-5 py-5 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama Logger</label>
                                <input type="text" value="Pos AWLR Sungai Brantas"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">ID Logger</label>
                                <input type="text" value="BCN-AWLR-0042" readonly
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Zona Waktu</label>
                                <select class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                                    <option selected>Asia/Jakarta (UTC+7)</option>
                                    <option>Asia/Makassar (UTC+8)</option>
                                    <option>Asia/Jayapura (UTC+9)</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Interval Kirim Data</label>
                                <div class="flex rounded-lg border border-slate-300 overflow-hidden focus-within:border-[#303481] focus-within:ring-1 focus-within:ring-[#303481] transition">
                                    <input type="number" value="15" min="1" max="1440"
                                        class="flex-1 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-0 outline-none">
                                    <span class="flex items-center border-l border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-500">menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Deskripsi / Catatan</label>
                            <textarea rows="3" placeholder="Tambahkan catatan tentang logger ini..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition resize-none">Logger utama untuk monitoring tinggi muka air Sungai Brantas di titik pengamatan utama.</textarea>
                        </div>
                    </div>
                </div>
            </div>
<div x-show="activeTab === 'sensor'" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full bg-[#303481]"></div>
                            <h2 class="text-base font-bold text-slate-900">Konfigurasi Sensor</h2>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">4 sensor terdaftar</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Sensor</th>
                                    <th class="px-5 py-3">Kolom</th>
                                    <th class="px-5 py-3">Satuan</th>
                                    <th class="px-5 py-3">Kalibrasi</th>
                                    <th class="px-5 py-3 text-center">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $sensors = [
                                        ['name' => 'TMA (Tinggi Muka Air)', 'col' => 'sensor_1', 'unit' => 'm',   'calib' => '+0.00', 'active' => true],
                                        ['name' => 'Debit Air',              'col' => 'sensor_2', 'unit' => 'mÃ‚Â³/s','calib' => '+0.00', 'active' => true],
                                        ['name' => 'Curah Hujan',            'col' => 'sensor_3', 'unit' => 'mm',  'calib' => '-0.05', 'active' => true],
                                        ['name' => 'Suhu Air',               'col' => 'sensor_4', 'unit' => 'Ã‚Â°C',  'calib' => '+0.00', 'active' => false],
                                    ];
                                @endphp
                                @foreach ($sensors as $i => $sensor)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex w-5 h-5 rounded-full items-center justify-center text-[10px] font-bold
                                                {{ $sensor['active'] ? 'bg-[#303481]/10 text-[#303481]' : 'bg-slate-100 text-slate-400' }}">{{ $i + 1 }}</span>
                                            <span class="font-semibold text-slate-800">{{ $sensor['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $sensor['col'] }}</td>
                                    <td class="px-5 py-3.5 text-slate-700">{{ $sensor['unit'] }}</td>
                                    <td class="px-5 py-3.5">
                                        <input type="text" value="{{ $sensor['calib'] }}"
                                            class="w-20 rounded-md border border-slate-200 px-2 py-1 text-xs text-center text-slate-700 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none">
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" {{ $sensor['active'] ? 'checked' : '' }}>
                                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#303481]"></div>
                                        </label>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
<div x-show="activeTab === 'komunikasi'" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="w-1 h-5 rounded-full bg-[#303481]"></div>
                        <h2 class="text-base font-bold text-slate-900">Konfigurasi Komunikasi</h2>
                    </div>
                    <div class="px-5 py-5 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Protokol</label>
                                <select class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                                    <option selected>MQTT</option>
                                    <option>HTTP</option>
                                    <option>FTP</option>
                                </select>
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Server / Broker</label>
                                <input type="text" value="mqtt.beacon-iot.id"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Port</label>
                                <input type="number" value="1883"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Client ID</label>
                                <input type="text" value="BCN-AWLR-0042"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">QoS Level</label>
                                <select class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                                    <option>0 Ã¢â‚¬â€œ At most once</option>
                                    <option selected>1 Ã¢â‚¬â€œ At least once</option>
                                    <option>2 Ã¢â‚¬â€œ Exactly once</option>
                                </select>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-5">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-3">Pengaturan SIM / APN</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">APN</label>
                                    <input type="text" value="internet" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Username SIM</label>
                                    <input type="text" placeholder="Kosongkan jika tidak ada" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-300 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Password SIM</label>
                                    <input type="password" placeholder="Kosongkan jika tidak ada" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-300 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<div x-show="activeTab === 'alarm'" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="w-1 h-5 rounded-full bg-[#303481]"></div>
                        <h2 class="text-base font-bold text-slate-900">Konfigurasi Alarm</h2>
                    </div>
                    <div class="px-5 py-5 space-y-5">
<div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-3">Batas Nilai Sensor (TMA)</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach([['label' => 'Batas Siaga 1 (Kritis)', 'val' => '3.50', 'color' => 'red'], ['label' => 'Batas Siaga 2 (Waspada)', 'val' => '2.80', 'color' => 'amber'], ['label' => 'Batas Siaga 3 (Normal Tinggi)', 'val' => '2.00', 'color' => 'yellow'], ['label' => 'Batas Normal', 'val' => '0.50', 'color' => 'emerald']] as $threshold)
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 bg-slate-50">
                                    <div class="w-2.5 h-10 rounded-full bg-{{ $threshold['color'] }}-400 flex-shrink-0"></div>
                                    <div class="flex-1">
                                        <label class="text-xs font-semibold text-slate-500">{{ $threshold['label'] }}</label>
                                        <div class="mt-1 flex rounded-md border border-slate-300 bg-white overflow-hidden focus-within:border-[#303481]">
                                            <input type="number" step="0.01" value="{{ $threshold['val'] }}"
                                                class="flex-1 border-0 px-2 py-1.5 text-sm text-slate-800 focus:ring-0 outline-none">
                                            <span class="flex items-center border-l border-slate-200 bg-slate-50 px-2 text-xs font-medium text-slate-500">m</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
<div class="border-t border-slate-100 pt-5">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-3">Notifikasi</p>
                            <div class="space-y-3">
                                @foreach([['label' => 'Notifikasi Email', 'desc' => 'Kirim email saat alarm terpicu', 'checked' => true], ['label' => 'Notifikasi WhatsApp', 'desc' => 'Kirim pesan WA saat alarm terpicu', 'checked' => true], ['label' => 'Bunyi Alarm Lokal', 'desc' => 'Aktifkan buzzer pada logger', 'checked' => false]] as $notif)
                                <div class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-slate-100 hover:bg-slate-50 transition">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $notif['label'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $notif['desc'] }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" {{ $notif['checked'] ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#303481]"></div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<div x-show="activeTab === 'daya'" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="w-1 h-5 rounded-full bg-[#303481]"></div>
                        <h2 class="text-base font-bold text-slate-900">Manajemen Daya</h2>
                    </div>
                    <div class="px-5 py-5 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Mode Daya</label>
                                <select class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#303481] focus:ring-1 focus:ring-[#303481] outline-none transition">
                                    <option selected>Normal (Selalu aktif)</option>
                                    <option>Hemat Daya (Sleep mode)</option>
                                    <option>Ultra Hemat (Deep sleep)</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Batas Baterai Minimum</label>
                                <div class="flex rounded-lg border border-slate-300 overflow-hidden focus-within:border-[#303481] focus-within:ring-1 focus-within:ring-[#303481] transition">
                                    <input type="number" value="20" min="5" max="50"
                                        class="flex-1 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-0 outline-none">
                                    <span class="flex items-center border-l border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-500">%</span>
                                </div>
                                <p class="text-[11px] text-slate-400">Logger akan berhenti kirim data di bawah batas ini</p>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-slate-100 pt-5">
                            @foreach([['label' => 'Panel Surya (Solar Panel)', 'desc' => 'Aktifkan pengisian baterai via solar panel', 'checked' => true], ['label' => 'Auto Sleep saat tidak ada data', 'desc' => 'Masuk sleep mode jika tidak ada perubahan data dalam 30 menit', 'checked' => false]] as $opt)
                            <div class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-slate-100 hover:bg-slate-50 transition">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $opt['label'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $opt['desc'] }}</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" {{ $opt['checked'] ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#303481]"></div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
<div x-show="activeTab === 'maintenance'" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="w-1 h-5 rounded-full bg-[#303481]"></div>
                        <h2 class="text-base font-bold text-slate-900">Maintenance & Diagnostik</h2>
                    </div>
                    <div class="px-5 py-5 space-y-5">
<div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-3">Informasi Firmware</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach([['label' => 'Versi Firmware', 'val' => 'v2.4.1'], ['label' => 'Build Date', 'val' => '2026-01-15'], ['label' => 'Uptime', 'val' => '14 hari'], ['label' => 'Free Memory', 'val' => '64 KB']] as $info)
                                <div>
                                    <p class="text-xs text-slate-400">{{ $info['label'] }}</p>
                                    <p class="text-sm font-bold text-slate-800 mt-0.5 font-mono">{{ $info['val'] }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
<div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-3">Tindakan</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all group text-left">
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center flex-shrink-0 transition">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Export Log Data</p>
                                        <p class="text-xs text-slate-400">Unduh log aktivitas logger</p>
                                    </div>
                                </button>
                                <button class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50 transition-all group text-left">
                                    <div class="w-9 h-9 rounded-lg bg-amber-100 group-hover:bg-amber-200 flex items-center justify-center flex-shrink-0 transition">
                                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Restart Logger</p>
                                        <p class="text-xs text-slate-400">Restart perangkat dari jarak jauh</p>
                                    </div>
                                </button>
                                <button class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50 transition-all group text-left">
                                    <div class="w-9 h-9 rounded-lg bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center flex-shrink-0 transition">
                                        <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Update Firmware OTA</p>
                                        <p class="text-xs text-slate-400">Update firmware via Over-the-Air</p>
                                    </div>
                                </button>
                                <button class="flex items-center gap-3 p-4 rounded-xl border border-red-100 hover:border-red-300 hover:bg-red-50 transition-all group text-left">
                                    <div class="w-9 h-9 rounded-lg bg-red-100 group-hover:bg-red-200 flex items-center justify-center flex-shrink-0 transition">
                                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-red-600">Factory Reset</p>
                                        <p class="text-xs text-slate-400">Reset ke pengaturan pabrik</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
function settingLogger() {
    return {
        activeTab: 'umum',
    }
}
</script>
@endpush
@endsection
