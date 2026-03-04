<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    @php
        // ── Resolve AWR sensor parameters ──────────────────────────────
        $awrSensorMap = [
            'kecepatan_angin' => [
                'label' => 'KECEPATAN ANGIN',
                'satuan' => 'Km/h',
                'icon' => 'icons/awr/kecepatan_angin.svg',
                'terms' => ['wind_speed', 'kecepatan_angin', 'kec_angin', 'wind speed', 'windspeed', 'ws'],
            ],
            'arah_angin' => [
                'label' => 'ARAH ANGIN',
                'satuan' => '°',
                'icon' => 'icons/awr/arah_angin.svg',
                'terms' => ['wind_direction', 'arah_angin', 'wind direction', 'wind_dir', 'wd'],
            ],
            'kecerahan' => [
                'label' => 'KECERAHAN',
                'satuan' => 'K Lux',
                'icon' => 'icons/awr/kecerahan.svg',
                'terms' => [
                    'solar',
                    'kecerahan',
                    'lux',
                    'solar_radiation',
                    'light',
                    'cahaya',
                    'illuminance',
                    'sr',
                    'brightness',
                    'intensity',
                ],
            ],
            'arah_cahaya' => [
                'label' => 'ARAH',
                'satuan' => '°',
                'icon' => 'icons/awr/arah.svg',
                'terms' => [
                    'arah_cahaya',
                    'light_dir',
                    'solar_dir',
                    'light_direction',
                    'solar_direction',
                    'arah cahaya',
                    'direction_light',
                    'sun_dir',
                    'sd',
                    'arah',
                ],
            ],
            'temperatur' => [
                'label' => 'TEMPERATUR',
                'satuan' => '°C',
                'icon' => 'icons/awr/temper.svg',
                'terms' => [
                    'temperatur',
                    'temperature',
                    'suhu_udara',
                    'air_temp',
                    'air_temperature',
                    'suhu_luar',
                    'temp_udara',
                ],
            ],
            'tekanan_udara' => [
                'label' => 'TEKANAN UDARA',
                'satuan' => 'hPa',
                'icon' => 'icons/awr/pressure.svg',
                'terms' => ['pressure', 'tekanan', 'barometer', 'tekanan_udara', 'baro', 'air_pressure', 'pa', 'bp'],
            ],
            'kelembaban' => [
                'label' => 'KELEMBABAN',
                'satuan' => '%',
                'icon' => 'icons/awr/humidity.svg',
                'terms' => ['humidity', 'kelembaban', 'rh', 'relative_humidity', 'rha', 'hum'],
            ],
        ];

        // Kata kunci parameter internal logger yang harus diabaikan (bukan sensor cuaca AWR)
        $loggerInternalKeys = ['_logger', 'battery', 'battery_logger', 'humidity_logger', 'temperature_logger'];

        $awrValues = [];
        foreach ($awrSensorMap as $key => $meta) {
            $terms = $meta['terms'];
            $param = $lg->params->first(function ($p) use ($terms, $loggerInternalKeys) {
                $namePar = strtolower(trim($p->nama_parameter));
                $kolom = strtolower(trim($p->kolom_sensor ?? ''));
                $utama = strtolower(trim($p->parameter_utama ?? ''));

                // Abaikan parameter internal logger
                foreach ($loggerInternalKeys as $internalKey) {
                    if (str_contains($namePar, $internalKey) || str_contains($utama, $internalKey)) {
                        return false;
                    }
                }

                foreach ($terms as $term) {
                    if (
                        $namePar === $term ||
                        $kolom === $term ||
                        $utama === $term ||
                        str_contains($namePar, $term) ||
                        str_contains($kolom, $term) ||
                        str_contains($utama, $term)
                    ) {
                        return true;
                    }
                }
                return false;
            });
            $kolom = $param?->kolom_sensor;
            $rawVal = $latest && $kolom ? $latest->{$kolom} ?? null : null;
            $awrValues[$key] = [
                'label' => $meta['label'],
                'satuan' => $meta['satuan'],
                'icon' => $meta['icon'],
                'param' => $param,
                'value' => is_numeric($rawVal) ? (float) $rawVal : null,
            ];
        }

        // ── Shorthand values ───────────────────────────────────────────
        $kecepatanAngin = $awrValues['kecepatan_angin']['value'];
        $arahAngin = $awrValues['arah_angin']['value'];
        $kecerahan = $awrValues['kecerahan']['value'];
        $arahCahaya = $awrValues['arah_cahaya']['value'];
        $temperatur = $awrValues['temperatur']['value'];
        $tekananUdara = $awrValues['tekanan_udara']['value'];
        $kelembaban = $awrValues['kelembaban']['value'];

        // ── Hujan: identik dengan ARR (SUM dari controller) ───────────
        $akuHarian = is_numeric($curahHujanHarian ?? null) ? (float) $curahHujanHarian : null;
        $akuPerJam = is_numeric($curahHujanPerJam ?? null) ? (float) $curahHujanPerJam : null;

        $loggerWaktu = $lg->latest_waktu ? \Carbon\Carbon::parse($lg->latest_waktu) : now();
        $loggerHour = (int) $loggerWaktu->format('H');
        $isPagi = $loggerHour >= 6 && $loggerHour < 18;
        $waktuSuffix = $isPagi ? '_pagi' : '_malam';
        $timeAwareStates = ['tidak_hujan', 'hujan_sangat_ringan', 'hujan_ringan'];

        $stateHarian =
            isset($stateHujanHarian) && preg_match('/^[a-z0-9_]+$/', (string) $stateHujanHarian)
                ? $stateHujanHarian
                : 'tidak_hujan';
        $statePerJam =
            isset($stateHujanPerJam) && preg_match('/^[a-z0-9_]+$/', (string) $stateHujanPerJam)
                ? $stateHujanPerJam
                : 'tidak_hujan';
        if (in_array($stateHarian, $timeAwareStates)) {
            $stateHarian .= $waktuSuffix;
        }
        if (in_array($statePerJam, $timeAwareStates)) {
            $statePerJam .= $waktuSuffix;
        }
        $defaultRainIcon = asset('klasifikasi_hujan/tidak_hujan_pagi.png');

        // ── Display formatting ─────────────────────────────────────────
        $dispKecepatan = is_numeric($kecepatanAngin) ? number_format($kecepatanAngin, 3) : '-';
        $dispArahAngin = is_numeric($arahAngin) ? number_format($arahAngin, 2) : '-';
        $dispAkuHarian = is_numeric($akuHarian) ? number_format($akuHarian, 3) : '-';
        $dispAkuPerJam = is_numeric($akuPerJam) ? number_format($akuPerJam, 3) : '-';
        $dispKecerahan = is_numeric($kecerahan) ? number_format($kecerahan, 3) : '-';
        $dispArahCahaya = is_numeric($arahCahaya) ? number_format($arahCahaya, 1) : '-';
        $dispTemperatur = is_numeric($temperatur) ? number_format($temperatur, 2) : '-';
        $dispTekanan = is_numeric($tekananUdara) ? number_format($tekananUdara, 1) : '-';
        $dispKelembaban = is_numeric($kelembaban) ? number_format($kelembaban, 2) : '-';

        $statusHarian = $statusHujanHarian ?? ($stateHujanHarian ?? '-');
        $statusPerJam = $statusHujanPerJam ?? ($stateHujanPerJam ?? '-');

        // Helper: route for a sensor
        $awrRoute = function ($key) use ($awrValues, $lg) {
            $param = $awrValues[$key]['param'] ?? null;
            return $param
                ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($param->nama_parameter)
                : route('analisa.index', $lg->id_logger);
        };
    @endphp

    <div class="p-4 space-y-4">

        {{-- ── ROW 1: Angin (kiri) | Hujan + Cahaya stacked (kanan) ─── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- ANGIN --}}
            <div class="rounded-xl border border-slate-200 p-4 bg-white">
                <div class="text-sm font-semibold text-slate-700 mb-3">Angin</div>
                <div class="flex flex-col items-center gap-4">
                    {{-- Wind Compass: tengah, max-width adaptif --}}
                    <div id="compassWrap_{{ $lg->id_logger }}" class="relative select-none w-full mx-auto"
                        style="max-width:min(260px, 100%)">
                        <canvas id="windCompass_{{ $lg->id_logger }}"
                            data-direction="{{ is_numeric($arahAngin) ? $arahAngin : 0 }}"
                            class="block w-full {{ $muted ? 'opacity-60' : '' }}">
                        </canvas>
                    </div>
                    {{-- Wind stats: selalu 2 kolom --}}
                    <div class="grid grid-cols-2 gap-3 w-full">
                        <a href="{{ $awrRoute('kecepatan_angin') }}"
                            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:shadow-md hover:border-blue-300 transition-all {{ $muted ? 'grayscale opacity-70' : '' }}">
                            <img src="{{ asset('icons/awr/kecepatan_angin.svg') }}" onerror="this.style.display='none'"
                                class="h-8 w-8 flex-shrink-0 object-contain" alt="Kecepatan Angin">
                            <div class="leading-tight min-w-0">
                                <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase">KECEPATAN
                                    ANGIN</div>
                                <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                    {{ $dispKecepatan }}<span
                                        class="text-[10px] font-bold text-slate-400 ml-0.5">Km/h</span>
                                </div>
                            </div>
                        </a>
                        <a href="{{ $awrRoute('arah_angin') }}"
                            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:shadow-md hover:border-blue-300 transition-all {{ $muted ? 'grayscale opacity-70' : '' }}">
                            <img src="{{ asset('icons/awr/arah_angin.svg') }}" onerror="this.style.display='none'"
                                class="h-8 w-8 flex-shrink-0 object-contain" alt="Arah Angin">
                            <div class="leading-tight min-w-0">
                                <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase">ARAH ANGIN
                                </div>
                                <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                    {{ $dispArahAngin }}<span
                                        class="text-[10px] font-bold text-slate-400 ml-0.5">°</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- KANAN: Hujan (atas) + Cahaya (bawah) --}}
            <div class="flex flex-col gap-4">

                {{-- HUJAN --}}
                <div class="rounded-xl border border-slate-200 p-4 bg-white flex-1">
                    <div class="text-sm font-semibold text-slate-700 mb-3">Hujan</div>
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Akumulasi Harian --}}
                        <div class="relative overflow-hidden rounded-xl border px-3 py-3 text-center">
                            <div class="text-[10px] font-semibold tracking-wide text-slate-700 uppercase">AKUMULASI
                                HARIAN</div>
                            <img src="{{ asset('klasifikasi_hujan/' . $stateHarian . '.png') }}"
                                onerror="this.onerror=null;this.src='{{ $defaultRainIcon }}';" alt="Hujan Harian"
                                class="pointer-events-none absolute right-[-0.5rem] top-8 h-20 w-20 sm:h-24 sm:w-24 object-contain {{ $muted ? 'opacity-60' : '' }}">
                            <div class="mt-20 sm:mt-24"></div>
                            <div
                                class="text-2xl font-extrabold text-slate-900 whitespace-nowrap {{ $muted ? 'opacity-60' : '' }}">
                                {{ $dispAkuHarian }}<span class="text-xs font-semibold ml-1">mm</span>
                            </div>
                            <div class="text-[10px] font-semibold uppercase {{ $muted ? 'opacity-60' : '' }}">
                                {{ $statusHarian !== '-' ? strtoupper($statusHarian) : 'TIDAK ADA DATA' }}
                            </div>
                        </div>
                        {{-- Akumulasi 1 Jam --}}
                        <div class="relative overflow-hidden rounded-xl border px-3 py-3 text-center">
                            <div class="text-[10px] font-semibold tracking-wide text-slate-700 uppercase">AKUMULASI 1
                                JAM</div>
                            <img src="{{ asset('klasifikasi_hujan/' . $statePerJam . '.png') }}"
                                onerror="this.onerror=null;this.src='{{ $defaultRainIcon }}';" alt="Hujan Per Jam"
                                class="pointer-events-none absolute right-[-0.5rem] top-8 h-20 w-20 sm:h-24 sm:w-24 object-contain {{ $muted ? 'opacity-60' : '' }}">
                            <div class="mt-20 sm:mt-24"></div>
                            <div
                                class="text-2xl font-extrabold text-slate-900 whitespace-nowrap {{ $muted ? 'opacity-60' : '' }}">
                                {{ $dispAkuPerJam }}<span class="text-xs font-semibold ml-1">mm</span>
                            </div>
                            <div class="text-[10px] font-semibold uppercase {{ $muted ? 'opacity-60' : '' }}">
                                {{ $statusPerJam !== '-' ? strtoupper($statusPerJam) : 'TIDAK ADA DATA' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CAHAYA --}}
                <div class="rounded-xl border border-slate-200 p-4 bg-white">
                    <div class="text-sm font-semibold text-slate-700 mb-3">Cahaya</div>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ $awrRoute('kecerahan') }}"
                            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:shadow-md hover:border-yellow-300 transition-all {{ $muted ? 'grayscale opacity-70' : '' }}">
                            <img src="{{ asset('icons/awr/kecerahan.svg') }}" onerror="this.style.display='none'"
                                class="h-9 w-9 flex-shrink-0 object-contain" alt="Kecerahan">
                            <div class="leading-tight min-w-0">
                                <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase">KECERAHAN
                                </div>
                                <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                    {{ $dispKecerahan }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">K
                                        Lux</span>
                                </div>
                            </div>
                        </a>
                        <a href="{{ $awrRoute('arah_cahaya') }}"
                            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:shadow-md hover:border-yellow-300 transition-all {{ $muted ? 'grayscale opacity-70' : '' }}">
                            <img src="{{ asset('icons/awr/arah.svg') }}" onerror="this.style.display='none'"
                                class="h-9 w-9 flex-shrink-0 object-contain" alt="Arah Cahaya">
                            <div class="leading-tight min-w-0">
                                <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase">ARAH</div>
                                <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                    {{ $dispArahCahaya }}<span
                                        class="text-[10px] font-bold text-slate-400 ml-0.5">°</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>{{-- akhir kolom kanan --}}
        </div>

        {{-- ── ROW 2: Udara — full width ──────────────────────────────── --}}
        <div class="rounded-xl border border-slate-200 p-4 bg-white">
            <div class="text-sm font-semibold text-slate-700 mb-3">Udara</div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ $awrRoute('temperatur') }}"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:shadow-md hover:border-orange-300 transition-all {{ $muted ? 'grayscale opacity-70' : '' }}">
                    <img src="{{ asset('icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg')) }}"
                        onerror="this.style.display='none'" class="h-9 w-9 flex-shrink-0 object-contain"
                        alt="Temperatur">
                    <div class="leading-tight min-w-0">
                        <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                            TEMPERATUR</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ $dispTemperatur }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">°C</span>
                        </div>
                    </div>
                </a>
                <a href="{{ $awrRoute('tekanan_udara') }}"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:shadow-md hover:border-blue-300 transition-all {{ $muted ? 'grayscale opacity-70' : '' }}">
                    <img src="{{ asset('icons/awr/tekanan_udara.svg') }}" onerror="this.style.display='none'"
                        class="h-9 w-9 flex-shrink-0 object-contain" alt="Tekanan Udara">
                    <div class="leading-tight min-w-0">
                        <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">TEKANAN
                            UDARA</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ $dispTekanan }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">hPa</span>
                        </div>
                    </div>
                </a>
                <a href="{{ $awrRoute('kelembaban') }}"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:shadow-md hover:border-cyan-300 transition-all {{ $muted ? 'grayscale opacity-70' : '' }}">
                    <img src="{{ asset('icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg')) }}"
                        onerror="this.style.display='none'" class="h-9 w-9 flex-shrink-0 object-contain"
                        alt="Kelembaban">
                    <div class="leading-tight min-w-0">
                        <div class="text-[9px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                            KELEMBABAN</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ $dispKelembaban }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">%</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 p-4 bg-white">
            <div class="text-md font-semibold text-slate-700 mb-3">Logger</div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                {{-- HUMIDITY --}}
                <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-2 shadow-sm transition-all hover:shadow-md hover:border-blue-300">
                    <div
                        class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50">
                        <img src="{{ asset('icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg')) }}"
                            alt="Humidity" class="h-full w-full object-cover {{ $iconClass }}">
                    </div>
                    <div class="leading-tight min-w-0">
                        <div class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                            HUMIDITY</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ $humidity ?? '-' }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">%</span>
                        </div>
                    </div>
                </a>

                {{-- BATTERY --}}
                <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-2 shadow-sm transition-all hover:shadow-md hover:border-green-300">
                    <div
                        class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50">
                        <img src="{{ asset('icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg')) }}"
                            alt="Battery" class="h-full w-full object-cover {{ $iconClass }}">
                    </div>
                    <div class="leading-tight min-w-0">
                        <div class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                            BATTERY</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ $battery ?? '-' }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">V</span>
                        </div>
                    </div>
                </a>

                {{-- TEMPERATURE --}}
                <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-2 shadow-sm transition-all hover:shadow-md hover:border-orange-300">
                    <div
                        class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50">
                        <img src="{{ asset('icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg')) }}"
                            alt="Temperature" class="h-full w-full object-cover {{ $iconClass }}">
                    </div>
                    <div class="leading-tight min-w-0">
                        <div class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                            TEMPERATURE</div>
                        <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                            {{ $temp ?? '-' }}<span class="text-[10px] font-bold text-slate-400 ml-0.5">°C</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>

