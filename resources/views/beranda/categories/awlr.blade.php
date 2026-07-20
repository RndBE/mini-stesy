<div class="overflow-visible rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    @if (($subKategoriAwlr ?? 'non_jiat') === 'jiat')

<div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
            @include('beranda.categories.partials.monitoring_well')

            @php $jiatHasPump = (bool) ($lg?->jiat?->has_pump); @endphp
            @if ($pHumidity || $pBattery || $pTemp || $jiatHasPump)
            <div class="col-span-12 md:col-span-4 space-y-4">
                @if ($pHumidity || $pBattery || $pTemp)
                <div class="space-y-3">
                    <div class="text-md font-semibold text-slate-700">Parameter Logger</div>
                    @include('beranda.categories.partials.logger_health_cards')
                </div>
                @endif
                @if ($jiatHasPump)
                    @include('beranda.categories.partials.jiat_flow_cards')
                @endif
            </div>
            @endif
        </div>

        @if ($lg?->jiat?->has_pump)
            @include('beranda.categories.partials.jiat_pump_panels')
        @endif
    @else
<div class="p-5 space-y-4">
<div class="grid grid-cols-12 gap-4">
<div class="col-span-12 md:col-span-9 space-y-3 md:border-r md:border-slate-200 md:pr-2">
                    @php
                        $scaleMin = is_numeric($lg->nonjiat?->elevasi_min)
                            ? (float) $lg->nonjiat->elevasi_min
                            : 0; // fallback 0 jika belum diset
                        $scaleMax = (is_numeric($lg->nonjiat?->elevasi_max) && (float)$lg->nonjiat->elevasi_max > $scaleMin)
                            ? (float) $lg->nonjiat->elevasi_max
                            : $scaleMin + 40; // fallback +40 dari min jika belum diset
                        $scaleMid = ($scaleMin + $scaleMax) / 2; // titik tengah (bagi 2)
                        $tmaVal = is_numeric($tma)
                            ? max($scaleMin, min($scaleMax, (float) $tma))
                            : $scaleMin;
                        $tiangX = 0;
                        $tiangY = 0;
                        $tiangW = 262;
                        $tiangH = 260;
                        $scaleH = 185;
                        $scaleW = round($scaleH * (38 / 174)); // ≈40
                        $scaleX = 457;
                        $scaleY = 76;
                        $sf = $scaleH / 174; // ≈1.063
                        $peilTopNativeY = 5.0;   // y native mark MAX
                        $peilBotNativeY = 166.0;  // y native mark MIN

                        $scaleTopY = $scaleY + $peilTopNativeY * $sf; // rendered y = scaleMax
                        $scaleBotY = $scaleY + $peilBotNativeY * $sf; // rendered y = scaleMin
                        $pxPerUnit = ($scaleBotY - $scaleTopY) / ($scaleMax - $scaleMin);
                        $waterTopY = $scaleBotY - ($tmaVal - $scaleMin) * $pxPerUnit;
                        $waterTopY = max($scaleTopY, min($scaleBotY, $waterTopY));
                        $scaleFortyY = $scaleTopY;
                        $scaleZeroY  = $scaleBotY;
                        $riverX = 0;
                        $riverEndX = $scaleX + 4; // menyentuh tepi kiri peil
                        $riverBotY = round($scaleZeroY) + 4; // sedikit di bawah tanda "0"
                        $tmaLineY = $waterTopY;
                        $waterOpac = '0.85';
                        $waveOpac  = '0.70';
                        $riverFill = 'url(#riverGrad-' . $lg->id_logger . ')';
                        $lineClr   = '#0284c7';
                        $tiangOpac = '1';
                        $tiangNonJiatAsset = $lg->nonjiat?->jenis_sensor === 'radar'
                            ? 'sungai/tiang-nonjiat-radar.svg'
                            : 'sungai/tiang-nonjiat-ultra.svg';
                        $peilOpac  = $isOnline ? '1' : '1'; // peil sedikit redup saat offline
                    @endphp

                    <svg viewBox="0 0 500 260" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto rounded-lg"
                        style="background: linear-gradient(to bottom, #ffffff, #ffffff);">
                        <defs>
                            <linearGradient id="riverGrad-{{ $lg->id_logger }}" x1="0" y1="0"
                                x2="0" y2="1">
                                <stop offset="0%" stop-color="#7dd3fc" />
                                <stop offset="55%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#0369a1" />
                            </linearGradient>
                            <linearGradient id="groundGrad-{{ $lg->id_logger }}" x1="0" y1="0"
                                x2="0" y2="1">
                                <stop offset="0%" stop-color="#d6b896" />
                                <stop offset="100%" stop-color="#92400e" />
                            </linearGradient>
                            <clipPath id="scaleClip-{{ $lg->id_logger }}">
                                <rect x="{{ $scaleX }}" y="{{ $scaleY }}" width="{{ $scaleW }}"
                                    height="{{ $scaleH }}" />
                            </clipPath>
                        </defs>
