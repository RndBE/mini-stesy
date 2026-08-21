{{-- Ilustrasi AWLR non-JIAT terpasang di ambang v-notch (sensor ultrasonic).
     Air, nappe, dan papan peil digambar dinamis; struktur beton dari aset public/vnotch. --}}
                @php
                    // ---- Geometri artwork (public/vnotch/*.svg, viewBox 0 0 359 289) ----
                    // Titik-titik ini terikat ke aset; ubah aset berarti ubah angka di sini.
                    $vnAx        = 180.25;   // sumbu simetri notch
                    $vnApexY     = 189.855;  // y apex pada bidang plat (hilir)
                    $vnDepthPx   = 54.376;   // apex -> crest, notch 90 derajat
                    $vnShift     = 72.0;     // geser vertikal bidang depan -> bidang dinding belakang
                    $vnApexBack  = $vnApexY - $vnShift;        // apex diproyeksi ke bidang peil
                    $vnBedY      = 195.0;    // dasar kolam hulu
                    $vnSplashY   = 240.0;    // ketinggian pecah air di kolam hilir

                    // ---- Konfigurasi per logger ----
                    $vnNonjiat  = $lg->nonjiat;
                    $vnApexElev = is_numeric($vnNonjiat?->elevasi_apex) ? (float) $vnNonjiat->elevasi_apex : null;
                    $vnMinElev  = is_numeric($vnNonjiat?->elevasi_min) ? (float) $vnNonjiat->elevasi_min : null;
                    $vnMaxElev  = is_numeric($vnNonjiat?->elevasi_max) ? (float) $vnNonjiat->elevasi_max : null;

                    // Kedalaman notch = crest - apex. elevasi_max dipakai sebagai elevasi crest;
                    // kalau belum diset jatuh ke kedalaman_notch, lalu ke 0,5 m.
                    $vnDepthM = null;
                    if ($vnApexElev !== null && $vnMaxElev !== null && $vnMaxElev > $vnApexElev) {
                        $vnDepthM = $vnMaxElev - $vnApexElev;
                    } elseif (is_numeric($vnNonjiat?->kedalaman_notch) && (float) $vnNonjiat->kedalaman_notch > 0) {
                        $vnDepthM = (float) $vnNonjiat->kedalaman_notch;
                    } else {
                        $vnDepthM = 0.5;
                    }

                    // Head di atas apex. Semua nilai satu satuan yang sama, tanpa konversi.
                    $vnHeadRaw = is_numeric($tma)
                        ? ($vnApexElev !== null ? (float) $tma - $vnApexElev : (float) $tma)
                        : null;
                    $vnHeadM = $vnHeadRaw !== null ? max(0.0, min($vnDepthM, $vnHeadRaw)) : 0.0;

                    // Label = elevasi sebenarnya kalau apex diketahui, kalau tidak jatuh ke head.
                    $vnLabelBase = $vnApexElev ?? 0.0;
                    // Satuan hanya label; nilai ditampilkan apa adanya.
                    $vnUnit = 'cm';
                    $vnFmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') ?: '0';

                    // ---- Skala peil: batas dari elevasi_min/elevasi_max, seperti AWLR sungai ----
                    // px per meter TIDAK bebas: apex dan crest ada di y tetap di artwork, jadi
                    // skalanya harus 54,376 px per (crest - apex). Kalau dilepas, muka air hasil
                    // hitungan skala tak lagi sejajar dengan notch yang tergambar.
                    $vnPpu    = $vnDepthPx / $vnDepthM;
                    // Geometri papan mengikuti papan peil di ilustrasi_vnotch.svg:
                    // x 111.5, lebar 26, y 0.5 .. 128.5 (tinggi 128). Semuanya tetap.
                    $vnBoardX   = 111.5;
                    $vnBoardW   = 26.0;
                    $vnBoardTop = 0.5;
                    $vnBoardBot = 128.5;
                    $vnBoardPad = 5.0;   // jarak tick ke sudut membulat papan

                    // Papan tetap, jadi rentang skala turunan dari papan (bukan sebaliknya).
                    // Karena apex dipaku di y tetap dan crest selalu 54,376 px di atasnya,
                    // papan ini selalu memuat sekitar 2,16 x kedalaman notch di atas apex.
                    $vnFitTopM = ($vnApexBack - $vnBoardTop - $vnBoardPad) / $vnPpu;
                    $vnFitBotM = -($vnBoardBot - $vnBoardPad - $vnApexBack) / $vnPpu;

                    $vnScaleTopM = $vnFitTopM;
                    // elevasi_min boleh menaikkan batas bawah tick; kalau lebih rendah dari
                    // yang muat, dijepit dan ditandai supaya tidak dipotong diam-diam.
                    $vnWantBotM  = $vnMinElev !== null && $vnApexElev !== null
                        ? min(0.0, $vnMinElev - $vnApexElev)
                        : $vnFitBotM;
                    $vnScaleBotM = max($vnFitBotM, $vnWantBotM);
                    $vnScaleClipped = ($vnScaleBotM - $vnWantBotM) > 1e-6;

                    // Langkah "bulat" terkecil yang jarak piksel antar mayornya masih longgar.
                    // Berbasis piksel, bukan jumlah interval, supaya kerapatan tick ikut tinggi
                    // papan dan px-per-meter — mirip kepadatan papan di ilustrasi asli.
                    $vnMinMajorPx = 17.0;
                    $vnMajor = 0.01;
                    foreach ([0.01, 0.02, 0.05, 0.1, 0.2, 0.25, 0.5, 1, 2, 2.5, 5, 10, 20, 25, 50, 100] as $vnCandidate) {
                        $vnMajor = $vnCandidate;
                        if ($vnCandidate * $vnPpu >= $vnMinMajorPx) {
                            break;
                        }
                    }
                    $vnTick       = $vnMajor / 2;
                    $vnMajorEvery = 2;
                    // Iterasi kelipatan tick supaya apex (0) selalu jatuh tepat di tick.
                    $vnStepFrom = (int) ceil($vnScaleBotM / $vnTick - 1e-9);
                    $vnStepTo   = (int) floor($vnScaleTopM / $vnTick + 1e-9);

                    // Label dihitung dulu supaya font dan lebar papan bisa ikut panjangnya.
                    // Elevasi dalam cm bisa 4-5 digit (1250) atau lebih (10120) tergantung datum.
                    $vnLabels = [];
                    for ($vnI = $vnStepFrom; $vnI <= $vnStepTo; $vnI++) {
                        if ($vnI % $vnMajorEvery !== 0) {
                            continue;
                        }
                        $vnLabels[$vnI] = $vnFmt($vnLabelBase + $vnI * $vnTick);
                    }
                    $vnLabelLen  = $vnLabels === [] ? 1 : max(array_map('strlen', $vnLabels));
                    // Papan tetap 26 unit seperti aslinya, jadi font yang menyesuaikan panjang label.
                    // Faktor 0,62 lebar-per-karakter diukur dari raster (digit ~0,61-0,65 x font).
                    // Area label = lebar papan - panjang tick mayor - margin kiri/kanan.
                    $vnLabelArea = $vnBoardW - $vnBoardW * 0.32 - 3.0;
                    $vnLabelFont = min(9.0, max(4.4, $vnLabelArea / (max(1, $vnLabelLen) * 0.62)));


                    // ---- Geometri air dinamis ----
                    // Level bertanda, relatif apex. Boleh negatif saat kolam turun di bawah apex.
                    $vnLevelM = $vnHeadRaw;
                    // Dari sisi hilir, kolam di bawah apex tertutup plat; yang masih terlihat
                    // hanya sampai dasar saluran yang tergambar.
                    $vnPoolBotM = -($vnBedY - $vnApexY) / $vnPpu;
                    $vnDrawM    = $vnLevelM !== null
                        ? max($vnPoolBotM, min($vnDepthM, $vnLevelM))
                        : null;

                    // Badan air: tampil selama masih ada air di atas dasar, tak peduli melimpah atau tidak.
                    $vnHasWater  = $vnDrawM !== null && $vnDrawM > $vnPoolBotM + 1e-9;
                    // Nappe + splash: hanya kalau benar-benar melimpah lewat notch.
                    $vnFlowing   = $vnDrawM !== null && $vnDrawM > 0.0;
                    $vnBelowApex = $vnLevelM !== null && $vnLevelM < 0.0;

                    $vnSurfY   = $vnApexY - ($vnDrawM ?? 0.0) * $vnPpu;   // muka air, bidang depan
                    $vnHeadPx  = $vnHeadM * $vnPpu;                        // head positif, untuk nappe
                    $vnNappeW  = 0.8 * $vnHeadPx;                   // separuh lebar nappe di muka air (notch 90 derajat)
                    $vnThroatW = max(2.5, 0.45 * $vnNappeW);        // separuh lebar leher pancuran
                    $vnDip     = 0.35 * $vnHeadPx;                  // lengkung nappe mengikuti tepi notch
                    $vnTmaY    = $vnApexBack - ($vnDrawM ?? 0.0) * $vnPpu; // garis muka air pada bidang peil
                    $vnLineClr = $vnBelowApex ? '#d97706' : '#0284c7';

                    // Angka yang dibaca operator: ketinggian air sekarang.
                    $vnNowValue = $vnLevelM !== null ? $vnLabelBase + $vnLevelM : null;
                    $vnNowText  = $vnNowValue !== null ? $vnFmt($vnNowValue) . ' ' . $vnUnit : '-';
                @endphp

                <svg viewBox="0 0 359 289" xmlns="http://www.w3.org/2000/svg"
                    class="mx-auto h-auto w-full max-w-[480px] {{ $muted ? 'grayscale opacity-70' : '' }}">
                    {{-- Struktur belakang: dinding hulu, dasar saluran, sensor, braket, kabel --}}
                    <image href="{{ asset('vnotch/vnotch_belakang.svg') }}" x="0" y="0" width="359" height="289"
                        preserveAspectRatio="xMidYMid meet" />

                    @if ($vnHasWater)
                        {{-- Kolam hulu: tampil selama ada air, walau tidak melimpah --}}
                        <path
                            d="M65.5 {{ $vnSurfY }}V{{ $vnBedY }}L293.5 {{ $vnBedY + 0.5 }}V{{ $vnSurfY }}L263.5 {{ $vnSurfY - $vnShift }}H95.5L65.5 {{ $vnSurfY }}Z"
                            fill="#5CD6FF" fill-opacity="0.5" />
                    @endif

                    {{-- Struktur depan: plat notch, pier, baut --}}
                    <image href="{{ asset('vnotch/vnotch_depan.svg') }}" x="0" y="0" width="359" height="289"
                        preserveAspectRatio="xMidYMid meet" />

                    @if ($vnFlowing)
                        {{-- Nappe: lebar di muka air ikut head, menyempit di leher --}}
                        <path
                            d="M{{ $vnAx - $vnNappeW }} {{ $vnSurfY }}L{{ $vnAx }} {{ $vnSurfY + $vnDip }}L{{ $vnAx + $vnNappeW }} {{ $vnSurfY }}
                               C{{ $vnAx + $vnNappeW }} {{ $vnSurfY + 18 }} {{ $vnAx + $vnThroatW }} {{ $vnSurfY + 30 }} {{ $vnAx + $vnThroatW }} {{ $vnSplashY }}
                               L{{ $vnAx - $vnThroatW }} {{ $vnSplashY }}
                               C{{ $vnAx - $vnThroatW }} {{ $vnSurfY + 30 }} {{ $vnAx - $vnNappeW }} {{ $vnSurfY + 18 }} {{ $vnAx - $vnNappeW }} {{ $vnSurfY }}Z"
                            fill="#AEEBFF" />
                        <image href="{{ asset('vnotch/vnotch_splash.svg') }}" x="0" y="0" width="359" height="289"
                            preserveAspectRatio="xMidYMid meet" />
                    @endif

                    {{-- Papan peil: datum terikat apex, label elevasi --}}
                    <rect x="{{ $vnBoardX }}" y="{{ $vnBoardTop }}" width="{{ $vnBoardW }}"
                        height="{{ $vnBoardBot - $vnBoardTop }}" rx="4" fill="#FFD178" stroke="black"
                        stroke-linecap="round" stroke-linejoin="round" />

                    @for ($i = $vnStepFrom; $i <= $vnStepTo; $i++)
                        @php
                            $vHead   = round($i * $vnTick, 4);
                            $ty      = $vnApexBack - $vHead * $vnPpu;
                            $isMajor = $i % $vnMajorEvery === 0;
                            $tickLen = $isMajor ? $vnBoardW * 0.32 : $vnBoardW * 0.18;
                        @endphp
                        <line x1="{{ $vnBoardX + $vnBoardW - $tickLen }}" y1="{{ $ty }}"
                            x2="{{ $vnBoardX + $vnBoardW }}" y2="{{ $ty }}"
                            stroke="{{ $isMajor ? '#92400e' : '#b45309' }}"
                            stroke-width="{{ $isMajor ? 1.4 : 0.7 }}" />
                        @if ($isMajor)
                            <text x="{{ $vnBoardX + $vnBoardW * 0.68 - 1.2 }}" y="{{ $ty + 2.8 }}"
                                font-size="{{ $vnLabelFont }}" text-anchor="end"
                                font-family="ui-sans-serif, system-ui, sans-serif"
                                fill="#78350f" font-weight="700">{{ $vnLabels[$i] ?? '' }}</text>
                        @endif
                    @endfor

                    {{-- Garis muka air + angka ketinggian air sekarang --}}
                    <line x1="{{ $vnBoardX - 50 }}" y1="{{ $vnTmaY }}" x2="{{ $vnBoardX + $vnBoardW }}"
                        y2="{{ $vnTmaY }}" stroke="{{ $vnLineClr }}" stroke-width="1.8"
                        stroke-dasharray="{{ $isOnline ? 'none' : '4 3' }}" />
                    @php
                        $vnBadgeW = max(42.0, strlen($vnNowText) * 4.9 + 8.0);
                        $vnBadgeX = $vnBoardX - 4 - $vnBadgeW;
                    @endphp
                    <rect x="{{ $vnBadgeX }}" y="{{ $vnTmaY - 7 }}" width="{{ $vnBadgeW }}" height="14" rx="3.5"
                        fill="{{ $vnLineClr }}" opacity="{{ $isOnline ? '1' : '0.65' }}" />
                    <text x="{{ $vnBadgeX + $vnBadgeW / 2 }}" y="{{ $vnTmaY + 3.6 }}" font-size="8.5"
                        text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" fill="#ffffff"
                        font-weight="700">{{ $vnNowText }}</text>
                </svg>

@if ($vnScaleClipped)
    <p class="text-[11px] text-amber-600">
        Rentang elevasi_min/elevasi_max lebih lebar dari ruang gambar — skala peil dipotong ke
        {{ $vnFmt($vnLabelBase + $vnScaleBotM) }}–{{ $vnFmt($vnLabelBase + $vnScaleTopM) }} {{ $vnUnit }}.
    </p>
@endif
@if ($vnApexElev === null)
    <p class="text-[11px] text-amber-600">
        Elevasi apex belum diset — nilai TMA dibaca langsung sebagai head, label peil jadi head bukan elevasi.
        Atur di Data Perangkat.
    </p>
@endif
