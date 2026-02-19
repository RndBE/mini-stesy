<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    <div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
        <div class="col-span-12 md:col-span-8 space-y-3 md:border-r md:border-slate-200 md:pr-2">
            <div class="text-md font-semibold text-slate-700">Data Sumur</div>

            <div class="relative overflow-hidden rounded-lg bg-white">
                <div class="absolute left-2 top-2 space-y-2 text-[10px] font-semibold text-slate-600">
                    <div
                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-orange-200 bg-orange-50 text-orange-700' : 'border-slate-300 bg-white text-slate-500' }}">
                        DATA AIR TANAH<br><span class="text-slate-900">{{ $dataAir ?? '-' }}</span>
                        <span class="text-slate-500">m</span>
                    </div>
                    <div
                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-300 bg-white text-slate-500' }}">
                        ELEVASI SENSOR<br><span class="text-slate-900">{{ $lg?->jiat?->kedalaman_sensor ?? '-' }}</span>
                        <span class="text-slate-500">m</span>
                    </div>
                </div>

                <div class="absolute right-2 top-2 space-y-2 text-[10px] font-semibold text-slate-600">
                    <div
                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-slate-300 bg-white text-slate-500' }}">
                        MUKA AIR TANAH<br><span class="text-slate-900">{{ $mukaAir ?? '-' }}</span>
                        <span class="text-slate-500">m</span>
                    </div>
                    <div
                        class="w-24 rounded-md border px-2 py-1 {{ $isOnline ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-300 bg-white text-slate-500' }}">
                        ELEVASI POMPA<br><span class="text-slate-900">{{ $lg?->jiat?->kedalaman_pompa ?? '-' }}</span>
                        <span class="text-slate-500">m</span>
                    </div>
                </div>

                @php
                    $kedalamanSumur = $lg?->jiat?->kedalaman_sumur ?? 100;
                    $mukaAirTanah = $mukaAir ?? 0;
                    $waterDepth =
                        is_numeric($mukaAirTanah) && is_numeric($kedalamanSumur)
                            ? max(0, $kedalamanSumur - $mukaAirTanah)
                            : 50;
                    $waterHeightPercent =
                        is_numeric($kedalamanSumur) && $kedalamanSumur > 0
                            ? min(100, ($waterDepth / $kedalamanSumur) * 100)
                            : 50;
                    $waterStartY = 45 + (230 * (100 - $waterHeightPercent)) / 100;
                    $waterHeight = 230 - (230 * (100 - $waterHeightPercent)) / 100;
                @endphp

                <div class="flex items-center justify-center p-4">
                    <svg width="180" height="280" viewBox="0 0 180 280" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="waterGradient-{{ $lg->id_logger }}" x1="0%" y1="0%"
                                x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#93C5FD;stop-opacity:1" />
                                <stop offset="50%" style="stop-color:#3B82F6;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" />
                            </linearGradient>
                            <pattern id="wallPattern-{{ $lg->id_logger }}" width="10" height="10"
                                patternUnits="userSpaceOnUse">
                                <rect width="10" height="10" fill="#CBD5E1" />
                                <circle cx="2" cy="2" r="0.5" fill="#94A3B8" />
                                <circle cx="7" cy="5" r="0.5" fill="#94A3B8" />
                                <circle cx="4" cy="8" r="0.5" fill="#94A3B8" />
                            </pattern>
                            <linearGradient id="wallGradient-{{ $lg->id_logger }}" x1="0%" y1="0%"
                                x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#94A3B8;stop-opacity:1" />
                                <stop offset="50%" style="stop-color:#CBD5E1;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#94A3B8;stop-opacity:1" />
                            </linearGradient>
                        </defs>

                        <rect x="0" y="35" width="180" height="10" fill="#1E293B" />
                        <rect x="30" y="30" width="50" height="15" fill="#334155" rx="2" />
                        <rect x="100" y="30" width="50" height="15" fill="#334155" rx="2" />
                        <rect x="82" y="5" width="16" height="30" fill="#9CA3AF" rx="2" />
                        <rect x="84" y="7" width="12" height="8" fill="#6B7280" rx="1" />
                        <rect x="35" y="45" width="15" height="230"
                            fill="url(#wallGradient-{{ $lg->id_logger }})" />
                        <rect x="50" y="45" width="15" height="230"
                            fill="url(#wallPattern-{{ $lg->id_logger }})" />
                        <rect x="115" y="45" width="15" height="230"
                            fill="url(#wallPattern-{{ $lg->id_logger }})" />
                        <rect x="130" y="45" width="15" height="230"
                            fill="url(#wallGradient-{{ $lg->id_logger }})" />
                        <rect x="35" y="275" width="110" height="5" fill="#1E293B" />

                        <rect x="65" y="{{ $waterStartY }}" width="50" height="{{ $waterHeight }}"
                            fill="url(#waterGradient-{{ $lg->id_logger }})" opacity="0.9" />
                        <ellipse cx="90" cy="{{ $waterStartY }}" rx="25" ry="3"
                            fill="#60A5FA" opacity="0.6" />
                        <rect x="88" y="35" width="4" height="240" fill="#6B7280" />
                        <rect x="85" y="265" width="10" height="8" fill="#E5E7EB" rx="1" />
                        <rect x="86" y="268" width="8" height="15" fill="#9CA3AF" rx="2" />

                        @for ($i = 0; $i < 23; $i++)
                            <line x1="48" y1="{{ 50 + $i * 10 }}" x2="52" y2="{{ 50 + $i * 10 }}"
                                stroke="#475569" stroke-width="1.5" />
                        @endfor
                        @for ($i = 0; $i < 23; $i++)
                            <line x1="128" y1="{{ 50 + $i * 10 }}" x2="132" y2="{{ 50 + $i * 10 }}"
                                stroke="#475569" stroke-width="1.5" />
                        @endfor
                    </svg>
                </div>

                <div class="px-4 pb-3 text-center text-[10px] font-semibold text-slate-500">
                    Kedalaman Sumur<br>
                    <span class="text-slate-700">{{ $lg?->jiat?->kedalaman_sumur ?? '-' }}</span> m
                </div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4 space-y-3">
            <div class="text-md font-semibold text-slate-700">Parameter Logger</div>
            @include('beranda.categories.partials.logger_health_cards')
        </div>
    </div>
</div>
