@extends('layouts.app')
@section('content')
    <style>
        .col-span-12 {
            grid-column: span 12 / span 12;
        }

        @media (min-width: 768px) {
            .md\:col-span-3 {
                grid-column: span 3 / span 3;
            }

            .md\:col-span-4 {
                grid-column: span 4 / span 4;
            }

            .md\:col-span-8 {
                grid-column: span 8 / span 8;
            }

            .md\:col-span-9 {
                grid-column: span 9 / span 9;
            }

            .md\:grid-cols-1 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            .md\:border-r {
                border-right-width: 1px;
            }

            .md\:border-slate-200 {
                --tw-border-opacity: 1;
                border-color: rgb(226 232 240 / var(--tw-border-opacity, 1));
            }

            .md\:pr-2 {
                padding-right: 0.5rem;
            }

            .md\:pr-4 {
                padding-right: 1rem;
            }

            .md\:text-left {
                text-align: left;
            }
        }

        @media (min-width: 640px) {
            .sm\:top-10 {
                top: 2.5rem;
            }

            .sm\:mt-24 {
                margin-top: 6rem;
            }

            .sm\:h-28 {
                height: 7rem;
            }

            .sm\:w-28 {
                width: 7rem;
            }

            .sm\:px-3 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .sm\:py-2\.5 {
                padding-top: 0.625rem;
                padding-bottom: 0.625rem;
            }
        }

        @media (min-width: 1024px) {
            .lg\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .lg\:h-32 {
                height: 8rem;
            }

            .lg\:w-32 {
                width: 8rem;
            }
        }

        @media (min-width: 1280px) {
            .xl\:mt-28 {
                margin-top: 7rem;
            }
        }

        @media (min-width: 1536px) {
            .\32xl\:top-7 {
                top: 1.75rem;
            }

            .\32xl\:h-36 {
                height: 9rem;
            }

            .\32xl\:w-36 {
                width: 9rem;
            }

            .\32xl\:mt-32 {
                margin-top: 8rem;
            }
        }

        .beranda-logger-grid > * {
            content-visibility: auto;
            contain-intrinsic-size: 460px;
        }
    </style>

    @php
        $kategoriTabs = $groupedLoggers
            ->map(function ($items, $key) {
                $label = $items->first()?->kategori?->nama_kategori ?? $key;
                return ['key' => $key, 'label' => $label];
            })
            ->values();
    @endphp

    <div x-data="{ selectedCategory: 'ALL' }" class="space-y-4">
        @if ($kategoriTabs->isNotEmpty())
            <div class="overflow-x-auto pb-1">
                <div class="inline-flex min-w-max items-center gap-2 rounded-2xl bg-slate-200 p-1">
                    <button type="button" @click="selectedCategory = 'ALL'"
                        :class="selectedCategory === 'ALL' ? 'bg-white text-slate-900 shadow-sm' :
                            'bg-transparent text-slate-700'"
                        class="rounded-xl px-5 py-2 text-sm font-semibold transition">
                        Semua Kategori
                    </button>
                    @foreach ($kategoriTabs as $tab)
                        <button type="button" @click="selectedCategory = @js($tab['key'])"
                            :class="selectedCategory === @js($tab['key']) ? 'bg-white text-slate-900 shadow-sm' :
                                'bg-transparent text-slate-700'"
                            class="rounded-xl px-5 py-2 text-sm font-semibold transition">
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        @forelse ($groupedLoggers as $kategoriName => $loggerItems)
            @php
                $kategoriShortName = $loggerItems->first()?->kategori?->nama_kategori ?? $kategoriName;
                $kategoriFullName = $loggerItems->first()?->kategori?->kepanjangan ?? $kategoriShortName;
            @endphp

            <section x-show="selectedCategory === 'ALL' || selectedCategory === @js($kategoriName)" x-cloak
                class="space-y-3">
                <div class="flex flex-wrap items-center justify-between">
                    <div class="text-lg text-slate-900">
                        <span class="font-bold">{{ $kategoriShortName }}</span>
                        <span class="font-normal">
                            {{ $kategoriFullName !== $kategoriShortName ? ' (' . $kategoriFullName . ')' : '' }}
                        </span>
                    </div>
                </div>

                <div class="beranda-logger-grid grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @foreach ($loggerItems as $lg)
                        @php
                            $latest = $lg->temp;
                            $waktu = $latest?->waktu ? \Carbon\Carbon::parse($latest->waktu)->format('Y-m-d H:i') : '-';

                            $isOnline = (bool) ($latest?->is_online ?? true);
                            $isOnline = $lg->status_logger === 'online' ? $isOnline : false;
                            $sdRawValue = $latest?->sensor13 ?? null;
                            $isSdOk = is_numeric($sdRawValue) ? ((int) $sdRawValue === 1) : false;

                            $timeClass = $isOnline
                                ? 'border-emerald-200 bg-[#DEF2E1] text-[#06C022]'
                                : 'bg-[#E6E6E6] text-black';
                            $dotClass = $isOnline ? 'bg-green-500' : 'bg-gray-800';
                            $badgeClass = $isOnline
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-rose-200 bg-rose-50 text-rose-700';
                            $sdClass = $isSdOk
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-rose-200 bg-rose-50 text-rose-700';
                            $statusText = $isOnline ? 'Koneksi Terhubung' : 'Koneksi Terputus';
                            $sdText = $isSdOk ? 'OK' : 'Bermasalah';

                            $normalizeParamKey = function ($value) {
                                $value = trim((string) $value);
                                if ($value === '') return '';
                                $value = preg_replace('/\s+/', '_', $value);
                                $value = preg_replace('/_+/', '_', $value);
                                return strtolower($value);
                            };
                            $findParamByBase = function (array $baseKeys) use ($lg, $normalizeParamKey) {
                                $baseKeys = collect($baseKeys)
                                    ->map(fn($key) => $normalizeParamKey($key))
                                    ->filter()
                                    ->values()
                                    ->all();

                                return $lg->params->first(function ($param) use ($baseKeys, $normalizeParamKey) {
                                    $utama = $normalizeParamKey($param->parameter_utama);
                                    $nama = $normalizeParamKey($param->nama_parameter);
                                    return in_array($utama, $baseKeys, true)
                                        || ($utama === '' && in_array($nama, $baseKeys, true));
                                });
                            };
                            $paramIconPath = function ($param, string $fallback) use ($isOnline) {
                                $icon = trim((string) ($param->icon_app ?? ''));
                                if ($icon === '' || !str_contains($icon, '/')) {
                                    return $fallback;
                                }

                                $icon = ltrim($icon, '/');
                                if (!$isOnline) {
                                    $offlineIcon = preg_replace('/_online(\.[a-z0-9]+)$/i', '_offline$1', $icon);
                                    if ($offlineIcon !== $icon) {
                                        return $offlineIcon;
                                    }
                                }

                                return $icon;
                            };

                            $pHumidity = $findParamByBase(['humidity_logger']);
                            $pBattery = $findParamByBase(['battery_logger']);
                            $pTemp = $findParamByBase(['temperature_logger']);
                            $pMukaAir = $findParamByBase(['muka_air_tanah']);
                            $pRain = $findParamByBase(['hujan', 'curah_hujan']);

                            $humidity =
                                $latest && $pHumidity && $pHumidity->kolom_sensor
                                    ? $latest->{$pHumidity->kolom_sensor} ?? null
                                    : null;
                            $battery =
                                $latest && $pBattery && $pBattery->kolom_sensor
                                    ? $latest->{$pBattery->kolom_sensor} ?? null
                                    : null;
                            $temp =
                                $latest && $pTemp && $pTemp->kolom_sensor
                                    ? $latest->{$pTemp->kolom_sensor} ?? null
                                    : null;
                            $mukaAir =
                                $latest && $pMukaAir && $pMukaAir->kolom_sensor
                                    ? $latest->{$pMukaAir->kolom_sensor} ?? null
                                    : null;
                            $curahHujan =
                                $latest && $pRain && $pRain->kolom_sensor
                                    ? $latest->{$pRain->kolom_sensor} ?? null
                                    : null;

                            $dataAir =
                                is_numeric($lg->jiat?->kedalaman_sumur) && is_numeric($mukaAir)
                                    ? $lg->jiat?->kedalaman_sumur - $mukaAir
                                    : null;
                            $subKategoriAwlr = (is_numeric($lg->jiat?->kedalaman_sumur) && (float)$lg->jiat->kedalaman_sumur > 0)
                                ? 'jiat'
                                : 'non_jiat';
                            $pTma = $findParamByBase(['tma', 'muka_air_tanah']);
                            $pDebit = $findParamByBase(['debit']);
                            $tma   = $latest && $pTma   && $pTma->kolom_sensor   ? $latest->{$pTma->kolom_sensor}   ?? null : null;
                            $debit = $latest && $pDebit && $pDebit->kolom_sensor ? $latest->{$pDebit->kolom_sensor} ?? null : null;
                            $pElevMukaAir = $findParamByBase(
                                ['elevasi_muka_air', 'elev_muka_air']
                            );
                            $pElevSensor = $findParamByBase(
                                ['elevasi_sensor', 'tinggi_sensor']
                            );
                            $pJarakSensor = $findParamByBase(['jarak_sensor', 'jarak_sensor_ke_air']);
                            $pLuas = $findParamByBase(
                                ['luas_penampang', 'luas_penampang_basah', 'luas_penampang_air']
                            );
                            $pAfmrDebit = $findParamByBase(['debit']);
                            $pFlowVelocity = $findParamByBase(
                                ['flow_velocity', 'kecepatan_aliran']
                            );
                            $elevMukaAir  = $latest && $pElevMukaAir  && $pElevMukaAir->kolom_sensor  ? $latest->{$pElevMukaAir->kolom_sensor}  ?? null : null;
                            $elevSensor   = $latest && $pElevSensor   && $pElevSensor->kolom_sensor   ? $latest->{$pElevSensor->kolom_sensor}   ?? null : null;
                            $jarakSensor  = $latest && $pJarakSensor  && $pJarakSensor->kolom_sensor  ? $latest->{$pJarakSensor->kolom_sensor}  ?? null : null;
                            $luasPenampang= $latest && $pLuas         && $pLuas->kolom_sensor         ? $latest->{$pLuas->kolom_sensor}         ?? null : null;
                            $afmrDebit    = $latest && $pAfmrDebit    && $pAfmrDebit->kolom_sensor    ? $latest->{$pAfmrDebit->kolom_sensor}    ?? null : null;
                            $flowVelocity = $latest && $pFlowVelocity && $pFlowVelocity->kolom_sensor ? $latest->{$pFlowVelocity->kolom_sensor} ?? null : null;

                            // AFMR contact (pipe flowmeter) parameters
                            $pFlowrate   = $findParamByBase(['flowrate', 'flow_rate']);
                            $pTotalizer1 = $findParamByBase(['totalizer', 'totalizer_1', 'totalizer1']);
                            $pTotalizer2 = $findParamByBase(['totalizer_2', 'totalizer2']);
                            $pPressure1  = $findParamByBase(['pressure_1', 'pressure1', 'tekanan_1']);
                            $pPressure2  = $findParamByBase(['pressure_2', 'pressure2', 'tekanan_2']);
                            $pFmBattery  = $findParamByBase(['flowmeter_battery']);
                            $pFault      = $findParamByBase(['fault']);
                            $flowrate   = $latest && $pFlowrate   && $pFlowrate->kolom_sensor   ? $latest->{$pFlowrate->kolom_sensor}   ?? null : null;
                            $totalizer1 = $latest && $pTotalizer1 && $pTotalizer1->kolom_sensor ? $latest->{$pTotalizer1->kolom_sensor} ?? null : null;
                            $totalizer2 = $latest && $pTotalizer2 && $pTotalizer2->kolom_sensor ? $latest->{$pTotalizer2->kolom_sensor} ?? null : null;
                            $pressure1  = $latest && $pPressure1  && $pPressure1->kolom_sensor  ? $latest->{$pPressure1->kolom_sensor}  ?? null : null;
                            $pressure2  = $latest && $pPressure2  && $pPressure2->kolom_sensor  ? $latest->{$pPressure2->kolom_sensor}  ?? null : null;
                            $fmBattery  = $latest && $pFmBattery  && $pFmBattery->kolom_sensor  ? $latest->{$pFmBattery->kolom_sensor}  ?? null : null;
                            $fault      = $latest && $pFault      && $pFault->kolom_sensor      ? $latest->{$pFault->kolom_sensor}      ?? null : null;

                            // AWLR Jiat — parameter monitoring pompa (listrik 3-fasa, flow, kualitas air)
                            $pumpBases = [
                                'voltage_r', 'voltage_s', 'voltage_t',
                                'ampere_r', 'ampere_s', 'ampere_t',
                                'flow_rate', 'flow_rate_signal',
                                'amonia', 'ph_air', 'suhu_air',
                            ];
                            $jiatPumpParams = [];
                            $jiatPumpValues = [];
                            foreach ($pumpBases as $base) {
                                $p = $findParamByBase([$base]);
                                $jiatPumpParams[$base] = $p;
                                $jiatPumpValues[$base] = $latest && $p && $p->kolom_sensor
                                    ? $latest->{$p->kolom_sensor} ?? null
                                    : null;
                            }

                            $muted = !$isOnline;
                            $iconClass = $muted ? 'grayscale opacity-40' : '';

                            $subKategoriAfmr = $lg->afmrContact ? 'contact' : 'non_contact';

                            $kategoriKey = strtoupper((string) ($lg->kategori?->nama_kategori ?? $kategoriName));
                            $kategoriView = match ($kategoriKey) {
                                'AWLR' => 'beranda.categories.awlr',
                                'ARR'  => 'beranda.categories.arr',
                                'AWQR' => 'beranda.categories.awqr',
                                'AWR'  => 'beranda.categories.awr',
                                'AFMR' => 'beranda.categories.afmr',
                                default => 'beranda.categories.default',
                            };
                        @endphp

                        @include($kategoriView, [
                            'lg'              => $lg,
                            'waktu'           => $waktu,
                            'isOnline'        => $isOnline,
                            'isSdOk'          => $isSdOk,
                            'timeClass'       => $timeClass,
                            'dotClass'        => $dotClass,
                            'badgeClass'      => $badgeClass,
                            'sdClass'         => $sdClass,
                            'statusText'      => $statusText,
                            'sdText'          => $sdText,
                            'pHumidity'       => $pHumidity,
                            'pBattery'        => $pBattery,
                            'pTemp'           => $pTemp,
                            'pMukaAir'        => $pMukaAir,
                            'pRain'           => $pRain,
                            'humidity'        => $humidity,
                            'battery'         => $battery,
                            'temp'            => $temp,
                            'mukaAir'         => $mukaAir,
                            'curahHujan'      => $curahHujan,
                            'curahHujanPerJam'   => $lg->arr_curah_hujan_perjam,
                            'curahHujanHarian'   => $lg->arr_curah_hujan_harian,
                            'statusHujanPerJam'  => $lg->arr_status_perjam,
                            'statusHujanHarian'  => $lg->arr_status_perhari,
                            'stateHujanPerJam'   => $lg->arr_state_perjam,
                            'stateHujanHarian'   => $lg->arr_state_perhari,
                            'dataAir'         => $dataAir,
                            'muted'           => $muted,
                            'iconClass'       => $iconClass,
                            'subKategoriAwlr' => $subKategoriAwlr ?? 'non_jiat',
                            'subKategoriAfmr' => $subKategoriAfmr ?? 'non_contact',
                            'pTma'            => $pTma ?? null,
                            'pDebit'          => $pDebit ?? null,
                            'tma'             => $tma ?? null,
                            'debit'           => $debit ?? null,
                            'pElevMukaAir'    => $pElevMukaAir   ?? null,
                            'pElevSensor'     => $pElevSensor    ?? null,
                            'pJarakSensor'    => $pJarakSensor   ?? null,
                            'pLuas'           => $pLuas          ?? null,
                            'pAfmrDebit'      => $pAfmrDebit     ?? null,
                            'pFlowVelocity'   => $pFlowVelocity  ?? null,
                            'elevMukaAir'     => $elevMukaAir    ?? null,
                            'elevSensor'      => $elevSensor     ?? null,
                            'jarakSensor'     => $jarakSensor    ?? null,
                            'luasPenampang'   => $luasPenampang  ?? null,
                            'afmrDebit'       => $afmrDebit      ?? null,
                            'flowVelocity'    => $flowVelocity   ?? null,
                            'pFlowrate'       => $pFlowrate      ?? null,
                            'pTotalizer1'     => $pTotalizer1    ?? null,
                            'pTotalizer2'     => $pTotalizer2    ?? null,
                            'pPressure1'      => $pPressure1     ?? null,
                            'pPressure2'      => $pPressure2     ?? null,
                            'pFmBattery'      => $pFmBattery     ?? null,
                            'pFault'          => $pFault         ?? null,
                            'flowrate'        => $flowrate       ?? null,
                            'totalizer1'      => $totalizer1     ?? null,
                            'totalizer2'      => $totalizer2     ?? null,
                            'pressure1'       => $pressure1      ?? null,
                            'pressure2'       => $pressure2      ?? null,
                            'fmBattery'       => $fmBattery      ?? null,
                            'fault'           => $fault          ?? null,
                            'jiatPumpParams'  => $jiatPumpParams ?? [],
                            'jiatPumpValues'  => $jiatPumpValues ?? [],
                        ])
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                Tidak ada data logger.
            </div>
        @endforelse
    </div>
@endsection
