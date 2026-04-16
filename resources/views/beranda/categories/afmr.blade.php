<div class="overflow-visible rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')
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
<div class="col-span-12 md:col-span-3 flex flex-col justify-start gap-2">
                <div class="text-sm font-semibold text-slate-700">Data Pengukuran</div>
@php
                    $luasUrl = $pLuas
                        ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pLuas->nama_parameter)
                        : route('analisa.index', $lg->id_logger);
                @endphp
                <a href="{{ $luasUrl }}"
                    class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                        {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                        <img src="{{ asset('icons/afmr/luas_penampang_air.svg') }}" alt="Luas" class="h-full w-full object-cover"
                            onerror="this.style.display='none'">
                    </div>
                    <div>
                        <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Luas Penampang Basah</div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ is_numeric($luasPenampang) ? number_format((float) $luasPenampang, 2) : '-' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-400">m²</span>
                        </div>
                    </div>
                </a>
@php
                    $afmrDebitUrl = $pAfmrDebit
                        ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pAfmrDebit->nama_parameter)
                        : route('analisa.index', $lg->id_logger);
                @endphp
                <a href="{{ $afmrDebitUrl }}"
                    class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                        {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                        <img src="{{ asset('icons/awlr/debit.svg') }}" alt="Debit" class="h-full w-full object-cover"
                            onerror="this.style.display='none'">
                    </div>
                    <div>
                        <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Debit</div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ is_numeric($afmrDebit) ? number_format((float) $afmrDebit, 2) : '-' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-400">m³/s</span>
                        </div>
                    </div>
                </a>
@php
                    $flowVelocityUrl = $pFlowVelocity
                        ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pFlowVelocity->nama_parameter)
                        : route('analisa.index', $lg->id_logger);
                @endphp
                <a href="{{ $flowVelocityUrl }}"
                    class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-indigo-300
                        {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                        <img src="{{ asset('icons/afmr/flow_velocity.svg') }}" alt="Flow Velocity" class="h-full w-full object-cover"
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
                                {{ is_numeric($flowVelocity) ? number_format((float) $flowVelocity, 2) : '-' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-400">m/s</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
<div class="grid grid-cols-3 gap-3">
@php
                $elevMukaAirUrl = $pElevMukaAir
                    ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pElevMukaAir->nama_parameter)
                    : route('analisa.index', $lg->id_logger);
            @endphp
            <a href="{{ $elevMukaAirUrl }}"
                class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                    {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                    <img src="{{ asset('icons/awlr/elevasi_muka_air.svg') }}"
                        alt="Elevasi Muka Air" class="h-full w-full object-cover"
                        onerror="this.style.display='none'">
                </div>
                <div class="min-w-0 overflow-hidden">
                    <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400 truncate">Elevasi Muka Air</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ is_numeric($elevMukaAir) ? number_format((float) $elevMukaAir, 3) : '-' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400">m</span>
                    </div>
                </div>
            </a>
@php
                $elevSensorUrl = $pElevSensor
                    ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pElevSensor->nama_parameter)
                    : route('analisa.index', $lg->id_logger);
            @endphp
            <a href="{{ $elevSensorUrl }}"
                class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-amber-300
                    {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                    <img src="{{ asset('icons/afmr/tinggi_sensor.svg') }}"
                        alt="Elevasi Sensor" class="h-full w-full object-cover"
                        onerror="this.style.display='none'">
                </div>
                <div class="min-w-0 overflow-hidden">
                    <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400 truncate">Elevasi Sensor</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ is_numeric($elevSensor) ? number_format((float) $elevSensor, 3) : '-' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400">m</span>
                    </div>
                </div>
            </a>
@php
                $jarakSensorUrl = $pJarakSensor
                    ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pJarakSensor->nama_parameter)
                    : route('analisa.index', $lg->id_logger);
            @endphp
            <a href="{{ $jarakSensorUrl }}"
                class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-rose-300
                    {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                    <img src="{{ asset('icons/afmr/jarak_sensor.svg') }}"
                        alt="Jarak Sensor" class="h-full w-full object-cover"
                        onerror="this.style.display='none'">
                </div>
                <div class="min-w-0 overflow-hidden">
                    <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400 truncate">Jarak Sensor</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ is_numeric($jarakSensor) ? number_format((float) $jarakSensor, 2) : '-' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400">m</span>
                    </div>
                </div>
            </a>
        </div>
<div class="border-t border-slate-100 pt-3">
            <div class="text-sm font-semibold text-slate-700 mb-2">Logger</div>
            <div class="grid grid-cols-3 gap-2">
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
                    class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-blue-300 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                            <img src="{{ asset('icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg')) }}"
                                alt="Humidity" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Humidity</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ $humidity ?? '-' }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">%</span>
                            </div>
                        </div>
                    </div>
                </a>
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
                    class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-green-300 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                            <img src="{{ asset('icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg')) }}"
                                alt="Battery" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Battery</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ $battery ?? '-' }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">Volt</span>
                            </div>
                        </div>
                    </div>
                </a>
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
                    class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-orange-300 px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                            <img src="{{ asset('icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg')) }}"
                                alt="Temperature" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">Temperature</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ $temp ?? '-' }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">°C</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>
