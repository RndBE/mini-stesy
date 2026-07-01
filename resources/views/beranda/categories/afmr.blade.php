<div class="overflow-visible rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

@if (($subKategoriAfmr ?? 'non_contact') === 'contact')
<div class="p-5">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- SVG Gauge for Flowrate -->
        <div class="md:col-span-5 flex flex-col items-center justify-center relative md:border-r md:border-slate-200 md:pr-4">
            <div class="absolute top-0 left-0 text-sm font-bold text-slate-800">Flowrate</div>

            <div class="w-full max-w-[260px] mt-6 block mx-auto">
                <svg viewBox="0 0 231 250" class="w-full h-auto drop-shadow-md relative">
                    <image href="{{ asset('pipa/afmr_contact.svg') }}" x="0" y="0" width="231" height="250" />

                    <text x="115" y="120" text-anchor="middle" font-family="ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace" font-size="34" font-weight="900" fill="#0f172a">
                        {{ is_numeric($flowrate) ? \App\Support\DisplayFormat::ukur($flowrate, 2) : '-' }}
                    </text>

                    <rect x="70" y="135" width="90" height="20" rx="10" fill="#cbd5e1" opacity="0.8"/>
                    <text x="115" y="148" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-size="10" font-weight="bold" fill="#334155">Flowrate (liter/s)</text>
                </svg>
            </div>
        </div>

        <!-- Data Cards -->
        <div class="md:col-span-7 space-y-5">
            @if ($pTotalizer1 || $pTotalizer2 || $pPressure1 || $pPressure2)
            <div>
                <div class="text-sm font-bold text-slate-800 mb-3">Data Pengukuran</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    @if ($pTotalizer1)
                    <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pTotalizer1->nama_parameter) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 bg-white shadow-sm hover:shadow-md hover:border-sky-300 transition-all {{ $isOnline ? '' : 'grayscale opacity-70' }}">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50">
                            <img src="{{ asset('icons/afmr/totalizer_debit.svg') }}" alt="Totalizer" class="h-6 w-6 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-slate-500">{{ $pTotalizer1->nama_parameter }}</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-lg font-extrabold text-slate-900">{{ is_numeric($totalizer1) ? \App\Support\DisplayFormat::ukur($totalizer1, 2) : '-' }}</span>
                                <span class="text-xs font-semibold text-slate-500">{{ $pTotalizer1->satuan }}</span>
                            </div>
                        </div>
                    </a>
                    @endif

                    @if ($pTotalizer2)
                    <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pTotalizer2->nama_parameter) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 bg-white shadow-sm hover:shadow-md hover:border-indigo-300 transition-all {{ $isOnline ? '' : 'grayscale opacity-70' }}">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50">
                            <img src="{{ asset('icons/afmr/totalizer_debit.svg') }}" alt="Totalizer 2" class="h-6 w-6 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-slate-500">{{ $pTotalizer2->nama_parameter }}</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-lg font-extrabold text-slate-900">{{ is_numeric($totalizer2) ? \App\Support\DisplayFormat::ukur($totalizer2, 2) : '-' }}</span>
                                <span class="text-xs font-semibold text-slate-500">{{ $pTotalizer2->satuan }}</span>
                            </div>
                        </div>
                    </a>
                    @endif

                    @if ($pPressure1)
                    <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pPressure1->nama_parameter) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 bg-white shadow-sm hover:shadow-md hover:border-rose-300 transition-all {{ $isOnline ? '' : 'grayscale opacity-70' }}">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50">
                            <img src="{{ asset('icons/afmr/sensor_prv.svg') }}" alt="Pressure 1" class="h-6 w-6 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-slate-500">{{ $pPressure1->nama_parameter }}</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-lg font-extrabold text-slate-900">{{ is_numeric($pressure1) ? \App\Support\DisplayFormat::ukur($pressure1, 2) : '-' }}</span>
                                <span class="text-xs font-semibold text-slate-500">{{ $pPressure1->satuan }}</span>
                            </div>
                        </div>
                    </a>
                    @endif

                    @if ($pPressure2)
                    <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pPressure2->nama_parameter) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 bg-white shadow-sm hover:shadow-md hover:border-amber-300 transition-all {{ $isOnline ? '' : 'grayscale opacity-70' }}">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50">
                            <img src="{{ asset('icons/afmr/sensor_prv.svg') }}" alt="Pressure 2" class="h-6 w-6 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase tracking-widest text-slate-500">{{ $pPressure2->nama_parameter }}</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-lg font-extrabold text-slate-900">{{ is_numeric($pressure2) ? \App\Support\DisplayFormat::ukur($pressure2, 2) : '-' }}</span>
                                <span class="text-xs font-semibold text-slate-500">{{ $pPressure2->satuan }}</span>
                            </div>
                        </div>
                    </a>
                    @endif

                </div>
            </div>
            @endif

            @if ($pFmBattery || $pFault)
            <div>
                <div class="text-sm font-bold text-slate-800 mb-3">Status Alat</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if ($pFmBattery)
                    <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pFmBattery->nama_parameter) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 bg-white shadow-sm hover:shadow-md hover:border-green-300 transition-all {{ $isOnline ? '' : 'grayscale opacity-70' }}">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md border border-slate-100 bg-slate-50">
                            <img src="{{ asset($paramIconPath($pFmBattery, 'icons/beranda/battery_online.svg')) }}" alt="Flowmeter Battery" class="h-5 w-5 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <div class="text-[8px] font-bold uppercase tracking-widest text-slate-500">{{ $pFmBattery->nama_parameter }}</div>
                            <div class="text-base font-extrabold text-slate-900">{{ \App\Support\DisplayFormat::ukur($fmBattery ?? '-') }}<span class="text-[9px] font-bold text-slate-500 ml-1">{{ $pFmBattery->satuan }}</span></div>
                        </div>
                    </a>
                    @endif

                    @if ($pFault)
                    @php
                        $faultLabel = is_numeric($fault)
                            ? \App\Support\FaultStatus::summary((int) $fault)
                            : ($fault ?? '-');
                        $faultOk = is_numeric($fault) && !\App\Support\FaultStatus::isFault((int) $fault);
                        $faultDetail = is_numeric($fault)
                            ? \App\Support\FaultStatus::decode((int) $fault)
                            : [];
                    @endphp
                    <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pFault->nama_parameter) }}" class="flex items-center gap-3 rounded-xl border px-3 py-2 bg-white shadow-sm hover:shadow-md transition-all {{ $faultOk ? 'border-emerald-200' : 'border-rose-200' }} {{ $isOnline ? '' : 'grayscale opacity-70' }}">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md border border-slate-100 bg-slate-50">
                            <img src="{{ asset('icons/afmr/fault_wm.svg') }}" alt="Fault" class="h-5 w-5 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <div class="text-[8px] font-bold uppercase tracking-widest text-slate-500">{{ $pFault->nama_parameter }}</div>
                            <div class="text-base font-extrabold {{ $faultOk ? 'text-emerald-700' : 'text-rose-700' }}"
                                @if(!empty($faultDetail)) title="{{ implode(', ', $faultDetail) }}" @endif>{{ $faultLabel }}</div>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @if (!($pTotalizer1 || $pTotalizer2 || $pPressure1 || $pPressure2 || $pFmBattery || $pFault))
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                Belum ada parameter pengukuran untuk alat ini.
            </div>
            @endif
        </div>
    </div>

    @if ($pHumidity || $pBattery || $pTemp)
    <div class="mt-6 border-t border-slate-100 pt-4">
        <div class="text-sm font-semibold text-slate-700 mb-2">Logger</div>
        <div class="grid grid-cols-3 gap-2">
            @if ($pHumidity)
            <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pHumidity->nama_parameter) }}"
                class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-blue-300 px-3 py-2.5">
                <div class="flex items-center gap-2">
                    <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                        <img src="{{ asset($paramIconPath($pHumidity, 'icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg'))) }}"
                            alt="Humidity" class="h-full w-full object-cover {{ $iconClass }}">
                    </div>
                    <div class="leading-tight min-w-0">
                        <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Humidity</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ \App\Support\DisplayFormat::ukur($humidity ?? '-') }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">{{ $pHumidity->satuan }}</span>
                        </div>
                    </div>
                </div>
            </a>
            @endif
            @if ($pBattery)
            <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pBattery->nama_parameter) }}"
                class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-green-300 px-3 py-2.5">
                <div class="flex items-center gap-2">
                    <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                        <img src="{{ asset($paramIconPath($pBattery, 'icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg'))) }}"
                            alt="Battery" class="h-full w-full object-cover {{ $iconClass }}">
                    </div>
                    <div class="leading-tight min-w-0">
                        <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Battery</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ \App\Support\DisplayFormat::ukur($battery ?? '-') }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">{{ $pBattery->satuan }}</span>
                        </div>
                    </div>
                </div>
            </a>
            @endif
            @if ($pTemp)
            <a href="{{ route('analisa.index', $lg->id_logger) }}?parameter={{ urlencode($pTemp->nama_parameter) }}"
                class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-orange-300 px-3 py-2.5">
                <div class="flex items-center gap-2">
                    <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                        <img src="{{ asset($paramIconPath($pTemp, 'icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg'))) }}"
                            alt="Temperature" class="h-full w-full object-cover {{ $iconClass }}">
                    </div>
                    <div class="leading-tight min-w-0">
                        <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Temperature</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ \App\Support\DisplayFormat::ukur($temp ?? '-') }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">{{ $pTemp->satuan }}</span>
                        </div>
                    </div>
                </div>
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
@else
<div class="p-5 space-y-4">
<div class="grid grid-cols-12 gap-4">
<div class="col-span-12 md:col-span-9 space-y-3 md:border-r md:border-slate-200 md:pr-2">
                @php
                    $afmrScaleMin = 0;
                    $afmrScaleMax = (is_numeric($elevSensor) && (float)$elevSensor > 0)
                        ? (float) $elevSensor
                        : 40; // fallback
                    $afmrTmaVal = is_numeric($elevMukaAir)
                        ? max($afmrScaleMin, min($afmrScaleMax, (float) $elevMukaAir))
                        : $afmrScaleMin;
                    $afmrTiangX = 0;
                    $afmrTiangY = 0;
                    $afmrTiangW = 265;
                    $afmrTiangH = 260;
                    $afmrScaleH = 185;
                    $afmrScaleW = round($afmrScaleH * (38 / 174)); // ≈40
                    $afmrScaleX = 457;
                    $afmrScaleY = 76;
                    $afmrSf          = $afmrScaleH / 174; // ≈1.063
                    $afmrTopNativeY  = 5.0;   // y native = scaleMax
                    $afmrBotNativeY  = 166.0;  // y native = scaleMin

                    $afmrScaleTopY   = $afmrScaleY + $afmrTopNativeY * $afmrSf;
                    $afmrScaleBotY   = $afmrScaleY + $afmrBotNativeY * $afmrSf;
                    $afmrPxPerUnit   = ($afmrScaleBotY - $afmrScaleTopY) / ($afmrScaleMax - $afmrScaleMin);
                    $afmrWaterTopY = $afmrScaleBotY - ($afmrTmaVal - $afmrScaleMin) * $afmrPxPerUnit;
                    $afmrWaterTopY = max($afmrScaleTopY, min($afmrScaleBotY, $afmrWaterTopY));
                    $afmrRiverX    = 0;
                    $afmrRiverEndX = $afmrScaleX + 4;
                    $afmrRiverBotY = round($afmrScaleBotY) + 4;
                    $afmrTmaLineY  = $afmrWaterTopY;
                    $afmrWaterOpac = '0.85';
                    $afmrWaveOpac  = '0.70';
                    $afmrRiverFill = 'url(#afmrRiverGrad-' . $lg->id_logger . ')';
                    $afmrLineClr   = '#0284c7';
                    $afmrTiangOpac = '1';
                    $afmrWx1 = $afmrRiverX + 20;
                    $afmrWx2 = round(($afmrRiverX + $afmrRiverEndX) / 2 - 40);
                    $afmrWx3 = round(($afmrRiverX + $afmrRiverEndX) / 2 + 40);
                    $afmrWx4 = $afmrRiverEndX - 10;
                @endphp

                <svg viewBox="0 0 500 260" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto rounded-lg"
                    style="background: linear-gradient(to bottom, #ffffff, #ffffff);">
                    <defs>
                        <linearGradient id="afmrRiverGrad-{{ $lg->id_logger }}" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#7dd3fc" />
                            <stop offset="55%"  stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#0369a1" />
                        </linearGradient>
                        <linearGradient id="afmrGroundGrad-{{ $lg->id_logger }}" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#d6b896" />
                            <stop offset="100%" stop-color="#92400e" />
                        </linearGradient>
                    </defs>
