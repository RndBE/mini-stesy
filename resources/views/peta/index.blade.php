@extends('layouts.app')
@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 100%;
            width: 100%;
            z-index: 10;
        }
.peta-wrapper {
            height: calc(100vh - 65px);
            height: calc(100dvh - 65px);
        }
#mobileLoggerToggleBtn {
            display: none;
        }
@media (max-width: 1023px) {
            .peta-wrapper {
                height: calc(100dvh - 65px);
                display: flex;
                flex-direction: column;
            }
        }

        .sidebar-item:hover {
            background-color: #f1f5f9;
            cursor: pointer;
        }

        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
.sidebar-panel {
            transition: width 0.3s ease, min-width 0.3s ease;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-panel.collapsed {
            width: 0 !important;
            min-width: 0 !important;
        }

        .sidebar-toggle-btn {
            position: absolute;
            top: 50%;
            left: -28px;
            transform: translateY(-50%);
            z-index: 500;
            width: 28px;
            height: 56px;
            background: white;
            border: 1px solid #e2e8f0;
            border-right: none;
            border-radius: 8px 0 0 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: -3px 0 8px rgba(0, 0, 0, 0.08);
            transition: background 0.2s;
        }

        .sidebar-toggle-btn:hover {
            background: #f1f5f9;
        }

        .sidebar-toggle-btn svg {
            transition: transform 0.3s ease;
        }

        .sidebar-toggle-btn.collapsed svg {
            transform: rotate(180deg);
        }
#petaSidebarBackdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 490;
        }
        #petaSidebarBackdrop.show {
            display: block;
        }
@media (max-width: 1023px) {
#petaSidebarBackdrop { display: none !important; }
.sidebar-toggle-btn { display: none !important; }
#petaSidebarWrapper {
                width: 100% !important;
                flex-shrink: 0;
                border-top: 1px solid #e2e8f0;
overflow: hidden;
                transition: max-height 0.35s cubic-bezier(0.4,0,0.2,1);
                max-height: 0;
            }
            #petaSidebarWrapper.mobile-expanded {
                max-height: 38vh;
            }
.sidebar-panel {
                position: static !important;
                transform: none !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                max-height: 38vh;
                border-left: none !important;
                box-shadow: none !important;
                white-space: normal !important;
                display: flex !important;
                flex-direction: column;
            }
.sidebar-panel.collapsed {
                width: 100% !important;
            }
#mobileLoggerToggleBtn {
                display: flex;
            }
        }

        .map-settings-btn {
            position: absolute;
            bottom: 12px;
            right: 12px;
            z-index: 1000;
            background: #303481;
            color: white;
            padding: 8px 16px;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            border: none;
            transition: all 0.2s, opacity 0.3s ease;
            opacity: 1;
        }

        .map-settings-btn:hover {
            background: #10134B;
        }

        @media (max-width: 1023px) {
            .map-settings-btn {
                bottom: auto;
                top: 12px;
                right: 12px;
                padding: 11px;
                z-index: 100;
background: rgba(0, 0, 0, 0.28);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                box-shadow: 0 2px 10px rgba(0,0,0,0.25);
                border: 1px solid rgba(255,255,255,0.22);
                color: white;
                border-radius: 12px;
            }
            .map-settings-btn:hover {
                background: rgba(0, 0, 0, 0.45);
            }
            .map-settings-btn .btn-label {
                display: none;
            }
            .map-settings-btn svg {
                margin-right: 0;
                width: 22px;
                height: 22px;
                stroke: white;
            }
#mobileLoggerToggleBtn {
                top: 12px;
                right: 68px;
                bottom: auto;
                padding: 11px;
            }
