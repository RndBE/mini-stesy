@extends('layouts.app')
@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 100%;
            width: 100%;
            z-index: 10;
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
            transition: all 0.2s;
        }

        .map-settings-btn:hover {
            background: #10134B;
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

        /* Custom Popup Styles */
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
    </style>
@endpush
@section('content')
    <div class="h-[calc(100vh-65px)] w-full">
        <div class="flex h-full w-full overflow-hidden bg-white shadow-sm ring-1 ring-slate-200">
            <div class="relative flex-1">
                <div id="map" class="h-full w-full"></div>
                <button class="map-settings-btn rounded-xl" id="mapSettingsBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Pengaturan Peta
                </button>
            </div>
            <div class="flex w-1/5   flex-col border-l border-slate-200 bg-white">
                <div class=" px-3 py-2">
                    <span class="text-lg font-semibold text-slate-900">Daftar Logger</span>
                    {{-- <p class="text-xs text-slate-500" id="loggerCount">{{ count($points) }} logger terdeteksi</p> --}}
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
                        <div class="sidebar-item mb-4 rounded-lg bg-white pt-3 pb-2 px-3 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md"
                            data-kategori="{{ $point['kategori'] }}" data-status="{{ $point['status'] }}"
                            data-arr-state="{{ $point['arr_state'] ?? '' }}"
                            data-logger-name="{{ strtolower($point['nama_logger']) }}"
                            data-logger-id="{{ strtolower($point['id_logger']) }}"
                            onclick="focusLogger({{ $point['lat'] }}, {{ $point['lng'] }}, '{{ $point['id_logger'] }}')">

                            <div class="flex items-center justify-between">
                                <div
                                    class="flex items-center gap-2 text-xs font-semibold {{ $point['status'] === 'online' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    <span
                                        class="h-2 w-2 rounded-full {{ $point['status'] === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $point['status'] === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus' }}
                                </div>
                                <div class="text-[10px] text-slate-500">
                                    {{ $point['last_time'] ? \Carbon\Carbon::parse($point['last_time'])->format('d M Y H:i') : '-' }}
                                </div>
                            </div>



                            <div class="flex justify-between items-center mt-1 border-b border-slate-200 pb-2">
                                <div class="font-semibold leading-tight">
                                    {{ $point['nama_logger'] }}
                                </div>

                                <div class="text-xs border border-slate-300 bg-slate-100 px-2  rounded-lg">
                                    ID : {{ substr($point['id_logger'], -5) }}
                                </div>
                            </div>
                            <div class="text-center my-2">
                                <div class="text-xl font-bold text-slate-900">{{ $point['kedalaman_sumur'] }} m</div>
                                <div class="text-xs text-slate-500">Kedalaman Air Sumur</div>
                            </div>
                            <div class="grid grid-cols-3 text-xs  text-slate-600 border-t">
                                <div class="flex flex-col items-center  py-3 ">
                                    <span class="text-blue-500 font-semibold">
                                        {{ $point['humidity'] ?? '—' }}%
                                    </span>
                                    <span>humidity</span>
                                </div>
                                <div class="flex flex-col items-center border-l py-3 border-r">
                                    <span class="text-amber-500 font-semibold">
                                        {{ $point['battery'] ?? '-' }} V
                                    </span>
                                    <span>battery</span>
                                </div>
                                <div class="flex flex-col items-center  py-3 ">
                                    <span class="text-rose-500 font-semibold">
                                        {{ $point['temp'] ?? '-' }} °C
                                    </span>
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
                    @endphp
                    @if ($arrThresholds->isNotEmpty())
                        <div class="border rounded-xl p-4 mb-4">
                            <label class="flex items-center gap-2 font-semibold mb-3">
                                <input type="checkbox" id="filterARR" checked class="accent-indigo-600"> ARR (Automatic Rain
                                Recorder)
                            </label>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                @foreach ($arrThresholds->sortBy('sort_order') as $threshold)
                                    @php
                                        $count = collect($points)
                                            ->where('kategori', 'ARR')
                                            ->where('arr_state', $threshold->state_key)
                                            ->count();
                                    @endphp
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" id="filterARR_{{ $threshold->state_key }}" checked>
                                        <span class="h-3 w-3 rounded-full"
                                            style="background-color: {{ $threshold->color_hex }}"></span>
                                        {{ $threshold->state_label }} ({{ $count }})
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @php
                        $awlrOnline = collect($points)->where('kategori', 'AWLR')->where('status', 'online')->count();
                        $awlrOffline = collect($points)->where('kategori', 'AWLR')->where('status', 'offline')->count();
                    @endphp
                    <div class="border rounded-xl p-4 mb-4">
                        <label class="flex items-center gap-2 font-semibold mb-3">
                            <input type="checkbox" id="filterAWLR" checked class="accent-indigo-600"> AWLR (Automatic
                            Water
                            Level Recorder)
                        </label>
                        <div class="flex gap-6 text-sm">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWLR_online" checked> <span
                                    class="h-3 w-3 rounded-full bg-green-500"></span> Koneksi Terhubung
                                ({{ $awlrOnline }})</label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="filterAWLR_offline" checked> <span
                                    class="h-3 w-3 rounded-full bg-black"></span> Koneksi Terputus
                                ({{ $awlrOffline }})</label>
                        </div>
                    </div>
                    <div class="border rounded-xl p-4">
                        <label class="flex items-center gap-2 font-semibold mb-3">
                            <input type="checkbox" id="filterAWR" class="accent-indigo-600"> AWR (Automatic Weather
                            Recorder)
                        </label>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-green-500"></span> Tidak Hujan (0)</label>
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-sky-300"></span> Hujan Sangat Ringan (0)</label>
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-blue-500"></span> Hujan Ringan (0)</label>
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-yellow-400"></span> Hujan Sedang (0)</label>
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-orange-500"></span> Hujan Lebat (0)</label>
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-red-500"></span> Hujan Sangat Lebat (0)</label>
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-amber-700"></span> Perbaikan (0)</label>
                            <label class="flex items-center gap-2"><input type="checkbox"> <span
                                    class="h-3 w-3 rounded-full bg-black"></span> Koneksi Terputus (0)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-section">
                    <div class="section-title">JENIS PETA</div>
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="hybrid" checked>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Hybrid
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="normal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Normal
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="satelit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Satellite
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="mapType" value="terrain">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
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

        function getGroupByKategori(kat) {
            const k = String(kat || '').toUpperCase();
            if (k === 'ARR') return groupARR;
            if (k === 'AWLR') return groupAWLR;
            if (k === 'AWR') return groupAWR;
            return null;
        }

        function awlrIcon(status) {
            const base = '/icons/awlr/';
            const map = {
                'online': 'online.svg',
                'offline': 'offline.svg'
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
                <div class="popup-close" onclick="document.querySelector('.leaflet-popup-close-button')?.click()">×</div>
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
                maxWidth: 380,
                className: 'custom-popup'
            });
            const g = getGroupByKategori(p.kategori);
            if (g) g.addLayer(marker);
            else marker.addTo(map);
            markers[p.id_logger] = marker;
            markerStore[p.id_logger] = {
                marker,
                kategori,
                status,
                arr_state: String(p.arr_state || '').toLowerCase()
            };
            // if (String(p.kategori || '').toUpperCase() === 'AWLR') {
            //     markers[p.id_logger].setIcon(awlrIcon(p.status));
            // }
            if (kategori === 'AWLR') {
                marker.setIcon(awlrIcon(status));
            }
            if (kategori === 'ARR') {
                marker.setIcon(arrIcon(p.arr_state));
            }
        });

        function applyKategoriFilter() {
            const showARR = document.getElementById('filterARR')?.checked ?? true;
            const showAWLR = document.getElementById('filterAWLR')?.checked ?? true;
            const showAWR = document.getElementById('filterAWR')?.checked ?? true;
            const showAWLRonline = document.getElementById('filterAWLR_online')?.checked ?? true;
            const showAWLRoffline = document.getElementById('filterAWLR_offline')?.checked ?? true;
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
                    visible = showAWR;
                } else if (o.kategori === 'AWLR') {
                    if (!showAWLR) visible = false;
                    else if (String(o.status || '').toLowerCase() === 'online') visible = showAWLRonline;
                    else if (String(o.status || '').toLowerCase() === 'offline') visible = showAWLRoffline;
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
                    visible = showAWR;
                } else if (k === 'AWLR') {
                    if (!showAWLR) visible = false;
                    else if (s === 'online') visible = showAWLRonline;
                    else if (s === 'offline') visible = showAWLRoffline;
                    else visible = true;
                }
                // Mark visibility state for search integration
                el.setAttribute('data-filter-visible', visible ? 'true' : 'false');
                el.style.display = visible ? '' : 'none';
            });
            // Update logger count
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
            'filterAWLR_online',
            'filterAWLR_offline',
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
        setTimeout(() => map.invalidateSize(), 500);
        window.addEventListener('resize', () => map.invalidateSize());
        // Listen for sidebar toggle transition to update map size faster
        const mainContent = document.getElementById('mainContent');
        if (mainContent) {
            mainContent.addEventListener('transitionend', function(e) {
                // Only trigger on margin-left transitions (sidebar toggle)
                if (e.propertyName === 'margin-left') {
                    map.invalidateSize();
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
        // Logger Search Functionality
        const searchInput = document.getElementById('searchLogger');
        const loggerCountEl = document.getElementById('loggerCount');
        const totalLoggers = {{ count($points) }};
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchText = e.target.value.toLowerCase().trim();
                let visibleCount = 0;
                if (searchText === '') {
                    // Reset: re-apply category filters to restore proper display
                    applyKategoriFilter();
                    return; // Exit early, applyKategoriFilter already updates counter
                }
                document.querySelectorAll('.sidebar-item').forEach(item => {
                    const loggerName = item.getAttribute('data-logger-name') || '';
                    const loggerId = item.getAttribute('data-logger-id') || '';
                    // Check if search text matches name or ID
                    const matchesSearch = loggerName.includes(searchText) || loggerId.includes(searchText);
                    // Check if item is currently visible (not hidden by filters)
                    const wasVisibleByFilter = item.getAttribute('data-filter-visible') !== 'false';
                    // Show only if matches search AND was visible by filter
                    if (matchesSearch && wasVisibleByFilter) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                // Update counter
                if (loggerCountEl) {
                    loggerCountEl.textContent = `${visibleCount} dari ${totalLoggers} logger ditemukan`;
                }
            });
        }
        // Update the applyKategoriFilter to reset search when filters change
        const originalApplyFilter = applyKategoriFilter;
        applyKategoriFilter = function() {
            originalApplyFilter();
            // Re-apply search filter after category filter
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