<path d="M{{ $afmrRiverX }},{{ $afmrWaterTopY }}
                            Q{{ round(($afmrRiverX + $afmrRiverEndX) / 2) }},{{ $afmrWaterTopY - 8 }}
                            {{ $afmrRiverEndX }},{{ $afmrWaterTopY }}
                            L{{ $afmrRiverEndX }},{{ $afmrRiverBotY }}
                            L{{ $afmrRiverX }},{{ $afmrRiverBotY }} Z"
                        fill="{{ $afmrRiverFill }}" opacity="{{ $afmrWaterOpac }}" />
<path d="M{{ $afmrWx1 }},{{ $afmrWaterTopY }}
                            Q{{ $afmrWx2 }},{{ $afmrWaterTopY - 5 }} {{ round(($afmrWx1 + $afmrWx2) / 2 + 20) }},{{ $afmrWaterTopY }}
                            Q{{ $afmrWx3 }},{{ $afmrWaterTopY + 4 }} {{ $afmrWx4 }},{{ $afmrWaterTopY - 2 }}"
                        fill="none" stroke="#bae6fd" stroke-width="1.8" opacity="{{ $afmrWaveOpac }}" />
<image href="{{ asset('sungai/tiang_afmr.svg') }}"
                        x="{{ $afmrTiangX }}" y="{{ $afmrTiangY }}"
                        width="{{ $afmrTiangW }}" height="{{ $afmrTiangH }}"
                        preserveAspectRatio="xMinYMax meet"
                        opacity="{{ $afmrTiangOpac }}" />

                    <image href="{{ asset('sumur/peil.svg') }}"
                        x="{{ $afmrScaleX }}" y="{{ $afmrScaleY }}"
                        width="{{ $afmrScaleW }}" height="{{ $afmrScaleH }}"
                        preserveAspectRatio="xMidYMid meet" />
