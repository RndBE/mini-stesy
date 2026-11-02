{{-- Ilustrasi AWLR non-JIAT dengan sensor transduser di pipa miring pada badan bendungan.
     Artwork dari visualisasi-bendungan (kanvas 347x281). Air waduk, papan duga, posisi sensor,
     garis TMA/sensor, dan dua kotak nilai digambar dinamis; badan bendungan, pipa, dan klem statis.
     File ini dihasilkan dari artwork — kalau artwork berubah, angka geometri di sini ikut berubah. --}}
@php
    // ---- Skala papan duga: elevasi_min di y 238, elevasi_max di y 36 ----
    $dmBotY = 238.0;
    $dmTopY = 36.0;
    $dmGroundY = 269.0;   // dasar waduk

    $dmNonjiat = $lg->nonjiat;
    $dmUnit    = $pTma?->satuan ?: 'm';
    $dmTmaRaw  = is_numeric($tma) ? (float) $tma : null;

    $dmMin    = is_numeric($dmNonjiat?->elevasi_min) ? (float) $dmNonjiat->elevasi_min : 0.0;
    $dmMaxCfg = is_numeric($dmNonjiat?->elevasi_max) ? (float) $dmNonjiat->elevasi_max : null;
    // elevasi_max belum diset: skala dibuat cukup tinggi supaya bacaan tetap terlihat.
    $dmMax = ($dmMaxCfg !== null && $dmMaxCfg > $dmMin)
        ? $dmMaxCfg
        : max($dmMin + 10.0, $dmTmaRaw !== null ? ceil($dmTmaRaw + 1.0) : 0.0);
    $dmRange = $dmMax - $dmMin;
    $dmPpu   = ($dmBotY - $dmTopY) / $dmRange;

    $dmYOf     = fn ($value) => $dmBotY - ($value - $dmMin) * $dmPpu;
    $dmClampY  = fn ($y) => min($dmBotY, max($dmTopY, $y));

    // ---- Muka air ----
    $dmLevelY = $dmTmaRaw !== null ? $dmClampY($dmYOf($dmTmaRaw)) : $dmBotY;
    $dmWaterH = $dmGroundY - $dmLevelY;
    $dmTmaOut = $dmTmaRaw !== null && ($dmTmaRaw > $dmMax + 1e-9 || $dmTmaRaw < $dmMin - 1e-9);

    // Tepi atas air bergelombang (3 gelombang, tinggi 1,033) persis artwork; tinggi badan air variabel.
    $dmWave = 'c0 0-15.629-1.033-25.667-1.033c-10.038 0-15.629 1.033-25.667 1.033';
    $dmBody = round($dmWaterH - 1.033, 3);
    $dmWaterPath = 'M154 ' . round($dmWaterH, 3) . 'l0-' . $dmBody . str_repeat($dmWave, 3) . 'l0 ' . $dmBody . ' 154 0z';

    // ---- Sensor: kedalaman_sensor diukur dari puncak (batas atas papan duga) ----
    $dmKed = is_numeric($dmNonjiat?->kedalaman_sensor) && (float) $dmNonjiat->kedalaman_sensor > 0
        ? (float) $dmNonjiat->kedalaman_sensor
        : null;
    $dmEs  = $dmKed !== null ? $dmMax - $dmKed : null;   // elevasi sensor di skala papan duga
    // Sensor hanya bisa bergeser sepanjang pipa: y 70..230 pada kanvas (artwork asli di y 217).
    $dmSensorYRaw = $dmEs !== null ? $dmYOf($dmEs) : 217.0;
    $dmSensorY    = min(230.0, max(70.0, $dmSensorYRaw));
    // Pipa miring 60°: geser sepanjang pipa sebesar d menggeser kanvas (0,5d ; 0,866d).
    $dmDelta   = round(($dmSensorY - 217.0) / 0.866, 3);
    $dmSensorX = round(158.934 + 0.5 * $dmDelta, 3);
    $dmCableX  = round(143.8 + 0.5 * $dmDelta, 3);
    $dmCableY  = round(196.787 + 0.866 * $dmDelta, 3);

    // ---- Tick papan duga: langkah "bulat" dipilih dari jarak piksel ----
    $dmNice  = [0.01, 0.02, 0.05, 0.1, 0.2, 0.25, 0.5, 1, 2, 2.5, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000];
    $dmMajor = end($dmNice);
    foreach ($dmNice as $dmCandidate) {
        $dmMajor = $dmCandidate;
        if ($dmCandidate * $dmPpu >= 22.0) {
            break;
        }
    }
    $dmMinor    = $dmMajor / 5 * $dmPpu >= 4.5 ? $dmMajor / 5 : $dmMajor / 2;
    $dmPerMajor = (int) round($dmMajor / $dmMinor);

    $dmFmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') ?: '0';

    $dmTicks = [];
    for ($dmI = 0; $dmI < 600; $dmI++) {
        $dmTickY = round($dmBotY - $dmI * $dmMinor * $dmPpu, 3);
        if ($dmTickY < $dmTopY - 0.05) {
            break;
        }
        $dmTicks[] = [
            'y'     => $dmTickY,
            'major' => $dmI % $dmPerMajor === 0,
            'label' => $dmFmt($dmMin + $dmI * $dmMinor),
        ];
    }

    // Label dibatasi lebar papan di kanan tick (±10,5 satuan); faktor 0,62 = lebar glyph digit.
    $dmLabelLen = 1;
    foreach ($dmTicks as $dmTick) {
        if ($dmTick['major']) {
            $dmLabelLen = max($dmLabelLen, strlen($dmTick['label']));
        }
    }
    $dmLabelFont = min(5.6, max(3.6, 10.5 / ($dmLabelLen * 0.62)));

    // ---- Teks kotak nilai ----
    $dmTmaText   = $dmTmaRaw !== null ? \App\Support\DisplayFormat::ukur($dmTmaRaw, 3) : '-';
    $dmDepthText = $dmKed !== null ? \App\Support\DisplayFormat::ukur($dmKed, 3) : '-';
    $dmValueFont = fn ($text) => round(min(14.0, 70.0 / (max(1, strlen($text)) * 0.6)), 2);

    $dmLineClr    = '#00C0E8';
    $dmSensorClr  = '#FF3333';
    $dmLineDash   = $isOnline ? 'none' : '4 3';
    $dmLineOpac   = $isOnline ? '1' : '0.6';
    $dmTmaBoxCy   = 65.949;    // pusat kotak TMA
    $dmSensBoxCy  = 217.0;     // pusat kotak sensor
    $dmUid        = $lg->id_logger;
