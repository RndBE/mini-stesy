<div class="overflow-visible rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    <div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
        <div class="col-span-12 md:col-span-8 space-y-3 md:border-r md:border-slate-200 md:pr-2">
            <div class="text-md font-semibold text-slate-700">Data Sumur</div>

            <div class="relative rounded-lg bg-white">

                @php
                    $badanH = 227;
                    $topH = 14;
                    $capAboveH = 13;
                    $totalH = $badanH + $topH + $capAboveH;

                    $topOffX = 20;
                    $dalOffX = 54;

                    $wellTop = 1.926 + $topH + $capAboveH;
                    $wellBot = 219.056 + $topH + $capAboveH;
                    $innerX = 54.185;
                    $innerW = 28.63;
                    $cx = 68.5;

                    $wellRange = $wellBot - $wellTop;

                    $kdlSumur = is_numeric($lg?->jiat?->kedalaman_sumur) ? (float) $lg->jiat->kedalaman_sumur : 0;
                    $kdlSensor = is_numeric($lg?->jiat?->kedalaman_sensor) ? (float) $lg->jiat->kedalaman_sensor : 0;
                    $kdlPompa = is_numeric($lg?->jiat?->kedalaman_pompa) ? (float) $lg->jiat->kedalaman_pompa : 0;
                    $matVal = is_numeric($mukaAir) ? (float) $mukaAir : 0;

                    $wStartRatio = $kdlSumur > 0 ? min(1, max(0, $matVal / $kdlSumur)) : 0.1;
                    $wStartY = round($wellTop + $wStartRatio * $wellRange, 3);
                    $wHeight = round($wellBot - $wStartY, 3);

                    $sensorRatio = $kdlSumur > 0 ? min(0.99, $kdlSensor / $kdlSumur) : 0.5;
                    $sensorY = round($wellTop + $sensorRatio * $wellRange, 3);

                    $hasPompa = $kdlPompa > 0;
                    $pompaRatio = $kdlSumur > 0 ? min(0.97, $kdlPompa / $kdlSumur) : 0.6;
                    $pompaY = round($wellTop + $pompaRatio * $wellRange, 3);

                    $headPad = 10;
                    $sensorY = max($wellTop + $headPad, min($wellBot - $headPad, $sensorY));

                    $minGap = $headPad * 2;
                    $eps = 1e-6;

                    if ($hasPompa) {
                        if ($kdlPompa > $kdlSensor + $eps) {
                            $pompaY = max($sensorY + $minGap, $pompaY);
                        } elseif ($kdlPompa < $kdlSensor - $eps) {
                            $sensorY = max($pompaY + $minGap, $sensorY);
                        } else {
                            $pompaY = $sensorY;
                        }
                        $sensorY = max($wellTop + $headPad, min($wellBot - $headPad, $sensorY));
                        $pompaY = max($wellTop + $headPad, min($wellBot - $headPad, $pompaY));
                    }

                    $wTop = '#7DDFFF';
                    $wMid = '#2EA7E8';
                    $wBot = '#0050A0';
                    $wSurf = '#B3F0FF';
                    $wOpac = '0.90';

                    $sCol = '#1E293B';
                    $sOpac = '1';

                    $pCol = '#F59E0B';
                    $pOpac = '0.95';

                    $uid = $lg->id_logger;

                    $dalOverlap = 12.9;
                    $dalTop = $capAboveH + $topH - $dalOverlap;
                    $dalH = 232 + $dalOverlap;

                    $joinFix = 1.2;
                    $badanTop = $capAboveH + $topH - $joinFix;
                    $topTop = $capAboveH;

                    $padX = 1.5;
                    $gapX = 2.0;

                    $targetPumpW = 18;
                    $targetSensW = 14;

                    $availW = $innerW - 2 * $padX - $gapX;
                    $scale = min(1.0, $availW / ($targetPumpW + $targetSensW));

                    $sensorHeadW = $targetSensW * $scale;
                    $sensorHeadH = $sensorHeadW;

                    $pumpHeadW = $targetPumpW * $scale;
                    $pumpHeadH = $pumpHeadW;

                    $sensorLineW = 4 * $scale;
                    $pumpLineW = 6 * $scale;

                    $sensorHeadX = $innerX + $padX;
                    $pumpHeadX = $sensorHeadX + $sensorHeadW + $gapX;

                    $pumpBias = -6;
                    $pumpHeadX = $pumpHeadX + $pumpBias;

                    $sensorHeadY = $sensorY - $sensorHeadH / 2;
                    $pumpHeadY = $pompaY - $pumpHeadH / 2;

                    $sensorLineX = $sensorHeadX + $sensorHeadW / 2 - $sensorLineW / 2;
                    $pumpLineX = $pumpHeadX + $pumpHeadW / 2 - $pumpLineW / 2;

                    $capHeadHRef = 5;
                    $capPumpYRef = 2;
                    $pumpLineTopY = $capPumpYRef + $capHeadHRef - 1;
                    $pumpLineBotY = $pumpHeadY;
                    $pumpLineH = max(2, $pumpLineBotY - $pumpLineTopY);

                    $sensorLineBotY = $sensorHeadY;

                    $capSensW = 28;
                    $capSensH = 16;
                    $capSensY = $topTop + 1;

                    $capSensAnchorX = 22.4;

                    $rodSensCX = $sensorLineX + $sensorLineW / 2;
                    $capSensX = $rodSensCX - $capSensAnchorX;

                    $stemSensW = $sensorLineW;
                    $stemSensX = $rodSensCX - $stemSensW / 2;

                    $stemSensTopY = $capSensY + $capSensH - 2;
                    $capSensYOffset = 0.4;
                    $capSensY = $topTop + 1 + $capSensYOffset;
                    $stemSensBotY = $wellTop - 2;
                    $stemSensH = max(2, $stemSensBotY - $stemSensTopY);

                    $sensorLineTopY = $stemSensBotY - 0.8;
                    $sensorLineH = max(2, $sensorLineBotY - $sensorLineTopY);

                    // === Leader line target positions (scaled 1.4x) ===
                    $sc = 1.4;
                    $waterSurfYScaled = round($wStartY * $sc);
                    $sensorYScaled = round($sensorY * $sc);
                    $pompaYScaled = round($pompaY * $sc);
                    $wellLeftXScaled = round($innerX * $sc);
                    $wellRightXScaled = round(($innerX + $innerW) * $sc);
                    $diagramWScaled = round(137 * $sc); // ≈192
                    $diagramHScaled = round($totalH * $sc);

                    // Container layout
                    $cW = 116; // card width px
                    $cGap = 28; // gap between card and well
                    $wellOff = $cW + $cGap;
                    $contW = $cW + $cGap + $diagramWScaled + $cGap + $cW;

                    // Well top & bottom in container coords (untuk depth indicator)
                    $wellTopYScaled = round($wellTop * $sc);
                    $wellBotYScaled = round($wellBot * $sc);

                    // Depth indicator: garis vertikal di kiri sumur (dalam gap)
                    $depthLineX = $wellOff + 24; // sedikit kiri dari tepi kiri sumur

                    // Well edges in container coords
                    $wlcX = $wellOff + $wellLeftXScaled;
                    $wrcX = $wellOff + $wellRightXScaled;

                    // Card positions — FIXED (card tidak bergerak, hanya garis yang dinamis)
                    $cH = 46; // tinggi card
                    $cGapV = 20; // gap antar card (atas-bawah)

                    // Posisi card: dekat bagian atas diagram
                    $cPadT = 8;

                    // Kiri
                    $datAirY = $cPadT; // DATA AIR TANAH (fixed)
                    $elevSensY = $cPadT + $cH + $cGapV; // ELEVASI SENSOR (fixed)

                    // Kanan
                    $mukaAirY = $cPadT; // MUKA AIR TANAH (fixed)
                    $elevPompaY = $cPadT + $cH + $cGapV; // ELEVASI POMPA (fixed)

                    // Titik tengah card (untuk ujung garis)
                    $datAirMidY = $datAirY + intval($cH / 2);
                    $elevSensMidY = $elevSensY + intval($cH / 2);
                    $mukaAirMidY = $mukaAirY + intval($cH / 2);
                    $elevPompaMidY = $elevPompaY + intval($cH / 2);

                    // X ujung garis di sisi card
                    $leftCardEndX = $cW; // sisi kanan card kiri
                    $rightCardEndX = $wellOff + $diagramWScaled + $cGap; // sisi kiri card kanan

                    // Extra ruang bawah untuk label kedalaman (dikecilkan agar HP tidak keliatan jarak besar)
                    $contH = max($diagramHScaled + 20, $elevSensY + $cH + 20);
                @endphp

                {{-- === INFOGRAPHIC LAYOUT === --}}
                {{-- JS mengontrol scaling: aktif jika diagram lebih lebar dari kolom (berlaku di semua orientasi) --}}
                <style>
                /* Base: scroll biasa jika diagram muat atau JS belum jalan */
                .sumur-scale-wrapper {
                    overflow-x: auto;
                }
                /* Saat JS aktifkan scaling (class is-scaled ditambahkan JS) */
                .sumur-scale-wrapper.is-scaled {
                    display: flex;
                    justify-content: center;
                    overflow: hidden;
                }
                .sumur-scale-wrapper.is-scaled .sumur-inner-container {
                    transform-origin: top center;
                    flex-shrink: 0;
                }
                </style>
                <div class="w-full sumur-scale-wrapper">
                    <div class="mx-auto py-3 sumur-inner-container"
                        style="position:relative; width:{{ $contW }}px; height:{{ $contH }}px;">

                        {{-- SVG LEADER LINES --}}
                        <svg style="position:absolute; top:0; left:0; overflow:visible; pointer-events:none; z-index:50;"
                            width="{{ $contW }}" height="{{ $contH }}">

                            {{-- DATA AIR TANAH → water surface (left, DINAMIS dari sumur ke card tetap) --}}
                            <circle cx="{{ $wlcX }}" cy="{{ $waterSurfYScaled }}" r="4"
                                fill="{{ $isOnline ? '#78716c' : '#9CA3AF' }}" />
                            <line x1="{{ $wlcX }}" y1="{{ $waterSurfYScaled }}" x2="{{ $leftCardEndX }}"
                                y2="{{ $datAirMidY }}" stroke="{{ $isOnline ? '#78716c' : '#9CA3AF' }}"
                                stroke-width="1.5" />

                            {{-- ELEVASI SENSOR → sensor (left, DINAMIS dari sumur ke card tetap) --}}
                            <circle cx="{{ $wlcX }}" cy="{{ $sensorYScaled }}" r="4"
                                fill="{{ $isOnline ? '#E11D48' : '#9CA3AF' }}" />
                            <line x1="{{ $wlcX }}" y1="{{ $sensorYScaled }}" x2="{{ $leftCardEndX }}"
                                y2="{{ $elevSensMidY }}" stroke="{{ $isOnline ? '#E11D48' : '#9CA3AF' }}"
                                stroke-width="1.5" />

                            {{-- MUKA AIR TANAH → water surface (right, DINAMIS dari sumur ke card tetap) --}}
                            <circle cx="{{ $wrcX }}" cy="{{ $waterSurfYScaled }}" r="4"
                                fill="{{ $isOnline ? '#0EA5E9' : '#9CA3AF' }}" />
                            <line x1="{{ $wrcX }}" y1="{{ $waterSurfYScaled }}" x2="{{ $rightCardEndX }}"
                                y2="{{ $mukaAirMidY }}" stroke="{{ $isOnline ? '#0EA5E9' : '#9CA3AF' }}"
                                stroke-width="1.5" />

                            {{-- ELEVASI POMPA → pompa (right, DINAMIS dari sumur ke card tetap) --}}
                            @if ($hasPompa)
                                <circle cx="{{ $wrcX }}" cy="{{ $pompaYScaled }}" r="4"
                                    fill="{{ $isOnline ? '#F59E0B' : '#9CA3AF' }}" />
                                <line x1="{{ $wrcX }}" y1="{{ $pompaYScaled }}" x2="{{ $rightCardEndX }}"
                                    y2="{{ $elevPompaMidY }}" stroke="{{ $isOnline ? '#F59E0B' : '#9CA3AF' }}"
                                    stroke-width="1.5" />
                            @endif

                            {{-- DEPTH INDICATOR --}}
                            @php
                                // Garis vertikal putus-putus (pendekin dikit biar gak kepanjangan)
                                $trim = 6; // px, adjust 6-12 sesuai feel
                                $depthY1 = $wellTopYScaled + $trim;
                                $depthY2 = $wellBotYScaled - $trim;

                                // Label di SAMPING garis putus-putus (sejajar/center)
                                $lblPadX = 10; // jarak label dari garis
                                $lblX = $depthLineX - $lblPadX; // taruh di kanan garis (ubah minus kalau mau kiri)

                                $lblY1 = $depthY2 - 22; // "Kedalaman Air Tanah" — di dekat ujung bawah
                                $lblY2 = $depthY2 - 8;  // nilai "10 m" — tepat di atas tick bawah

                                // Format angka biar rapi (opsional)
                                $depthVal = rtrim(rtrim(number_format((float) $kdlSumur, 2, '.', ''), '0'), '.');
                            @endphp

                            {{-- Garis vertikal putus-putus (trimmed) --}}
                            <line x1="{{ $depthLineX }}" y1="{{ $depthY1 }}" x2="{{ $depthLineX }}"
                                y2="{{ $depthY2 }}" stroke="#475569" stroke-width="1.4" stroke-dasharray="5 3"
                                marker-start="url(#arrowUp-{{ $uid }})"
                                marker-end="url(#arrowDown-{{ $uid }})" />

                            {{-- Tick mark atas (ikut y1) --}}
                            <line x1="{{ $depthLineX - 7 }}" y1="{{ $depthY1 }}" x2="{{ $depthLineX + 7 }}"
                                y2="{{ $depthY1 }}" stroke="#333" stroke-width="2" />

                            {{-- Tick mark bawah (ikut y2) --}}
                            <line x1="{{ $depthLineX - 7 }}" y1="{{ $depthY2 }}" x2="{{ $depthLineX + 7 }}"
                                y2="{{ $depthY2 }}" stroke="#333" stroke-width="2" />

                            {{-- Label: "Kedalaman Air Tanah" (di samping garis) --}}
                            <text x="{{ $lblX }}" y="{{ $lblY1 }}" text-anchor="end" font-size="9"
                                fill="#64748B" font-family="ui-sans-serif,system-ui,sans-serif">
                                Kedalaman Air Tanah
                            </text>

                            {{-- Nilai bold: "10 m" (di samping garis, tepat di bawah label) --}}
                            <text x="{{ $lblX }}" y="{{ $lblY2 }}" text-anchor="end" font-size="13"
                                font-weight="700" fill="#1E293B" font-family="ui-sans-serif,system-ui,sans-serif">
                                {{ $depthVal }} m
                            </text>

                        </svg>

                        {{-- WELL DIAGRAM --}}
                        <div style="position:absolute; left:{{ $wellOff }}px; top:0;">
                            <div
                                style="width:{{ $diagramWScaled }}px; height:{{ $diagramHScaled }}px; display:flex; align-items:flex-start; justify-content:center;">
                                <div class="relative"
                                    style="width:137px; height:{{ $totalH }}px; overflow:visible; transform:scale(1.4); transform-origin:top center;">

                                    <img src="{{ asset('sumur/top_sumur.svg') }}" width="97" height="14"
                                        class=""
                                        style="position:absolute; top:{{ $topTop }}px; left:{{ $topOffX }}px; z-index:50; display:block; image-rendering:auto;"
                                        alt="Top Sumur" />

                                    <img src="{{ asset('sumur/badan_sumur.svg') }}" width="137"
                                        height="{{ $badanH }}" class=""
                                        style="position:absolute; top:{{ $badanTop }}px; left:0; z-index:10; display:block; image-rendering:auto;"
                                        alt="Badan Sumur" />

                                    {{-- AIR --}}
                                    <svg style="position:absolute; top:0; left:0; z-index:15;" width="137"
                                        height="{{ $totalH }}" viewBox="0 0 137 {{ $totalH }}"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <defs>
                                            <linearGradient id="wG-{{ $uid }}" x1="0%" y1="0%"
                                                x2="0%" y2="100%">
                                                <stop offset="0%"
                                                    style="stop-color:{{ $wSurf }};stop-opacity:1" />
                                                <stop offset="20%"
                                                    style="stop-color:{{ $wTop }};stop-opacity:1" />
                                                <stop offset="60%"
                                                    style="stop-color:{{ $wMid }};stop-opacity:1" />
                                                <stop offset="100%"
                                                    style="stop-color:{{ $wBot }};stop-opacity:1" />
                                            </linearGradient>
                                            <clipPath id="wClip-{{ $uid }}">
                                                <rect x="{{ $innerX }}" y="{{ $wellTop }}"
                                                    width="{{ $innerW }}" height="{{ $wellRange }}" />
                                            </clipPath>
                                        </defs>
                                        <rect x="{{ $innerX }}" y="{{ $wStartY }}"
                                            width="{{ $innerW }}" height="{{ $wHeight }}"
                                            fill="url(#wG-{{ $uid }})" opacity="{{ $wOpac }}"
                                            clip-path="url(#wClip-{{ $uid }})" />
                                        <ellipse cx="{{ $cx }}" cy="{{ $wStartY }}" rx="12"
                                            ry="1.5" fill="{{ $wSurf }}"
                                            opacity="{{ $isOnline ? '0.85' : '0.20' }}" />
                                    </svg>

                                    {{-- CASING DALAM --}}
                                    <img src="{{ asset('sumur/dalam_sumur.svg') }}" width="29"
                                        height="{{ $dalH }}" class=""
                                        style="position:absolute; top:{{ $dalTop }}px; left:{{ $dalOffX }}px; z-index:20; display:block; transform:translateY(-1px) scaleY(1.02); transform-origin:top;"
                                        alt="Dalam Sumur" />

                                    {{-- CAP SENSOR --}}
                                    <svg style="position:absolute; top:0; left:0; z-index:94;" width="137"
                                        height="{{ $totalH }}" viewBox="0 0 137 {{ $totalH }}"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <image href="{{ asset('sumur/cap_sensor.svg') }}" x="{{ $capSensX }}"
                                            y="{{ $capSensY }}" width="{{ $capSensW }}"
                                            height="{{ $capSensH }}" preserveAspectRatio="xMidYMid meet"
                                            opacity="{{ $sOpac }}" />
                                    </svg>

                                    {{-- SENSOR & POMPA --}}
                                    <svg style="position:absolute; top:0; left:0; z-index:60;" width="137"
                                        height="{{ $totalH }}" viewBox="0 0 137 {{ $totalH }}"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <image href="{{ asset('sumur/line_sensor.svg') }}" x="{{ $sensorLineX }}"
                                            y="{{ $sensorLineTopY - 0.5 }}" width="{{ $sensorLineW }}"
                                            height="{{ $sensorLineH + 0.5 }}" preserveAspectRatio="none"
                                            opacity="{{ $sOpac }}" />
                                        <image href="{{ asset('sumur/kepala_sensor.svg') }}"
                                            x="{{ $sensorHeadX }}" y="{{ $sensorHeadY }}"
                                            width="{{ $sensorHeadW }}" height="{{ $sensorHeadH }}"
                                            preserveAspectRatio="xMidYMid meet" opacity="{{ $sOpac }}" />
                                        @if ($hasPompa)
                                            <image href="{{ asset('sumur/line_pompa.svg') }}"
                                                x="{{ $pumpLineX }}" y="{{ $pumpLineTopY - 0.3 }}"
                                                width="{{ $pumpLineW }}" height="{{ $pumpLineH + 0.5 }}"
                                                preserveAspectRatio="none" opacity="{{ $sOpac }}" />
                                            <image href="{{ asset('sumur/kepala_pompa.svg') }}"
                                                x="{{ $pumpHeadX }}" y="{{ $pumpHeadY }}"
                                                width="{{ $pumpHeadW }}" height="{{ $pumpHeadH }}"
                                                preserveAspectRatio="xMidYMid meet" opacity="{{ $pOpac }}" />
                                        @endif
                                    </svg>

                                    {{-- CAP POMPA --}}
                                    @if ($hasPompa)
                                        @php
                                            $capHeadH = 5;
                                            $capW = $pumpLineW * 2.5;
                                            $capX = $pumpLineX + $pumpLineW / 2 - $capW / 2;
                                            $capHeadY = 2;
                                        @endphp
                                        <svg style="position:absolute; top:0; left:0; z-index:95;" width="137"
                                            height="{{ $totalH }}" viewBox="0 0 137 {{ $totalH }}"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <defs>
                                                <linearGradient id="capGrad-{{ $uid }}" x1="0%"
                                                    y1="0%" x2="100%" y2="0%">
                                                    <stop offset="0%" stop-color="white" />
                                                    <stop offset="50%" stop-color="#C0C4CA" />
                                                    <stop offset="100%" stop-color="#B3B8BF" />
                                                </linearGradient>
                                            </defs>
                                            <rect x="{{ $capX }}" y="{{ $capHeadY }}"
                                                width="{{ $capW }}" height="{{ $capHeadH }}"
                                                rx="1.5" ry="1.5"
                                                fill="url(#capGrad-{{ $uid }})" stroke="#959CA3"
                                                stroke-width="0.5" opacity="{{ $pOpac }}" />
                                        </svg>
                                    @endif
                                </div>
                            </div>

                        </div>

                        {{-- LEFT: DATA AIR TANAH --}}
                        @php
                            $analisaUrl = $pMukaAir
                                ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pMukaAir->nama_parameter)
                                : route('analisa.index', $lg->id_logger);
                        @endphp
                        <div style="position:absolute; left:0; top:{{ $datAirY }}px; width:{{ $cW }}px; z-index:60;"
                            class="rounded-lg border-2 px-2 py-1.5 {{ $isOnline ? 'border-stone-400 bg-stone-200' : 'border-slate-300 bg-slate-100 grayscale' }}">
                            <div
                                class="text-[9px] font-bold uppercase tracking-wide {{ $isOnline ? 'text-stone-700' : 'text-slate-500' }}">
                                DATA AIR TANAH</div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-500' }}">{{ $dataAir ?? '-' }}</span>
                                <span class="text-xs font-semibold text-slate-400">m</span>
                            </div>
                        </div>

                        {{-- LEFT: ELEVASI SENSOR --}}
                        <div style="position:absolute; left:0; top:{{ $elevSensY }}px; width:{{ $cW }}px; z-index:60;"
                            class="rounded-lg border-2 px-2 py-1.5 {{ $isOnline ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-slate-100 grayscale' }}">
                            <div
                                class="text-[9px] font-bold uppercase tracking-wide {{ $isOnline ? 'text-rose-700' : 'text-slate-500' }}">
                                ELEVASI SENSOR</div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-500' }}">{{ $lg?->jiat?->kedalaman_sensor ?? '-' }}</span>
                                <span class="text-xs font-semibold text-slate-400">m</span>
                            </div>
                        </div>

                        {{-- RIGHT: MUKA AIR TANAH --}}
                        <a href="{{ $analisaUrl }}"
                            style="position:absolute; right:0; top:{{ $mukaAirY }}px; width:{{ $cW }}px; z-index:60;"
                            class="block rounded-lg border-2 px-2 py-1.5 transition-all hover:scale-105 hover:shadow-md {{ $isOnline ? 'border-sky-300 bg-sky-50 hover:border-sky-400' : 'border-slate-300 bg-slate-100 grayscale hover:shadow-sm' }}">
                            <div
                                class="text-[9px] font-bold uppercase tracking-wide {{ $isOnline ? 'text-sky-700' : 'text-slate-500' }}">
                                MUKA AIR TANAH</div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-500' }}">{{ $mukaAir ?? '-' }}</span>
                                <span class="text-xs font-semibold text-slate-400">m</span>
                            </div>
                        </a>

                        {{-- RIGHT: ELEVASI POMPA --}}
                        <div style="position:absolute; right:0; top:{{ $elevPompaY }}px; width:{{ $cW }}px; z-index:60;"
                            class="rounded-lg border-2 px-2 py-1.5 {{ $isOnline ? 'border-amber-300 bg-amber-50' : 'border-slate-300 bg-slate-100 grayscale' }}">
                            <div
                                class="text-[9px] font-bold uppercase tracking-wide {{ $isOnline ? 'text-amber-700' : 'text-slate-500' }}">
                                ELEVASI POMPA</div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-500' }}">{{ $lg?->jiat?->kedalaman_pompa ?? '-' }}</span>
                                <span class="text-xs font-semibold text-slate-400">m</span>
                            </div>
                        </div>

                    </div>{{-- end infographic container --}}
                </div>{{-- end overflow-x-auto wrapper --}}

                <script>
                (function () {
                    var contW = {{ $contW }};
                    var contH = {{ $contH }};
                    // innerH = contH saja (py-3 ada di luar area infographic, tidak perlu ditambah)
                    var innerH = contH;

                    function applyScale() {
                        document.querySelectorAll('.sumur-scale-wrapper').forEach(function (wrap) {
                            var inner = wrap.querySelector('.sumur-inner-container');
                            if (!inner) return;

                            // Ukur lebar kolom yang tersedia
                            var availW = wrap.getBoundingClientRect().width - 4;
                            if (availW <= 0) return; // layout belum siap, tunggu retry

                            if (availW >= contW) {
                                // ── Cukup muat: tampilkan penuh, hapus scaling ──
                                wrap.classList.remove('is-scaled');
                                inner.style.transform       = '';
                                inner.style.transformOrigin = '';
                                wrap.style.height           = '';
                                return;
                            }

                            // ── Tidak muat: scale agar pas di kolom ──
                            var scale = availW / contW;

                            wrap.classList.add('is-scaled');
                            inner.style.transformOrigin = 'top center';
                            inner.style.transform       = 'scale(' + scale + ')';

                            // Tinggi wrapper = contH * scale + sedikit napas bawah (8px)
                            wrap.style.height = Math.ceil(innerH * scale) + 8 + 'px';
                        });
                    }

                    function scheduleScale() {
                        // Double rAF: pastikan browser sudah selesai layout & paint
                        // sebelum kita baca clientWidth / getBoundingClientRect
                        requestAnimationFrame(function () {
                            requestAnimationFrame(applyScale);
                        });
                    }

                    document.addEventListener('DOMContentLoaded', scheduleScale);
                    window.addEventListener('resize', applyScale);
                    window.addEventListener('orientationchange', function () {
                        setTimeout(applyScale, 200);
                    });

                    // Fallback: jalankan lagi setelah 300ms kalau rAF belum dapat width
                    document.addEventListener('DOMContentLoaded', function () {
                        setTimeout(applyScale, 300);
                    });
                })();
                </script>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4 space-y-3">
            <div class="text-md font-semibold text-slate-700">Parameter Logger</div>
            @include('beranda.categories.partials.logger_health_cards')
        </div>
    </div>
</div>
