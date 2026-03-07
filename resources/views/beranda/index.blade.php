@extends('layouts.app')
@section('content')
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

                            $pHumidity =
                                $lg->params->firstWhere('parameter_utama', 'humidity_logger') ??
                                $lg->params->firstWhere('nama_parameter', 'humidity_logger');
                            $pBattery =
                                $lg->params->firstWhere('parameter_utama', 'battery_logger') ??
                                $lg->params->firstWhere('nama_parameter', 'battery_logger');
                            $pTemp =
                                $lg->params->firstWhere('parameter_utama', 'temperature_logger') ??
                                $lg->params->firstWhere('nama_parameter', 'temperature_logger');
                            $pMukaAir =
                                $lg->params->firstWhere('parameter_utama', 'muka_air_tanah') ??
                                $lg->params->firstWhere('nama_parameter', 'muka_air_tanah');
                            $pRain =
                                $lg->params->firstWhere('parameter_utama', 'hujan') ??
                                ($lg->params->firstWhere('nama_parameter', 'Curah Hujan') ??
                                    $lg->params->first(function ($param) {
                                        $name = strtolower(trim((string) $param->nama_parameter));
                                        $utama = strtolower(trim((string) $param->parameter_utama));
                                        return str_contains($name, 'hujan') ||
                                            str_contains($name, 'rain') ||
                                            str_contains($utama, 'hujan') ||
                                            str_contains($utama, 'rain');
                                    }));

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

                            // AWLR sub-kategori: jiat jika ada kedalaman_sumur > 0
                            $subKategoriAwlr = (is_numeric($lg->jiat?->kedalaman_sumur) && (float)$lg->jiat->kedalaman_sumur > 0)
                                ? 'jiat'
                                : 'non_jiat';

                            // Parameter TMA dan Debit untuk non-jiat
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

                            // ===== AFMR Parameters =====
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
                            // AWLR fields
                            'subKategoriAwlr' => $subKategoriAwlr ?? 'non_jiat',
                            'pTma'            => $pTma ?? null,
                            'pDebit'          => $pDebit ?? null,
                            'tma'             => $tma ?? null,
                            'debit'           => $debit ?? null,
                            // AFMR fields
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
