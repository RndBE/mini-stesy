<div class="overflow-visible rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    @if (($subKategoriAwlr ?? 'non_jiat') === 'jiat')

<div class="grid grid-cols-12 gap-4 p-5 md:grid-cols-12">
            @include('beranda.categories.partials.monitoring_well')

            @php $jiatHasPump = (bool) ($lg?->jiat?->has_pump); @endphp
            @if ($pHumidity || $pBattery || $pTemp || $jiatHasPump)
            <div class="col-span-12 md:col-span-4 space-y-4">
                @if ($pHumidity || $pBattery || $pTemp)
                <div class="space-y-3">
                    <div class="text-md font-semibold text-slate-700">Parameter Logger</div>
                    @include('beranda.categories.partials.logger_health_cards')
                </div>
                @endif
                @if ($jiatHasPump)
                    @include('beranda.categories.partials.jiat_flow_cards')
                @endif
            </div>
            @endif
        </div>

        @if ($lg?->jiat?->has_pump)
            @include('beranda.categories.partials.jiat_pump_panels')
        @endif
    @else
        @php
            // Pemasangan non-JIAT: sungai/saluran (tiang) atau ambang v-notch.
            $nonJiatMount = ($lg->nonjiat?->jenis_pemasangan ?? 'sungai') === 'v_notch'
                ? 'v_notch'
                : 'sungai';

            // Nilai apa adanya; yang berbeda hanya label satuannya.
            $tmaShow     = is_numeric($tma) ? (float) $tma : null;
            $tmaShowUnit = $nonJiatMount === 'v_notch' ? 'cm' : ($pTma?->satuan ?: 'm');
            $tmaShowDec  = 3;
        @endphp
<div class="p-5 space-y-4">
<div class="grid grid-cols-12 gap-4">
<div class="col-span-12 {{ $nonJiatMount === 'v_notch' ? 'md:col-span-8' : 'md:col-span-9' }} space-y-3 md:border-r md:border-slate-200 md:pr-4">
                    @if ($nonJiatMount === 'v_notch')
                        @include('beranda.categories.partials.vnotch_weir')
                    @else
                        @include('beranda.categories.partials.river_channel')
                    @endif

                </div>
@if ($pTma || $pDebit || $pHumidity || $pBattery || $pTemp)
<div class="col-span-12 {{ $nonJiatMount === 'v_notch' ? 'md:col-span-4' : 'md:col-span-3' }} flex flex-col justify-start gap-2">
                    <div class="text-sm font-semibold text-slate-700">Data Pengukuran</div>
@if ($pTma)
@php
                        $tmaAnalisaUrl = $pTma
                            ? route('analisa.index', $lg->id_logger) . '?parameter=' . urlencode($pTma->nama_parameter)
                            : route('analisa.index', $lg->id_logger);
                    @endphp
                    <a href="{{ $tmaAnalisaUrl }}"
                        class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                            {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                        <div
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                            <img src="{{ asset($paramIconPath($pTma, 'icons/awlr/elevasi_muka_air.svg')) }}"
                                alt="TMA" class="h-full w-full object-cover"
                                onerror="this.style.display='none';this.parentElement.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' class=\'h-5 w-5 text-sky-500\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10\'/></svg>'">
                        </div>
                        <div>
                            <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Tinggi Muka Air
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ $tmaShow !== null ? \App\Support\DisplayFormat::ukur($tmaShow, $tmaShowDec) : '-' }}
                                </span>
                                <span class="text-xs font-semibold text-slate-400">{{ $tmaShowUnit }}</span>
                            </div>
                        </div>
                    </a>
@endif
@if ($pDebit)
@php
                        $debitAnalisaUrl = $pDebit
                            ? route('analisa.index', $lg->id_logger) .
                                '?parameter=' .
                                urlencode($pDebit->nama_parameter)
                            : route('analisa.index', $lg->id_logger);
                    @endphp
                    <a href="{{ $debitAnalisaUrl }}"
                        class="flex items-center gap-2 rounded-xl border px-2.5 py-1.5 bg-white shadow-sm transition-all hover:shadow-md hover:border-sky-300
                            {{ $isOnline ? 'border-slate-200' : 'border-slate-200 grayscale opacity-70' }}">
                        <div
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 overflow-hidden">
                            <img src="{{ asset($paramIconPath($pDebit, 'icons/awlr/debit.svg')) }}"
                                alt="Debit" class="h-full w-full object-cover"
                                onerror="this.style.display='none';this.parentElement.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' class=\'h-5 w-5 text-indigo-500\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13 10V3L4 14h7v7l9-11h-7z\'/></svg>'">
                        </div>
                        <div>
                            <div class="text-[8px] font-bold uppercase tracking-widest text-slate-400">Debit</div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-lg font-extrabold {{ $isOnline ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ is_numeric($debit) ? \App\Support\DisplayFormat::ukur($debit, 3) : '-' }}
                                </span>
                                <span class="text-xs font-semibold text-slate-400">m³/s</span>
                            </div>
                        </div>
                    </a>
@endif
@if ($nonJiatMount === 'v_notch' && ($pHumidity || $pBattery || $pTemp))
                    <div class="mt-1 space-y-2 border-t border-slate-100 pt-3">
                        <div class="text-sm font-semibold text-slate-700">Parameter Logger</div>
                        @include('beranda.categories.partials.logger_health_cards')
                    </div>
@endif
                </div>
@endif
            </div>
@if ($nonJiatMount !== 'v_notch' && ($pHumidity || $pBattery || $pTemp))
            <div class="border-t border-slate-100 pt-3">
                <div class="text-sm font-semibold text-slate-700 mb-2">Logger</div>
                @include('beranda.categories.partials.logger_health_cards', ['gridClass' => 'grid grid-cols-3 gap-2'])
            </div>
@endif
        </div>

    @endif
</div>
