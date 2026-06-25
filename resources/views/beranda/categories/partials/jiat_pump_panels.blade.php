{{--
    Baris bawah AWLR Jiat (stasiun has_pump): Pompa & Kelistrikan (kartu per-fasa
    R/S/T) + Kualitas Air. Disertakan dari awlr.blade.php (cabang JIAT).
    Mewarisi: $lg, $isOnline, $muted, $iconClass, $jiatPumpParams, $jiatPumpValues.
--}}
@php
    $pp = $jiatPumpParams ?? [];
    $pv = $jiatPumpValues ?? [];
    $hasP = fn($b) => !empty($pp[$b]);
    $fmtP = function ($v) {
        if ($v === null || $v === '') return '-';
        if (!is_numeric($v)) return e($v);
        return rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    };
    $valP = fn($b) => $fmtP($pv[$b] ?? null);
    $unitP = fn($b) => trim((string) optional($pp[$b])->satuan);
    $linkP = function ($b) use ($pp, $lg) {
        $p = $pp[$b] ?? null;
        $url = route('analisa.index', $lg->id_logger);
        return $p && $p->nama_parameter ? $url . '?parameter=' . urlencode($p->nama_parameter) : $url;
    };

    // Fasa listrik: warna mengikuti konvensi PLN (R/S/T).
    $phases = [
        ['key' => 'r', 'label' => 'R', 'dot' => 'bg-rose-500',  'bar' => 'from-rose-400 to-rose-500',  'tint' => 'bg-rose-50/40',  'ring' => 'ring-rose-100'],
        ['key' => 's', 'label' => 'S', 'dot' => 'bg-amber-500', 'bar' => 'from-amber-400 to-amber-500', 'tint' => 'bg-amber-50/40', 'ring' => 'ring-amber-100'],
        ['key' => 't', 'label' => 'T', 'dot' => 'bg-sky-500',   'bar' => 'from-sky-400 to-sky-500',    'tint' => 'bg-sky-50/40',   'ring' => 'ring-sky-100'],
    ];
    $hasElec = collect($phases)->contains(fn($p) => $hasP('voltage_' . $p['key']) || $hasP('ampere_' . $p['key']));

    $qual = [
        ['base' => 'ph_air',   'label' => 'pH',       'unit' => '',     'icon' => 'icons/awgr/ph_air.svg',   'accent' => 'hover:border-violet-300',  'chip' => 'bg-violet-100 text-violet-600'],
        ['base' => 'amonia',   'label' => 'Amonia',   'unit' => 'mg/L', 'icon' => null,                       'accent' => 'hover:border-lime-300',    'chip' => 'bg-lime-100 text-lime-600'],
        ['base' => 'suhu_air', 'label' => 'Suhu Air', 'unit' => '°C',   'icon' => 'icons/awgr/suhu_air.svg', 'accent' => 'hover:border-emerald-300', 'chip' => 'bg-emerald-100 text-emerald-600'],
    ];
    $hasQual = collect($qual)->contains(fn($q) => $hasP($q['base']));

    $elecSpan = $hasQual ? 'md:col-span-8' : 'md:col-span-12';
    $qualSpan = $hasElec ? 'md:col-span-4' : 'md:col-span-12';
@endphp

@if ($hasElec || $hasQual)
<div class="border-t border-slate-200 px-5 pb-5 pt-4">
    <div class="grid grid-cols-12 gap-4">

        {{-- Pompa & Kelistrikan --}}
        @if ($hasElec)
        <div class="col-span-12 {{ $elecSpan }}">
            <div class="mb-2 text-md font-semibold text-slate-700">
                Pompa &amp; Kelistrikan
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach ($phases as $ph)
                    @php
                        $vBase = 'voltage_' . $ph['key'];
                        $aBase = 'ampere_' . $ph['key'];
                        $cellActive = $hasP($vBase) || $hasP($aBase);
                    @endphp
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md {{ $muted ? 'grayscale' : '' }}">
                        <div class="flex items-center gap-2 border-b border-slate-100 {{ $ph['tint'] }} px-3 py-2">
                            <span class="h-2.5 w-2.5 rounded-full {{ $ph['dot'] }} ring-2 ring-white {{ $cellActive ? '' : 'opacity-40' }}"></span>
                            <span class="text-sm font-bold tracking-wide text-slate-700">Fasa {{ $ph['label'] }}</span>
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-slate-100 sm:block sm:divide-x-0 sm:divide-y">
                            <div class="flex flex-col items-center gap-1 px-3 py-2.5 text-center sm:flex-row sm:justify-between sm:text-left">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Voltage</span>
                                <span class="flex min-w-0 flex-wrap items-baseline justify-center gap-x-1 sm:justify-start">
                                    <span class="text-lg font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">{{ $valP($vBase) }}</span>
                                    <span class="text-[10px] font-bold text-slate-400">{{ $unitP($vBase) ?: 'V' }}</span>
                                </span>
                            </div>
                            <div class="flex flex-col items-center gap-1 px-3 py-2.5 text-center sm:flex-row sm:justify-between sm:text-left">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Ampere</span>
                                <span class="flex min-w-0 flex-wrap items-baseline justify-center gap-x-1 sm:justify-start">
                                    <span class="text-lg font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">{{ $valP($aBase) }}</span>
                                    <span class="text-[10px] font-bold text-slate-400">{{ $unitP($aBase) ?: 'A' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Kualitas Air --}}
        @if ($hasQual)
        <div class="col-span-12 {{ $qualSpan }}">
            <div class="mb-2 text-md font-semibold text-slate-700">
                Kualitas Air
            </div>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 md:grid-cols-1">
                @foreach ($qual as $q)
                    @if ($hasP($q['base']))
                    <a href="{{ $linkP($q['base']) }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md {{ $q['accent'] }} {{ $muted ? 'grayscale' : '' }}">
                        <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg {{ $q['chip'] }}">
                            @if ($q['icon'])
                                <img src="{{ asset($q['icon']) }}" alt="{{ $q['label'] }}" class="h-6 w-6 object-contain {{ $iconClass ?? '' }}">
                            @else
                                <span class="text-[11px] font-black">NH₃</span>
                            @endif
                        </span>
                        <div class="min-w-0 leading-tight">
                            <div class="truncate text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $q['label'] }}</div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">{{ $valP($q['base']) }}</span>
                                <span class="text-[10px] font-bold text-slate-400">{{ $unitP($q['base']) ?: $q['unit'] }}</span>
                            </div>
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endif
