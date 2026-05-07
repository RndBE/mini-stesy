<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    @php
        $sensorMap = [
            'tma' => ['label' => 'TINGGI MUKA AIR', 'satuan' => 'mdpl', 'icon' => 'icons/awgr/elevasi_muka_air.svg'],
            'ph_air' => ['label' => 'pH AIR', 'satuan' => '', 'icon' => 'icons/awgr/ph_air.svg'],
            'suhu_air' => ['label' => 'SUHU AIR', 'satuan' => '°C', 'icon' => 'icons/awgr/suhu_air.svg'],
            'orp' => ['label' => 'ORP', 'satuan' => 'mV', 'icon' => 'icons/awgr/orp.svg'],
            'conductivity' => ['label' => 'CONDUCTIVITY', 'satuan' => 'µS/cm', 'icon' => 'icons/awgr/conductivity.svg'],
            'salinity' => ['label' => 'SALINITY', 'satuan' => 'PSU', 'icon' => 'icons/awgr/salinity.svg'],
            'tds' => [
                'label' => 'TOTAL DISSOLVED SOLIDS',
                'satuan' => 'mg/L',
                'icon' => 'icons/awgr/total_dissolved_solids.svg',
            ],
            'turbidity' => ['label' => 'TURBIDITY', 'satuan' => 'NTU', 'icon' => 'icons/awgr/turbidity.svg'],
            'tinggi_sensor' => ['label' => 'TINGGI SENSOR', 'satuan' => 'm', 'icon' => 'icons/awgr/tinggi_sensor.svg'],
        ];

        $sensorAliases = [
            'tma' => ['tma', 'tinggi_muka_air', 'elevasi_muka_air', 'water_level'],
            'tds' => ['tds', 'total_dissolved_solids', 'dissolved_solids'],
        ];

        $sensorValues = [];
        foreach ($sensorMap as $key => $meta) {
            $terms = $sensorAliases[$key] ?? [$key];
            $param = $lg->params->first(function ($p) use ($terms) {
                $utama = strtolower(trim($p->parameter_utama ?? ''));
                foreach ($terms as $term) {
                    $term = strtolower(str_replace(' ', '_', trim($term)));
                    if ($utama === $term) {
                        return true;
                    }
                }
                return false;
            });
            $kolom  = $param?->kolom_sensor;
            $rawVal = $latest && $kolom ? ($latest->{$kolom} ?? null) : null;
            $sensorValues[$key] = [
                'label'  => $meta['label'],
                'satuan' => $meta['satuan'],
                'icon'   => $paramIconPath($param, $meta['icon']),
                'param'  => $param,
                'value'  => is_numeric($rawVal) ? $rawVal : null,
            ];
        }
        $phVal = $sensorValues['ph_air']['value'];
        $suhuVal = $sensorValues['suhu_air']['value'];
        $phDisplay = is_numeric($phVal) ? number_format((float) $phVal, 2) : '-';
        $suhuDisplay = is_numeric($suhuVal) ? number_format((float) $suhuVal, 1) : '-';
    @endphp

    <div class="flex flex-col lg:flex-row gap-4 p-5">
<div
            class="flex flex-col items-center flex-shrink-0 lg:w-32 pb-2 lg:pb-0 lg:border-r lg:border-slate-200 lg:pr-4">
            <div class="text-md font-semibold text-slate-700 mb-3 self-start">pH Meter</div>

<div class="flex flex-col items-center select-none" style="width:73px">
<div class="relative w-full">
<img src="{{ asset('alat-ukur-air/atas-alat.svg') }}" class="w-full block" alt="">
<div class="absolute" style="top:10%;left:50%;transform:translateX(-50%);width:60px">
                        <div class="relative">
                            <img src="{{ asset('alat-ukur-air/bagian-tengah-alat.svg') }}" class="w-full block" alt="">
<div class="absolute" style="top:6%;left:8%;width:84%;height:55%;background:#E6E6E6;border-radius:3px;padding:4px 5px;box-sizing:border-box;font-family:ui-monospace,monospace;{{ $muted ? 'opacity:0.6' : '' }}">
                                <div style="font-size:10px;font-weight:700;text-align:right;color:#333;line-height:1;margin-bottom:3px">pH</div>
                                <div style="font-size:15px;font-weight:900;color:#111;line-height:1.1">{{ $phDisplay }}</div>
                                <div style="font-size:10px;font-weight:700;color:#333;line-height:1.3">{{ $suhuDisplay }}<span style="font-size:10px">°C</span></div>
                            </div>
