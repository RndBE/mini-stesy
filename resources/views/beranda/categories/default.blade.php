<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @include('beranda.categories.partials.logger_header')

    @php
        $metrics = collect([
            [
                'param' => $pHumidity,
                'label' => 'Humidity',
                'value' => $humidity,
                'unit' => '%',
                'linkParam' => $pHumidity?->nama_parameter,
            ],
            [
                'param' => $pBattery,
                'label' => 'Battery',
                'value' => $battery,
                'unit' => 'Volt',
                'linkParam' => $pBattery?->nama_parameter,
            ],
            [
                'param' => $pTemp,
                'label' => 'Temperature',
                'value' => $temp,
                'unit' => '°C',
                'linkParam' => $pTemp?->nama_parameter,
            ],
            [
                'param' => $pMukaAir,
                'label' => 'Muka Air Tanah',
                'value' => $mukaAir,
                'unit' => 'm',
                'linkParam' => $pMukaAir?->nama_parameter,
            ],
            [
                'param' => $pRain,
                'label' => 'Curah Hujan',
                'value' => $curahHujan,
                'unit' => 'mm',
                'linkParam' => $pRain?->nama_parameter,
            ],
        ])->filter(fn($item) => $item['param']);
    @endphp

    <div class="grid grid-cols-12 gap-4 p-5">
        <div class="col-span-12 md:col-span-8">
            <div class="mb-3 text-md font-semibold text-slate-700">Data Parameter</div>

            @if ($metrics->isEmpty())
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Belum ada parameter terkonfigurasi untuk logger ini.
                </div>
            @else
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($metrics as $metric)
                        <a href="{{ route('analisa.index', $lg->id_logger) }}{{ $metric['linkParam'] ? '?parameter=' . urlencode($metric['linkParam']) : '' }}"
                            class="block rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                            <div class="text-[11px] font-semibold tracking-wide text-slate-500">
                                {{ strtoupper($metric['label']) }}</div>
                            <div class="mt-1 text-2xl font-bold text-slate-900">
                                {{ $metric['value'] ?? '-' }}
                                <span class="text-xs font-semibold text-slate-500">{{ $metric['unit'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-span-12 md:col-span-4 space-y-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="text-[11px] font-semibold text-slate-500">Kategori</div>
                <div class="mt-1 text-sm font-semibold text-slate-900">{{ $lg->kategori?->nama_kategori ?? '-' }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="text-[11px] font-semibold text-slate-500">Lokasi</div>
                <div class="mt-1 text-sm font-semibold text-slate-900">{{ $lg->lokasi?->nama_lokasi ?? '-' }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="text-[11px] font-semibold text-slate-500">ID Logger</div>
                <div class="mt-1 text-sm font-semibold text-slate-900">{{ $lg->id_logger }}</div>
            </div>
        </div>
    </div>
</div>
