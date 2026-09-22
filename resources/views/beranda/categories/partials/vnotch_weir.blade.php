{{-- Ilustrasi AWLR non-JIAT terpasang di ambang v-notch (sensor ultrasonic).
     Air hulu, nappe, kolam hilir, dan papan peil digambar dinamis;
     struktur beton, sensor, dan splash dari aset public/vnotch. --}}
                @php
                    // ---- Geometri artwork (public/vnotch/vn_*.svg, viewBox 0 0 359 289) ----
                    // Angka-angka ini dibaca langsung dari aset; ubah aset berarti ubah angka di sini.
                    $vnAx        = 180.25;   // sumbu simetri notch
                    $vnApexUp    = 139.887;  // apex V pada muka hulu plat -> datum air hulu
                    $vnCrestUp   = 86.50;    // tepi atas plat pada muka hulu
                    $vnNotchPx   = $vnApexUp - $vnCrestUp;      // 53.387 px untuk satu kedalaman notch
                    $vnEdgeSlope = 1.4906;   // dx/dy tepi V muka hulu (px mendatar per px tegak)
                    $vnApexF     = 142.855;  // apex V pada muka hilir plat (yang menutupi nappe)
                    $vnSlopeF    = 1.4823;   // dx/dy tepi V muka hilir
                    $vnUpShift   = 25.5;     // geser bidang depan -> bidang belakang, kolam hulu
                    $vnUpBotY    = 148.5;    // dasar air hulu yang tergambar (kaki plat)

                    // Kolam hilir (stilling basin) di latar depan.
                    $vnBasinShift  = 63.0;   // geser bidang depan -> bidang belakang
                    $vnBasinFloorY = 288.0;  // dasar kolam, bidang depan
                    $vnBasinTopY   = 216.5;  // muka air tertinggi yang masih wajar digambar
                    $vnSplashY0    = 255.0;  // muka air kolam di artwork asli (acuan geser splash)

                    // Papan peil (artwork: x 40.5..66.5, y 160.5..288.5, rx 4).
                    $vnBoardX   = 40.5;
                    $vnBoardW   = 26.0;
                    $vnBoardTop = 160.5;
                    $vnBoardBot = 288.5;
                    $vnBoardPad = 7.0;       // jarak tick ke sudut membulat papan
                    $vnTickX    = $vnBoardX + 2.166;   // pangkal tick, sisi kiri papan
                    $vnScaleTopY = $vnBoardTop + $vnBoardPad;   // 165.5 -> elevasi maks
                    $vnScaleBotY = $vnBoardBot - $vnBoardPad;   // 283.5 -> elevasi min

                    // ---- Konfigurasi per logger ----
                    $vnNonjiat  = $lg->nonjiat;
                    $vnApexElev = is_numeric($vnNonjiat?->elevasi_apex) ? (float) $vnNonjiat->elevasi_apex : null;
                    $vnMinElev  = is_numeric($vnNonjiat?->elevasi_min) ? (float) $vnNonjiat->elevasi_min : null;
                    $vnMaxElev  = is_numeric($vnNonjiat?->elevasi_max) ? (float) $vnNonjiat->elevasi_max : null;

                    // Kedalaman notch hanya untuk menggambar air di ambang (apex -> crest),
                    // bukan untuk skala peil. Data lama mengisi elevasi_max sebagai crest,
                    // jadi itu dipakai sebagai cadangan kalau kedalaman_notch kosong.
                    if (is_numeric($vnNonjiat?->kedalaman_notch) && (float) $vnNonjiat->kedalaman_notch > 0) {
                        $vnNotchM = (float) $vnNonjiat->kedalaman_notch;
                    } elseif ($vnApexElev !== null && $vnMaxElev !== null && $vnMaxElev > $vnApexElev) {
                        $vnNotchM = $vnMaxElev - $vnApexElev;
                    } else {
                        $vnNotchM = 0.5;
                    }

                    // Satuan hanya label; nilai ditampilkan apa adanya.
                    $vnUnit = 'cm';
                    $vnFmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') ?: '0';

                    // ---- Skala papan peil: batas atas = elevasi_max (Batas Atas Peil) ----
                    // Papan berdiri sendiri: rentangnya elevasi_min..elevasi_max, tidak lagi
                    // diikat ke kedalaman notch. Kalau elevasi_max kosong, dipakai apex + notch.
                    $vnScaleBot = $vnMinElev ?? $vnApexElev ?? 0.0;
                    $vnScaleTop = $vnMaxElev ?? (($vnApexElev ?? 0.0) + $vnNotchM);
                    $vnMaxMissing = $vnMaxElev === null;
                    if ($vnScaleTop <= $vnScaleBot) {
                        // Isian terbalik/serupa: buka rentang seadanya supaya papan tetap terbaca.
                        $vnScaleTop = $vnScaleBot + max($vnNotchM, 0.1);
                        $vnScaleBad = true;
                    } else {
                        $vnScaleBad = false;
                    }
                    $vnPpuBoard = ($vnScaleBotY - $vnScaleTopY) / ($vnScaleTop - $vnScaleBot);
                    $vnElevToY = fn ($elev) => $vnScaleBotY - ($elev - $vnScaleBot) * $vnPpuBoard;

                    // ---- Nilai terukur ----
                    $vnTma     = is_numeric($tma) ? (float) $tma : null;
                    // Head di atas apex. Semua nilai satu satuan yang sama, tanpa konversi.
                    $vnHeadRaw = $vnTma !== null ? ($vnApexElev !== null ? $vnTma - $vnApexElev : $vnTma) : null;
                    // Elevasi yang dibaca papan. Kalau apex belum diset, TMA dibaca apa adanya.
                    $vnLevelElev = $vnTma;

                    // ---- Air hulu di ambang: skala terkunci ke notch yang tergambar ----
                    // px per satuan TIDAK bebas: apex dan crest ada di y tetap di artwork, jadi
                    // skalanya harus 53,387 px per kedalaman notch. Kalau dilepas, muka air
                    // hasil hitungan tak lagi sejajar dengan V yang tergambar.
                    $vnPpuNotch = $vnNotchPx / $vnNotchM;
                    $vnHeadPx   = $vnHeadRaw !== null
                        ? max(-($vnUpBotY - $vnApexUp), min($vnNotchPx, $vnHeadRaw * $vnPpuNotch))
                        : null;
                    $vnSurfY    = $vnHeadPx !== null ? $vnApexUp - $vnHeadPx : null;
                    // Ambang dianggap kering selama head belum benar-benar di atas apex.
                    // Ambangnya px, bukan satuan ukur, supaya berlaku sama untuk notch besar
                    // maupun kecil: di bawah ini airnya tak akan kelihatan pun kalau digambar.
                    // Air hulu tetap ada selama masih di atas dasar saluran — di head 0 pun
                    // permukaannya persis di apex dan masih terlihat lewat bukaan V.
                    // Yang berhenti di head 0 hanya limpasannya.
                    $vnHasUpWater = $vnSurfY !== null && $vnSurfY < $vnUpBotY - 0.5;
                    // Melimpah ditentukan nilainya, bukan pikselnya: head di atas apex berapa pun
                    // artinya mengalir, dan pancurannya digambar dengan lebar minimum supaya
                    // tetap terlihat. Percikan menyusul kalau pancurannya sudah cukup tebal.
                    $vnFlowing    = $vnHeadRaw !== null && $vnHeadRaw > 0.0;  // ada head = ada pancuran
                    $vnSplashOn   = $vnHeadPx !== null && $vnHeadPx >= 5.0;   // baru cukup deras untuk pecah
                    $vnBelowApex  = $vnHeadRaw !== null && $vnHeadRaw < 0.0;

                    // ---- Muka air kolam hilir: ikut garis papan supaya papan dan air sejalan ----
                    $vnLineYRaw = $vnLevelElev !== null
                        ? $vnElevToY(max($vnScaleBot, min($vnScaleTop, $vnLevelElev)))
                        : null;
                    $vnBasinY = $vnLineYRaw !== null
                        ? max($vnBasinTopY, min($vnBasinFloorY, $vnLineYRaw))
                        : $vnBasinFloorY;
                    $vnBasinBackY = $vnBasinY - $vnBasinShift;
                    // Di angka terbawah papan (dan tanpa data) kolam digambar kosong, bukan setipis apa pun.
                    $vnHasBasin   = $vnLevelElev !== null
                        && $vnLevelElev > $vnScaleBot + 1e-9
                        && $vnBasinY < $vnBasinFloorY - 0.5;

                    // ---- Nappe: bentuk mengikuti artwork ----
                    // Pangkal selebar bukaan V di muka air (dengan lekuk V di tengah),
                    // menyempit di leher tepat di bawah apex, lalu melebar sebelum pecah.
                    // Perbandingan artwork: leher 0,54 x pangkal, kaki 1,28 x pangkal.
                    $vnVHalf     = max(2.4, ($vnHeadPx ?? 0.0) * $vnEdgeSlope);   // separuh lebar V di muka air
                    $vnJetWaist  = max(1.8, 0.54 * $vnVHalf);                     // leher, kontraksi jet
                    $vnJetBotY   = $vnBasinBackY + 1.0;                           // jatuh di muka air kolam
                    $vnJetWaistY = min($vnApexF + 14.0, $vnJetBotY - 8.0);
                    // Kaki dijaga tetap di dalam kolam (dinding dalam x 88,5..271,5).
                    $vnJetBot    = max(3.6, min(88.0, 1.28 * $vnVHalf));
                    $vnDip       = $vnHeadPx ?? 0.0;                              // lekuk pangkal = apex V

                    // Percikan melebar mengikuti kaki nappe, dan ikut muka air kolam.
                    $vnSplashSx  = min(1.8, max(0.85, $vnJetBot / 18.0));

                    // ---- Tick papan ----
                    // Langkah dipilih yang MEMBAGI HABIS rentang peil, dihitung dari Elevasi Min,
                    // supaya kedua ujung papan jatuh tepat di angka dan jaraknya rata.
                    // Kalau tak ada yang membagi habis, baru pakai kelipatan bulat biasa dan
                    // kedua ujung ditambahkan sebagai angka tersendiri.
                    $vnMajorEvery = 5;                 // 5 tick kecil per angka, seperti artwork
                    $vnMinMajorPx = 18.0;              // jarak minimal antar angka
                    $vnSteps = [0.01, 0.02, 0.05, 0.1, 0.2, 0.25, 0.5, 1, 2, 2.5, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 2500, 5000];
                    $vnRange = $vnScaleTop - $vnScaleBot;

                    $vnMajor = null;
                    foreach ($vnSteps as $vnCandidate) {
                        if ($vnCandidate * $vnPpuBoard < $vnMinMajorPx) {
                            continue;
                        }
                        $vnQuot = $vnRange / $vnCandidate;
                        if (abs($vnQuot - round($vnQuot)) < 1e-6 && round($vnQuot) >= 2) {
                            $vnMajor = $vnCandidate;
                            break;
                        }
                    }
                    $vnFitsRange = $vnMajor !== null;
                    if (!$vnFitsRange) {
                        // Tidak ada langkah bulat yang pas; ambil yang terkecil dan masih longgar.
                        $vnMajor = end($vnSteps);
                        foreach ($vnSteps as $vnCandidate) {
                            if ($vnCandidate * $vnPpuBoard >= $vnMinMajorPx) {
                                $vnMajor = $vnCandidate;
                                break;
                            }
                        }
                    }
                    $vnTick    = $vnMajor / $vnMajorEvery;
                    $vnMajorPx = $vnMajor * $vnPpuBoard;

                    $vnTicks = [];
                    if ($vnFitsRange) {
                        // Dihitung dari batas bawah, jadi batas atas otomatis jadi tick terakhir.
                        $vnCount = (int) round($vnRange / $vnTick);
                        for ($vnI = 0; $vnI <= $vnCount; $vnI++) {
                            $vnTicks[] = [
                                'e'     => $vnScaleBot + $vnI * $vnTick,
                                'major' => $vnI % $vnMajorEvery === 0,
                            ];
                        }
                    } else {
                        // Ujung papan tetap berlabel; tick bulat yang mepet ujung dibuang.
                        $vnTicks[] = ['e' => $vnScaleTop, 'major' => true];
                        $vnTicks[] = ['e' => $vnScaleBot, 'major' => true];
                        $vnStepFrom = (int) ceil($vnScaleBot / $vnTick - 1e-9);
                        $vnStepTo   = (int) floor($vnScaleTop / $vnTick + 1e-9);
                        if ($vnStepTo - $vnStepFrom > 400) {
                            $vnStepTo = $vnStepFrom + 400;
                        }
                        for ($vnI = $vnStepFrom; $vnI <= $vnStepTo; $vnI++) {
                            $vnE     = $vnI * $vnTick;
                            $vnIsMaj = $vnI % $vnMajorEvery === 0;
                            $vnGapPx = min(abs($vnE - $vnScaleTop), abs($vnE - $vnScaleBot)) * $vnPpuBoard;
                            if ($vnGapPx < ($vnIsMaj ? 0.55 : 0.30) * $vnMajorPx) {
                                continue;
                            }
                            $vnTicks[] = ['e' => $vnE, 'major' => $vnIsMaj];
                        }
                    }

                    // Label dihitung dulu supaya panjang tick dan font bisa ikut panjangnya.
                    // Elevasi dalam cm bisa 4-5 digit (1250) atau lebih tergantung datum.
                    foreach ($vnTicks as $vnK => $vnT) {
                        $vnTicks[$vnK]['label'] = $vnT['major'] ? $vnFmt($vnT['e']) : null;
                    }
                    $vnLabelLens = array_map(
                        fn ($t) => strlen((string) $t['label']),
                        array_filter($vnTicks, fn ($t) => $t['major'])
                    );
                    $vnLabelLen = $vnLabelLens === [] ? 1 : max($vnLabelLens);
                    // Papan tetap 26 unit seperti artwork, jadi tick dan font yang menyesuaikan.
                    // Panjang tick artwork: mayor 13, minor 8,667. Dipendekkan kalau labelnya panjang.
                    $vnMajorLen = $vnLabelLen <= 2 ? 13.0 : 8.5;
                    $vnMinorLen = $vnMajorLen * 0.667;
                    // Area label = sisa papan di kanan tick, dikurangi margin kiri/kanan.
                    // Faktor 0,62 lebar-per-karakter diukur dari raster (digit ~0,61-0,65 x font).
                    $vnLabelArea = $vnBoardX + $vnBoardW - ($vnTickX + $vnMajorLen) - 2.6;
                    $vnLabelFont = min(8.0, max(4.2, $vnLabelArea / (max(1, $vnLabelLen) * 0.62)));
                    $vnLabelX    = $vnTickX + $vnMajorLen + 1.2;

                    // ---- Garis muka air + angka yang dibaca operator ----
                    $vnLineY   = $vnLineYRaw ?? $vnSplashY0;
                    $vnLineClr = $vnBelowApex ? '#d97706' : '#0284c7';
                    $vnClipped = $vnLevelElev !== null
                        && ($vnLevelElev > $vnScaleTop + 1e-6 || $vnLevelElev < $vnScaleBot - 1e-6);
                    $vnNowText = $vnLevelElev !== null ? $vnFmt($vnLevelElev) . ' ' . $vnUnit : '-';
                    $vnBadgeW  = max(42.0, strlen($vnNowText) * 4.9 + 8.0);
                    $vnBadgeX  = $vnBoardX + $vnBoardW + 4.0;
                @endphp

                <svg viewBox="0 0 359 289" xmlns="http://www.w3.org/2000/svg"
                    class="mx-auto h-auto w-full max-w-[480px] {{ $muted ? 'grayscale opacity-70' : '' }}">
                    {{-- Dinding hulu + bidang belakang --}}
                    <image href="{{ asset('vnotch/vn_belakang.svg') }}" x="0" y="0" width="359" height="289"
                        preserveAspectRatio="xMidYMid meet" />

                    @if ($vnHasUpWater)
                        {{-- Air hulu: terlihat lewat bukaan V dan di atas plat --}}
                        <path
                            d="M84 {{ $vnSurfY }}V{{ $vnUpBotY }}H275V{{ $vnSurfY }}L263.5 {{ $vnSurfY - $vnUpShift }}H95.5Z"
                            fill="#95E5FF" />
                    @endif

                    {{-- Ambang, pilar, lantai kolam --}}
                    <image href="{{ asset('vnotch/vn_struktur.svg') }}" x="0" y="0" width="359" height="289"
                        preserveAspectRatio="xMidYMid meet" />

                    @if ($vnHasBasin)
                        {{-- Kolam hilir: mukanya sejajar garis papan peil --}}
                        <path
                            d="M66 {{ $vnBasinY }}V{{ $vnBasinFloorY }}H293V{{ $vnBasinY }}L271.5 {{ $vnBasinBackY }}H88.5Z"
                            fill="#5CD6FF" fill-opacity="0.5" />
                    @endif

                    {{-- Plat notch + baut (bidang depan) --}}
                    <image href="{{ asset('vnotch/vn_depan.svg') }}" x="0" y="0" width="359" height="289"
                        preserveAspectRatio="xMidYMid meet" />

                    @if ($vnFlowing)
                        {{-- Nappe: pangkal selebar takik di muka air, leher, lalu melebar --}}
                        <path
                            d="M{{ $vnAx - $vnVHalf }} {{ $vnSurfY }}
                               L{{ $vnAx }} {{ $vnSurfY + $vnDip }}
                               L{{ $vnAx + $vnVHalf }} {{ $vnSurfY }}
                               C{{ $vnAx + $vnVHalf }} {{ $vnSurfY }} {{ $vnAx + $vnJetWaist }} {{ $vnJetWaistY - 16 }} {{ $vnAx + $vnJetWaist }} {{ $vnJetWaistY }}
                               C{{ $vnAx + $vnJetWaist }} {{ $vnJetWaistY + 10 }} {{ $vnAx + $vnJetBot * 0.93 }} {{ $vnJetBotY - 14 }} {{ $vnAx + $vnJetBot }} {{ $vnJetBotY }}
                               H{{ $vnAx - $vnJetBot }}
                               C{{ $vnAx - $vnJetBot * 0.93 }} {{ $vnJetBotY - 14 }} {{ $vnAx - $vnJetWaist }} {{ $vnJetWaistY + 10 }} {{ $vnAx - $vnJetWaist }} {{ $vnJetWaistY }}
                               C{{ $vnAx - $vnJetWaist }} {{ $vnJetWaistY - 16 }} {{ $vnAx - $vnVHalf }} {{ $vnSurfY }} {{ $vnAx - $vnVHalf }} {{ $vnSurfY }}Z"
                            fill="#95E5FF" />
                        @if ($vnSplashOn)
                        {{-- Percikan ikut turun/naik bersama muka air kolam --}}
                        <g transform="translate({{ $vnAx }} {{ $vnBasinY }}) scale({{ $vnSplashSx }} 1) translate({{ -$vnAx }} {{ -$vnSplashY0 }})">
                            <image href="{{ asset('vnotch/vn_splash.svg') }}" x="0" y="0" width="359" height="289"
                                preserveAspectRatio="xMidYMid meet" />
                        </g>
                        @endif
                    @endif

                    {{-- Sensor, braket, kabel --}}
                    <image href="{{ asset('vnotch/vn_sensor.svg') }}" x="0" y="0" width="359" height="289"
                        preserveAspectRatio="xMidYMid meet" />

                    {{-- Papan peil: rentang elevasi_min .. elevasi_max (Batas Atas Peil) --}}
                    <path
                        d="M{{ $vnBoardX + $vnBoardW - 4 }} {{ $vnBoardBot }}H{{ $vnBoardX + 4 }}C{{ $vnBoardX + 1.79 }} {{ $vnBoardBot }} {{ $vnBoardX }} {{ $vnBoardBot - 1.79 }} {{ $vnBoardX }} {{ $vnBoardBot - 4 }}V{{ $vnBoardTop + 4 }}C{{ $vnBoardX }} {{ $vnBoardTop + 1.79 }} {{ $vnBoardX + 1.79 }} {{ $vnBoardTop }} {{ $vnBoardX + 4 }} {{ $vnBoardTop }}H{{ $vnBoardX + $vnBoardW - 4 }}C{{ $vnBoardX + $vnBoardW - 1.79 }} {{ $vnBoardTop }} {{ $vnBoardX + $vnBoardW }} {{ $vnBoardTop + 1.79 }} {{ $vnBoardX + $vnBoardW }} {{ $vnBoardTop + 4 }}V{{ $vnBoardBot - 4 }}C{{ $vnBoardX + $vnBoardW }} {{ $vnBoardBot - 1.79 }} {{ $vnBoardX + $vnBoardW - 1.79 }} {{ $vnBoardBot }} {{ $vnBoardX + $vnBoardW - 4 }} {{ $vnBoardBot }}Z"
                        fill="#FFD178" stroke="black" stroke-linecap="round" stroke-linejoin="round" />

                    @foreach ($vnTicks as $vnT)
                        @php $ty = $vnElevToY($vnT['e']); @endphp
                        @if ($vnT['major'])
                            {{-- Tick mayor: baji merah seperti artwork --}}
                            <path
                                d="M{{ $vnTickX }} {{ $ty - 1.2 }}H{{ $vnTickX + $vnMajorLen }}L{{ $vnTickX + $vnMajorLen - 1.45 }} {{ $ty + 1.2 }}H{{ $vnTickX }}Z"
                                fill="#FF0000" />
                            <text x="{{ $vnLabelX }}" y="{{ $ty + $vnLabelFont * 0.35 }}"
                                font-size="{{ $vnLabelFont }}" text-anchor="start"
                                font-family="ui-sans-serif, system-ui, sans-serif" fill="#1f2937"
                                font-weight="700">{{ $vnT['label'] }}</text>
                        @else
                            <path d="M{{ $vnTickX }} {{ $ty }}H{{ $vnTickX + $vnMinorLen }}" stroke="black"
                                stroke-width="0.9" stroke-linejoin="round" />
                        @endif
                    @endforeach

                    {{-- Garis muka air + angka ketinggian air sekarang --}}
                    <line x1="{{ $vnBoardX }}" y1="{{ $vnLineY }}"
                        x2="{{ $vnBadgeX + $vnBadgeW }}" y2="{{ $vnLineY }}" stroke="{{ $vnLineClr }}"
                        stroke-width="1.8" stroke-dasharray="{{ $isOnline ? 'none' : '4 3' }}" />
                    <rect x="{{ $vnBadgeX }}" y="{{ $vnLineY - 7 }}" width="{{ $vnBadgeW }}" height="14" rx="3.5"
                        fill="{{ $vnLineClr }}" opacity="{{ $isOnline ? '1' : '0.65' }}" />
                    <text x="{{ $vnBadgeX + $vnBadgeW / 2 }}" y="{{ $vnLineY + 3.6 }}" font-size="8.5"
                        text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" fill="#ffffff"
                        font-weight="700">{{ $vnNowText }}</text>
                </svg>

@if ($vnScaleBad)
    <p class="text-[11px] text-amber-600">
        Elevasi Maks (Batas Atas Peil) harus lebih besar dari Elevasi Min — skala papan dipakai seadanya.
        Perbaiki di Data Perangkat.
    </p>
@elseif ($vnMaxMissing)
    <p class="text-[11px] text-amber-600">
        Elevasi Maks (Batas Atas Peil) belum diset — batas atas papan memakai apex + kedalaman notch.
        Atur di Data Perangkat.
    </p>
@endif
@if ($vnClipped)
    <p class="text-[11px] text-amber-600">
        TMA {{ $vnFmt($vnLevelElev) }} {{ $vnUnit }} di luar rentang papan
        {{ $vnFmt($vnScaleBot) }}–{{ $vnFmt($vnScaleTop) }} {{ $vnUnit }} — garis muka air dijepit di batas.
    </p>
@endif
@if ($vnApexElev === null)
    <p class="text-[11px] text-amber-600">
        Elevasi apex belum diset — nilai TMA dibaca langsung sebagai head, tinggi air di ambang bisa meleset.
        Atur di Data Perangkat.
    </p>
@endif
