@extends('layouts.app')

@section('content')
    {{-- CSS kritis ditaruh di atas konten (bukan di @push) supaya aktif sejak paint
         pertama -> tidak ada FOUC (label pin numpuk / stage belum ke-fit). --}}
    <style>
        /* Stage disembunyikan dulu, muncul fade-in setelah gambar siap -> tanpa blink */
        #pipa-stage { opacity: 0; transition: opacity .35s ease; }
        #pipa-stage.ready { opacity: 1; }
        /* Spinner kecil selama gambar dimuat */
        #pipa-loading {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            z-index: 5; pointer-events: none; transition: opacity .3s ease;
        }
        #pipa-loading.hide { opacity: 0; }
        #pipa-loading .spin {
            width: 34px; height: 34px; border-radius: 9999px;
            border: 3px solid #e2e8f0; border-top-color: #303481; animation: pipa-spin .8s linear infinite;
        }
        @keyframes pipa-spin { to { transform: rotate(360deg); } }

        /* ===== Pin marker (default offline; online/reservoir via aturan di bawah) ===== */
        .pipa-pin {
            position: absolute;
            transform: translate(-50%, -100%);   /* ujung teardrop tepat di titik koordinat */
            width: 26px;
            height: 31.6px;                       /* rasio 112:136 */
            background: url('{{ asset('marker_pipa_offline.svg') }}') no-repeat center / contain;
            border: none;
            padding: 0;
            cursor: pointer;
            z-index: 10;
            transition: transform .12s ease;
        }
        .pipa-pin:hover { transform: translate(-50%, -100%) scale(1.18); z-index: 15; }
        .pipa-pin:focus-visible { outline: 2px solid #303481; outline-offset: 2px; }

        /* Marker mengikuti STATUS logger (seragam untuk semua skema):
           online -> hijau, selain itu (offline / tak diketahui) -> hitam (default).
           Reservoir dikecualikan karena punya penanganan sendiri. */
        .pipa-pin:not(.pipa-pin--reservoir)[data-status="normal"],
        .pipa-pin:not(.pipa-pin--reservoir)[data-status="online"] {
            background-image: url('{{ asset('marker_pipa_online.svg') }}');
        }

        /* Reservoir: MARKER disembunyikan (tank sudah ada di gambar) + tanpa callout,
           tapi NAMA/label tetap tampil. Di mode Kelola marker muncul lagi (bisa diedit). */
        .pipa-pin--reservoir { background: none; cursor: default; pointer-events: none; }
        .pipa-pin--reservoir:hover { transform: translate(-50%, -100%); }
        #pipa-viewport.managing .pipa-pin--reservoir {
            background: url('{{ asset('marker_pipa_reservoir.svg') }}') no-repeat center / contain;
            cursor: pointer; pointer-events: auto; opacity: .8;
        }

        .pipa-pin__label {
            position: absolute;
            top: calc(100% + 3px);
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 10px;
            font-weight: 800;
            line-height: 1;
            color: #1e293b;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(148, 163, 184, .6);
            border-radius: 6px;
            padding: 2px 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .15);
            pointer-events: none;
        }
        #pipa-stage.hide-labels .pipa-pin__label { display: none; }

        #pipa-viewport.calib { cursor: crosshair; }
        #pipa-viewport.calib .pipa-pin { outline: 2px dashed #303481; outline-offset: 2px; }
        /* Mode kelola titik */
        #pipa-viewport.managing { cursor: crosshair; }
        #pipa-viewport.managing .pipa-pin { cursor: pointer; outline: 2px dashed #303481; outline-offset: 3px; }

        /* ===== Callout popup (kotak data tanpa card putih, dekat pin) ===== */
        /* Posisi (left/top/transform) diatur sepenuhnya oleh JS positionCallout()
           supaya kotak selalu utuh di dalam viewport. */
        #pin-callout {
            position: absolute; z-index: 55;
        }
        #pin-callout.hidden { display: none; }

        .pc-box {
            position: relative;
            min-width: 280px;
            max-width: 340px;
            overflow: hidden;
            border: 1px solid rgba(48, 52, 129, .24);
            border-left: 5px solid #303481;
            border-radius: 8px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 16px 34px rgba(15, 23, 42, .18), 0 3px 10px rgba(48, 52, 129, .12);
            backdrop-filter: blur(10px);
        }
        .pc-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 16px 11px;
            border-bottom: 1px solid rgba(148, 163, 184, .22);
            background: linear-gradient(180deg, rgba(238, 240, 251, .84), rgba(255, 255, 255, .96));
        }
        .pc-eyebrow {
            font-size: 10px;
            font-weight: 500;
            line-height: 1;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #64748b;
        }
        .pc-title {
            margin-top: 4px;
            max-width: 200px;
            overflow: hidden;
            color: #0f172a;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.18;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pc-header-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .pc-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 10px;
            font-weight: 500;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .pc-status.offline {
            background: #f1f5f9;
            color: #64748b;
        }
        /* Sumber data tidak jelas (mis. logger sudah dihapus) -> jangan tampil hijau. */
        .pc-status.unknown {
            background: #fef3c7;
            color: #b45309;
        }
        .pc-status__dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
            box-shadow: 0 0 0 3px color-mix(in srgb, currentColor 14%, transparent);
        }
        .pc-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            padding: 0;
            border: 1px solid rgba(148, 163, 184, .45);
            border-radius: 999px;
            background: #fff;
            color: #475569;
            cursor: pointer;
        }
        .pc-close svg { display: block; }
        .pc-close:hover { background: #f8fafc; color: #0f172a; }
        .pc-body { padding: 6px 16px; }
        .pc-metric {
            display: grid;
            grid-template-columns: minmax(78px, 1fr) auto;
            align-items: baseline;
            gap: 12px;
            padding: 9px 0;
            white-space: nowrap;
        }
        .pc-metric + .pc-metric { border-top: 1px solid rgba(226, 232, 240, .95); }
        .pc-metric__label {
            overflow: hidden;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.2;
            text-overflow: ellipsis;
        }
        .pc-metric__reading {
            display: inline-flex;
            align-items: baseline;
            justify-content: flex-end;
            gap: 4px;
            color: #0f172a;
        }
        .pc-metric__value {
            color: #0f2c66;
            font-size: 16px;
            font-weight: 500;
            line-height: 1;
        }
        .pc-metric__unit {
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
        }
        .pc-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 16px;
            border-top: 1px solid rgba(148, 163, 184, .22);
            background: #f8fafc;
        }
        .pc-updated {
            min-width: 0;
            overflow: hidden;
            color: #64748b;
            font-size: 11px;
            font-weight: 500;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pc-detail {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 24px;
            flex: 0 0 auto;
            border: 1px solid rgba(48, 52, 129, .26);
            border-radius: 7px;
            background: #eef0f9;
            color: #303481;
            text-decoration: none;
            transition: background .12s, color .12s;
        }
        .pc-detail:hover { background: #e2e5f4; color: #10134B; }

        /* ===== Panel Edit/Tambah Titik (senada callout, aksen brand navy #303481) ===== */
        #point-editor {
            width: 300px;
            border: 1px solid rgba(48, 52, 129, .22);
            border-left: 5px solid #303481;
            border-radius: 12px;
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 18px 40px rgba(15, 23, 42, .20), 0 3px 10px rgba(48, 52, 129, .10);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        /* Muncul halus: geser masuk dari kanan + fade + sedikit skala,
           dijangkar di pojok kanan-atas (bukan pop mendadak). */
        #point-editor:not(.hidden) {
            transform-origin: top right;
            animation: pe-appear .2s cubic-bezier(.16, .84, .44, 1) both;
        }
        @keyframes pe-appear {
            from { opacity: 0; transform: translate(12px, -6px) scale(.96); }
            to   { opacity: 1; transform: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            #point-editor:not(.hidden) { animation: none; }
        }
        .pe-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(148, 163, 184, .22);
            background: linear-gradient(180deg, rgba(238, 240, 251, .9), rgba(255, 255, 255, .96));
        }
        .pe-eyebrow {
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #303481;
        }
        .pe-title {
            margin-top: 4px;
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.1;
        }
        .pe-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            flex: 0 0 auto;
            padding: 0;
            border: 1px solid rgba(148, 163, 184, .45);
            border-radius: 999px;
            background: #fff;
            color: #475569;
            cursor: pointer;
        }
        .pe-close:hover { background: #f8fafc; color: #0f172a; }
        .pe-close svg { display: block; }

        .pe-body { padding: 14px 16px; display: flex; flex-direction: column; gap: 12px; }
        .pe-field { display: flex; flex-direction: column; gap: 5px; }
        .pe-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .pe-label-txt {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }
        .pe-input, .pe-select {
            width: 100%;
            height: 38px;
            padding: 0 11px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            background: #fff;
            color: #0f172a;
            font-size: 13px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .pe-input::placeholder { color: #9aa6bb; }
        .pe-select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }
        .pe-input:focus, .pe-select:focus {
            outline: none;
            border-color: #5257a3;
            box-shadow: 0 0 0 3px rgba(48, 52, 129, .18);
        }
        .pe-hint { font-size: 10px; line-height: 1.35; color: #94a3b8; }

        .pe-posbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 11px;
            border: 1px solid rgba(148, 163, 184, .28);
            border-radius: 9px;
            background: #f8fafc;
            font-size: 11px;
            color: #475569;
        }
        .pe-pos-ico { display: inline-flex; align-items: center; gap: 6px; min-width: 0; }
        .pe-pos-val { font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums; }
        .pe-move {
            flex: 0 0 auto;
            border: 1px solid rgba(48, 52, 129, .35);
            border-radius: 7px;
            background: #eef0f9;
            color: #303481;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 9px;
            cursor: pointer;
            transition: background .15s ease;
        }
        .pe-move:hover { background: #e2e5f4; }

        .pe-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid rgba(148, 163, 184, .22);
            background: #f8fafc;
        }
        .pe-btn {
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 14px;
            cursor: pointer;
            transition: background .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .pe-btn-danger { margin-right: auto; border: 1px solid #fecaca; background: #fff; color: #dc2626; }
        .pe-btn-danger:hover { background: #fef2f2; }
        .pe-btn-ghost { margin-left: auto; border: 1px solid #d1d5db; background: #fff; color: #475569; }
        .pe-btn-ghost:hover { background: #f1f5f9; }
        .pe-btn-primary {
            border: 1px solid #303481;
            background: #303481;
            color: #fff;
            box-shadow: 0 8px 16px -8px rgba(48, 52, 129, .7);
        }
        .pe-btn-primary:hover { background: #10134B; }

        /* ===== Legenda / ringkasan status (kiri atas, di bawah tombol skema), tema navy ===== */
        #pipa-legend {
            min-width: 194px;
            border-left: 4px solid #303481;
        }
        .pl-header {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 10px 15px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #303481;
            background: linear-gradient(180deg, rgba(238, 240, 251, .92), rgba(255, 255, 255, .96));
            border-bottom: 1px solid rgba(148, 163, 184, .22);
        }
        .pl-live {
            width: 7px;
            height: 7px;
            flex: 0 0 auto;
            border-radius: 999px;
            background: #3EB51F;
            animation: pl-pulse 2.2s infinite;
        }
        @keyframes pl-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(62, 181, 31, .45); }
            70%  { box-shadow: 0 0 0 6px rgba(62, 181, 31, 0); }
            100% { box-shadow: 0 0 0 0 rgba(62, 181, 31, 0); }
        }
        .pl-body { padding: 9px 15px 11px; display: flex; flex-direction: column; }
        .legend-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 4px 0;
        }
        .legend-name {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }
        .legend-dot {
            width: 12px;
            height: 12px;
            flex: 0 0 auto;
            border-radius: 999px;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, .85), 0 1px 2px rgba(0, 0, 0, .28);
        }
        .legend-count {
            font-size: 15px;
            font-weight: 800;
            color: #303481;
            font-variant-numeric: tabular-nums;
            min-width: 18px;
            text-align: right;
        }
        .pl-total {
            margin-top: 3px;
            padding-top: 6px;
            border-top: 1px solid rgba(226, 232, 240, .95);
        }
        /* Baris agregat (tanpa titik warna) diselaraskan dengan baris ber-titik. */
        .pl-name-nodot { padding-left: 20px; color: #64748b; }
        .pl-unit { font-size: 10px; font-weight: 600; color: #94a3b8; margin-left: 3px; }
        /* Bar persentase online */
        .pl-bar-wrap {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 9px;
            padding-top: 10px;
            border-top: 1px solid rgba(226, 232, 240, .95);
        }
        .pl-bar {
            flex: 1;
            height: 7px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .pl-bar-fill {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #3EB51F, #2f9e18);
            transition: width .35s ease;
        }
        .pl-bar-label { flex: 0 0 auto; font-size: 11px; font-weight: 600; color: #64748b; white-space: nowrap; }
        .pl-bar-label b { color: #303481; font-weight: 800; font-size: 12px; }
    </style>

    <div class="relative w-full h-[calc(100vh-4rem)] overflow-hidden bg-white select-none">

        {{-- ===== Header kiri-atas: pemilih skema + ringkasan status ===== --}}
        @php
            $__pts = collect($scheme['points'] ?? []);
            $__loggers = $__pts->where('kind', '!=', 'reservoir');
            $__online = $__loggers->where('status', 'normal')->count();
            $__offline = $__loggers->count() - $__online;
            $__total = $__loggers->count();
            $__debit = (float) $__loggers->sum('flowrate');
            $__pct = $__total > 0 ? (int) round($__online / $__total * 100) : 0;
        @endphp
        <div class="absolute top-2 left-4 z-40 flex flex-col items-start gap-6">
            <div class="inline-flex rounded-xl bg-white/90 backdrop-blur border border-slate-200 shadow-lg p-1">
                @foreach ($schemeList as $s)
                    @if ($s['available'])
                        <a href="{{ route('skema-pipa', $s['key']) }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition
                                {{ $s['key'] === $schemeKey ? 'bg-[#303481] text-white shadow' : 'text-slate-600 hover:bg-slate-100' }}">
                            {{ $s['name'] }}
                        </a>
                    @else
                        <span title="Artwork belum tersedia"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 cursor-not-allowed">
                            {{ $s['name'] }}
                            <span class="ml-1 text-[8px] uppercase tracking-wide">soon</span>
                        </span>
                    @endif
                @endforeach
            </div>

            <div id="pipa-legend"
                class="rounded-xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg overflow-hidden">
                <div class="pl-header"><span class="pl-live"></span>Status Logger</div>
                <div class="pl-body">
                    <div class="legend-row">
                        <span class="legend-name"><span class="legend-dot" style="background:#3EB51F"></span>Online</span>
                        <span id="pl-online" class="legend-count">{{ $__online }}</span>
                    </div>
                    <div class="legend-row">
                        <span class="legend-name"><span class="legend-dot" style="background:#111827"></span>Offline</span>
                        <span id="pl-offline" class="legend-count">{{ $__offline }}</span>
                    </div>
                    <div class="legend-row pl-total">
                        <span class="legend-name pl-name-nodot">Total logger</span>
                        <span id="pl-total" class="legend-count">{{ $__total }}</span>
                    </div>
                    <div class="legend-row">
                        <span class="legend-name pl-name-nodot">Total debit</span>
                        <span class="legend-count"><b id="pl-debit">{{ number_format($__debit, 1, '.', '') }}</b><span class="pl-unit">l/s</span></span>
                    </div>
                    <div class="pl-bar-wrap">
                        <div class="pl-bar"><span id="pl-bar-fill" class="pl-bar-fill" style="width: {{ $__pct }}%"></span></div>
                        <div class="pl-bar-label"><b id="pl-pct">{{ $__pct }}</b>% online</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Kontrol kanan atas ===== --}}
        <div class="absolute top-2 right-4 z-40 flex items-center gap-2">
            <button type="button" id="btn-reset"
                class="rounded-lg bg-white/90 backdrop-blur border border-slate-200 shadow px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                Reset Tampilan
            </button>
            @if (!empty($canManage))
                <button type="button" id="btn-manage"
                    class="rounded-lg bg-white/90 backdrop-blur border border-slate-200 shadow px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    Kelola Titik: <span id="manage-state">Off</span>
                </button>
            @endif
        </div>


        {{-- ===== Viewport panzoom ===== --}}
        <div id="pipa-viewport" class="absolute inset-0 overflow-hidden cursor-grab active:cursor-grabbing">
            @if ($scheme['available'])
                <div id="pipa-stage" class="relative origin-top-left"
                    style="width: 1400px; aspect-ratio: {{ $scheme['art_width'] }} / {{ $scheme['art_height'] }};">

                    {{-- Layer paling bawah (opsional): garis/jalan --}}
                    @if (!empty($scheme['layers']['under']))
                        <img id="layer-under" src="{{ asset($scheme['layers']['under']) }}" alt="Garis {{ $scheme['name'] }}"
                            class="absolute inset-0 w-full h-full block pointer-events-none" draggable="false" />
                    @endif

                    {{-- Layer tengah: jaringan pipa (transparan) --}}
                    <img id="layer-base" src="{{ asset($scheme['layers']['base']) }}" alt="Jaringan Pipa {{ $scheme['name'] }}"
                        class="absolute inset-0 w-full h-full block pointer-events-none" draggable="false" />

                    {{-- Layer atas (DI DEPAN pipa): bangunan + pohon (solid, latar transparan,
                         bisa dimatikan). detail_offset menggeser posisinya (persen). --}}
                    @if (!empty($scheme['layers']['detail']))
                        @php $doff = $scheme['detail_offset'] ?? ['x' => 0, 'y' => 0]; @endphp
                        <img id="layer-detail" src="{{ asset($scheme['layers']['detail']) }}" alt="Detail bangunan {{ $scheme['name'] }}"
                            class="absolute inset-0 w-full h-full block pointer-events-none" draggable="false"
                            style="transform: translate({{ $doff['x'] }}%, {{ $doff['y'] }}%);" />
                    @endif

                    {{-- Pin overlay yang bisa diklik --}}
                    @foreach ($scheme['points'] as $p)
                        <button type="button"
                            class="pipa-pin pipa-pin--{{ $p['kind'] }}"
                            data-id="{{ $p['id'] }}"
                            data-status="{{ $p['status'] ?? '' }}"
                            style="left: {{ $p['x'] }}%; top: {{ $p['y'] }}%;"
                            aria-label="{{ $p['label'] }}">
                            <span class="pipa-pin__label">{{ $p['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Spinner selama gambar dimuat (dihilangkan setelah siap) --}}
                <div id="pipa-loading"><div class="spin"></div></div>
            @else
                <div class="flex h-full items-center justify-center text-slate-400 text-sm">
                    Artwork skema ini belum tersedia.
                </div>
            @endif
        </div>

        {{-- ===== Callout popup (muncul dekat pin saat diklik) ===== --}}
        <div id="pin-callout" class="hidden">
            <div class="pc-box">
                <div class="pc-header">
                    <div>
                        <div class="pc-eyebrow">Telemetry Point</div>
                        <div id="pc-title" class="pc-title">-</div>
                    </div>
                    <div class="pc-header-actions">
                        <span id="pc-status" class="pc-status"><span class="pc-status__dot"></span><span id="pc-status-text">-</span></span>
                        <button type="button" id="pc-close" class="pc-close" title="Tutup" aria-label="Tutup callout">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round">
                                <path d="M6 6l12 12M18 6L6 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="pc-body">
                    <div id="pc-pressure-rows"></div>
                    <div class="pc-metric">
                        <span class="pc-metric__label">Flowrate</span>
                        <span class="pc-metric__reading"><b id="pc-flow" class="pc-metric__value">0.00</b><span class="pc-metric__unit">l/s</span></span>
                    </div>
                    <div class="pc-metric">
                        <span class="pc-metric__label">Totalizer</span>
                        <span class="pc-metric__reading"><b id="pc-total" class="pc-metric__value">0</b><span class="pc-metric__unit">m³</span></span>
                    </div>
                </div>
                <div class="pc-footer">
                    <span id="pc-updated" class="pc-updated">Updated -</span>
                    <a id="pc-detail" class="pc-detail" title="Buka detail logger" aria-label="Buka detail logger" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M7 17L17 7"/>
                            <path d="M9 7h8v8"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Toast kalibrasi --}}
        <div id="calib-toast"
            class="hidden absolute bottom-4 left-1/2 -translate-x-1/2 z-50 rounded-lg bg-slate-900 text-white text-xs font-mono px-4 py-2 shadow-xl">
        </div>

        {{-- Hint mode kelola --}}
        <div id="manage-hint"
            class="hidden absolute bottom-4 left-1/2 -translate-x-1/2 z-40 rounded-full bg-[#303481] text-white text-xs font-semibold px-4 py-2 shadow-lg">
            Mode kelola: klik peta untuk <b>tambah titik</b>, klik pin untuk <b>edit</b>.
        </div>

        {{-- ===== Form editor titik ===== --}}
        <div id="point-editor" style="z-index:60"
            class="hidden absolute top-20 right-4 max-w-[85vw]">
            <div class="pe-header">
                <div>
                    <div class="pe-eyebrow">Kelola Titik</div>
                    <div id="pe-heading" class="pe-title">Tambah Titik</div>
                </div>
                <button type="button" id="pe-close" class="pe-close" title="Tutup" aria-label="Tutup editor">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <div class="pe-body">
                <div class="pe-field">
                    <label class="pe-label-txt" for="pe-label">Label</label>
                    <input id="pe-label" type="text" class="pe-input" placeholder="mis. DMA 20 INLET" />
                </div>

                <div class="pe-grid2">
                    <div class="pe-field">
                        <label class="pe-label-txt" for="pe-kind">Jenis</label>
                        <select id="pe-kind" class="pe-select">
                            {{-- Seragam semua skema: marker mengikuti status logger (online/offline),
                                 jadi jenis cukup Logger vs Reservoir. --}}
                            <option value="outlet">Logger</option>
                            <option value="reservoir">Reservoir</option>
                        </select>
                    </div>
                    <div class="pe-field">
                        <label class="pe-label-txt" for="pe-pressure-display">Pressure</label>
                        <select id="pe-pressure-display" class="pe-select">
                            <option value="auto">Auto</option>
                            <option value="pressure_1">Pressure 1 saja</option>
                            <option value="pressure_2">Pressure 2 saja</option>
                            <option value="both">Pressure 1 &amp; 2</option>
                        </select>
                    </div>
                </div>
                <p class="pe-hint -mt-1">Auto menampilkan dua pressure kalau Pressure 2 ada nilainya.</p>

                <div class="pe-field">
                    <label class="pe-label-txt" for="pe-logger">Logger (sumber data)</label>
                    <select id="pe-logger" class="pe-select">
                        <option value="">— tanpa logger (data manual) —</option>
                    </select>
                    <p class="pe-hint">Kosongkan label untuk ikut nama logger.</p>
                </div>

                <div class="pe-posbar">
                    <span class="pe-pos-ico">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21s-7-6.3-7-11a7 7 0 1 1 14 0c0 4.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>
                        </svg>
                        <span>Posisi <b id="pe-x" class="pe-pos-val">-</b>%, <b id="pe-y" class="pe-pos-val">-</b>%</span>
                    </span>
                    <button type="button" id="pe-move" class="pe-move">Pindah posisi</button>
                </div>
            </div>

            <div class="pe-footer">
                <button type="button" id="pe-delete" class="pe-btn pe-btn-danger hidden">Hapus</button>
                <button type="button" id="pe-cancel" class="pe-btn pe-btn-ghost">Batal</button>
                <button type="button" id="pe-save" class="pe-btn pe-btn-primary">Simpan</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- panzoom di-host lokal (bukan CDN) supaya halaman tetap jalan tanpa
         akses internet keluar / saat CDN down. Lihat public/js/panzoom.min.js. --}}
    <script src="{{ asset('js/panzoom.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const points = @json($scheme['points'] ?? []);
            const SCHEME = @json($schemeKey);
            const CSRF = @json(csrf_token());
            const viewport = document.getElementById('pipa-viewport');
            const stage = document.getElementById('pipa-stage');
            if (!stage) return; // skema belum tersedia

            // Skala "fit": seluruh skema muat penuh di viewport (dengan sedikit margin).
            function computeFitScale() {
                const cw = viewport.clientWidth, ch = viewport.clientHeight;
                const sw = stage.offsetWidth, sh = stage.offsetHeight;
                if (!sw || !sh) return 0.2;
                return Math.min(cw / sw, ch / sh) * 0.95;
            }

            // ===== panzoom =====
            // minZoom = skala fit -> Reset Tampilan = posisi paling zoom-out,
            // tidak bisa mengecil lebih jauh dari itu.
            let fitScale = computeFitScale();

            // panzoom di-host lokal; tapi kalau toh gagal dimuat/inisialisasi,
            // jangan sampai seluruh interaksi mati. pz=null -> peta statis
            // (di-fit via CSS transform), pin TETAP bisa diklik.
            let pz = null;
            if (typeof panzoom === 'function') {
                try {
                    pz = panzoom(stage, {
                        maxZoom: 8,
                        minZoom: fitScale,
                        zoomDoubleClickSpeed: 1,
                        smoothScroll: false,
                    });
                } catch (err) {
                    console.error('panzoom gagal inisialisasi, peta ditampilkan statis.', err);
                }
            } else {
                console.warn('panzoom tidak tersedia — peta ditampilkan statis (pin tetap aktif).');
            }

            function fitView() {
                const cw = viewport.clientWidth, ch = viewport.clientHeight;
                const sw = stage.offsetWidth, sh = stage.offsetHeight;
                if (!sw || !sh) return;
                fitScale = computeFitScale();
                const x = (cw - sw * fitScale) / 2;
                const y = (ch - sh * fitScale) / 2;
                if (pz) {
                    if (pz.setMinZoom) pz.setMinZoom(fitScale); // ikuti perubahan ukuran layar
                    pz.zoomAbs(0, 0, fitScale);
                    pz.moveTo(x, y);
                } else {
                    // Fallback statis: fit lewat CSS (origin sudah top-left via class).
                    stage.style.transform = `translate(${x}px, ${y}px) scale(${fitScale})`;
                }
            }
            // Hitung posisi awal langsung (stage masih tersembunyi via CSS opacity:0)
            fitView();
            window.addEventListener('resize', fitView);
            document.getElementById('btn-reset').addEventListener('click', fitView);

            // Reveal halus: tunggu SEMUA gambar layer termuat, fit ulang, lalu fade-in.
            const loadingEl = document.getElementById('pipa-loading');
            const layerImgs = Array.from(stage.querySelectorAll('img'));
            const waitImg = im => (im.complete && im.naturalWidth)
                ? Promise.resolve()
                : new Promise(res => {
                    im.addEventListener('load', res, { once: true });
                    im.addEventListener('error', res, { once: true });
                });
            let revealed = false;
            const reveal = () => {
                if (revealed) return;
                revealed = true;
                fitView();
                stage.classList.add('ready');
                loadingEl && loadingEl.classList.add('hide');
            };
            Promise.all(layerImgs.map(waitImg)).then(reveal);
            // Pengaman: kalau ada gambar yang gagal/lama, tetap tampilkan setelah 4 dtk
            setTimeout(reveal, 4000);

            // ===== Callout popup (kartu data dekat pin) =====
            const byId = id => document.getElementById(id);
            const fmt = n => Number(n).toLocaleString('id-ID');
            const callout = byId('pin-callout');
            // Penanda "tidak ada data" -> dibedakan dari pembacaan asli bernilai 0.
            const NODATA = '–'; // en dash
            const num = v => (v == null ? NODATA : Number(v).toFixed(2));

            function hideCallout() { callout.classList.add('hidden'); }

            function addMetricRow(parent, label, value, unit) {
                const row = document.createElement('div');
                row.className = 'pc-metric';

                const labelEl = document.createElement('span');
                labelEl.className = 'pc-metric__label';
                labelEl.textContent = label;
                row.appendChild(labelEl);

                const reading = document.createElement('span');
                reading.className = 'pc-metric__reading';

                const strong = document.createElement('b');
                strong.className = 'pc-metric__value';
                strong.textContent = value;
                reading.appendChild(strong);

                const unitEl = document.createElement('span');
                unitEl.className = 'pc-metric__unit';
                unitEl.textContent = unit;
                reading.appendChild(unitEl);

                row.appendChild(reading);
                parent.appendChild(row);
            }

            function renderCalloutHeader(p) {
                byId('pc-title').textContent = p.label || p.logger_name || 'Titik Pipa';

                // Tiga status, bukan dua: status kosong TIDAK dianggap Online
                // (mis. logger sudah dihapus -> sumber data tidak jelas).
                const status = (p.status || 'unknown').toString().toLowerCase();
                const statusEl = byId('pc-status');
                statusEl.classList.remove('offline', 'unknown');
                let statusText = 'Online';
                if (status === 'offline') {
                    statusEl.classList.add('offline');
                    statusText = 'Offline';
                } else if (status !== 'normal' && status !== 'online') {
                    statusEl.classList.add('unknown');
                    statusText = 'Tak diketahui';
                }
                byId('pc-status-text').textContent = statusText;

                byId('pc-updated').textContent = p.updated_at ? ('Updated ' + p.updated_at) : 'Updated -';
            }

            function renderPressureRows(p) {
                const target = byId('pc-pressure-rows');
                target.textContent = '';

                const display = ['auto', 'pressure_1', 'pressure_2', 'both'].includes(p.pressure_display)
                    ? p.pressure_display
                    : 'auto';
                const pressure1 = p.pressure_1 ?? p.pressure;
                const pressure2 = p.pressure_2;
                const hasPressure2 = pressure2 !== null && pressure2 !== undefined;

                if (display === 'pressure_1') {
                    addMetricRow(target, 'Pressure 1', num(pressure1), 'bar');
                    return;
                }

                if (display === 'pressure_2') {
                    addMetricRow(target, 'Pressure 2', num(pressure2), 'bar');
                    return;
                }

                if (display === 'both' || hasPressure2) {
                    addMetricRow(target, 'Pressure 1', num(pressure1), 'bar');
                    addMetricRow(target, 'Pressure 2', num(pressure2), 'bar');
                    return;
                }

                addMetricRow(target, 'Pressure', num(pressure1), 'bar');
            }

            function showCallout(p, pinEl) {
                renderCalloutHeader(p);
                renderPressureRows(p);
                byId('pc-flow').textContent  = num(p.flowrate);
                byId('pc-total').textContent = p.totalizer != null ? fmt(p.totalizer) : NODATA;

                const det = byId('pc-detail');
                if (p.logger_id) { det.href = '/analisa/' + p.logger_id; det.style.display = ''; }
                else det.style.display = 'none';

                // Tampilkan dulu supaya ukuran kotak bisa diukur, baru diposisikan.
                callout.classList.remove('hidden', 'below');
                positionCallout(pinEl);
            }

            // Posisikan callout supaya SELALU utuh di dalam viewport: default di atas
            // pin; kalau ruang atas kurang -> pindah ke bawah pin; sisi kiri/kanan
            // di-klamp agar tidak terpotong tepi. Mengukur tinggi kotak yang
            // sebenarnya (jumlah baris pressure bisa 1 atau 2).
            function positionCallout(pinEl) {
                const vr = viewport.getBoundingClientRect();
                const pr = pinEl.getBoundingClientRect();
                const cw = callout.offsetWidth;
                const ch = callout.offsetHeight;
                const gap = 12;      // jarak kotak <-> pin
                const margin = 10;   // jarak minimum dari tepi viewport

                // Horizontal: pusatkan ke pin, lalu klamp agar tidak keluar tepi.
                const anchorX = pr.left - vr.left + pr.width / 2;
                let left = anchorX - cw / 2;
                left = Math.max(margin, Math.min(vr.width - margin - cw, left));

                // Vertikal: coba di atas pin; kalau tidak muat, taruh di bawah pin.
                const pinTop = pr.top - vr.top;
                const pinBottom = pr.bottom - vr.top;
                let top = pinTop - gap - ch;
                if (top < margin) {
                    top = pinBottom + gap;
                }
                // Klamp akhir supaya tetap di dalam viewport.
                top = Math.max(margin, Math.min(vr.height - margin - ch, top));

                callout.style.transform = 'none'; // kontrol penuh lewat left/top
                callout.style.left = left + 'px';
                callout.style.top  = top + 'px';
            }
            byId('pc-close').addEventListener('click', hideCallout);

            // ===== Manajemen pin (dinamis) =====
            const pointById = Object.fromEntries(points.map(p => [p.id, p]));
            const pinEls = {};

            // Ringkasan status di legenda (dihitung ulang saat titik berubah).
            function updateLegend() {
                const vals = Object.values(pointById);
                const loggers = vals.filter(p => p.kind !== 'reservoir');
                const total = loggers.length;
                const online = loggers.filter(p => (p.status || '') === 'normal').length;
                const debit = loggers.reduce((s, p) => s + (Number(p.flowrate) || 0), 0);
                const pct = total ? Math.round(online / total * 100) : 0;
                const set = (id, v) => { const el = byId(id); if (el) el.textContent = v; };
                set('pl-online', online);
                set('pl-offline', total - online);
                set('pl-total', total);
                set('pl-debit', debit.toFixed(1));
                set('pl-pct', pct);
                const bar = byId('pl-bar-fill');
                if (bar) bar.style.width = pct + '%';
            }

            function coordFromEvent(e) {
                const r = stage.getBoundingClientRect();
                return {
                    x: Math.min(100, Math.max(0, ((e.clientX - r.left) / r.width) * 100)),
                    y: Math.min(100, Math.max(0, ((e.clientY - r.top) / r.height) * 100)),
                };
            }

            function wirePin(btn) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (calibOn) return;
                    const p = pointById[btn.dataset.id];
                    if (!p) return;
                    if (managing) { openEditorEdit(p); return; }
                    if (p.kind === 'reservoir') return; // reservoir: tanpa callout
                    showCallout(p, btn);
                });
            }

            function upsertPin(p) {
                pointById[p.id] = p;
                let btn = pinEls[p.id];
                if (!btn) {
                    btn = document.createElement('button');
                    btn.type = 'button';
                    btn.dataset.id = p.id;
                    btn.innerHTML = '<span class="pipa-pin__label"></span>';
                    stage.appendChild(btn);
                    pinEls[p.id] = btn;
                    wirePin(btn);
                }
                btn.className = 'pipa-pin pipa-pin--' + p.kind;
                btn.dataset.status = p.status || '';
                btn.style.left = p.x + '%';
                btn.style.top = p.y + '%';
                btn.setAttribute('aria-label', p.label || '');
                btn.querySelector('.pipa-pin__label').textContent = p.label || '';
                updateLegend();
            }

            function removePin(id) {
                if (pinEls[id]) pinEls[id].remove();
                delete pinEls[id];
                delete pointById[id];
                updateLegend();
            }

            // Wire pin yang sudah dirender server
            stage.querySelectorAll('.pipa-pin').forEach(btn => {
                pinEls[btn.dataset.id] = btn;
                wirePin(btn);
            });

            // Tutup callout saat geser/zoom atau klik area kosong
            pz && pz.on && pz.on('panstart', hideCallout);
            pz && pz.on && pz.on('zoom', hideCallout);
            viewport.addEventListener('pointerdown', (e) => {
                if (!e.target.closest('#pin-callout') && !e.target.closest('.pipa-pin')) hideCallout();
            });

            // ===== Alt+klik = tampilkan koordinat (helper tersembunyi, tanpa tombol) =====
            const toast = document.getElementById('calib-toast');
            const calibOn = false;
            let toastTimer = null;

            function reportCoord(e) {
                const r = stage.getBoundingClientRect();
                const x = ((e.clientX - r.left) / r.width) * 100;
                const y = ((e.clientY - r.top) / r.height) * 100;
                const snippet = `'x' => ${x.toFixed(2)}, 'y' => ${y.toFixed(2)},`;
                toast.textContent = `x: ${x.toFixed(2)}%  ·  y: ${y.toFixed(2)}%  (tersalin)`;
                toast.classList.remove('hidden');
                console.log('[kalibrasi pipa]', snippet);
                if (navigator.clipboard) navigator.clipboard.writeText(snippet).catch(() => {});
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
            }

            viewport.addEventListener('click', (e) => {
                if (calibOn || e.altKey) {
                    e.preventDefault();
                    reportCoord(e);
                    return;
                }
                if (!managing) return;
                if (e.target.closest('.pipa-pin')) return; // klik pin -> edit (dihandle pin)
                const { x, y } = coordFromEvent(e);
                if (awaitingMove) {
                    editState.x = x; editState.y = y; awaitingMove = false;
                    showPos();
                    editor.classList.remove('hidden');
                } else {
                    openEditorAdd(x, y);
                }
            });

            // ===== Mode kelola titik (CRUD dinamis) =====
            const btnManage = byId('btn-manage');
            const manageState = byId('manage-state');
            const manageHint = byId('manage-hint');
            const editor = byId('point-editor');
            const peLabel = byId('pe-label'), peKind = byId('pe-kind'), pePressureDisplay = byId('pe-pressure-display'), peLogger = byId('pe-logger');
            const peX = byId('pe-x'), peY = byId('pe-y'), peHeading = byId('pe-heading'), peDelete = byId('pe-delete');
            let managing = false, loggersLoaded = false, awaitingMove = false;
            let editState = { mode: 'add', point_id: null, id: null, x: 0, y: 0 };

            async function loadLoggers() {
                if (loggersLoaded) return;
                try {
                    const res = await fetch('/api/skema-pipa/loggers', { headers: { 'Accept': 'application/json' } });
                    (await res.json()).forEach(l => {
                        const opt = document.createElement('option');
                        opt.value = l.id_logger;
                        opt.textContent = l.nama_logger + ' (' + l.id_logger + ')';
                        peLogger.appendChild(opt);
                    });
                    loggersLoaded = true;
                } catch (err) { console.error('gagal load logger', err); }
            }

            function showPos() { peX.textContent = editState.x.toFixed(2); peY.textContent = editState.y.toFixed(2); }
            function closeEditor() { editor.classList.add('hidden'); awaitingMove = false; }

            function setManaging(on) {
                managing = on;
                viewport.classList.toggle('managing', on);
                manageState.textContent = on ? 'On' : 'Off';
                manageHint.classList.toggle('hidden', !on);
                if (on) { hideCallout(); loadLoggers(); } else closeEditor();
            }
            // Tombol hanya dirender untuk admin Wosusokas; kalau tidak ada, mode
            // kelola tidak pernah aktif (endpoint CRUD juga sudah ditolak server).
            if (btnManage) btnManage.addEventListener('click', () => setManaging(!managing));

            function openEditorAdd(x, y) {
                editState = { mode: 'add', point_id: null, id: null, x, y };
                peHeading.textContent = 'Tambah Titik';
                peLabel.value = ''; peKind.value = 'outlet'; pePressureDisplay.value = 'auto'; peLogger.value = '';
                peDelete.classList.add('hidden');
                showPos(); editor.classList.remove('hidden');
            }
            function openEditorEdit(p) {
                editState = { mode: 'edit', point_id: p.point_id, id: p.id, x: +p.x, y: +p.y };
                peHeading.textContent = 'Edit Titik';
                peLabel.value = p.label || ''; peKind.value = p.kind || 'outlet'; pePressureDisplay.value = p.pressure_display || 'auto'; peLogger.value = p.logger_id || '';
                peDelete.classList.remove('hidden');
                showPos(); editor.classList.remove('hidden');
            }

            byId('pe-close').addEventListener('click', closeEditor);
            byId('pe-cancel').addEventListener('click', closeEditor);
            byId('pe-move').addEventListener('click', () => { awaitingMove = true; editor.classList.add('hidden'); });

            byId('pe-save').addEventListener('click', async () => {
                const payload = {
                    scheme: SCHEME,
                    label: peLabel.value.trim() || null,
                    kind: peKind.value,
                    pressure_display: pePressureDisplay.value || 'auto',
                    logger_id: peLogger.value || null,
                    x: editState.x, y: editState.y,
                };
                const isEdit = editState.mode === 'edit';
                const url = isEdit ? ('/api/skema-pipa/points/' + editState.point_id) : '/api/skema-pipa/points';
                try {
                    const res = await fetch(url, {
                        method: isEdit ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(payload),
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        alert('Gagal menyimpan: ' + (err.message || ('HTTP ' + res.status)));
                        return;
                    }
                    const data = await res.json();
                    if (data.ok && data.point) { upsertPin(data.point); closeEditor(); }
                } catch (err) { alert('Gagal menyimpan (jaringan).'); }
            });

            byId('pe-delete').addEventListener('click', async () => {
                if (editState.mode !== 'edit' || !editState.point_id) return;
                if (!confirm('Hapus titik ini?')) return;
                try {
                    const res = await fetch('/api/skema-pipa/points/' + editState.point_id, {
                        method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    });
                    const data = await res.json();
                    if (data.ok) { removePin(editState.id); closeEditor(); }
                } catch (err) { alert('Gagal menghapus.'); }
            });
        });
    </script>
@endpush
