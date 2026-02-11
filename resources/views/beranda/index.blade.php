@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div class="text-sm font-extrabold text-slate-900">AWLR (Automatic Water Level Recorder)</div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @foreach ($loggers as $lg)
                @php
                    $latest = $lg->temp;
                    $waktu = $latest?->waktu ? \Carbon\Carbon::parse($latest->waktu)->format('Y-m-d H:i') : '-';

                    $isOnline = (bool) ($latest?->is_online ?? true);
                    $isOnline = $lg->status_logger === 'online' ? $isOnline : false;
                    $isSdOk = (bool) ($latest?->is_sd_ok ?? true);

                    $timeClass = 'bg-slate-100 text-slate-700';
                    $dotClass = $isOnline ? 'bg-green-500' : 'bg-gray-800';

                    $badgeClass = $isOnline
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-rose-200 bg-rose-50 text-rose-700';
                    $sdClass = $isSdOk
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-rose-200 bg-rose-50 text-rose-700';

                    $statusText = $isOnline ? 'Koneksi Terhubung' : 'Koneksi Terputus';
                    $sdText = $isSdOk ? 'OK' : 'Bermasalah';

                    $pHumidity =
                        $lg->params->firstWhere('parameter_utama', 'humidity_logger') ??
                        $lg->params->firstWhere('nama_parameter', 'humidity_logger');

                    $pBattery =
                        $lg->params->firstWhere('parameter_utama', 'battery_logger') ??
                        $lg->params->firstWhere('nama_parameter', 'battery_logger');

                    $pTemp =
                        $lg->params->firstWhere('parameter_utama', 'temperature_logger') ??
                        $lg->params->firstWhere('nama_parameter', 'temperature_logger');

                    $pMukaAir =
                        $lg->params->firstWhere('parameter_utama', 'muka_air_tanah') ??
                        $lg->params->firstWhere('nama_parameter', 'muka_air_tanah');

                    $humidity =
                        $latest && $pHumidity && $pHumidity->kolom_sensor
                            ? $latest->{$pHumidity->kolom_sensor} ?? null
                            : null;
                    $battery =
                        $latest && $pBattery && $pBattery->kolom_sensor
                            ? $latest->{$pBattery->kolom_sensor} ?? null
                            : null;
                    $temp = $latest && $pTemp && $pTemp->kolom_sensor ? $latest->{$pTemp->kolom_sensor} ?? null : null;

                    $MukaAir =
                        $latest && $pMukaAir && $pMukaAir->kolom_sensor
                            ? $latest->{$pMukaAir->kolom_sensor} ?? null
                            : null;

                    $DataAir =
                        is_numeric($lg->jiat?->kedalaman_sumur) && is_numeric($MukaAir)
                            ? $lg->jiat?->kedalaman_sumur - $MukaAir
                            : null;

                    $muted = !$isOnline;
                    $iconClass = $muted ? 'grayscale opacity-40' : '';
                @endphp

                {{-- @php
                    $waktu = \Carbon\Carbon::parse($lg['waktu'])->format('Y-m-d H:i');

                    $isOnline = $lg['status'] === 'online';
                    $isSdOk = true; // API ini belum kirim status SD

                    $timeClass = $isOnline ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                    $dotClass = $isOnline ? 'bg-emerald-500' : 'bg-rose-500';

                    $badgeClass = $isOnline
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-rose-200 bg-rose-50 text-rose-700';

                    $sdClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';

                    $statusText = $isOnline ? 'Koneksi Terhubung' : 'Koneksi Terputus';
                    $sdText = 'OK';

                    $humidity = $lg['humidity'] ?? null;
                    $battery = $lg['battery'] ?? null;
                    $temp = $lg['temp'] ?? null;

                    $DataAir = null;
                    $MukaAir = null;

                    $muted = !$isOnline;
                @endphp --}}

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between bg-neutral-100 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="text-sm font-extrabold text-slate-900">
                                {{ $lg->nama_logger }}
                            </div>
                            <div x-data="{ open: false }" class="relative">
                                <button type="button" @click="open = !open" @keydown.escape.window="open = false"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-indigo-600 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    aria-label="Info Logger">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 9h.01" />
                                        <path d="M11 12h1v4h1" />
                                        <path d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition.origin.top.left @click.outside="open = false"
                                    class="absolute left-0 z-30 mt-2 w-[360px] max-w-[90vw]" style="display: none;">
                                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                        <div
                                            class="grid grid-cols-3 gap-0 border-b border-slate-200 bg-white text-xs font-semibold text-slate-700">
                                            <div class="px-3 py-2">ID Logger</div>
                                            <div class="col-span-2 px-3 py-2 text-right text-slate-900">{{ $lg->id_logger }}
                                            </div>
                                        </div>

                                        <div
                                            class="grid grid-cols-3 gap-0 border-b border-slate-200 bg-white text-xs font-semibold text-slate-700">
                                            <div class="px-3 py-2">Status Logger</div>
                                            <div
                                                class="col-span-2 flex items-center justify-end gap-2 px-3 py-2 text-slate-900">
                                                <span>{{ $statusText }}</span>
                                                <span
                                                    class="inline-flex h-4 w-4 items-center justify-center rounded-full border {{ $badgeClass }}">
                                                    {!! $isOnline
                                                        ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>'
                                                        : '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>' !!}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-0 bg-white text-xs font-semibold text-slate-700">
                                            <div class="px-3 py-2">Status SD Card</div>
                                            <div
                                                class="col-span-2 flex items-center justify-end gap-2 px-3 py-2 text-slate-900">
                                                <span>{{ $sdText }}</span>
                                                <span
                                                    class="inline-flex h-4 w-4 items-center justify-center rounded-full border {{ $sdClass }}">
                                                    {!! $isSdOk
                                                        ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>'
                                                        : '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>' !!}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold {{ $timeClass }}">
                            <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
                            <span>{{ $waktu }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-bold text-slate-700">Data Sumur</div>
                                <div
                                    class="rounded-full px-3 py-1 text-[11px] font-semibold ring-1 ring-slate-200 text-slate-700">
                                    {{ $lg->kategori?->nama_kategori ?? '—' }}
                                </div>
                            </div>

                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="absolute left-2 top-2 space-y-2 text-[10px] font-semibold text-slate-600">
                                    <div
                                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-orange-200 bg-orange-50 text-orange-700' : 'border-slate-300 bg-white text-slate-500' }}">
                                        DATA AIR TANAH<br><span class="text-slate-900">{{ $DataAir ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                    <div
                                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-300 bg-white text-slate-500' }}">
                                        ELEVASI SENSOR<br><span
                                            class="text-slate-900">{{ $lg?->jiat?->kedalaman_sensor ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                </div>

                                <div class="absolute right-2 top-2 space-y-2 text-[10px] font-semibold text-slate-600">
                                    <div
                                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-slate-300 bg-white text-slate-500' }}">
                                        MUKA AIR TANAH<br><span class="text-slate-900">{{ $MukaAir ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                    <div
                                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-300 bg-white text-slate-500' }}">
                                        ELEVASI POMPA<br><span
                                            class="text-slate-900">{{ $lg?->jiat?->kedalaman_pompa ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                </div>

                                @php
                                    // Calculate water height percentage for visualization
                                    $kedalamanSumur = $lg?->jiat?->kedalaman_sumur ?? 100;
                                    $mukaAirTanah = $MukaAir ?? 0;
                                    $waterDepth =
                                        is_numeric($mukaAirTanah) && is_numeric($kedalamanSumur)
                                            ? max(0, $kedalamanSumur - $mukaAirTanah)
                                            : 50;
                                    $waterHeightPercent =
                                        is_numeric($kedalamanSumur) && $kedalamanSumur > 0
                                            ? min(100, ($waterDepth / $kedalamanSumur) * 100)
                                            : 50;
                                @endphp

                                <div class="flex items-center justify-center p-4">
                                    <svg width="180" height="280" viewBox="0 0 180 280"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <!-- Gradient Definitions -->
                                        <defs>
                                            <!-- Water gradient -->
                                            <linearGradient id="waterGradient-{{ $lg->id_logger }}" x1="0%"
                                                y1="0%" x2="0%" y2="100%">
                                                <stop offset="0%" style="stop-color:#93C5FD;stop-opacity:1" />
                                                <stop offset="50%" style="stop-color:#3B82F6;stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" />
                                            </linearGradient>

                                            <!-- Wall texture pattern -->
                                            <pattern id="wallPattern-{{ $lg->id_logger }}" width="10" height="10"
                                                patternUnits="userSpaceOnUse">
                                                <rect width="10" height="10" fill="#CBD5E1" />
                                                <circle cx="2" cy="2" r="0.5" fill="#94A3B8" />
                                                <circle cx="7" cy="5" r="0.5" fill="#94A3B8" />
                                                <circle cx="4" cy="8" r="0.5" fill="#94A3B8" />
                                            </pattern>

                                            <!-- Concrete wall gradient -->
                                            <linearGradient id="wallGradient-{{ $lg->id_logger }}" x1="0%"
                                                y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" style="stop-color:#94A3B8;stop-opacity:1" />
                                                <stop offset="50%" style="stop-color:#CBD5E1;stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:#94A3B8;stop-opacity:1" />
                                            </linearGradient>
                                        </defs>

                                        <!-- Ground level (top cover) -->
                                        <rect x="0" y="35" width="180" height="10" fill="#1E293B" />

                                        <!-- Well cover structure -->
                                        <rect x="30" y="30" width="50" height="15" fill="#334155"
                                            rx="2" />
                                        <rect x="100" y="30" width="50" height="15" fill="#334155"
                                            rx="2" />

                                        <!-- Sensor housing at top -->
                                        <rect x="82" y="5" width="16" height="30" fill="#9CA3AF"
                                            rx="2" />
                                        <rect x="84" y="7" width="12" height="8" fill="#6B7280"
                                            rx="1" />

                                        <!-- Left wall (outer) -->
                                        <rect x="35" y="45" width="15" height="230"
                                            fill="url(#wallGradient-{{ $lg->id_logger }})" />
                                        <!-- Left wall (inner with pattern) -->
                                        <rect x="50" y="45" width="15" height="230"
                                            fill="url(#wallPattern-{{ $lg->id_logger }})" />

                                        <!-- Right wall (inner with pattern) -->
                                        <rect x="115" y="45" width="15" height="230"
                                            fill="url(#wallPattern-{{ $lg->id_logger }})" />
                                        <!-- Right wall (outer) -->
                                        <rect x="130" y="45" width="15" height="230"
                                            fill="url(#wallGradient-{{ $lg->id_logger }})" />

                                        <!-- Bottom of well -->
                                        <rect x="35" y="275" width="110" height="5" fill="#1E293B" />

                                        @php
                                            $waterStartY = 45 + (230 * (100 - $waterHeightPercent)) / 100;
                                            $waterHeight = 230 - (230 * (100 - $waterHeightPercent)) / 100;
                                        @endphp

                                        <!-- Water fill -->
                                        <rect x="65" y="{{ $waterStartY }}" width="50" height="{{ $waterHeight }}"
                                            fill="url(#waterGradient-{{ $lg->id_logger }})" opacity="0.9" />

                                        <!-- Water surface effect -->
                                        <ellipse cx="90" cy="{{ $waterStartY }}" rx="25" ry="3"
                                            fill="#60A5FA" opacity="0.6" />

                                        <!-- Sensor cable (gray wire) -->
                                        <rect x="88" y="35" width="4" height="240" fill="#6B7280" />

                                        <!-- Sensor probe -->
                                        <rect x="85" y="265" width="10" height="8" fill="#E5E7EB"
                                            rx="1" />
                                        <rect x="86" y="268" width="8" height="15" fill="#9CA3AF"
                                            rx="2" />

                                        <!-- Dashed measurement lines on walls (left side) -->
                                        @for ($i = 0; $i < 23; $i++)
                                            <line x1="48" y1="{{ 50 + $i * 10 }}" x2="52"
                                                y2="{{ 50 + $i * 10 }}" stroke="#475569" stroke-width="1.5" />
                                        @endfor

                                        <!-- Dashed measurement lines on walls (right side) -->
                                        @for ($i = 0; $i < 23; $i++)
                                            <line x1="128" y1="{{ 50 + $i * 10 }}" x2="132"
                                                y2="{{ 50 + $i * 10 }}" stroke="#475569" stroke-width="1.5" />
                                        @endfor
                                    </svg>
                                </div>

                                <div class="px-4 pb-3 text-center text-[10px] font-semibold text-slate-500">
                                    Kedalaman Sumur<br>
                                    <span class="text-slate-700">{{ $lg?->jiat?->kedalaman_sumur ?? '-' }}</span> m
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">

                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <div class="text-[11px] font-semibold text-slate-500">Lokasi</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ $lg->lokasi->nama_lokasi ?? '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    DAS: {{ $lg->lokasi?->das?->nama_das ?? '-' }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
                                    class="block rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition-all hover:shadow-md hover:border-blue-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 overflow-hidden">
                                                <img src="{{ asset('icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg')) }}"
                                                    alt="Humidity"
                                                    class="h-full w-full object-cover {{ $iconClass }}">
                                            </div>
                                            <div class="leading-tight">
                                                <div class="text-[10px] font-extrabold tracking-wider text-slate-500">
                                                    HUMIDITY</div>
                                                <div
                                                    class="text-lg font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                                    {{ $humidity ?? '-' }}
                                                    <span class="text-xs font-bold text-slate-500">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
                                    class="block rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition-all hover:shadow-md hover:border-green-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 overflow-hidden">
                                                <img src="{{ asset('icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg')) }}"
                                                    alt="Battery"
                                                    class="h-full w-full object-cover {{ $iconClass }}">
                                            </div>
                                            <div class="leading-tight">
                                                <div class="text-[10px] font-extrabold tracking-wider text-slate-500">
                                                    BATTERY</div>
                                                <div
                                                    class="text-lg font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                                    {{ $battery ?? '-' }}
                                                    <span class="text-xs font-bold text-slate-500">Volt</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
                                    class="block rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition-all hover:shadow-md hover:border-orange-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 overflow-hidden">
                                                <img src="{{ asset('icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg')) }}"
                                                    alt="Temperature"
                                                    class="h-full w-full object-cover {{ $iconClass }}">
                                            </div>
                                            <div class="leading-tight">
                                                <div class="text-[10px] font-extrabold tracking-wider text-slate-500">
                                                    TEMPERATURE</div>
                                                <div
                                                    class="text-lg font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                                    {{ $temp ?? '-' }}
                                                    <span class="text-xs font-bold text-slate-500">°C</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            {{-- <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                    <div class="text-[11px] font-semibold text-slate-500">Tabel</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ $lg->tabel_main }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                    <div class="text-[11px] font-semibold text-slate-500">Notif</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ $lg->jeda_notif }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                    <div class="text-[11px] font-semibold text-slate-500">Sensor</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ $lg->sensor_count }}</div>
                                </div>
                            </div> --}}

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="container-xl">
        <div class="row text-center align-items-center justify-content-center">
            <div class="col-12 text-center">
                <div class="pb-2">Beacon Engineering @ {{ now()->year }}</div>
                <img src="{{ asset('images/mini_stesy.png') }}" alt="Beacon Engineering" height="40"
                    class="mx-auto d-block">
            </div>
        </div>
    </div>
@endsection