@for ($v = $afmrScaleMin; $v <= $afmrScaleMax; $v += 2)
                        @php
                            $ty      = $afmrScaleBotY - ($v - $afmrScaleMin) * $afmrPxPerUnit;
                            $isMajor = $v % 10 === 0;
                            $isMid   = $v % 5 === 0 && !$isMajor;
                            $tickLen = $isMajor ? $afmrScaleW * 0.55 : ($isMid ? $afmrScaleW * 0.35 : $afmrScaleW * 0.2);
                        @endphp
                        <line x1="{{ $afmrScaleX + $afmrScaleW - $tickLen }}" y1="{{ $ty }}"
                              x2="{{ $afmrScaleX + $afmrScaleW }}" y2="{{ $ty }}"
                              stroke="{{ $isMajor ? '#92400e' : '#b45309' }}"
                              stroke-width="{{ $isMajor ? 1.2 : 0.6 }}" />
                        @if($isMajor)
                            <text x="{{ $afmrScaleX + $afmrScaleW * 0.3 }}" y="{{ $ty + 3 }}"
                                  font-size="7.5" text-anchor="middle"
                                  font-family="ui-sans-serif, system-ui, sans-serif"
                                  fill="#78350f" font-weight="600">{{ $v }}</text>
                        @endif
                    @endfor