.map-settings-btn.behind-sidebar-overlay {
                z-index: 30 !important;
            }
        }

        .settings-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .settings-modal-overlay.show {
            display: flex;
        }

        .settings-modal {
            background: #fff;
            border-radius: 12px;
            width: min(1100px, 96vw);
            height: min(90vh, 900px);
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .modal-close {
            cursor: pointer;
            color: #64748b;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: #1e293b;
        }

        .modal-body {
            padding: 24px;
            overflow: auto;
            flex: 1;
        }

        .modal-body.modal-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 16px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .modal-body.modal-grid {
                grid-template-columns: 1fr;
            }
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }

        .modal-section {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .filter-group {
            margin-bottom: 16px;
        }

        .filter-category {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .filter-category input[type="checkbox"] {
            margin-right: 8px;
        }

        .filter-items {
            margin-left: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .filter-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #475569;
        }

        .filter-item input {
            margin-right: 6px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #475569;
            cursor: pointer;
        }

        .radio-item input {
            margin-right: 8px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }

        .btn-apply {
            background: #3730a3;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }

        .btn-apply:hover {
            background: #312e81;
        }
.leaflet-popup-content-wrapper {
            border-radius: 8px;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .leaflet-popup-content {
            margin: 0;
            width: 380px !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
@media (max-width: 1023px) {
            .leaflet-popup-content {
                width: min(90vw, 340px) !important;
            }
            .leaflet-popup-content-wrapper {
                max-width: 92vw;
            }
            .popup-header {
                padding: 14px 14px 12px 14px;
            }
            .popup-title {
                font-size: 14px;
            }
            .popup-body {
                padding: 10px 14px;
            }
            .popup-info-row {
                padding: 7px 0;
            }
            .popup-label,
            .popup-value {
                font-size: 12px;
            }
            .popup-buttons {
                padding: 0 14px 14px 14px;
                gap: 8px;
            }
            .popup-btn {
                font-size: 12px;
                padding: 8px 0;
            }
        }

        .leaflet-popup-close-button {
            display: none !important;
        }

        .popup-header {
            padding: 20px 20px 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .popup-title {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.4;
            flex: 1;
        }

        .popup-close {
            cursor: pointer;
            color: #64748b;
            font-size: 20px;
            line-height: 1;
            padding: 0;
            margin-left: 12px;
            transition: color 0.2s;
        }

        .popup-close:hover {
            color: #0f172a;
        }

        .popup-body {
            padding: 16px 20px;
        }

        .popup-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .popup-info-row:last-child {
            border-bottom: none;
        }

        .popup-label {
            font-size: 13px;
            font-weight: 400;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .popup-value {
            font-size: 13px;
            font-weight: 400;
            color: #0f172a;
            text-align: right;
        }

        .popup-buttons {
            display: flex;
            gap: 10px;
            padding: 0px 20px 20px 20px;
        }

        .popup-btn {
            flex: 1;

            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .popup-btn-outline {
            background: white;
            border: 1.5px solid #cbd5e1;
            color: #475569;
        }

        .popup-btn-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .popup-btn-solid {
            background: #3730a3;
            color: white;
        }

        .popup-btn-solid:hover {
            background: #312e81;
        }

        .status-online {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #10b981;
            font-weight: 400;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
        }
@keyframes marker-bounce {
            0%, 100% { transform: translateY(0);         animation-timing-function: cubic-bezier(0.8,0,1,1); }
            50%       { transform: translateY(-10px);     animation-timing-function: cubic-bezier(0,0,0.2,1); }
        }
        .marker-bounce {
            animation: marker-bounce 1.2s infinite;
            transform-origin: bottom center;
        }
    </style>
@endpush
@section('content')
    <div class="peta-wrapper w-full">
<div id="petaSidebarBackdrop" onclick="closePetaSidebar()"></div>
<div class="flex h-full w-full flex-col overflow-hidden bg-white shadow-sm ring-1 ring-slate-200 lg:flex-row"
            x-data="{
                sidebarOpen: window.innerWidth >= 1024,
                isMobile: window.innerWidth < 1024,
                init() {
                    this.sidebarOpen = window.innerWidth >= 1024;
                    this.isMobile    = window.innerWidth < 1024;
                },
                toggleMobileSidebar() {
                    const wrap = document.getElementById('petaSidebarWrapper');
                    if (!wrap) return;
                    wrap.classList.toggle('mobile-expanded');
                }
            }"
            @close-peta-sidebar.window="sidebarOpen = false">
            <div class="relative flex-1 min-h-0" id="mapContainer">
                <div id="map" class="h-full w-full"></div>
<button class="map-settings-btn rounded-xl" id="mapSettingsBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span class="btn-label">Pengaturan Peta</span>
                </button>
<button id="mobileLoggerToggleBtn"
                    @click="toggleMobileSidebar()"
                    class="map-settings-btn rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
            </div>
<div class="relative flex-shrink-0" id="petaSidebarWrapper">
<button
                    @click="
                        sidebarOpen = !sidebarOpen;
                        togglePetaSidebar(sidebarOpen);
                        const dur = 320, step = 16;
                        let t = 0;
                        const iv = setInterval(() => {
                            if(typeof map !== 'undefined') map.invalidateSize({animate: false});
                            t += step;
                            if(t >= dur) clearInterval(iv);
                        }, step);"
                    :class="!sidebarOpen ? 'collapsed' : ''" class="sidebar-toggle-btn"
                    :title="sidebarOpen ? 'Tutup sidebar' : 'Buka sidebar'">
<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
<div :class="sidebarOpen ? 'w-96' : 'w-0'"
                    class="sidebar-panel flex flex-col border-l border-slate-200 bg-white h-full">
<div class="flex items-center justify-between px-3 py-2">
                    <span class="text-lg font-semibold text-slate-900">Daftar Logger</span>
<button
                        @click="toggleMobileSidebar()"
                        class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                        title="Tutup daftar logger">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                    <div class="px-3 mb-2 pb-3 border-slate-200">
                        <div class="relative">
                            <input type="text" id="searchLogger" placeholder="Cari logger..."
                                class="w-full px-4 py-2 pl-10 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto sidebar-scroll px-3 pt-1" id="loggerList">
                        @forelse ($points as $point)
                            @php
                                $kat    = strtoupper($point['kategori'] ?? '');
                                $isOnline = ($point['status'] ?? '') === 'online';
                                $dotCls  = $isOnline ? 'bg-emerald-500' : 'bg-rose-500';
                                $txtCls  = $isOnline ? 'text-emerald-600' : 'text-rose-600';
                                $statusLabel = $isOnline ? 'Koneksi Terhubung' : 'Koneksi Terputus';
                                $fmt = function($v, $dec = 3) {
                                    if (!is_numeric($v)) return '-';
                                    $s = number_format((float)$v, $dec, '.', ',');
                                    if (str_contains($s, '.')) {
                                        [$int, $dec] = explode('.', $s);
                                        $dec = rtrim($dec, '0');
                                        return $dec !== '' ? $int . '.' . $dec : $int;
                                    }
                                    return $s;
                                };
                            @endphp

                            <div class="sidebar-item mb-4 rounded-lg bg-white pt-3 pb-2 px-3 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md"
                                data-kategori="{{ $point['kategori'] }}" data-status="{{ $point['status'] }}"
                                data-arr-state="{{ $point['arr_state'] ?? '' }}"
                                data-logger-name="{{ strtolower($point['nama_logger']) }}"
                                data-logger-id="{{ strtolower($point['id_logger']) }}"
                                onclick="focusLogger({{ $point['lat'] }}, {{ $point['lng'] }}, '{{ $point['id_logger'] }}')">
<div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs font-semibold {{ $txtCls }}">
                                        <span class="h-2 w-2 rounded-full {{ $dotCls }}"></span>
                                        {{ $statusLabel }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        {{ $point['last_time'] ? \Carbon\Carbon::parse($point['last_time'])->format('Y-m-d H:i') : '-' }}
                                    </div>
                                </div>
<div class="flex justify-between items-center mt-1 border-b border-slate-200 pb-2">
                                    <div class="font-semibold leading-tight text-sm">{{ $point['nama_logger'] }}</div>
                                    <div class="text-xs border border-slate-300 bg-slate-100 px-2 rounded-lg">
                                        ID: {{ substr($point['id_logger'], -5) }}
                                    </div>
                                </div>

@if ($kat === 'AWLR' && ($point['sub_kategori'] ?? '') === 'jiat')
                                    <div class="text-center my-2">
                                        <div class="text-xl font-bold text-slate-900">
                                            {{ is_numeric($point['kedalaman_sumur']) ? $fmt($point['kedalaman_sumur'], 3) . ' m' : '-' }}
                                        </div>
                                        <div class="text-xs text-slate-500">Kedalaman Air Sumur</div>
                                    </div>

@elseif ($kat === 'AWLR')
                                    <div class="grid grid-cols-2 gap-x-2 my-2">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-slate-900">
                                                {{ is_numeric($point['tma']) ? $fmt($point['tma'], 3) . ' m' : '-' }}
                                            </div>
                                            <div class="text-[10px] text-slate-500">Tinggi Muka Air</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-slate-900">
                                                {{ is_numeric($point['debit']) ? $fmt($point['debit'], 3) . ' m³/s' : '-' }}
                                            </div>
                                            <div class="text-[10px] text-slate-500">Debit</div>
                                        </div>
                                    </div>

@elseif ($kat === 'AFMR')
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 my-2">
                                        @php
                                            $afmrRows = [
                                                [$point['luas_penampang'],   'm²',  'Luas Penampang Basah',  $point['debit'],         'm³/s', 'Debit'],
                                                [$point['flow_velocity'],    'm/s', 'Flow Velocity',          $point['elevasi_muka_air'], 'm',   'Elevasi Muka Air'],
                                                [$point['jarak_sensor'],     'm',   'Jarak Sensor',           $point['elevasi_sensor'],   'm',   'Elevasi Sensor'],
                                            ];
                                        @endphp
                                        @foreach ($afmrRows as [$v1, $u1, $l1, $v2, $u2, $l2])
                                            <div class="text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">
                                                    {{ is_numeric($v1) ? $fmt($v1, 2) . ' ' . $u1 : '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">{{ $l1 }}</div>
                                            </div>
                                            <div class="text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">
                                                    {{ is_numeric($v2) ? $fmt($v2, 3) . ' ' . $u2 : '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">{{ $l2 }}</div>
                                            </div>
                                        @endforeach
                                    </div>

@elseif ($kat === 'ARR')
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 my-2">
                                        @php
                                            $arrRows = [
                                                [$point['kecepatan_angin'], 'Km',  'Kecepatan Angin',  $point['arah_angin'],   '°',  'Arah Angin'],
                                                [$point['kecerahan'],      'K Lux','Kecerahan',        $point['arah_cahaya'],  '°',  'Arah Cahaya'],
                                                [$point['curah_hujan'],    'mm',  'Curah Hujan 1',     $point['curah_hujan_2'],'mm', 'Curah Hujan 2'],
                                            ];
                                        @endphp
                                        @foreach ($arrRows as [$v1, $u1, $l1, $v2, $u2, $l2])
                                            <div class="text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">
                                                    {{ is_numeric($v1) ? $fmt($v1, 3) . ' ' . $u1 : '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">{{ $l1 }}</div>
                                            </div>
                                            <div class="text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">
                                                    {{ is_numeric($v2) ? $fmt($v2, 3) . ' ' . $u2 : '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">{{ $l2 }}</div>
                                            </div>
                                        @endforeach
                                    </div>

@elseif ($kat === 'AWR')
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 my-2">
                                        @php
                                            $awrRows = [
                                                [$point['kecepatan_angin'],  'Km',   'Kecepatan Angin',   $point['arah_angin'],      '°',    'Arah Angin'],
                                                [$point['temperatur_udara'], '°C',   'Temperatur Udara',  $point['kelembaban_udara'], '%',    'Kelembaban Udara'],
                                                [$point['tekanan_udara'],    'hPa',  'Tekanan Udara',     $point['kecerahan'],        'K Lux','Kecerahan'],
                                                [$point['arah_cahaya'],      '°',    'Arah Cahaya',       $point['curah_hujan'],      'mm',   'Curah Hujan 1'],
                                                [$point['curah_hujan_2'],    'mm',   'Curah Hujan 2',     null,                       '',     ''],
                                            ];
                                        @endphp
                                        @foreach ($awrRows as [$v1, $u1, $l1, $v2, $u2, $l2])
                                            <div class="text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">
                                                    {{ is_numeric($v1) ? $fmt($v1, 3) . ' ' . $u1 : '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">{{ $l1 }}</div>
                                            </div>
                                            <div class="text-center py-0.5">
                                                @if($l2 !== '')
                                                    <div class="text-sm font-bold text-slate-900">
                                                        {{ is_numeric($v2) ? $fmt($v2, 3) . ' ' . $u2 : '-' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-500">{{ $l2 }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

@elseif ($kat === 'AWQR')
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 my-2">
                                        @php
                                            $awqrRows = [
                                                [$point['tma'],          'mdpl', 'Tinggi Muka Air',  $point['ph_air'],     '',    'pH Air'],
                                                [$point['suhu_air'],     '°C',   'Suhu Air',          $point['orp'],        'mV',  'ORP'],
                                                [$point['conductivity'], 'µS/cm','Conductivity',      $point['salinity'],   'PSU', 'Salinity'],
                                                [$point['tds'],          '°',    'Total Dissolved Solids', $point['turbidity'], 'NTU', 'Turbidity'],
                                            ];
                                        @endphp
                                        @foreach ($awqrRows as [$v1, $u1, $l1, $v2, $u2, $l2])
                                            <div class="text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">
                                                    {{ is_numeric($v1) ? $fmt($v1, 2) . ($u1 ? ' '.$u1 : '') : '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">{{ $l1 }}</div>
                                            </div>
                                            <div class="text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">
                                                    {{ is_numeric($v2) ? $fmt($v2, 2) . ($u2 ? ' '.$u2 : '') : '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">{{ $l2 }}</div>
                                            </div>
                                        @endforeach
                                        @if(is_numeric($point['tinggi_sensor_awqr'] ?? null))
                                            <div class="col-span-2 text-center py-0.5">
                                                <div class="text-sm font-bold text-slate-900">{{ $fmt($point['tinggi_sensor_awqr'], 2) }} m</div>
                                                <div class="text-[10px] text-slate-500">Tinggi Sensor</div>
                                            </div>
                                        @endif
                                    </div>

@else
                                    <div class="text-center my-2 text-sm text-slate-500">{{ $kat ?: '-' }}</div>
                                @endif
<div class="grid grid-cols-3 text-xs text-slate-600 border-t mt-1">
                                    <div class="flex flex-col items-center py-2">
                                        <span class="text-blue-500 font-semibold">{{ $point['humidity'] !== null ? $point['humidity'].'%' : '-' }}</span>
                                        <span>humidity</span>
                                    </div>
                                    <div class="flex flex-col items-center border-l border-r py-2">
                                        <span class="text-amber-500 font-semibold">{{ $point['battery'] !== null ? $point['battery'].' V' : '-' }}</span>
                                        <span>battery</span>
                                    </div>
                                    <div class="flex flex-col items-center py-2">
                                        <span class="text-rose-500 font-semibold">{{ $point['temp'] !== null ? $point['temp'].' °C' : '-' }}</span>
                                        <span>temp</span>
                                    </div>
                                </div>

                            </div>
                        @empty
                            <div class="p-4 text-center text-sm text-slate-500">
                                Tidak ada data logger.
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="settings-modal-overlay" id="settingsModal">
        <div class="settings-modal">
            <div class="modal-header">
                <div class="modal-title">Pengaturan Peta</div>
                <div class="modal-close" onclick="closeSettingsModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
            <div class="modal-body modal-grid">
                <div>
                    <h3 class="font-semibold text-sm mb-4">FILTER PETA</h3>
                    @php
                        $arrThresholds = $thresholds['ARR'] ?? collect();
                        $arrTotal = collect($points)->where('kategori', 'ARR')->count();
                    @endphp
                    @if ($arrThresholds->isNotEmpty() && $arrTotal > 0)
                        <div class="border rounded-xl p-4 mb-4">
                            <label class="flex items-center gap-2 font-semibold mb-3">
                                <input type="checkbox" id="filterARR" checked class="accent-indigo-600">
                                <img src="{{ asset('icons/arr/ikon_arr.svg') }}" class="h-5 w-5 mr-2 inline-block">
                                ARR (Automatic Rain Recorder)
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                @foreach ($arrThresholds->sortBy('sort_order') as $threshold)
                                    @php
                                        $count = collect($points)
                                            ->where('kategori', 'ARR')
                                            ->where('arr_state', $threshold->state_key)
                                            ->count();
                                    @endphp
                                    @if($count > 0)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" id="filterARR_{{ $threshold->state_key }}" checked>
                                        <img src="{{ asset('icons/arr/' . $threshold->state_key . '.svg') }}"
                                            class="h-7 w-7 inline-block" alt="{{ $threshold->state_label }}">
                                        {{ $threshold->state_label }} ({{ $count }})
                                    </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @php
                        $awlrOnline    = collect($points)->where('kategori', 'AWLR')->where('status', 'online')->count();
                        $awlrOffline   = collect($points)->where('kategori', 'AWLR')->where('status', 'offline')->count();
                        $awlrPerbaikan = collect($points)->where('kategori', 'AWLR')->where('status', 'perbaikan')->count();
                    @endphp
                    @if($awlrOnline + $awlrOffline + $awlrPerbaikan > 0)
                    <div class="border rounded-xl p-4 mb-4">
                        <label class="flex items-center gap-2 font-semibold mb-3">
                            <input type="checkbox" id="filterAWLR" checked class="accent-indigo-600">
                            <img src="{{ asset('icons/awlr/ikon_awlr.svg') }}" class="h-5 w-5 mr-2 inline-block">
                            AWLR (Automatic Water Level Recorder)
                        </label>
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-6 text-sm">
                            @if($awlrOnline > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWLR_online" checked>
                                <img src="{{ asset('icons/awlr/online.svg') }}" class="h-7 w-7 inline-block">
                                Koneksi Terhubung ({{ $awlrOnline }})
                            </label>
                            @endif
                            @if($awlrOffline > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWLR_offline" checked>
                                <img src="{{ asset('icons/awlr/offline.svg') }}" class="h-7 w-7 inline-block">
                                Koneksi Terputus ({{ $awlrOffline }})
                            </label>
                            @endif
                            @if($awlrPerbaikan > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWLR_perbaikan" checked>
                                <img src="{{ asset('icons/awlr/perbaikan.svg') }}" class="h-7 w-7 inline-block">
                                Perbaikan ({{ $awlrPerbaikan }})
                            </label>
                            @endif
                        </div>
                    </div>
                    @endif
                    @php
                        $afmrOnline    = collect($points)->where('kategori', 'AFMR')->where('status', 'online')->count();
                        $afmrOffline   = collect($points)->where('kategori', 'AFMR')->where('status', 'offline')->count();
                        $afmrPerbaikan = collect($points)->where('kategori', 'AFMR')->where('status', 'perbaikan')->count();
                    @endphp
                    @if($afmrOnline + $afmrOffline + $afmrPerbaikan > 0)
                    <div class="border rounded-xl p-4 mb-4">
                        <label class="flex items-center gap-2 font-semibold mb-3">
                            <input type="checkbox" id="filterAFMR" checked class="accent-indigo-600">
                            <img src="{{ asset('icons/afmr/online.svg') }}" class="h-5 w-5 mr-2 inline-block">
                            AFMR (Automatic Flow Measurement Recorder)
                        </label>
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-6 text-sm">
                            @if($afmrOnline > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAFMR_online" checked>
                                <img src="{{ asset('icons/afmr/online.svg') }}" class="h-7 w-7 inline-block">
                                Koneksi Terhubung ({{ $afmrOnline }})
                            </label>
                            @endif
                            @if($afmrOffline > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAFMR_offline" checked>
                                <img src="{{ asset('icons/afmr/offline.svg') }}" class="h-7 w-7 inline-block">
                                Koneksi Terputus ({{ $afmrOffline }})
                            </label>
                            @endif
                            @if($afmrPerbaikan > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAFMR_perbaikan" checked>
                                <img src="{{ asset('icons/afmr/perbaikan.svg') }}" class="h-7 w-7 inline-block">
                                Perbaikan ({{ $afmrPerbaikan }})
                            </label>
                            @endif
                        </div>
                    </div>
                    @endif
                    @php
                        $awqrOnline    = collect($points)->where('kategori', 'AWQR')->where('status', 'online')->count();
                        $awqrOffline   = collect($points)->where('kategori', 'AWQR')->where('status', 'offline')->count();
                        $awqrPerbaikan = collect($points)->where('kategori', 'AWQR')->where('status', 'perbaikan')->count();
                    @endphp
                    @if($awqrOnline + $awqrOffline + $awqrPerbaikan > 0)
                    <div class="border rounded-xl p-4 mb-4">
                        <label class="flex items-center gap-2 font-semibold mb-3">
                            <input type="checkbox" id="filterAWQR" checked class="accent-indigo-600">
                            <img src="{{ asset('icons/awgr/ph_air.svg') }}" class="h-5 w-5 mr-2 inline-block">
                            AWQR (Automatic Water Quality Recorder)
                        </label>
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-6 text-sm">
                            @if($awqrOnline > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWQR_online" checked>
                                <img src="{{ asset('icons/awgr/awlr_map_pins_on.svg') }}" class="h-7 w-7 inline-block">
                                Koneksi Terhubung ({{ $awqrOnline }})
                            </label>
                            @endif
                            @if($awqrOffline > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWQR_offline" checked>
                                <img src="{{ asset('icons/awgr/awlr_map_pins_off.svg') }}" class="h-7 w-7 inline-block">
                                Koneksi Terputus ({{ $awqrOffline }})
                            </label>
                            @endif
                            @if($awqrPerbaikan > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWQR_perbaikan" checked>
                                <img src="{{ asset('icons/awgr/perbaikan_awqr.svg') }}" class="h-7 w-7 inline-block">
                                Perbaikan ({{ $awqrPerbaikan }})
                            </label>
                            @endif
                        </div>
                    </div>
                    @endif
                    @php
                        $awrTotal         = collect($points)->where('kategori', 'AWR')->count();
                        $awrOnline        = collect($points)->where('kategori', 'AWR')->where('status', 'online')->count();
                        $awrOffline       = collect($points)->where('kategori', 'AWR')->where('status', 'offline')->count();
                        $awrSangatRingan  = collect($points)->where('kategori', 'AWR')->where('arr_state', 'awr_sangat_ringan')->count();
                        $awrRingan        = collect($points)->where('kategori', 'AWR')->where('arr_state', 'awr_ringan')->count();
                        $awrSedang        = collect($points)->where('kategori', 'AWR')->where('arr_state', 'awr_sedang')->count();
                        $awrLebat         = collect($points)->where('kategori', 'AWR')->where('arr_state', 'awr_lebat')->count();
                        $awrSangatLebat   = collect($points)->where('kategori', 'AWR')->where('arr_state', 'awr_sangat_lebat')->count();
                        $awrPerbaikan     = collect($points)->where('kategori', 'AWR')->where('arr_state', 'perbaikan')->count();
                        $awrKoneksiPutus  = collect($points)->where('kategori', 'AWR')->filter(function($p) {
                            return $p['arr_state'] === 'koneksi_terputus'
                                || ($p['status'] === 'offline' && empty($p['arr_state']));
                        })->count();

                    @endphp
                    @if($awrTotal > 0)
                    <div class="border rounded-xl p-4">
                        <label class="flex items-center gap-2 font-semibold mb-3">
                            <input type="checkbox" id="filterAWR" checked class="accent-indigo-600">
                            <img src="{{ asset('icons/awr/ikon_awr.svg') }}" class="h-5 w-5 mr-2 inline-block">
                            AWR (Automatic Weather Recorder)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            @if($awrOnline > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_online" checked>
                                <img src="{{ asset('icons/awr/online.svg') }}" class="h-7 w-7 inline-block">
                                Terhubung ({{ $awrOnline }})
                            </label>
                            @endif
                            @if($awrSangatRingan > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_awr_sangat_ringan" checked>
                                <img src="{{ asset('icons/awr/sangat_ringan.svg') }}" class="h-7 w-7 inline-block">
                                Sangat Ringan ({{ $awrSangatRingan }})
                            </label>
                            @endif
                            @if($awrRingan > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_awr_ringan" checked>
                                <img src="{{ asset('icons/awr/ringan.svg') }}" class="h-7 w-7 inline-block">
                                Ringan ({{ $awrRingan }})
                            </label>
                            @endif
                            @if($awrSedang > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_awr_sedang" checked>
                                <img src="{{ asset('icons/awr/sedang.svg') }}" class="h-7 w-7 inline-block">
                                Sedang ({{ $awrSedang }})
                            </label>
                            @endif
                            @if($awrLebat > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_awr_lebat" checked>
                                <img src="{{ asset('icons/awr/lebat.svg') }}" class="h-7 w-7 inline-block">
                                Lebat ({{ $awrLebat }})
                            </label>
                            @endif
                            @if($awrSangatLebat > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_awr_sangat_lebat" checked>
                                <img src="{{ asset('icons/awr/sangat_lebat.svg') }}" class="h-7 w-7 inline-block">
                                Sangat Lebat ({{ $awrSangatLebat }})
                            </label>
                            @endif
                            @if($awrPerbaikan > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_perbaikan" checked>
                                <img src="{{ asset('icons/awr/perbaikan.svg') }}" class="h-7 w-7 inline-block">
                                Perbaikan ({{ $awrPerbaikan }})
                            </label>
                            @endif
                            @if($awrKoneksiPutus > 0)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWR_koneksi_terputus" checked>
                                <img src="{{ asset('icons/awr/offline.svg') }}" class="h-7 w-7 inline-block">
                                Koneksi Terputus ({{ $awrKoneksiPutus }})
                            </label>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-section">
                    <div class="section-title">JENIS PETA</div>
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="hybrid" checked>
                            <img src="{{ asset('icons/peta_hybrid.svg') }}" class="h-5 w-5 mr-2 inline-block"
                                alt="Hybrid">
                            Hybrid
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="normal">
                            <img src="{{ asset('icons/peta_normal.svg') }}" class="h-5 w-5 mr-2 inline-block"
                                alt="Normal">
                            Normal
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="satelit">
                            <img src="{{ asset('icons/peta_satellite.svg') }}" class="h-5 w-5 mr-2 inline-block"
                                alt="Satellite">
                            Satellite
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="terrain">
                            <img src="{{ asset('icons/peta_terrain.svg') }}" class="h-5 w-5 mr-2 inline-block"
                                alt="Terrain">
                            Terrain
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-apply" onclick="applySettings()">Terapkan</button>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script>
        const points = @json($points);
        const markers = {};
        const markerStore = {};
        const fallback = [-7.79558, 110.36949];
        const first = points.length ? [points[0].lat, points[0].lng] : fallback;
        const map = L.map('map', {
            zoomControl: false
        }).setView(first, points.length ? 10 : 13);
        const googleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
        const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        }).addTo(map);
        const googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
        const googleTerrain = L.tileLayer('https://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
        const layers = {
            normal: googleStreets,
            hybrid: googleHybrid,
            satelit: googleSat,
            terrain: googleTerrain
        };
        let currentLayer = 'hybrid';
        const settingsBtn = document.getElementById('mapSettingsBtn');
        const settingsModal = document.getElementById('settingsModal');
        if (settingsBtn && settingsModal) {
            settingsBtn.addEventListener('click', () => settingsModal.classList.add('show'));
            settingsModal.addEventListener('click', e => {
                if (e.target === settingsModal) closeSettingsModal();
            });
        }
        window.closeSettingsModal = function() {
            settingsModal?.classList.remove('show');
        };
function isMobilePeta() { return window.innerWidth < 1024; }

        window.togglePetaSidebar = function(isOpen) {
            if (!isMobilePeta()) return;
            const panel = document.querySelector('.sidebar-panel');
            const backdrop = document.getElementById('petaSidebarBackdrop');
            if (!panel || !backdrop) return;
            if (isOpen) {
                panel.classList.add('mobile-open');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
            } else {
                panel.classList.remove('mobile-open');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
        };

        window.closePetaSidebar = function() {
window.dispatchEvent(new Event('close-peta-sidebar'));
        };
document.addEventListener('DOMContentLoaded', function() {
            if (isMobilePeta()) {
                const panel = document.querySelector('.sidebar-panel');
                if (panel) panel.classList.remove('mobile-open');
            }
        });

        window.addEventListener('resize', function() {
            if (!isMobilePeta()) {
const backdrop = document.getElementById('petaSidebarBackdrop');
                if (backdrop) backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
        window.applySettings = function() {
            const selectedLayer = document.querySelector('input[name="mapType"]:checked')?.value || 'hybrid';
            if (layers[currentLayer]) map.removeLayer(layers[currentLayer]);
            if (layers[selectedLayer]) {
                layers[selectedLayer].addTo(map);
                currentLayer = selectedLayer;
            }
            closeSettingsModal();
        };
        const groupARR = L.layerGroup().addTo(map);
        const groupAWLR = L.layerGroup().addTo(map);
        const groupAWR = L.layerGroup().addTo(map);
        const groupAWQR = L.layerGroup().addTo(map);
        const groupAFMR = L.layerGroup().addTo(map);

        function getGroupByKategori(kat) {
            const k = String(kat || '').toUpperCase();
            if (k === 'ARR') return groupARR;
            if (k === 'AWLR') return groupAWLR;
            if (k === 'AWR') return groupAWR;
            if (k === 'AWQR') return groupAWQR;
            if (k === 'AFMR') return groupAFMR;
            return null;
        }

        function awlrIcon(status) {
            const base = '/icons/awlr/';
            const map = {
                'online'    : 'online.svg',
                'offline'   : 'offline.svg',
                'perbaikan' : 'perbaikan.svg'
            };
            const file = map[String(status).toLowerCase()] || map['offline'];
            return L.icon({
                iconUrl: base + file,
                iconSize: [32, 40],
                iconAnchor: [16, 40],
                popupAnchor: [0, -36]
            });
        }

        function arrIcon(arrState) {
            const k = String(arrState || '').toLowerCase();
            const base = '/icons/arr/';
            const map = {
                'tidak_hujan': 'tidak_hujan.svg',
                'koneksi_terputus': 'koneksi_terputus.svg',
                'perbaikan': 'perbaikan.svg',
                'hujan_sangat_lebat': 'hujan_sangat_lebat.svg',
                'hujan_lebat': 'hujan_lebat.svg',
                'hujan_sedang': 'hujan_sedang.svg',
                'hujan_ringan': 'hujan_ringan.svg',
                'hujan_sangat_ringan': 'hujan_sangat_ringan.svg'
            };
            const file = map[k] || map['tidak_hujan'];
            return L.icon({
                iconUrl: base + file,
                iconSize: [36, 44],
                iconAnchor: [18, 44],
                popupAnchor: [0, -40]
            });
        }

        function arrLabel(state) {
            const k = String(state || '').toLowerCase();
            const map = {
                'tidak_hujan': 'Tidak Hujan',
                'koneksi_terputus': 'Koneksi Terputus',
                'perbaikan': 'Perbaikan',
                'hujan_sangat_ringan': 'Hujan Sangat Ringan',
                'hujan_ringan': 'Hujan Ringan',
                'hujan_sedang': 'Hujan Sedang',
                'hujan_lebat': 'Hujan Lebat',
                'hujan_sangat_lebat': 'Hujan Sangat Lebat'
            };
            return map[k] || 'Tidak Hujan';
        }

        function awqrIcon(status) {
            const base = '/icons/awgr/';
            const map = {
                'online'    : 'awlr_map_pins_on.svg',
                'offline'   : 'awlr_map_pins_off.svg',
                'perbaikan' : 'perbaikan_awqr.svg'
            };
            const file = map[String(status).toLowerCase()] || map['offline'];
            return L.icon({
                iconUrl: base + file,
                iconSize: [32, 40],
                iconAnchor: [16, 40],
                popupAnchor: [0, -36]
            });
        }

        function awrIcon(awrState) {
            const k = String(awrState || '').toLowerCase();
            const base = '/icons/awr/';
            const fileMap = {
                'awr_sangat_ringan' : 'awr_sangat_ringan.svg',
                'awr_ringan'        : 'awr_ringan.svg',
                'awr_sedang'        : 'awr_sedang.svg',
                'awr_lebat'         : 'awr_lebat.svg',
                'awr_sangat_lebat'  : 'awr_sangat_lebat.svg',
                'perbaikan'         : 'perbaikan.svg',
                'koneksi_terputus'  : 'offline.svg',
                'offline'           : 'offline.svg',
                'online'            : 'online.svg',
            };
            const file = fileMap[k] || 'online.svg';
            return L.icon({
                iconUrl: base + file,
                iconSize: [36, 44],
                iconAnchor: [18, 44],
                popupAnchor: [0, -40]
            });
        }

        function afmrIcon(status) {
            const base = '/icons/afmr/';
            const fileMap = {
                'online'    : 'online.svg',
                'offline'   : 'offline.svg',
                'perbaikan' : 'perbaikan.svg'
            };
            const file = fileMap[String(status).toLowerCase()] || fileMap['offline'];
            return L.icon({
                iconUrl: base + file,
                iconSize: [32, 40],
                iconAnchor: [16, 40],
                popupAnchor: [0, -36]
            });
        }


        points.forEach(p => {
            if (!p.lat || !p.lng) return;
            const marker = L.marker([p.lat, p.lng]);
            const kategori = String(p.kategori || '').toUpperCase();
            const status = String(p.status || '').toLowerCase();
            const statusText = p.status === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus';
            const warna = p.status === 'online' ? 'text-green-500' : 'text-red-500';
            const dot = p.status === 'online' ? 'bg-green-500' : 'bg-red-500';
            const arrMonitoringRow = (kategori === 'ARR') ?
                `
                    <div class="popup-info-row">
                        <span class="popup-label">STATUS PEMANTAUAN</span>
                        <span class="popup-value">${arrLabel(p.arr_state)}</span>
                    </div>
                ` :
                '';
            marker.bindPopup(`
                <div class="popup-header">
                <div class="popup-title">${p.nama_logger}</div>
                <div class="popup-close" onclick="document.querySelector('.leaflet-popup-close-button')?.click()">X</div>
                </div>
                <div class="popup-body">
                <div class="popup-info-row">
                    <span class="popup-label">LATITUDE</span>
                    <span class="popup-value">${p.lat}</span>
                </div>
                <div class="popup-info-row">
                    <span class="popup-label">LONGITUDE</span>
                    <span class="popup-value">${p.lng}</span>
                </div>
                <div class="popup-info-row">
                    <span class="popup-label">KEDALAMAN SUMUR</span>
                    <span class="popup-value">${p.kedalaman_sumur ? p.kedalaman_sumur + ' m' : '-'}</span>
                </div>
                <div class="popup-info-row">
                    <span class="popup-label">STATUS KONEKSI</span>
                    <span class="${warna}" style="display:inline-flex;align-items:center;gap:6px;">
                    <span class="h-2 w-2 rounded-full ${dot}"></span>
                    ${statusText}
                    </span>
                </div>
                ${arrMonitoringRow}
                <div class="popup-info-row">
                    <span class="popup-label">STATUS SD CARD</span>
                    <span class="popup-value">OK</span>
                </div>
                </div>
                <div class="popup-buttons">
                <button class="popup-btn popup-btn-outline py-2" onclick="window.open('https://www.google.com/maps?q=${p.lat},${p.lng}', '_blank')">
                    <span>
<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_1005_1485)">
<path d="M15.0001 0.666748L0.666748 7.33342H8.33341V15.3334L15.0001 0.666748Z" stroke="#303481" stroke-width="1.5" stroke-linejoin="round"/>
</g>
<defs>
<clipPath id="clip0_1005_1485">
<rect width="16" height="16" fill="white"/>
</clipPath>
</defs>
</svg>
</span> <span class="text-sm"> Menuju Lokasi</span>
                </button>
                <button class="popup-btn popup-btn-solid py-2" onclick="window.location.href='/analisa/${p.id_logger}'">
                    <span><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.87134 14.8716H14.7952C14.8052 14.8717 14.8144 14.8762 14.8215 14.8833C14.8287 14.8905 14.8333 14.9004 14.8333 14.9106C14.8331 14.9207 14.8287 14.9299 14.8215 14.937C14.8144 14.9441 14.8052 14.9486 14.7952 14.9487H1.87134C1.86128 14.9486 1.85209 14.9441 1.84497 14.937C1.83785 14.9299 1.83336 14.9207 1.83325 14.9106C1.83325 14.9004 1.83776 14.8905 1.84497 14.8833C1.85208 14.8762 1.86132 14.8717 1.87134 14.8716ZM3.30786 6.07666C3.6603 6.07666 3.87423 6.07837 4.02856 6.09912C4.09971 6.10869 4.1378 6.12034 4.15747 6.12842C4.17311 6.13485 4.17759 6.13877 4.17993 6.14111C4.18231 6.14349 4.18647 6.14764 4.19263 6.1626C4.2007 6.18223 4.21234 6.22037 4.22192 6.2915C4.24269 6.44588 4.24341 6.66052 4.24341 7.01318V11.3208C4.24341 11.6732 4.24267 11.8872 4.22192 12.0415C4.21238 12.1124 4.2007 12.1507 4.19263 12.1704C4.18619 12.186 4.18227 12.1905 4.17993 12.1929C4.17759 12.1952 4.17305 12.1992 4.15747 12.2056C4.13774 12.2136 4.09942 12.2253 4.02856 12.2349C3.87424 12.2556 3.66023 12.2563 3.30786 12.2563C2.9552 12.2563 2.74056 12.2556 2.58618 12.2349C2.51505 12.2253 2.47688 12.2136 2.45728 12.2056C2.44227 12.1994 2.43816 12.1952 2.43579 12.1929C2.43345 12.1905 2.42953 12.186 2.4231 12.1704C2.41502 12.1507 2.40337 12.1126 2.3938 12.0415C2.37305 11.8872 2.37134 11.6732 2.37134 11.3208V7.01318C2.37134 6.66052 2.37303 6.44588 2.3938 6.2915C2.40339 6.22034 2.41502 6.1822 2.4231 6.1626C2.42945 6.1472 2.43345 6.14345 2.43579 6.14111C2.43812 6.13879 2.44187 6.13479 2.45728 6.12842C2.47687 6.12035 2.51511 6.1087 2.58618 6.09912C2.74056 6.07835 2.9552 6.07666 3.30786 6.07666ZM8.33325 3.20557C8.68562 3.20557 8.89961 3.20633 9.05396 3.22705C9.12526 3.23664 9.16421 3.24827 9.18384 3.25635C9.19915 3.26269 9.203 3.26672 9.20532 3.26904C9.20766 3.27138 9.21169 3.27523 9.21802 3.29053C9.22608 3.31011 9.23773 3.34843 9.24731 3.41943C9.26808 3.57381 9.2688 3.78845 9.2688 4.14111V11.3208C9.2688 11.6732 9.26806 11.8872 9.24731 12.0415C9.23776 12.1125 9.22609 12.1507 9.21802 12.1704C9.21158 12.186 9.20766 12.1905 9.20532 12.1929C9.20298 12.1952 9.19894 12.1993 9.18384 12.2056C9.16421 12.2136 9.12526 12.2253 9.05396 12.2349C8.89961 12.2556 8.68558 12.2563 8.33325 12.2563C7.98092 12.2563 7.76689 12.2556 7.61255 12.2349C7.54124 12.2253 7.50229 12.2136 7.48267 12.2056C7.46757 12.1993 7.46353 12.1952 7.46118 12.1929C7.45884 12.1905 7.45492 12.186 7.44849 12.1704C7.44041 12.1507 7.42875 12.1125 7.41919 12.0415C7.39844 11.8872 7.39771 11.6732 7.39771 11.3208V4.14111C7.39771 3.78845 7.39842 3.57381 7.41919 3.41943C7.42877 3.34843 7.44042 3.31011 7.44849 3.29053C7.45481 3.27523 7.45885 3.27138 7.46118 3.26904C7.4635 3.26672 7.46736 3.26269 7.48267 3.25635C7.50229 3.24827 7.54124 3.23664 7.61255 3.22705C7.7669 3.20633 7.98088 3.20557 8.33325 3.20557ZM13.3586 1.05127C13.7113 1.05127 13.9259 1.05199 14.0803 1.07275C14.1515 1.08234 14.1896 1.09398 14.2092 1.10205C14.2242 1.10822 14.2283 1.11237 14.2307 1.11475C14.2331 1.11709 14.237 1.12157 14.2434 1.13721C14.2515 1.15686 14.2631 1.19492 14.2727 1.26611C14.2935 1.42044 14.2952 1.63438 14.2952 1.98682V11.3208C14.2952 11.6732 14.2935 11.8872 14.2727 12.0415C14.2631 12.1126 14.2515 12.1507 14.2434 12.1704C14.237 12.186 14.2331 12.1905 14.2307 12.1929C14.2283 12.1952 14.2242 12.1994 14.2092 12.2056C14.1896 12.2136 14.1515 12.2253 14.0803 12.2349C13.9259 12.2556 13.7113 12.2563 13.3586 12.2563C13.0063 12.2563 12.7923 12.2556 12.6379 12.2349C12.5671 12.2253 12.5288 12.2136 12.509 12.2056C12.4935 12.1992 12.4889 12.1952 12.4866 12.1929C12.4842 12.1905 12.4803 12.186 12.4739 12.1704C12.4658 12.1507 12.4541 12.1124 12.4446 12.0415C12.4238 11.8872 12.4231 11.6732 12.4231 11.3208V1.98682C12.4231 1.63443 12.4238 1.42043 12.4446 1.26611C12.4541 1.19513 12.4658 1.15691 12.4739 1.13721C12.4803 1.12157 12.4842 1.11709 12.4866 1.11475C12.4889 1.1124 12.4935 1.10844 12.509 1.10205C12.5288 1.09398 12.5671 1.08228 12.6379 1.07275C12.7923 1.05202 13.0063 1.05127 13.3586 1.05127Z" fill="white" stroke="white"/>
</svg>
</span> <span class="text-sm">Analisa Data</span>
                </button>
                </div>
            `, {
                maxWidth: window.innerWidth < 1024
                    ? Math.min(Math.round(window.innerWidth * 0.88), 320)
                    : 380,
                className: 'custom-popup'
            });
            marker.addTo(map);
            markers[p.id_logger] = marker;
            markerStore[p.id_logger] = {
                marker,
                kategori,
                status,
                arr_state: String(p.arr_state || '').toLowerCase()
            };
            if (kategori === 'AWLR') {
                marker.setIcon(awlrIcon(status));
            }
            if (kategori === 'ARR') {
                marker.setIcon(arrIcon(p.arr_state));
            }
            if (kategori === 'AWQR') {
                marker.setIcon(awqrIcon(status));
            }
            if (kategori === 'AWR') {
                marker.setIcon(awrIcon(p.arr_state || status));
            }
            if (kategori === 'AFMR') {
                marker.setIcon(afmrIcon(status));
            }

        });

        function applyKategoriFilter() {
            const showARR  = document.getElementById('filterARR')?.checked ?? true;
            const showAWLR = document.getElementById('filterAWLR')?.checked ?? true;
            const showAWR  = document.getElementById('filterAWR')?.checked ?? true;
            const showAWQR = document.getElementById('filterAWQR')?.checked ?? true;
            const showAFMR = document.getElementById('filterAFMR')?.checked ?? true;
            const showAWLRonline    = document.getElementById('filterAWLR_online')?.checked ?? true;
            const showAWLRoffline   = document.getElementById('filterAWLR_offline')?.checked ?? true;
            const showAWLRperbaikan = document.getElementById('filterAWLR_perbaikan')?.checked ?? true;
            const showAWQRonline    = document.getElementById('filterAWQR_online')?.checked ?? true;
            const showAWQRoffline   = document.getElementById('filterAWQR_offline')?.checked ?? true;
            const showAWQRperbaikan = document.getElementById('filterAWQR_perbaikan')?.checked ?? true;
            const showAFMRonline    = document.getElementById('filterAFMR_online')?.checked ?? true;
            const showAFMRoffline   = document.getElementById('filterAFMR_offline')?.checked ?? true;
            const showAFMRperbaikan = document.getElementById('filterAFMR_perbaikan')?.checked ?? true;
            const arrStates = [
                'tidak_hujan',
                'hujan_sangat_ringan',
                'hujan_ringan',
                'hujan_sedang',
                'hujan_lebat',
                'hujan_sangat_lebat',
                'perbaikan',
                'koneksi_terputus'
            ];
            const showArrStates = {};
            arrStates.forEach(st => {
                const cb = document.getElementById(`filterARR_${st}`);
                showArrStates[st] = cb ? cb.checked : true;
            });
            const awrStates = ['online','awr_sangat_ringan','awr_ringan','awr_sedang','awr_lebat','awr_sangat_lebat','perbaikan','koneksi_terputus'];
            const showAwrStates = {};
            awrStates.forEach(st => {
                const cb = document.getElementById(`filterAWR_${st}`);
                showAwrStates[st] = cb ? cb.checked : true;
            });
            Object.values(markerStore).forEach(o => {
                if (!o?.marker) return;
                let visible = true;
                if (o.kategori === 'ARR') {
                    if (!showARR) visible = false;
                    else {
                        const st = String(o.arr_state || 'tidak_hujan').toLowerCase();
                        visible = showArrStates[st] ?? true;
                    }
                } else if (o.kategori === 'AWR') {
                    if (!showAWR) visible = false;
                    else {
                        let awrSt = String(o.arr_state || '').toLowerCase();
                        if (!awrSt || awrSt === 'offline') awrSt = (o.status === 'offline') ? 'koneksi_terputus' : 'online';
                        visible = showAwrStates[awrSt] ?? true;
                    }
                } else if (o.kategori === 'AWLR') {
                    if (!showAWLR) visible = false;
                    else if (String(o.status || '').toLowerCase() === 'online')     visible = showAWLRonline;
                    else if (String(o.status || '').toLowerCase() === 'offline')    visible = showAWLRoffline;
                    else if (String(o.status || '').toLowerCase() === 'perbaikan')  visible = showAWLRperbaikan;
                    else visible = true;
                } else if (o.kategori === 'AWQR') {
                    if (!showAWQR) visible = false;
                    else if (String(o.status || '').toLowerCase() === 'online')     visible = showAWQRonline;
                    else if (String(o.status || '').toLowerCase() === 'offline')    visible = showAWQRoffline;
                    else if (String(o.status || '').toLowerCase() === 'perbaikan')  visible = showAWQRperbaikan;
                    else visible = true;
                } else if (o.kategori === 'AFMR') {
                    if (!showAFMR) visible = false;
                    else if (String(o.status || '').toLowerCase() === 'online')     visible = showAFMRonline;
                    else if (String(o.status || '').toLowerCase() === 'offline')    visible = showAFMRoffline;
                    else if (String(o.status || '').toLowerCase() === 'perbaikan')  visible = showAFMRperbaikan;
                    else visible = true;
                }
                if (visible) {
                    if (!map.hasLayer(o.marker)) o.marker.addTo(map);
                } else {
                    if (map.hasLayer(o.marker)) map.removeLayer(o.marker);
                }
            });
            document.querySelectorAll('.sidebar-item').forEach(el => {
                const k = String(el.dataset.kategori || '').toUpperCase();
                const s = String(el.dataset.status || '').toLowerCase();
                const st = String(el.dataset.arrState || el.dataset.arr_state || '').toLowerCase();
                let visible = true;
                if (k === 'ARR') {
                    if (!showARR) visible = false;
                    else visible = showArrStates[st || 'tidak_hujan'] ?? true;
                } else if (k === 'AWR') {
                    if (!showAWR) visible = false;
                    else {
                        let awrSt = String(st || '').toLowerCase();
                        if (!awrSt || awrSt === 'offline') awrSt = (s === 'offline') ? 'koneksi_terputus' : 'online';
                        visible = showAwrStates[awrSt] ?? true;
                    }
                } else if (k === 'AWLR') {
                    if (!showAWLR) visible = false;
                    else if (s === 'online')     visible = showAWLRonline;
                    else if (s === 'offline')    visible = showAWLRoffline;
                    else if (s === 'perbaikan')  visible = showAWLRperbaikan;
                    else visible = true;
                } else if (k === 'AWQR') {
                    if (!showAWQR) visible = false;
                    else if (s === 'online')     visible = showAWQRonline;
                    else if (s === 'offline')    visible = showAWQRoffline;
                    else if (s === 'perbaikan')  visible = showAWQRperbaikan;
                    else visible = true;
                } else if (k === 'AFMR') {
                    if (!showAFMR) visible = false;
                    else if (s === 'online')     visible = showAFMRonline;
                    else if (s === 'offline')    visible = showAFMRoffline;
                    else if (s === 'perbaikan')  visible = showAFMRperbaikan;
                    else visible = true;
                }
                el.setAttribute('data-filter-visible', visible ? 'true' : 'false');
                el.style.display = visible ? '' : 'none';
            });
            const visibleCount = Array.from(document.querySelectorAll('.sidebar-item')).filter(
                item => item.style.display !== 'none'
            ).length;
            const loggerCountNode = document.getElementById('loggerCount');
            if (loggerCountNode) {
                loggerCountNode.textContent = `${visibleCount} logger terdeteksi`;
            }
            setTimeout(() => map.invalidateSize(), 0);
        }
        [
            'filterARR',
            'filterAWLR',
            'filterAWR',
            'filterAWR_online',
            'filterAWR_awr_sangat_ringan',
            'filterAWR_awr_ringan',
            'filterAWR_awr_sedang',
            'filterAWR_awr_lebat',
            'filterAWR_awr_sangat_lebat',
            'filterAWR_perbaikan',
            'filterAWR_koneksi_terputus',
            'filterAWQR',
            'filterAWLR_online',
            'filterAWLR_offline',
            'filterAWLR_perbaikan',
            'filterAWQR_online',
            'filterAWQR_offline',
            'filterAWQR_perbaikan',
            'filterAFMR',
            'filterAFMR_online',
            'filterAFMR_offline',
            'filterAFMR_perbaikan',
            'filterARR_tidak_hujan',
            'filterARR_hujan_sangat_ringan',
            'filterARR_hujan_ringan',
            'filterARR_hujan_sedang',
            'filterARR_hujan_lebat',
            'filterARR_hujan_sangat_lebat',
            'filterARR_perbaikan',
            'filterARR_koneksi_terputus'
        ].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', applyKategoriFilter);
        });
        applyKategoriFilter();
        applyKategoriFilter();
        function safeInvalidate() {
            if (typeof map !== 'undefined') map.invalidateSize({ animate: false });
        }
        [200, 500, 1000, 2000].forEach(ms => setTimeout(safeInvalidate, ms));
        window.addEventListener('resize', safeInvalidate);
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', safeInvalidate);
            window.visualViewport.addEventListener('scroll', safeInvalidate);
        }
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(safeInvalidate).observe(document.getElementById('map'));
        }
        const mainContent = document.getElementById('mainContent');
        if (mainContent) {
            mainContent.addEventListener('transitionend', function(e) {
                if (e.propertyName === 'margin-left') safeInvalidate();
            });
        }
        const sidebarPanel = document.querySelector('.sidebar-panel');
        if (sidebarPanel) {
            sidebarPanel.addEventListener('transitionend', function(e) {
                if (e.propertyName === 'transform' || e.propertyName === 'width') {
                    safeInvalidate();
                }
            });
        }
        window.focusLogger = function(lat, lng, id) {
            if (!lat || !lng) return;
            const target = L.latLng(lat, lng);
            const zoom = 15;
            const verticalOffset = 120;
            const targetPoint = map.project(target, zoom);
            const adjustedCenterPoint = targetPoint.subtract([0, verticalOffset]);
            const adjustedCenter = map.unproject(adjustedCenterPoint, zoom);

            map.flyTo(adjustedCenter, zoom, {
                animate: true,
                duration: 1.5
            });
            if (markers[id]) markers[id].openPopup();
        };
        const searchInput = document.getElementById('searchLogger');
        const loggerCountEl = document.getElementById('loggerCount');
        const totalLoggers = {{ count($points) }};
        if (searchInput) {
            const buildSearchData = () =>
                Array.from(document.querySelectorAll('.sidebar-item')).map(item => ({
                    el: item,
                    loggerName: item.getAttribute('data-logger-name') || '',
                    loggerId:   item.getAttribute('data-logger-id')   || '',
                    kategori:   (item.getAttribute('data-kategori')   || '').toLowerCase(),
                }));

            searchInput.addEventListener('input', function(e) {
                const searchText = e.target.value.trim();
                let visibleCount = 0;

                if (searchText === '') {
                    applyKategoriFilter();
                    return;
                }

                const items = buildSearchData();
                const ql = searchText.toLowerCase();
                const fuse = new Fuse(items, {
                    threshold: 0.45,          // lebih toleran dari 0.35
                    ignoreLocation: true,     // cari di seluruh string
                    keys: [
                        { name: 'loggerName', weight: 0.6 },
                        { name: 'loggerId',   weight: 0.3 },
                        { name: 'kategori',   weight: 0.1 },
                    ]
                });
                const fuzzyMatched = new Set(fuse.search(searchText).map(r => r.item.el));
                const exactMatched = new Set(
                    items
                        .filter(it =>
                            it.loggerName.includes(ql) ||
                            it.loggerId.includes(ql)   ||
                            it.kategori.includes(ql)
                        )
                        .map(it => it.el)
                );

                items.forEach(({ el }) => {
                    const matchesSearch = fuzzyMatched.has(el) || exactMatched.has(el);
                    el.style.display = matchesSearch ? '' : 'none';
                    if (matchesSearch) visibleCount++;
                });

                if (loggerCountEl) {
                    loggerCountEl.textContent = `${visibleCount} dari ${totalLoggers} logger ditemukan`;
                }
            });
        }
        const originalApplyFilter = applyKategoriFilter;
        applyKategoriFilter = function() {
            originalApplyFilter();
            const searchText = searchInput ? searchInput.value.toLowerCase().trim() : '';
            if (searchText !== '') {
                let visibleCount = 0;
                document.querySelectorAll('.sidebar-item').forEach(item => {
                    const loggerName = item.getAttribute('data-logger-name') || '';
                    const loggerId = item.getAttribute('data-logger-id') || '';
                    const matchesSearch = loggerName.includes(searchText) || loggerId.includes(searchText);
                    if (!matchesSearch && item.style.display !== 'none') {
                        item.style.display = 'none';
                    }
                    if (item.style.display !== 'none') {
                        visibleCount++;
                    }
                });
                if (loggerCountEl) {
                    loggerCountEl.textContent = `${visibleCount} dari ${totalLoggers} logger ditemukan`;
                }
            } else {
                const visibleCount = Array.from(document.querySelectorAll('.sidebar-item')).filter(
                    item => item.style.display !== 'none'
                ).length;
                if (loggerCountEl) {
                    loggerCountEl.textContent = `${visibleCount} logger terdeteksi`;
                }
            }
        };
        setTimeout(() => map.invalidateSize(), 500);
        window.addEventListener('resize', () => map.invalidateSize());
    </script>
@endpush
