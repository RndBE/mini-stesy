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

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @foreach ($loggerItems as $lg)
                        @php
                            $latest = $lg->temp;
                            $waktu = $latest?->waktu ? \Carbon\Carbon::parse($latest->waktu)->format('Y-m-d H:i') : '-';

                            $isOnline = (bool) ($latest?->is_online ?? true);
                            $isOnline = $lg->status_logger === 'online' ? $isOnline : false;
                            $isSdOk = (bool) ($latest?->is_sd_ok ?? true);

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

                            $pHumidity = $lg->params->first(function ($p) {
                                $u = strtolower(trim((string) $p->parameter_utama));
                                $n = strtolower(trim((string) $p->nama_parameter));
                                return $u === 'humidity_logger' || $n === 'humidity_logger'
                                    || str_contains($n, 'humidity') || str_contains($u, 'humidity');
                            });
                            $pBattery = $lg->params->first(function ($p) {
                                $u = strtolower(trim((string) $p->parameter_utama));
                                $n = strtolower(trim((string) $p->nama_parameter));
                                return $u === 'battery_logger' || $n === 'battery_logger'
                                    || str_contains($n, 'battery') || str_contains($u, 'battery');
                            });
                            $pTemp = $lg->params->first(function ($p) {
                                $u = strtolower(trim((string) $p->parameter_utama));
                                $n = strtolower(trim((string) $p->nama_parameter));
                                return $u === 'temperature_logger' || $n === 'temperature_logger'
                                    || str_contains($n, 'temperature') || str_contains($u, 'temperature');
                            });
                            $pMukaAir = $lg->params->first(function ($p) {
                                $u = strtolower(trim((string) $p->parameter_utama));
                                $n = strtolower(trim((string) $p->nama_parameter));
                                return $u === 'muka_air_tanah' || $n === 'muka_air_tanah'
                                    || str_contains($n, 'muka') || str_contains($u, 'muka');
                            });
                            $pRain = $lg->params->first(function ($param) {
                                $name = strtolower(trim((string) $param->nama_parameter));
                                $utama = strtolower(trim((string) $param->parameter_utama));
                                return $utama === 'hujan' || $name === 'curah hujan'
                                    || str_contains($name, 'hujan') || str_contains($name, 'rain')
                                    || str_contains($utama, 'hujan') || str_contains($utama, 'rain');
                            });

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
                            $pTma = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return str_contains($n, 'muka') || str_contains($n, 'tma')
                                    || str_contains($u, 'muka') || str_contains($u, 'tma');
                            });
                            $pDebit = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return str_contains($n, 'debit') || str_contains($u, 'debit');
                            });
                            $tma   = $latest && $pTma   && $pTma->kolom_sensor   ? $latest->{$pTma->kolom_sensor}   ?? null : null;
                            $debit = $latest && $pDebit && $pDebit->kolom_sensor ? $latest->{$pDebit->kolom_sensor} ?? null : null;
                            $pElevMukaAir = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return str_contains($n, 'elevasi') || str_contains($n, 'elev')
                                    || str_contains($u, 'elevasi') || str_contains($u, 'elev');
                            });
                            $pElevSensor = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return (str_contains($n, 'sensor') && str_contains($n, 'elev'))
                                    || (str_contains($u, 'sensor') && str_contains($u, 'elev'));
                            });
                            $pJarakSensor = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return str_contains($n, 'jarak') || str_contains($u, 'jarak');
                            });
                            $pLuas = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return str_contains($n, 'luas') || str_contains($u, 'luas');
                            });
                            $pAfmrDebit = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return str_contains($n, 'debit') || str_contains($u, 'debit');
                            });
                            $pFlowVelocity = $lg->params->first(function ($param) {
                                $n = strtolower(trim((string) $param->nama_parameter));
                                $u = strtolower(trim((string) $param->parameter_utama));
                                return str_contains($n, 'velocity') || str_contains($n, 'kecepatan')
                                    || str_contains($u, 'velocity') || str_contains($u, 'kecepatan');
                            });
                            $elevMukaAir  = $latest && $pElevMukaAir  && $pElevMukaAir->kolom_sensor  ? $latest->{$pElevMukaAir->kolom_sensor}  ?? null : null;
                            $elevSensor   = $latest && $pElevSensor   && $pElevSensor->kolom_sensor   ? $latest->{$pElevSensor->kolom_sensor}   ?? null : null;
                            $jarakSensor  = $latest && $pJarakSensor  && $pJarakSensor->kolom_sensor  ? $latest->{$pJarakSensor->kolom_sensor}  ?? null : null;
                            $luasPenampang= $latest && $pLuas         && $pLuas->kolom_sensor         ? $latest->{$pLuas->kolom_sensor}         ?? null : null;
                            $afmrDebit    = $latest && $pAfmrDebit    && $pAfmrDebit->kolom_sensor    ? $latest->{$pAfmrDebit->kolom_sensor}    ?? null : null;
                            $flowVelocity = $latest && $pFlowVelocity && $pFlowVelocity->kolom_sensor ? $latest->{$pFlowVelocity->kolom_sensor} ?? null : null;

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