@endphp

<svg viewBox="0 0 347 281" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
    aria-label="Ilustrasi bendungan dengan sensor transduser"
    class="mx-auto h-auto w-full max-w-[460px] {{ $muted ? 'grayscale opacity-70' : '' }}">
    <defs>
        <linearGradient id="dmAir-{{ $dmUid }}" gradientUnits="userSpaceOnUse" x1="77" y1="-15" x2="77"
            y2="{{ round($dmWaterH, 3) }}">
            <stop offset="0%" stop-color="rgb(158,213,244)" />
            <stop offset="25%" stop-color="rgb(121,194,236)" />
            <stop offset="50%" stop-color="rgb(71,162,221)" />
            <stop offset="100%" stop-color="rgb(45,137,205)" />
        </linearGradient>
    </defs>

    {{-- Air waduk: tepi atas mengikuti TMA, dasar tetap di dasar waduk --}}
    <g transform="translate(40 {{ round($dmLevelY, 3) }})">
        <path d="{{ $dmWaterPath }}" fill="url(#dmAir-{{ $dmUid }})" />
    </g>

    {{-- Dasar waduk dan badan bendungan --}}
    <svg x="81" y="261" width="113" height="8" viewBox="0 0.00001387670636177063 113 7.999525975435972" preserveAspectRatio="none" overflow="visible"><path d="M113 7.99954l-113 0 0-4.854c0 0 8.593-1.492 14.125-1.71399 5.516-0.22198 8.609 0.793 14.125 0.57101 5.532-0.22198 8.593-1.491 14.125-1.71301 5.51601-0.22198 8.60899 0.793 14.125 0.57102 5.532-0.22299 8.593-0.48901 14.125-0.711 5.51601-0.22198 8.60899 1.79599 14.125 1.574 5.532-0.22198 8.593-1.491 14.125-1.71301 5.51601-0.22198 14.125 3.134 14.125 3.134l0 4.85498z" fill="#958271" /></svg>
    <svg x="12" y="33" width="164.255" height="236" viewBox="0 0 164.25466352875165 236" preserveAspectRatio="none" overflow="visible"><path d="M0 0L28 0L164.25466352875165 236L0 236Z" fill="#D9D9D9"/></svg>

    {{-- Kabel sensor: dari puncak bendungan sampai kelenjar kabel di ujung atas sensor --}}
    <path d="M12 32H48.66L{{ $dmCableX }} {{ $dmCableY }}" stroke="#000" stroke-width="2.5"
        stroke-linejoin="miter" vector-effect="non-scaling-stroke" />

    {{-- Pipa miring 60° (diputar -30° dari tegak). Sensor bergeser sepanjang pipa. --}}
    <g transform="translate(15.117 43.902) rotate(-30)">
        <g transform="translate(0 {{ $dmDelta }})">
            <svg x="32" y="208.853" width="6" height="26.291" viewBox="0 0 10 43.81800842285156" preserveAspectRatio="none" overflow="visible"><path d="M3 43.81801l-3-2.121 10 0-3 2.121-4 0z m-3-6.36501l0-37.453c0 0 3.033 0.63 5 0.63 1.967 0 5-0.63 5-0.63l0 37.453-10 0z" fill="#fff" /><path d="M3 43.81801l-3-2.121 10 0-3 2.121-4 0z m-3-6.36501l0-37.453c0 0 3.033 0.63 5 0.63 1.967 0 5-0.63 5-0.63l0 37.453-10 0z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="34.647" y="211.877" width="1" height="1" viewBox="5.960464477539063e-8 0 1.1769899725914001 1.2609999179840088" preserveAspectRatio="none" overflow="visible"><path d="M1.17699 0.63c0-0.34801-0.264-0.63-0.58899-0.63-0.325 0-0.588 0.282-0.588 0.63 0 0.34801 0.263 0.631 0.588 0.631 0.325 0 0.589-0.282 0.58899-0.631z" fill="#373737" /><path d="M1.17699 0.63c0-0.34801-0.264-0.63-0.58899-0.63-0.325 0-0.588 0.282-0.588 0.63 0 0.34801 0.263 0.631 0.588 0.631 0.325 0 0.589-0.282 0.58899-0.631z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="32" y="231.325" width="6" height="2.545" viewBox="0 0 10 4.242000102996826" preserveAspectRatio="none" overflow="visible"><path d="M0 4.242l0-4.242 10 0 0 4.242-10 0z" fill="#fff"/><path d="M0 4.242l0-4.242 10 0 0 4.242-10 0z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="33.06" y="196.744" width="3.882" height="3.785" viewBox="0 0 6.46999979019165 6.307999610900879" preserveAspectRatio="none" overflow="visible"><path d="M1.175 2.207l0 2.207 4.11801 0 0-2.207-4.11801 0z m0.294-2.207l0 2.207 3.53 0 0-2.207-3.53 0z m-0.293 4.416l-1.176 1.892 6.47 0-1.176-1.892-4.118 0z" fill="#4A4A4A" /><path d="M1.175 2.207l0 2.207 4.11801 0 0-2.207-4.11801 0z m0.294-2.207l0 2.207 3.53 0 0-2.207-3.53 0z m-0.293 4.416l-1.176 1.892 6.47 0-1.176-1.892-4.118 0z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="32" y="204.689" width="6" height="4.541" viewBox="-4.76837158203125e-7 0 10.000000476837158 7.567990303039551" preserveAspectRatio="none" overflow="visible"><path d="M0 2.707l1.791-2.707c0 0 1.95 0.31599 3.209 0.31599 1.258 0 3.209-0.31599 3.209-0.31599l1.791 2.707 0 4.23c0 0-3.044 0.631-5 0.63099-1.956 0-5-0.631-5-0.63099l0-4.23z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="32" y="206.297" width="6" height="2.932" viewBox="0 0 10 4.887010097503662" preserveAspectRatio="none" overflow="visible"><path d="M10 4.257l0-4.257c0 0-3.017 0.631-5 0.631-1.983 0-5-0.631-5-0.631l0 4.257c0 0 3.044 0.63 5 0.63001 1.956 0 5-0.63 5-0.63001z" fill="#4A4A4A" /><path d="M10 4.257l0-4.257c0 0-3.017 0.631-5 0.631-1.983 0-5-0.631-5-0.631l0 4.257c0 0 3.044 0.63 5 0.63001 1.956 0 5-0.63 5-0.63001z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="32" y="204.689" width="6" height="1.987" viewBox="0 0 10 3.31099009513855" preserveAspectRatio="none" overflow="visible"><path d="M1.765 0l-1.765 2.67999c0 0 3.017 0.631 5 0.631 1.983 0 5-0.631 5-0.631l-1.765-2.67999c0 0-1.966 0.31599-3.235 0.31599-1.269 0-3.235-0.31599-3.235-0.31599z" fill="#4A4A4A" /><path d="M1.765 0l-1.765 2.67999c0 0 3.017 0.631 5 0.631 1.983 0 5-0.631 5-0.631l-1.765-2.67999c0 0-1.966 0.31599-3.235 0.31599-1.269 0-3.235-0.31599-3.235-0.31599z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="33.06" y="200.528" width="3.882" height="4.351" viewBox="0 0 6.46999979019165 7.251999855041504" preserveAspectRatio="none" overflow="visible"><path d="M0 0l0 6.937c0 0 1.966 0.315 3.235 0.315 1.269 0 3.235-0.315 3.235-0.315l0-6.937-6.47 0z" fill="#4A4A4A" /><path d="M0 0l0 6.937c0 0 1.966 0.315 3.235 0.315 1.269 0 3.235-0.315 3.235-0.315l0-6.937-6.47 0z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
        </g>
        <svg x="29" y="12.5" width="12" height="239.584" viewBox="0 0 12 239.58370121529927" preserveAspectRatio="none" overflow="visible"><path d="M12 0l0 238.38370121529928c0 0-1.2 1.2-6 1.2-4.8 0-6-1.2-6-1.2l0-238.38370121529928c0 0 1.2 1.2 6 1.2 4.8 0 6-1.2 6-1.2" fill="#ececec4d" /></svg>
                <svg x="29" y="12.5" width="12" height="239.584" viewBox="0 0 12 239.58370121529927" preserveAspectRatio="none" overflow="visible"><path d="M12 0l0 238.38370121529928c0 0-1.2 1.2-6 1.2-4.8 0-6-1.2-6-1.2l0-238.38370121529928c0 0 1.2 1.2 6 1.2 4.8 0 6-1.2 6-1.2z" fill="none" stroke="#000" stroke-width="1" vector-effect="non-scaling-stroke" /></svg>
                <svg x="29" y="11.3" width="12" height="2.4" viewBox="0 0 20 4" preserveAspectRatio="none" overflow="visible"><path d="M10 4c8 0 10-2 10-2 0 0-2-2-10-2-8 0-10 2-10 2 0 0 2 2 10 2z" fill="#ececec4d" /><path d="M10 4c8 0 10-2 10-2 0 0-2-2-10-2-8 0-10 2-10 2 0 0 2 2 10 2z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="22" y="19.5" width="21.5" height="6" viewBox="0 0 21.5 6" preserveAspectRatio="none" overflow="visible"><path d="M5.5 0l-5.5 0 0 5 5.5 0 0-5z m0 0l0 5c0 0 4.712 1 7.75 1 3.037 0 7.75-1 7.75-1 0 0 0.5-0.5 0.5-2.5 0-2-0.5-2.5-0.5-2.5 0 0-4.712 1-7.75 1-3.037 0-7.75-1-7.75-1z" fill="#D9D9D9" /><path d="M5.5 0l-5.5 0 0 5 5.5 0 0-5z m0 0l0 5c0 0 4.712 1 7.75 1 3.037 0 7.75-1 7.75-1 0 0 0.5-0.5 0.5-2.5 0-2-0.5-2.5-0.5-2.5 0 0-4.712 1-7.75 1-3.037 0-7.75-1-7.75-1z" fill="none" stroke="#000" stroke-width="1" vector-effect="non-scaling-stroke" /></svg>
                <svg x="23.75" y="21" width="2" height="2" viewBox="0 0 2 2" preserveAspectRatio="none" overflow="visible"><path d="M2 1c0-0.55228-0.44772-1-1-1-0.55228 0-1 0.44772-1 1 0 0.55228 0.44772 1 1 1 0.55228 0 1-0.44772 1-1z" fill="#fff" /><path d="M2 1c0-0.55228-0.44772-1-1-1-0.55228 0-1 0.44772-1 1 0 0.55228 0.44772 1 1 1 0.55228 0 1-0.44772 1-1z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="22" y="88.5" width="21.5" height="6" viewBox="0 0 21.5 6" preserveAspectRatio="none" overflow="visible"><path d="M5.5 0l-5.5 0 0 5 5.5 0 0-5z m0 0l0 5c0 0 4.712 1 7.75 1 3.037 0 7.75-1 7.75-1 0 0 0.5-0.5 0.5-2.5 0-2-0.5-2.5-0.5-2.5 0 0-4.712 1-7.75 1-3.037 0-7.75-1-7.75-1z" fill="#D9D9D9" /><path d="M5.5 0l-5.5 0 0 5 5.5 0 0-5z m0 0l0 5c0 0 4.712 1 7.75 1 3.037 0 7.75-1 7.75-1 0 0 0.5-0.5 0.5-2.5 0-2-0.5-2.5-0.5-2.5 0 0-4.712 1-7.75 1-3.037 0-7.75-1-7.75-1z" fill="none" stroke="#000" stroke-width="1" vector-effect="non-scaling-stroke" /></svg>
                <svg x="23.75" y="90" width="2" height="2" viewBox="0 0 2 2" preserveAspectRatio="none" overflow="visible"><path d="M2 1c0-0.55228-0.44772-1-1-1-0.55228 0-1 0.44772-1 1 0 0.55228 0.44772 1 1 1 0.55228 0 1-0.44772 1-1z" fill="#fff" /><path d="M2 1c0-0.55228-0.44772-1-1-1-0.55228 0-1 0.44772-1 1 0 0.55228 0.44772 1 1 1 0.55228 0 1-0.44772 1-1z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
                <svg x="22" y="157.5" width="21.5" height="6" viewBox="0 0 21.5 6" preserveAspectRatio="none" overflow="visible"><path d="M5.5 0l-5.5 0 0 5 5.5 0 0-5z m0 0l0 5c0 0 4.712 1 7.75 1 3.037 0 7.75-1 7.75-1 0 0 0.5-0.5 0.5-2.5 0-2-0.5-2.5-0.5-2.5 0 0-4.712 1-7.75 1-3.037 0-7.75-1-7.75-1z" fill="#D9D9D9" /><path d="M5.5 0l-5.5 0 0 5 5.5 0 0-5z m0 0l0 5c0 0 4.712 1 7.75 1 3.037 0 7.75-1 7.75-1 0 0 0.5-0.5 0.5-2.5 0-2-0.5-2.5-0.5-2.5 0 0-4.712 1-7.75 1-3.037 0-7.75-1-7.75-1z" fill="none" stroke="#000" stroke-width="1" vector-effect="non-scaling-stroke" /></svg>
                <svg x="23.75" y="159" width="2" height="2" viewBox="0 0 2 2" preserveAspectRatio="none" overflow="visible"><path d="M2 1c0-0.55228-0.44772-1-1-1-0.55228 0-1 0.44772-1 1 0 0.55228 0.44772 1 1 1 0.55228 0 1-0.44772 1-1z" fill="#fff" /><path d="M2 1c0-0.55228-0.44772-1-1-1-0.55228 0-1 0.44772-1 1 0 0.55228 0.44772 1 1 1 0.55228 0 1-0.44772 1-1z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
        <circle cx="30.8" cy="244.284" r="0.8" fill="#3590D2" stroke="#000" stroke-width="0.3"/>
                <circle cx="33.6" cy="244.684" r="0.8" fill="#3590D2" stroke="#000" stroke-width="0.3"/>
                <circle cx="36.4" cy="244.684" r="0.8" fill="#3590D2" stroke="#000" stroke-width="0.3"/>
                <circle cx="39.2" cy="244.284" r="0.8" fill="#3590D2" stroke="#000" stroke-width="0.3"/>
    </g>

    {{-- Papan duga --}}
    <svg x="18" y="33.5" width="18" height="207.499" viewBox="0 0 18 207.49899291992188" preserveAspectRatio="none" overflow="visible"><path d="M16 0l-14 0c-1.10457 0-2 0.89543-2 2l0 203.49899c0 1.10459 0.89543 2 2 2l14 0c1.10457 0 2-0.89541 2-2l0-203.49899c0-1.10457-0.89543-2-2-2z" fill="#FFE3AC" /><path d="M16 0l-14 0c-1.10457 0-2 0.89543-2 2l0 203.49899c0 1.10459 0.89543 2 2 2l14 0c1.10457 0 2-0.89541 2-2l0-203.49899c0-1.10457-0.89543-2-2-2z" fill="none" stroke="#000" stroke-width="1" stroke-linecap="butt" stroke-linejoin="round" vector-effect="non-scaling-stroke" /></svg>
    @foreach ($dmTicks as $dmTick)
        @if ($dmTick['major'])
            <path d="M18 {{ $dmTick['y'] }}H24" stroke="#F33" stroke-width="1.2"
                vector-effect="non-scaling-stroke" />
            <text x="30.2" y="{{ round($dmTick['y'] + $dmLabelFont * 0.35, 3) }}"
                font-size="{{ round($dmLabelFont, 2) }}" text-anchor="middle"
                font-family="ui-sans-serif, system-ui, sans-serif" font-weight="600"
                fill="#000">{{ $dmTick['label'] }}</text>
        @else
            <path d="M18 {{ $dmTick['y'] }}H22" stroke="#000" stroke-width="0.8"
                vector-effect="non-scaling-stroke" />
        @endif
    @endforeach

    {{-- Kotak TMA --}}
    <rect x="215.25" y="46.199" width="111.5" height="39.5" rx="3.75" fill="#E6F9FD"
        stroke="{{ $dmLineClr }}" stroke-width="0.5" />
    <text x="227.344" y="60" font-size="7.2" font-weight="700" letter-spacing="0.3"
        font-family="ui-sans-serif, system-ui, sans-serif" fill="#000">TINGGI MUKA AIR</text>
    <text x="227.344" y="78" font-size="{{ $dmValueFont($dmTmaText) }}" font-weight="800"
        font-family="ui-sans-serif, system-ui, sans-serif" fill="{{ $dmTmaRaw !== null ? '#000' : '#94a3b8' }}">{{ $dmTmaText }}<tspan
            font-size="8" font-weight="600" dx="2">{{ $dmTmaRaw !== null ? $dmUnit : '' }}</tspan></text>

    {{-- Kotak sensor --}}
    <rect x="215.25" y="197.25" width="111.5" height="39.5" rx="3.75" fill="#FFE0E0"
        stroke="{{ $dmSensorClr }}" stroke-width="0.5" />
    <text x="227.68" y="211.4" font-size="7.2" font-weight="700" letter-spacing="0.3"
        font-family="ui-sans-serif, system-ui, sans-serif" fill="#000">KEDALAMAN SENSOR</text>
    <text x="227.68" y="229" font-size="{{ $dmValueFont($dmDepthText) }}" font-weight="800"
        font-family="ui-sans-serif, system-ui, sans-serif" fill="{{ $dmKed !== null ? '#000' : '#94a3b8' }}">{{ $dmDepthText }}<tspan
            font-size="8" font-weight="600" dx="2">{{ $dmKed !== null ? $dmUnit : '' }}</tspan></text>

    {{-- Garis TMA: dari papan duga menembus bendungan, lalu ke kotak TMA --}}
    <polyline points="18,{{ round($dmLevelY, 3) }} 197,{{ round($dmLevelY, 3) }} 213,{{ $dmTmaBoxCy }}"
        stroke="{{ $dmLineClr }}" stroke-width="1.2" stroke-dasharray="{{ $dmLineDash }}"
        stroke-linejoin="round" vector-effect="non-scaling-stroke" opacity="{{ $dmLineOpac }}" />
    <circle cx="213" cy="{{ $dmTmaBoxCy }}" r="2" fill="{{ $dmLineClr }}" opacity="{{ $dmLineOpac }}" />

    {{-- Garis sensor: dari sensor ke kotak kedalaman sensor --}}
    <polyline points="{{ $dmSensorX }},{{ round($dmSensorY, 3) }} 197,{{ round($dmSensorY, 3) }} 213,{{ $dmSensBoxCy }}"
        stroke="{{ $dmSensorClr }}" stroke-width="1.2" stroke-dasharray="{{ $dmLineDash }}"
        stroke-linejoin="round" vector-effect="non-scaling-stroke" opacity="{{ $dmLineOpac }}" />
    <circle cx="213" cy="{{ $dmSensBoxCy }}" r="1.6" fill="{{ $dmSensorClr }}" opacity="{{ $dmLineOpac }}" />
</svg>

@if ($dmTmaOut)
    <p class="text-[12px] leading-snug text-amber-600">
        Bacaan {{ $dmFmt($dmTmaRaw) }} di luar rentang elevasi {{ $dmFmt($dmMin) }}–{{ $dmFmt($dmMax) }};
        gambar air berhenti di ujung papan duga, nilai di kotak tetap mengikuti bacaan.
    </p>
@endif
@if ($dmKed === null)
    <p class="text-[12px] leading-snug text-slate-500">
        Isi &ldquo;Kedalaman Sensor&rdquo; di Edit Device supaya posisi dan kedalaman sensor tampil.
    </p>
@endif
