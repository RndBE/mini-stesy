@extends('layouts.app')

@push('head')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    /* Custom Map Styling for Light Theme (similar to peta index) */
    #leaflet-map {
        height: 100% !important;
        width: 100% !important;
        min-height: 80vh;
        z-index: 10;
        background: #f8fafc; /* light background */
    }
    
    .leaflet-popup-content-wrapper {
        background: white;
        color: #1e293b;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .leaflet-popup-tip {
        background: white;
    }

    /* Override info panel to be light theme */
    #info-panel {
        background: white !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    }
    #info-panel .panel-header {
        background: #f1f5f9 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        color: #475569 !important;
    }
    #info-panel .panel-title-block {
        background: white !important;
        color: #1e293b !important;
        border-bottom: 1px solid #f1f5f9;
    }
    #info-panel .panel-title-block .neon-bar {
        background: #0ea5e9 !important;
        box-shadow: none !important;
    }
    #info-panel .text-slate-300 { color: #64748b !important; }
    #info-panel .text-[#38bdf8] { color: #0284c7 !important; }
    #info-panel .bg-slate-900\/90 { background: white !important; }
    #info-panel .border-slate-800\/80 { border-color: #f1f5f9 !important; }
    #info-panel .text-slate-400 { color: #64748b !important; }
    #info-panel .text-slate-500 { color: #94a3b8 !important; }
    #info-panel .bg-slate-800\/30 { background: #f8fafc !important; }
</style>
@endpush

@section('content')

<div style="height: calc(100vh - 65px); width: 100%;" class="relative overflow-hidden">
    
    <!-- Informasi DSS Floating Panel (Light Theme) -->
    <div id="info-panel" class="hidden absolute top-4 right-4 overflow-hidden" style="width: 320px; border-radius: 8px; z-index: 1000;">
        <!-- Header -->
        <div class="panel-header flex justify-between items-center px-4 py-2.5">
            <span class="text-[11px] font-bold tracking-widest uppercase">Detail Lokasi</span>
            <button id="close-info" class="text-slate-500 hover:text-slate-800 transition-colors text-xl leading-none">&times;</button>
        </div>
        
        <!-- Title Block -->
        <div class="panel-title-block px-4 py-3.5 flex relative overflow-hidden group">
            <div class="neon-bar absolute left-0 top-0 bottom-0 w-1"></div>
            <div class="pl-2 relative z-10 w-full">
                <div class="text-[11px] mb-1 font-semibold text-[#38bdf8] uppercase tracking-wider" id="info-type">Tipe</div>
                <div class="text-lg font-extrabold tracking-tight truncate" id="info-name">Nama Lokasi</div>
            </div>
        </div>

        <!-- Details List -->
        <div class="bg-slate-900/90 text-sm" id="info-details">
            <!-- Dynamic Content Injected Here -->
        </div>
    </div>

    <!-- Peta Leaflet Container -->
    <div id="leaflet-map" class="absolute inset-0" style="z-index: 10;"></div>
</div>

@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Inisialisasi Peta Leaflet
        const map = L.map('leaflet-map', {
            center: [-7.2278, 107.9086],
            zoom: 12,
            zoomControl: false 
        });

        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // 2. Tambahkan Tile Layer (Berdasarkan tema peta/index.blade.php - Light OSM)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Force peta untuk merender ulang dimensinya supaya tidak ada layar putih/abu
        setTimeout(() => { map.invalidateSize(); }, 500);
        window.addEventListener('resize', () => map.invalidateSize());

        // Referensi UI DSS Panel
        const infoPanel = document.getElementById('info-panel');
        const infoName = document.getElementById('info-name');
        const infoType = document.getElementById('info-type');
        const infoDetails = document.getElementById('info-details');

        document.getElementById('close-info').addEventListener('click', () => {
            infoPanel.classList.add('hidden');
        });

        // 3. Fungsi untuk mewarnai/styling data GeoJSON Sungai (Tiru warna cyan biru terang)
        function styleFeature(feature) {
            const props = feature.properties;
            if (props.type === 'sungai' || props.type === 'sungai_utama') {
                return {
                    color: '#00bfff', // Bright blue/cyan seperti contoh gambar
                    weight: props.type === 'sungai_utama' ? 4 : 2,
                    opacity: 0.9,
                    lineJoin: 'round'
                };
            }
            return { color: '#0ea5e9' };
        }

        // 5. Muat Data GeoJSON dan tambahkan interaksi
        fetch("{{ asset('geojson/leuwigoong.json') }}")
            .then(res => {
                if(!res.ok) throw new Error("HTTP " + res.status);
                return res.json();
            })
            .then(data => {
                const geoJsonLayer = L.geoJSON(data, {
                    style: styleFeature,
                    onEachFeature: function(feature, layer) {
                        if (feature.properties && feature.properties.name) {
                            layer.bindTooltip(feature.properties.name, {
                                className: 'bg-white text-slate-800 border shadow font-bold px-2 py-1',
                                direction: 'auto'
                            });
                        }

                        layer.on('click', function(e) {
                            L.DomEvent.stopPropagation(e); 
                            const props = feature.properties;
                            
                            infoName.textContent = props.name || 'Cabang Sungai';
                            infoType.textContent = (props.type || 'Sungai').replace('_', ' ').toUpperCase();

                            let detailsHtml = '';
                            if(props.luas_ha) {
                                detailsHtml += `
                                <div class="flex justify-between items-center px-4 py-3 border-b border-slate-800/80 text-slate-400">
                                    <span class="text-xs font-medium text-slate-600">Luas Area</span>
                                    <div>
                                        <span class="font-bold text-[#38bdf8] text-base">${props.luas_ha}</span> <span class="text-[10px]">Ha.</span>
                                    </div>
                                </div>`;
                            }
                            if(props.debit) {
                                detailsHtml += `
                                <div class="flex justify-between items-center px-4 py-3 border-b border-slate-800/80 bg-slate-800/30">
                                    <span class="text-xs text-slate-300 font-medium text-slate-600">Pengukuran Debit</span>
                                    <div class="font-mono">
                                        <span class="font-black text-xl text-[#0ea5e9] drop-shadow-[0_0_5px_#0ea5e9]">${props.debit}</span>
                                    </div>
                                </div>`;
                            }
                            
                            if(!detailsHtml) {
                                detailsHtml = `<div class="px-4 py-4 text-center text-xs text-slate-500 italic">Data terperinci aliran tidak tersedia</div>`;
                            }
                            
                            infoDetails.innerHTML = detailsHtml;
                            infoPanel.classList.remove('hidden');
                        });
                    }
                }).addTo(map);

                // Fit bounds kalau ada layernya
                if (Object.keys(geoJsonLayer._layers).length > 0) {
                    map.fitBounds(geoJsonLayer.getBounds(), { padding: [50, 50] });
                }
            })
            .catch(err => console.error("Error memuat geojson:", err));

        map.on('click', function() {
            infoPanel.classList.add('hidden');
        });
    });
</script>
@endpush