<path d="M{{ $riverX }},{{ $waterTopY }}
                                Q{{ round(($riverX + $riverEndX) / 2) }},{{ $waterTopY - 8 }}
                                {{ $riverEndX }},{{ $waterTopY }}
                                L{{ $riverEndX }},{{ $riverBotY }}
                                L{{ $riverX }},{{ $riverBotY }} Z" fill="{{ $riverFill }}"
                            opacity="{{ $waterOpac }}" />
@php
                            $wx1 = $riverX + 20;
                            $wx2 = round(($riverX + $riverEndX) / 2 - 40);
                            $wx3 = round(($riverX + $riverEndX) / 2 + 40);
                            $wx4 = $riverEndX - 10;
                        @endphp
                        <path
                            d="M{{ $wx1 }},{{ $waterTopY }}
                                Q{{ $wx2 }},{{ $waterTopY - 5 }} {{ round(($wx1 + $wx2) / 2 + 20) }},{{ $waterTopY }}
                                Q{{ $wx3 }},{{ $waterTopY + 4 }} {{ $wx4 }},{{ $waterTopY - 2 }}"
                            fill="none" stroke="#bae6fd" stroke-width="1.8" opacity="{{ $waveOpac }}" />
<image href="{{ asset($tiangNonJiatAsset) }}" x="{{ $tiangX }}"
                            y="{{ $tiangY }}" width="{{ $tiangW }}" height="{{ $tiangH }}"
                            preserveAspectRatio="xMinYMax meet" opacity="{{ $tiangOpac }}" />
@php
                            $bandDefs = [
                                ['from' => 30, 'to' => 40, 'fill' => '#fca5a5'], // merah
                                ['from' => 15, 'to' => 30, 'fill' => '#fde68a'], // kuning
                                ['from' => 0,  'to' => 15, 'fill' => '#bbf7d0'], // hijau
                            ];
                        @endphp
<image href="{{ asset('sumur/peil.svg') }}"
                               x="{{ $scaleX }}" y="{{ $scaleY }}"
                               width="{{ $scaleW }}" height="{{ $scaleH }}"
                               preserveAspectRatio="xMidYMid meet" />
@php
                            $scaleRange  = $scaleMax - $scaleMin;
                            $tickStep    = $scaleRange > 100 ? 10 : ($scaleRange > 20 ? 5 : 2);
                            $majorStep   = $scaleRange > 100 ? 50 : ($scaleRange > 20 ? 10 : 5);
                        @endphp
                        @for ($v = $scaleMin; $v <= $scaleMax + 0.001; $v += $tickStep)
                            @php
                                $vRound  = round($v, 4);
                                $ty      = $scaleBotY - ($vRound - $scaleMin) * $pxPerUnit;
                                $isMajor = fmod(abs($vRound - $scaleMin), $majorStep) < 0.001;
                                $isMid   = !$isMajor && fmod(abs($vRound - $scaleMin), ($majorStep / 2)) < 0.001;
                                $tickLen = $isMajor ? $scaleW * 0.55 : ($isMid ? $scaleW * 0.35 : $scaleW * 0.2);
                            @endphp
                            <line x1="{{ $scaleX + $scaleW - $tickLen }}" y1="{{ $ty }}"
                                  x2="{{ $scaleX + $scaleW }}" y2="{{ $ty }}"
                                  stroke="{{ $isMajor ? '#92400e' : '#b45309' }}"
                                  stroke-width="{{ $isMajor ? 1.2 : 0.6 }}" />
                            @if($isMajor)
                                <text x="{{ $scaleX + $scaleW * 0.3 }}" y="{{ $ty + 3 }}"
                                      font-size="7.5" text-anchor="middle"
                                      font-family="ui-sans-serif, system-ui, sans-serif"
                                      fill="#78350f" font-weight="600">{{ $vRound }}</text>
                            @endif
                        @endfor
@php
                            $lastMajorV   = floor(($scaleMax - $scaleMin) / $majorStep) * $majorStep + $scaleMin;
                            $lastMajorY   = $scaleBotY - ($lastMajorV - $scaleMin) * $pxPerUnit;
                            $maxPixGap    = $lastMajorY - $scaleTopY; // jarak vertikal SVG unit
                            $maxNotOnTick = fmod(round(($scaleMax - $scaleMin) * 1000), round($tickStep * 1000)) > 1;
                            $showMaxLbl   = $maxNotOnTick && $maxPixGap > 10;
                        @endphp
                        @if($showMaxLbl)
                            @php $ty = $scaleTopY; $tickLen = $scaleW * 0.48; @endphp
                            <line x1="{{ $scaleX + $scaleW - $tickLen }}" y1="{{ $ty }}"
                                  x2="{{ $scaleX + $scaleW }}" y2="{{ $ty }}"
                                  stroke="#92400e" stroke-width="1.2" />
                            <text x="{{ $scaleX + $scaleW * 0.3 }}" y="{{ $ty + 9 }}"
                                  font-size="7.5" text-anchor="middle"
                                  font-family="ui-sans-serif, system-ui, sans-serif"
                                  fill="#78350f" font-weight="600">{{ $scaleMax }}</text>
                        @endif