@php
                        $afmrLastMajorV   = floor(($afmrScaleMax - $afmrScaleMin) / 10) * 10 + $afmrScaleMin;
                        $afmrLastMajorY   = $afmrScaleBotY - ($afmrLastMajorV - $afmrScaleMin) * $afmrPxPerUnit;
                        $afmrMaxPixGap    = $afmrLastMajorY - $afmrScaleTopY;
                        $afmrMaxNotOnTick = fmod(round($afmrScaleMax * 100), 200) > 1; // tick step = 2
                        $afmrShowMax      = $afmrMaxNotOnTick && $afmrMaxPixGap > 10;
                    @endphp
                    @if($afmrShowMax)
                        @php $tickLen = $afmrScaleW * 0.48; @endphp
                        <line x1="{{ $afmrScaleX + $afmrScaleW - $tickLen }}" y1="{{ $afmrScaleTopY }}"
                              x2="{{ $afmrScaleX + $afmrScaleW }}" y2="{{ $afmrScaleTopY }}"
                              stroke="#92400e" stroke-width="1.2" />
                        <text x="{{ $afmrScaleX + $afmrScaleW * 0.3 }}" y="{{ $afmrScaleTopY + 3 }}"
                              font-size="7.5" text-anchor="middle"
                              font-family="ui-sans-serif, system-ui, sans-serif"
                              fill="#78350f" font-weight="600">{{ $afmrScaleMax }}</text>
                    @endif
