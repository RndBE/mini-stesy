{{--
    Flow Meter — kolom kanan AWLR Jiat (di bawah Parameter Logger).
    Mewarisi: $lg, $isOnline, $muted, $jiatPumpParams, $jiatPumpValues.
--}}
@php
    $pp = $jiatPumpParams ?? [];
    $pv = $jiatPumpValues ?? [];
    $hasP = fn($b) => !empty($pp[$b]);
    $fmtP = function ($v) {
        if ($v === null || $v === '') return '-';
        if (!is_numeric($v)) return e($v);
        return rtrim(rtrim(str_replace(',', '', \App\Support\DisplayFormat::ukur($v, 2)), '0'), '.');
    };
    $valP = fn($b) => $fmtP($pv[$b] ?? null);
    $unitP = fn($b) => trim((string) optional($pp[$b])->satuan);
    $linkP = function ($b) use ($pp, $lg) {
        $p = $pp[$b] ?? null;
        $url = route('analisa.index', $lg->id_logger);
        return $p && $p->nama_parameter ? $url . '?parameter=' . urlencode($p->nama_parameter) : $url;
    };
    $sigRaw = $pv['flow_rate_signal'] ?? null;
    $sigPct = is_numeric($sigRaw) ? max(0, min(100, (float) $sigRaw)) : null;
@endphp

@if ($hasP('flow_rate') || $hasP('flow_rate_signal'))
<div class="space-y-3">
    <div class="flex items-center gap-2 text-md font-semibold text-slate-700">
        <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-cyan-100 text-cyan-600">
            <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5"><path d="M12 21c-3.3 0-6-2.6-6-5.9C6 10.8 12 4 12 4s6 6.8 6 11.1C18 18.4 15.3 21 12 21Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </span>
        Flow Meter
    </div>

    @if ($hasP('flow_rate'))
    <a href="{{ $linkP('flow_rate') }}"
        class="group block overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-br from-cyan-50/80 to-white shadow-sm transition-all hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md {{ $muted ? 'grayscale' : '' }}">
        <div class="flex items-center justify-between px-4 py-3">
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-cyan-700/70">Flow Rate</div>
                <div class="mt-0.5 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-900 {{ $muted ? 'opacity-60' : '' }}">{{ $valP('flow_rate') }}</span>
                    <span class="text-xs font-bold text-slate-400">{{ $unitP('flow_rate') ?: 'm³/h' }}</span>
                </div>
            </div>
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/70 ring-1 ring-cyan-100">
                <img src="{{ asset('icons/awlr/flow_rate.svg') }}" alt="Flow Rate" class="h-5 w-5 object-contain {{ $iconClass ?? '' }}">
            </span>
        </div>
    </a>
    @endif

    @if ($hasP('flow_rate_signal'))
    <a href="{{ $linkP('flow_rate_signal') }}"
        class="block rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition-all hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md {{ $muted ? 'grayscale' : '' }}">
        <div class="flex items-center justify-between">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Flow Rate Signal</div>
            <div class="flex items-baseline gap-1">
                <span class="text-base font-extrabold text-slate-900 {{ $muted ? 'opacity-60' : '' }}">{{ $valP('flow_rate_signal') }}</span>
                <span class="text-[11px] font-bold text-slate-400">{{ $unitP('flow_rate_signal') ?: '%' }}</span>
            </div>
        </div>
        @if ($sigPct !== null)
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-sky-500" style="width: {{ $sigPct }}%"></div>
        </div>
        @endif
    </a>
    @endif
</div>
@endif