{{-- ── Wind Compass Script ───────────────────────────────────────────── --}}
<script>
    (function() {
        var COMPASS_IMG_SRC = '{{ asset('icons/awr/kompas.svg') }}';
        var DPR = window.devicePixelRatio || 1;

        // Pre-load gambar sekali, dipakai semua kompas di halaman
        var _compassImg = null;
        var _compassImgReady = false;
        var _compassImgFailed = false;
        var _pendingDraws = [];

        function getCompassImg(callback) {
            if (_compassImgReady) {
                callback(_compassImg);
                return;
            }
            if (_compassImgFailed) {
                callback(null);
                return;
            }
            _pendingDraws.push(callback);
            if (_pendingDraws.length > 1) return;
            var img = new Image();
            img.onload = function() {
                _compassImg = img;
                _compassImgReady = true;
                _pendingDraws.forEach(function(cb) {
                    cb(img);
                });
                _pendingDraws = [];
            };
            img.onerror = function() {
                _compassImgFailed = true;
                _pendingDraws.forEach(function(cb) {
                    cb(null);
                });
                _pendingDraws = [];
            };
            img.src = COMPASS_IMG_SRC;
        }

        // Setup canvas agar tajam di layar HiDPI/Retina
        function setupHiDPI(canvas, cssSize) {
            canvas.width = cssSize * DPR;
            canvas.height = cssSize * DPR;
            canvas.style.width = cssSize + 'px';
            canvas.style.height = cssSize + 'px';
            var ctx = canvas.getContext('2d');
            ctx.scale(DPR, DPR);
            return ctx;
        }

        function drawWindCompass(canvasId, directionDeg, muted) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) return;

            // CSS_SIZE mengikuti lebar container agar responsif
            var wrap = canvas.parentElement;
            var CSS_SIZE = wrap ? wrap.offsetWidth : 220;
            CSS_SIZE = Math.max(160, Math.min(CSS_SIZE, 320)); // min 160, max 320

            var ctx = setupHiDPI(canvas, CSS_SIZE);
            var w = CSS_SIZE,
                h = CSS_SIZE;
            var cx = w / 2,
                cy = h / 2;
            var R = Math.min(w, h) / 2 - 4;

            getCompassImg(function(img) {
                ctx.clearRect(0, 0, w, h);

                // ── Layer 1: Arc arah angin (di bawah SVG agar masuk alur ring) ──
                var arcR = R * 0.84;
                var startRad = -Math.PI / 2;
                var endRad = (directionDeg - 90) * Math.PI / 180;
                ctx.beginPath();
                ctx.arc(cx, cy, arcR, startRad, endRad);
                ctx.strokeStyle = muted ? '#94a3b8' : '#0000FF';
                ctx.lineWidth = 6;
                ctx.lineCap = 'butt';
                ctx.stroke();

                // ── Layer 2: Gambar SVG kompas di atas arc ────────────────────────
                if (img) {
                    ctx.drawImage(img, 0, 0, w, h);
                } else {
                    // ── Fallback: gambar manual ───────────────────────────────────
                    ctx.beginPath();
                    ctx.arc(cx, cy, R, 0, Math.PI * 2);
                    ctx.strokeStyle = '#cbd5e1';
                    ctx.lineWidth = 2;
                    ctx.stroke();

                    ctx.beginPath();
                    ctx.arc(cx, cy, R * 0.82, 0, Math.PI * 2);
                    ctx.strokeStyle = '#e2e8f0';
                    ctx.lineWidth = 1;
                    ctx.stroke();

                    [0, 45, 90, 135].forEach(function(deg) {
                        var rad = (deg - 90) * Math.PI / 180;
                        ctx.beginPath();
                        ctx.moveTo(cx + R * 0.88 * Math.cos(rad), cy + R * 0.88 * Math.sin(rad));
                        ctx.lineTo(cx - R * 0.88 * Math.cos(rad), cy - R * 0.88 * Math.sin(rad));
                        ctx.strokeStyle = '#e2e8f0';
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    });

                    ctx.font = '9px ui-sans-serif, system-ui, sans-serif';
                    ctx.fillStyle = '#94a3b8';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    [0, 45, 90, 135, 180, 225, 270, 315].forEach(function(deg) {
                        var rad = (deg - 90) * Math.PI / 180;
                        ctx.fillText(deg + '°', cx + R * 0.70 * Math.cos(rad), cy + R * 0.70 * Math
                            .sin(rad));
                    });
                }

                // ── Layer 3: Panah segitiga (di atas SVG) ────────────────────────
                var arrowRad = (directionDeg - 90) * Math.PI / 180;
                var arrowLen = R * 0.38;
                ctx.save();
                ctx.translate(cx, cy);
                ctx.rotate(arrowRad + Math.PI / 2);
                ctx.translate(0, -(R * 0.35));
                ctx.beginPath();
                ctx.moveTo(0, -arrowLen * 0.95);
                ctx.lineTo(arrowLen * 0.22, arrowLen * 0.1);
                ctx.lineTo(-arrowLen * 0.22, arrowLen * 0.1);
                ctx.closePath();
                ctx.fillStyle = muted ? '#94a3b8' : '#0000FF';
                ctx.fill();
                ctx.restore();

                // ── Layer 4: Titik tengah ─────────────────────────────────────────
                // ctx.beginPath();
                // ctx.arc(cx, cy, 4, 0, Math.PI * 2);
                // ctx.fillStyle = '#475569';
                // ctx.fill();
            });
        }

        window.drawWindCompass = drawWindCompass; // expose untuk re-draw

        document.addEventListener('DOMContentLoaded', function() {
            var canvasId = 'windCompass_{{ $lg->id_logger }}';
            var canvas = document.getElementById(canvasId);
            if (!canvas) return;

            var dir = parseFloat(canvas.dataset.direction) || 0;
            var muted = {{ $muted ? 'true' : 'false' }};

            // Gambar pertama kali
            drawWindCompass(canvasId, dir, muted);

            // Re-draw otomatis saat container berubah ukuran (rotasi HP, resize browser)
            if (typeof ResizeObserver !== 'undefined') {
                var wrap = canvas.parentElement;
                if (wrap) {
                    var _resizeTimer = null;
                    new ResizeObserver(function() {
                        clearTimeout(_resizeTimer);
                        _resizeTimer = setTimeout(function() {
                            drawWindCompass(canvasId, dir, muted);
                        }, 60); // debounce 60ms agar tidak terlalu sering
                    }).observe(wrap);
                }
            }
        });
    })();
</script>
