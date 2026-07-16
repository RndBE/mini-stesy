<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    @php
        $normalizeApmsKey = function ($value) {
            $value = trim((string) $value);
            if ($value === '') return '';
            $value = preg_replace('/\s+/', '_', $value);
            return strtolower(preg_replace('/_+/', '_', $value));
        };

        $findApmsParam = function (array $keys) use ($lg, $normalizeApmsKey) {
            $keys = collect($keys)
                ->map(fn($key) => $normalizeApmsKey($key))
                ->filter()
                ->values()
                ->all();

            return $lg->params->first(function ($param) use ($keys, $normalizeApmsKey) {
                $utama = $normalizeApmsKey($param->parameter_utama);
                $nama = $normalizeApmsKey($param->nama_parameter);
                return in_array($utama, $keys, true) || in_array($nama, $keys, true);
            });
        };

        $readApmsValue = function ($param) use ($latest) {
            $column = $param?->kolom_sensor;
            $value = $latest && $column ? ($latest->{$column} ?? null) : null;
            return is_numeric($value) ? $value : null;
        };

        $apmsDefinitions = [
            [
                'keys' => ['ph_tanah', 'soil_ph'],
                'label' => 'pH Tanah',
                'unit' => '',
                'icon' => 'icons/apms/ph_tanah.svg',
            ],
            [
                'keys' => ['electrical_conductivity', 'conductivity_tanah', 'soil_conductivity'],
                'label' => 'Electrical Conductivity',
                'unit' => 'uS/cm',
                'icon' => 'icons/apms/electrical_conductivity.svg',
            ],
            [
                'keys' => ['kelembaban_tanah', 'soil_moisture'],
                'label' => 'Kelembaban Tanah',
                'unit' => '%',
                'icon' => 'icons/apms/soil_moisture.svg',
            ],
            [
                'keys' => ['temperature_tanah', 'temperatur_tanah', 'soil_temperature'],
                'label' => 'Temperature Tanah',
                'unit' => '°C',
                'icon' => 'icons/apms/soil_temperature.svg',
            ],
            [
                'keys' => ['salinity'],
                'label' => 'Salinity',
                'unit' => 'PSU',
                'icon' => 'icons/apms/soil_salinity.svg',
            ],
        ];

        $apmsMeasurements = collect($apmsDefinitions)
            ->map(function ($definition) use ($findApmsParam, $readApmsValue, $paramIconPath) {
                $param = $findApmsParam($definition['keys']);
                $configuredUnit = trim((string) ($param?->satuan ?? ''));

                return $definition + [
                    'param' => $param,
                    'value' => $readApmsValue($param),
                    'display_unit' => $configuredUnit !== '' ? $configuredUnit : $definition['unit'],
                    'display_icon' => $paramIconPath($param, $definition['icon']),
                ];
            });
    @endphp

    <div class="space-y-5 p-5">
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-4 text-balance text-base font-semibold text-slate-800">Data Pengukuran</h3>

            <div class="grid grid-cols-12 gap-4 md:grid-cols-12">
                @include('beranda.categories.partials.monitoring_well', [
                    'showWellHardware' => false,
                    'showWellHeading' => false,
                ])

                <div class="col-span-12 space-y-2 md:col-span-4">
                    @foreach ($apmsMeasurements as $measurement)
                        @php
                            $analysisUrl = $measurement['param']
                                ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($measurement['param']->nama_parameter)
                                : route('analisa.index', $lg->id_logger);
                            $value = is_numeric($measurement['value'])
                                ? \App\Support\DisplayFormat::ukur($measurement['value'])
                                : '-';
                        @endphp

                        <a href="{{ $analysisUrl }}"
                            class="group flex min-h-14 min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-300 {{ $muted ? 'grayscale opacity-70' : '' }}">
                            <span class="flex size-11 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                <img src="{{ asset($measurement['display_icon']) }}" alt=""
                                    class="size-9 object-contain"
                                    onerror="this.onerror=null;this.style.display='none';">
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-balance text-[10px] font-semibold uppercase leading-tight text-slate-500">
                                    {{ $measurement['label'] }}
                                </span>
                                <span class="mt-0.5 flex min-w-0 items-baseline gap-1.5">
                                    <span class="text-2xl font-extrabold tabular-nums leading-none text-slate-950">{{ $value }}</span>
                                    @if ($measurement['display_unit'])
                                        <span class="min-w-0 text-xs font-medium text-slate-600">{{ $measurement['display_unit'] }}</span>
                                    @endif
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            @if ($pHumidity || $pBattery || $pTemp)
                <section>
                    <h3 class="mb-3 text-balance text-base font-semibold text-slate-800">Data Logger</h3>
                    @include('beranda.categories.partials.logger_health_cards')
                </section>
            @endif

            @if ($pRain)
                <section>
                    <h3 class="mb-3 text-balance text-base font-semibold text-slate-800">Curah Hujan</h3>
                    @include('beranda.categories.partials.rainfall_cards', [
                        'desktopCardClass' => 'lg:h-[202px] lg:min-h-0 lg:py-2',
                        'desktopIconClass' => 'lg:h-24 lg:w-32',
                        'desktopIconWrapClass' => 'lg:py-1',
                    ])
                </section>
            @endif
        </div>
    </div>
</div>
