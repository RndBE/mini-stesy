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

                    $timeClass = $isOnline ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                    $dotClass = $isOnline ? 'bg-emerald-500' : 'bg-rose-500';

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
                    <div class="flex items-center justify-between bg-slate-50 px-5 py-3">
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
                                    <div class="w-24 rounded-md border border-slate-200 bg-white/90 px-2 py-1">
                                        DATA AIR TANAH<br><span class="text-slate-900">{{ $DataAir ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                    <div class="w-24 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-rose-700">
                                        ELEVASI SENSOR<br><span
                                            class="text-slate-900">{{ $lg?->jiat?->kedalaman_sensor ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                </div>

                                <div class="absolute right-2 top-2 space-y-2 text-[10px] font-semibold text-slate-600">
                                    <div class="w-24 rounded-md border border-sky-200 bg-sky-50 px-2 py-1 text-sky-700">
                                        MUKA AIR TANAH<br><span class="text-slate-900">{{ $MukaAir ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                    <div
                                        class="w-24 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-amber-700">
                                        ELEVASI POMPA<br><span
                                            class="text-slate-900">{{ $lg?->jiat?->kedalaman_pompa ?? '-' }}</span> <span
                                            class="text-slate-500">m</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center p-4">
                                    <div
                                        class="h-44 w-28 rounded-lg border border-slate-200 bg-gradient-to-b from-slate-100 via-slate-50 to-slate-100">
                                        <div
                                            class="mx-auto mt-2 h-[160px] w-10 rounded-md bg-gradient-to-b from-sky-200 via-sky-500 to-indigo-700">
                                        </div>
                                    </div>
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
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path d="M12 2s6 7 6 12a6 6 0 0 1-12 0C6 9 12 2 12 2z" />
                                                </svg>
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
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path d="M7 7h10v10H7z" />
                                                    <path d="M17 10h1v4h-1" />
                                                </svg>
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
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path d="M14 14.76V3.5a2 2 0 1 0-4 0v11.26a4 4 0 1 0 4 0z" />
                                                </svg>
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
                                </div>
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