<line x1="{{ $afmrScaleX - 8 }}" y1="{{ $afmrTmaLineY }}"
                        x2="{{ $afmrScaleX + $afmrScaleW + 2 }}" y2="{{ $afmrTmaLineY }}"
                        stroke="{{ $afmrLineClr }}" stroke-width="2"
                        stroke-dasharray="{{ $isOnline ? 'none' : '4 3' }}" />
                    <circle cx="{{ $afmrScaleX - 8 }}" cy="{{ $afmrTmaLineY }}" r="3.5"
                        fill="{{ $afmrLineClr }}" opacity="{{ $isOnline ? '1' : '0.6' }}" />
                </svg>

            </div>
@if ($pLuas || $pAfmrDebit || $pFlowVelocity)
<div class="col-span-12 md:col-span-3 flex flex-col justify-start gap-2">
                <div class="text-sm font-semibold text-slate-700">Data Pengukuran</div>
@if ($pLuas)
@php
                    $luasUrl = $pLuas
                        ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pLuas->nama_parameter)
                        : route('analisa.index', $lg->id_logger);
                @endphp
                <a href="{{ $luasUrl }}"
                    class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                        {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                        <img src="{{ asset($paramIconPath($pLuas, 'icons/afmr/luas_penampang_air.svg')) }}" alt="Luas" class="h-full w-full object-cover"
                            onerror="this.style.display='none'">
                    </div>
                    <div>
                        <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Luas Penampang Basah</div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ is_numeric($luasPenampang) ? \App\Support\DisplayFormat::ukur($luasPenampang, 2) : '-' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-400">m²</span>
                        </div>
                    </div>
                </a>
@endif
@if ($pAfmrDebit)
@php
                    $afmrDebitUrl = $pAfmrDebit
                        ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pAfmrDebit->nama_parameter)
                        : route('analisa.index', $lg->id_logger);
                @endphp
                <a href="{{ $afmrDebitUrl }}"
                    class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                        {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                        <img src="{{ asset($paramIconPath($pAfmrDebit, 'icons/awlr/debit.svg')) }}" alt="Debit" class="h-full w-full object-cover"
                            onerror="this.style.display='none'">
                    </div>
                    <div>
                        <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Debit</div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ is_numeric($afmrDebit) ? \App\Support\DisplayFormat::ukur($afmrDebit, 2) : '-' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-400">m³/s</span>
                        </div>
                    </div>
                </a>
@endif
@if ($pFlowVelocity)
@php
                    $flowVelocityUrl = $pFlowVelocity
                        ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pFlowVelocity->nama_parameter)
                        : route('analisa.index', $lg->id_logger);
                @endphp
                <a href="{{ $flowVelocityUrl }}"
                    class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-indigo-300
                        {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                        <img src="{{ asset($paramIconPath($pFlowVelocity, 'icons/afmr/flow_velocity.svg')) }}" alt="Flow Velocity" class="h-full w-full object-cover"
                            onerror="this.style.display='none'">
                    </div>
                    <div>
                        <div class="flex items-center gap-1">
                            <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Flow Velocity</div>
                            @if($isOnline)
                                <span class="text-[7px] font-bold px-1 py-0.5 rounded bg-sky-100 text-sky-600 uppercase tracking-wide">Live</span>
                            @endif
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ is_numeric($flowVelocity) ? \App\Support\DisplayFormat::ukur($flowVelocity, 2) : '-' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-400">m/s</span>
                        </div>
                    </div>
                </a>
@endif
            </div>
@endif
        </div>
@if ($pElevMukaAir || $pElevSensor || $pJarakSensor)
<div class="grid grid-cols-3 gap-3">
@if ($pElevMukaAir)
@php
                $elevMukaAirUrl = $pElevMukaAir
                    ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pElevMukaAir->nama_parameter)
                    : route('analisa.index', $lg->id_logger);
            @endphp
            <a href="{{ $elevMukaAirUrl }}"
                class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                    {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                    <img src="{{ asset($paramIconPath($pElevMukaAir, 'icons/awlr/elevasi_muka_air.svg')) }}"
                        alt="Elevasi Muka Air" class="h-full w-full object-cover"
                        onerror="this.style.display='none'">
                </div>
                <div class="min-w-0 overflow-hidden">
                    <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400 truncate">Elevasi Muka Air</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ is_numeric($elevMukaAir) ? \App\Support\DisplayFormat::ukur($elevMukaAir, 3) : '-' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400">m</span>
                    </div>
                </div>
            </a>
@endif
@if ($pElevSensor)
@php
                $elevSensorUrl = $pElevSensor
                    ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pElevSensor->nama_parameter)
                    : route('analisa.index', $lg->id_logger);
            @endphp
            <a href="{{ $elevSensorUrl }}"
                class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-amber-300
                    {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                    <img src="{{ asset($paramIconPath($pElevSensor, 'icons/afmr/tinggi_sensor.svg')) }}"
                        alt="Elevasi Sensor" class="h-full w-full object-cover"
                        onerror="this.style.display='none'">
                </div>
                <div class="min-w-0 overflow-hidden">
                    <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400 truncate">Elevasi Sensor</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ is_numeric($elevSensor) ? \App\Support\DisplayFormat::ukur($elevSensor, 3) : '-' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400">m</span>
                    </div>
                </div>
            </a>
@endif
@if ($pJarakSensor)
@php
                $jarakSensorUrl = $pJarakSensor
                    ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pJarakSensor->nama_parameter)
                    : route('analisa.index', $lg->id_logger);
            @endphp
            <a href="{{ $jarakSensorUrl }}"
                class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-rose-300
                    {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                    <img src="{{ asset($paramIconPath($pJarakSensor, 'icons/afmr/jarak_sensor.svg')) }}"
                        alt="Jarak Sensor" class="h-full w-full object-cover"
                        onerror="this.style.display='none'">
                </div>
                <div class="min-w-0 overflow-hidden">
                    <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400 truncate">Jarak Sensor</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ is_numeric($jarakSensor) ? \App\Support\DisplayFormat::ukur($jarakSensor, 2) : '-' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400">m</span>
                    </div>
                </div>
            </a>
@endif
        </div>
@endif
@if ($pHumidity || $pBattery || $pTemp)
<div class="border-t border-slate-100 pt-3">
            <div class="text-sm font-semibold text-slate-700 mb-2">Logger</div>
            <div class="grid grid-cols-3 gap-2">
@if ($pHumidity)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
                    class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-blue-300 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                            <img src="{{ asset($paramIconPath($pHumidity, 'icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg'))) }}"
                                alt="Humidity" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Humidity</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ \App\Support\DisplayFormat::ukur($humidity ?? '-') }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">%</span>
                            </div>
                        </div>
                    </div>
                </a>
@endif
@if ($pBattery)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
                    class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-green-300 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                            <img src="{{ asset($paramIconPath($pBattery, 'icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg'))) }}"
                                alt="Battery" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Battery</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ \App\Support\DisplayFormat::ukur($battery ?? '-') }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">Volt</span>
                            </div>
                        </div>
                    </div>
                </a>
@endif
@if ($pTemp)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
                    class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-orange-300 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                            <img src="{{ asset($paramIconPath($pTemp, 'icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg'))) }}"
                                alt="Temperature" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Temperature</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ \App\Support\DisplayFormat::ukur($temp ?? '-') }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">°C</span>
                            </div>
                        </div>
                    </div>
                </a>
@endif
            </div>
        </div>
@endif
    </div>
@endif
</div>