<line x1="{{ $scaleX - 8 }}" y1="{{ $tmaLineY }}"
                            x2="{{ $scaleX + $scaleW + 2 }}" y2="{{ $tmaLineY }}"
                            stroke="{{ $lineClr }}" stroke-width="2"
                            stroke-dasharray="{{ $isOnline ? 'none' : '4 3' }}" />
<circle cx="{{ $scaleX - 8 }}" cy="{{ $tmaLineY }}" r="3.5"
                            fill="{{ $lineClr }}" opacity="{{ $isOnline ? '1' : '0.6' }}" />
                    </svg>

                </div>
@if ($pTma || $pDebit)
<div class="col-span-12 md:col-span-3 flex flex-col justify-start gap-2">
                    <div class="text-sm font-semibold text-slate-700">Data Pengukuran</div>
@if ($pTma)
@php
                        $tmaAnalisaUrl = $pTma
                            ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pTma->nama_parameter)
                            : route('analisa.index', $lg->id_logger);
                    @endphp
                    <a href="{{ $tmaAnalisaUrl }}"
                        class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                            {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                        <div
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                            <img src="{{ asset($paramIconPath($pTma, 'icons/awlr/elevasi_muka_air.svg')) }}"
                                alt="TMA" class="h-full w-full object-cover"
                                onerror="this.style.display='none';this.parentElement.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' class=\'h-5 w-5 text-sky-500\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10\'/></svg>'">
                        </div>
                        <div>
                            <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Tinggi Muka Air
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ is_numeric($tma) ? \App\Support\DisplayFormat::ukur($tma, 3) : '-' }}
                                </span>
                                <span class="text-xs font-semibold text-slate-400">m</span>
                            </div>
                        </div>
                    </a>
@endif
@if ($pDebit)
@php
                        $debitAnalisaUrl = $pDebit
                            ? route('analisa.index', $lg->id_logger) .
                                '?parameter=' .
                                urlencode($pDebit->nama_parameter)
                            : route('analisa.index', $lg->id_logger);
                    @endphp
                    <a href="{{ $debitAnalisaUrl }}"
                        class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                            {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                        <div
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                            <img src="{{ asset($paramIconPath($pDebit, 'icons/awlr/debit.svg')) }}"
                                alt="Debit" class="h-full w-full object-cover"
                                onerror="this.style.display='none';this.parentElement.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' class=\'h-5 w-5 text-indigo-500\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13 10V3L4 14h7v7l9-11h-7z\'/></svg>'">
                        </div>
                        <div>
                            <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Debit</div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ is_numeric($debit) ? \App\Support\DisplayFormat::ukur($debit, 3) : '-' }}
                                </span>
                                <span class="text-xs font-semibold text-slate-400">m³/s</span>
                            </div>
                        </div>
                    </a>
@endif
                </div>
@endif
            </div>
@if ($pHumidity || $pBattery || $pTemp)
<div class="border-t border-slate-100 pt-3">
                <div class="text-sm font-semibold text-slate-700 mb-2">Logger</div>
<div class="grid grid-cols-3 gap-2">
@if ($pHumidity)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
                        class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-blue-300 px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                                <img src="{{ asset($paramIconPath($pHumidity, 'icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg'))) }}"
                                    alt="Humidity" class="h-full w-full object-cover {{ $iconClass }}">
                            </div>
                            <div class="leading-tight min-w-0">
                                <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                                    Humidity</div>
                                <div
                                    class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                    {{ \App\Support\DisplayFormat::ukur($humidity ?? '-') }}<span
                                        class="text-[10px] font-bold text-slate-400 ml-0.5">%</span>
                                </div>
                            </div>
                        </div>
                    </a>
@endif
@if ($pBattery)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
                        class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-green-300 px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                                <img src="{{ asset($paramIconPath($pBattery, 'icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg'))) }}"
                                    alt="Battery" class="h-full w-full object-cover {{ $iconClass }}">
                            </div>
                            <div class="leading-tight min-w-0">
                                <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                                    Battery</div>
                                <div
                                    class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                    {{ \App\Support\DisplayFormat::ukur($battery ?? '-') }}<span
                                        class="text-[10px] font-bold text-slate-400 ml-0.5">Volt</span>
                                </div>
                            </div>
                        </div>
                    </a>
@endif
@if ($pTemp)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
                        class="block rounded-lg border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-orange-300 px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 overflow-hidden">
                                <img src="{{ asset($paramIconPath($pTemp, 'icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg'))) }}"
                                    alt="Temperature" class="h-full w-full object-cover {{ $iconClass }}">
                            </div>
                            <div class="leading-tight min-w-0">
                                <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                                    Temperature</div>
                                <div
                                    class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                    {{ \App\Support\DisplayFormat::ukur($temp ?? '-') }}<span
                                        class="text-[10px] font-bold text-slate-400 ml-0.5">°C</span>
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