<div class="absolute flex flex-col items-center" style="bottom:10%;left:50%;transform:translateX(-50%);gap:5px">
                                <img src="{{ asset('alat-ukur-air/tombol-alat.svg') }}" style="width:13px;height:13px;display:block" alt="">
                                <img src="{{ asset('alat-ukur-air/tombol-alat.svg') }}" style="width:13px;height:13px;display:block" alt="">
                            </div>
                        </div>
                    </div>
                </div>
<img src="{{ asset('alat-ukur-air/bawah-alat.svg') }}" style="width:60px;display:block" alt="">
            </div>

        </div>
<div class="flex-1 min-w-0 space-y-4">
@if (collect($sensorValues)->contains(fn($sensor) => $sensor['param']))
<div>
                <div class="text-md font-semibold text-slate-700 mb-3">Data Pengukuran</div>
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach (['tma', 'ph_air', 'suhu_air', 'orp', 'conductivity', 'salinity', 'tds', 'turbidity', 'tinggi_sensor'] as $sKey)
                        @php
                            $s = $sensorValues[$sKey];
                            if (!$s['param']) continue;
                            $dispV = is_numeric($s['value']) ? $s['value'] : '-';
                            $href = $s['param']
                                ? route('analisa.index', $lg->id_logger) .
                                    '?parameter=' .
                                    urlencode($s['param']->nama_parameter)
                                : route('analisa.index', $lg->id_logger);
                        @endphp
                        <a href="{{ $href }}"
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 py-2 shadow-sm transition-all hover:shadow-md hover:border-blue-300 overflow-hidden {{ $muted ? 'grayscale opacity-70' : '' }}">
<div
                                class="flex-shrink-0 flex h-10 w-10 items-center justify-center rounded-lg border border-slate-100 bg-slate-50">
                                <img src="{{ asset($s['icon']) }}" alt="{{ $s['label'] }}"
                                    class="h-8 w-8 object-contain"
                                    onerror="this.onerror=null;this.style.display='none';">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                                    {{ $s['label'] }}
                                </div>
                                <div class="flex items-baseline gap-0.5 mt-0.5 min-w-0">
                                    <span class="text-base font-extrabold text-slate-900 truncate">{{ $dispV }}</span>
                                    @if ($s['satuan'])
                                        <span class="text-[10px] font-semibold text-slate-400 flex-shrink-0">{{ $s['satuan'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
@endif
@if ($pHumidity || $pBattery || $pTemp)
<div>
                <div class="text-md font-semibold text-slate-700 mb-3">Logger</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
@if ($pHumidity)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pHumidity ? '?parameter=' . urlencode($pHumidity->nama_parameter) : '' }}"
                        class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-2 shadow-sm transition-all hover:shadow-md hover:border-blue-300">
                        <div
                            class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50">
                            <img src="{{ asset($paramIconPath($pHumidity, 'icons/beranda/' . ($isOnline ? 'humidity_online.svg' : 'humidity_offline.svg'))) }}"
                                alt="Humidity" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                                HUMIDITY</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ $humidity ?? '-' }}<span
                                    class="text-[10px] font-bold text-slate-400 ml-0.5">%</span>
                            </div>
                        </div>
                    </a>
@endif
@if ($pBattery)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pBattery ? '?parameter=' . urlencode($pBattery->nama_parameter) : '' }}"
                        class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-2 shadow-sm transition-all hover:shadow-md hover:border-green-300">
                        <div
                            class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50">
                            <img src="{{ asset($paramIconPath($pBattery, 'icons/beranda/' . ($isOnline ? 'battery_online.svg' : 'battery_offline.svg'))) }}"
                                alt="Battery" class="h-full w-full object-cover {{ $iconClass }}">
                        </div>
                        <div class="leading-tight min-w-0">
                            <div class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase truncate">
                                BATTERY</div>
                            <div class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">
                                {{ $battery ?? '-' }}<span
                                    class="text-[10px] font-bold text-slate-400 ml-0.5">V</span>
                            </div>
                        </div>
                    </a>
@endif
@if ($pTemp)
<a href="{{ route('analisa.index', $lg->id_logger) }}{{ $pTemp ? '?parameter=' . urlencode($pTemp->nama_parameter) : '' }}"
                        class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-2 shadow-sm transition-all hover:shadow-md hover:border-orange-300">
                        <div
                            class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50">
                            <img src="{{ asset($paramIconPath($pTemp, 'icons/beranda/' . ($isOnline ? 'temper_online.svg' : 'temper_offline.svg'))) }}"
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
@endif
                </div>
            </div>
@endif
        </div>
    </div>
</div>
