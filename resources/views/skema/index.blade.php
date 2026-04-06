@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] w-full overflow-hidden relative shadow-inner flex" style="background-color: #ffffff;">
    
    <!-- Informasi DSS Floating Panel -->
    <div id="info-panel" class="hidden absolute top-4 left-4 bg-[#1e293b] shadow-2xl shadow-black/40 border border-slate-700/60 overflow-hidden" style="width: 340px; border-radius: 8px; z-index: 50; backdrop-filter: blur(12px);">
        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-2.5 border-b border-slate-700/80 bg-slate-800/80">
            <div class="flex items-center gap-2">
                <!-- Badge tipe alat (dinamis) -->
                <span id="panel-type-badge" class="text-[9px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-full bg-slate-700 text-slate-400">DSS</span>
                <span class="text-slate-300 text-[11px] font-bold tracking-widest uppercase">Informasi Panel</span>
            </div>
            <div class="flex items-center gap-2">
                <!-- Status Online/Offline indicator -->
                <span id="panel-status-dot" class="hidden w-2 h-2 rounded-full bg-slate-500"></span>
                <span id="panel-status-text" class="hidden text-[9px] text-slate-500 font-semibold"></span>
                <button id="close-info" class="text-slate-400 hover:text-white transition-colors text-xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Blue Title Block (Neon Style) -->
        <div class="px-4 py-3.5 flex relative bg-slate-800/90 text-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0ea5e9]/10 to-transparent"></div>
            <div id="panel-left-accent" class="absolute left-0 top-0 bottom-0 w-1 bg-[#0ea5e9] shadow-[0_0_10px_#0ea5e9]"></div>
            <div class="pl-2 relative z-10 w-full">
                <div class="text-[11px] mb-1 font-semibold text-[#38bdf8] uppercase tracking-wider" id="info-source">Saluran Irigasi</div>
                <div class="text-lg font-extrabold tracking-tight text-white drop-shadow-md truncate" id="info-title">-</div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- PANEL MODE: DEFAULT (DSS/Persimpangan Biasa)                 -->
        <!-- ============================================================ -->
        <div id="panel-mode-default" class="bg-slate-900/90 text-sm">
            <div class="flex justify-between items-center px-4 py-3 border-b border-slate-800/80 text-slate-400">
                <span class="text-xs font-medium">Luas Area Layanan</span>
                <div><span class="font-bold text-[#38bdf8] text-base" id="info-area">-</span> <span class="text-[10px]">Ha.</span></div>
            </div>
            <div class="px-4 py-2.5 border-b border-slate-800/80 text-slate-400 text-xs">
                <div class="flex justify-between items-center mb-1.5">
                    <span>Kebutuhan Air Irigasi</span>
                    <span class="text-slate-300 font-mono">0 <span class="text-[9px] text-slate-500">lt/dt</span></span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Nilai kehilangan 0.20%</span>
                    <span class="text-slate-300 font-mono">0 <span class="text-[9px] text-slate-500">lt/dt</span></span>
                </div>
            </div>
            <div class="px-4 py-2.5 border-b border-slate-800/80 text-slate-400 text-xs">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-slate-300">Total Kebutuhan Air</span>
                    <span class="text-white font-mono font-medium">0 <span class="text-[9px] text-slate-500">lt/dt</span></span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Faktor K</span>
                    <span class="text-[#38bdf8] font-bold">1.0</span>
                </div>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-b border-slate-800/80 bg-slate-800/30">
                <span class="text-xs text-slate-300 font-medium">Perintah Debit Dialirkan</span>
                <div><span class="font-black text-xl text-[#0ea5e9] drop-shadow-[0_0_5px_#0ea5e9]">0</span> <span class="text-[10px] text-slate-500">lt/dt</span></div>
            </div>
            <div class="px-4 py-3 pb-4 text-slate-400 space-y-2.5 text-xs bg-slate-900">
                <div class="flex justify-between items-center">
                    <span>Menuju <span class="font-bold text-slate-200">Selanjutnya</span></span>
                    <div class="font-mono"><span class="font-bold text-[#0ea5e9] text-sm">0</span> <span class="text-[9px] text-slate-500">lt/dt</span></div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- PANEL MODE: AWLR (Sensor Tinggi Muka Air)                    -->
        <!-- ============================================================ -->
        <div id="panel-mode-awlr" class="hidden bg-slate-900/90 text-sm">
            <!-- TMA Besar -->
            <div class="px-4 py-4 border-b border-slate-800/80 text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-[#0ea5e9]/5 to-transparent"></div>
                <div class="relative z-10">
                    <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Tinggi Muka Air (TMA)</div>
                    <div class="flex items-end justify-center gap-1">
                        <span class="text-4xl font-black text-white drop-shadow-[0_0_8px_#0ea5e9]" id="awlr-tma-value">-</span>
                        <span class="text-slate-400 text-sm mb-1">cm</span>
                    </div>
                    <!-- Badge Status Siaga -->
                    <div class="mt-2">
                        <span id="awlr-status-badge" class="text-[10px] font-bold px-3 py-1 rounded-full bg-emerald-900/60 text-emerald-400 border border-emerald-700/50">Normal</span>
                    </div>
                </div>
            </div>
            <!-- Info Rows -->
            <div class="px-4 py-2.5 border-b border-slate-800/80 flex justify-between text-xs text-slate-400">
                <span>ID Logger</span>
                <span class="text-slate-200 font-mono" id="awlr-id-logger">-</span>
            </div>
            <div class="px-4 py-2.5 border-b border-slate-800/80 flex justify-between text-xs text-slate-400">
                <span>Update Terakhir</span>
                <span class="text-slate-200 font-mono" id="awlr-last-time">-</span>
            </div>
            <!-- Chart Area TMA -->
            <div class="px-4 py-3">
                <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-2">Tren TMA 6 Jam Terakhir</div>
                <div class="relative" style="height: 80px;">
                    <canvas id="awlr-chart" style="height: 80px; width: 100%;"></canvas>
                    <div id="awlr-chart-loading" class="absolute inset-0 flex items-center justify-center">
                        <span class="text-[10px] text-slate-500 animate-pulse">Memuat data...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- PANEL MODE: AWGC (Kontrol Pintu Air Otomatis)               -->
        <!-- ============================================================ -->
        <div id="panel-mode-awgc" class="hidden bg-slate-900/90 text-sm">
            <!-- Bukaan Saat Ini (Visual Progress Bar) -->
            <div class="px-4 py-4 border-b border-slate-800/80">
                <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-2">Posisi Bukaan Pintu Saat Ini</div>
                <!-- Gauge bar -->
                <div class="w-full bg-slate-800 rounded-full h-4 overflow-hidden border border-slate-700">
                    <div id="awgc-bukaan-bar" class="h-4 bg-gradient-to-r from-[#0ea5e9] to-[#38bdf8] rounded-full transition-all duration-700" style="width: 0%"></div>
                </div>
                <div class="flex justify-between mt-1.5">
                    <span class="text-[10px] text-slate-500">Tutup (0%)</span>
                    <span class="font-bold text-white text-sm"><span id="awgc-bukaan-value">0</span><span class="text-slate-400 text-xs">%</span></span>
                    <span class="text-[10px] text-slate-500">Buka Penuh (100%)</span>
                </div>
            </div>
            <!-- Info Rows -->
            <div class="px-4 py-2 border-b border-slate-800/80 flex justify-between text-xs text-slate-400">
                <span>ID Logger</span>
                <span class="text-slate-200 font-mono" id="awgc-id-logger">-</span>
            </div>
            <div class="px-4 py-2 border-b border-slate-800/80 flex justify-between text-xs text-slate-400">
                <span>Update Terakhir</span>
                <span class="text-slate-200 font-mono" id="awgc-last-time">-</span>
            </div>
            <!-- ======= BAGIAN KONTROL / SETTING ======= -->
            <div class="px-4 py-3 bg-slate-800/50 border-t border-cyan-900/30">
                <div class="text-[10px] text-cyan-400 uppercase tracking-widest mb-3 font-bold">⚙ Panel Kontrol Pintu Air</div>
                <!-- Slider target -->
                <div class="mb-3">
                    <div class="flex justify-between text-[10px] text-slate-400 mb-1.5">
                        <span>Target Bukaan</span>
                        <span>
                            <span id="awgc-target-display" class="text-white font-bold text-sm">50</span>
                            <span class="text-slate-500">%</span>
                        </span>
                    </div>
                    <input id="awgc-slider" type="range" min="0" max="100" value="50"
                        class="w-full h-2 rounded-lg appearance-none cursor-pointer bg-slate-700 accent-cyan-400">
                </div>
                <!-- Tombol terapkan -->
                <button id="awgc-btn-send"
                    onclick="sendAwgcCommand()"
                    class="w-full py-2.5 rounded-lg font-bold text-sm transition-all duration-200 bg-[#0ea5e9] hover:bg-[#0284c7] active:scale-95 text-white shadow-lg shadow-cyan-900/50"
                    style="letter-spacing: 0.02em;">
                    <span id="awgc-btn-text">⚡ Terapkan Posisi Pintu</span>
                </button>
                <!-- Status feedback command -->
                <div id="awgc-command-status" class="hidden mt-2 text-center text-[10px] py-1.5 px-2 rounded bg-slate-800 border border-slate-700">
                    <span id="awgc-command-status-text" class="text-slate-400"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- The Panzoom Container -->
    <div id="panzoom-container" class="w-full h-full !cursor-default active:!cursor-grabbing">
        <svg id="network-svg" width="3000" height="2000" class="origin-top-left font-sans">
            <!-- Layers -->
            <g id="edges-layer-bg"></g> <!-- Border lines -->
            <g id="edges-layer-fg"></g> <!-- Inner lines -->
            <g id="edges-layer-anim"></g> <!-- Flow Animations -->
            <g id="nodes-layer"></g>    <!-- Nodes and Texts -->
        </svg>
    </div>
</div>

@endsection

@push('scripts')
<style>
@verbatim
    @keyframes flow-1 { from { stroke-dashoffset: 54; } to { stroke-dashoffset: 0; } }
    @keyframes flow-2 { from { stroke-dashoffset: 67; } to { stroke-dashoffset: 0; } }
    @keyframes flow-trickle { from { stroke-dashoffset: 40; } to { stroke-dashoffset: 0; } }
    @keyframes flow-high { from { stroke-dashoffset: 26; } to { stroke-dashoffset: 0; } }
    @keyframes flow-overflow { from { stroke-dashoffset: 25; } to { stroke-dashoffset: 0; } }
@endverbatim

    /* Partikel utama air yang bergerak normal */
    .flow-line-1 {
        animation: flow-1 1.2s linear infinite;
        stroke-dasharray: 0, 54;
        stroke-linecap: round;
        filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.8));
        opacity: 0.9;
    }
    /* Partikel percikan kecil dengan jeda lebih jauh */
    .flow-line-2 {
        animation: flow-2 1.8s linear infinite;
        stroke-dasharray: 0, 67;
        stroke-linecap: round;
        opacity: 0.5;
    }
    .flow-line-trickle {
        animation: flow-trickle 4s linear infinite;
        stroke-dasharray: 0, 40;
        stroke-linecap: round;
        opacity: 0.6;
    }
    .flow-line-high {
        animation: flow-high 0.6s linear infinite;
        stroke-dasharray: 0, 26;
        stroke-linecap: round;
        filter: drop-shadow(0 0 4px #ffc107);
        opacity: 0.95;
    }
    .flow-line-overflow {
        animation: flow-overflow 0.3s linear infinite;
        stroke-dasharray: 0, 25;
        stroke-linecap: round;
        filter: drop-shadow(0 0 6px #ff00ff);
        opacity: 1;
    }
</style>
<script src="https://unpkg.com/panzoom@9.4.0/dist/panzoom.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const svgContainer = document.getElementById('panzoom-container');
        const edgesLayerBg = document.getElementById('edges-layer-bg');
        const edgesLayerFg = document.getElementById('edges-layer-fg');
        const edgesLayerAnim = document.getElementById('edges-layer-anim');
        const nodesLayer = document.getElementById('nodes-layer');
        const infoPanel = document.getElementById('info-panel');
        
        // Initialize Panzoom
        let w = svgContainer.clientWidth || 1000;
        const panzoomInstance = panzoom(svgContainer, {
            maxZoom: 3,
            minZoom: 0.3,
            initialX: (w / 2) - 1000,
            initialY: -50,
            initialZoom: 1
        });

        document.getElementById('close-info').addEventListener('click', (e) => {
            e.stopPropagation();
            infoPanel.classList.add('hidden');
        });

        // ─── State Global ────────────────────────────────────────────────
        let currentTopologyData = { nodes: [], edges: [] };  // Cache data topologi
        let activeNodeData = null;  // Node yang sedang dibuka di panel
        let activeCommandId = null; // ID perintah AWGC yang sedang berjalan
        let awlrChartInstance = null; // Instance Chart.js untuk panel AWLR
        let commandPollInterval = null; // Interval polling status AWGC

        // ─── Panel Mode Switcher ─────────────────────────────────────────
        const switchPanelMode = (mode) => {
            document.getElementById('panel-mode-default').classList.add('hidden');
            document.getElementById('panel-mode-awlr').classList.add('hidden');
            document.getElementById('panel-mode-awgc').classList.add('hidden');

            const badge = document.getElementById('panel-type-badge');
            const accent = document.getElementById('panel-left-accent');

            if (mode === 'AWLR') {
                document.getElementById('panel-mode-awlr').classList.remove('hidden');
                badge.textContent = 'AWLR';
                badge.className = 'text-[9px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-full bg-blue-900/60 text-blue-300 border border-blue-700/50';
                accent.style.background = '#3b82f6';
                accent.style.boxShadow = '0 0 10px #3b82f6';
            } else if (mode === 'AWGC') {
                document.getElementById('panel-mode-awgc').classList.remove('hidden');
                badge.textContent = 'AWGC';
                badge.className = 'text-[9px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-full bg-cyan-900/60 text-cyan-300 border border-cyan-700/50';
                accent.style.background = '#0ea5e9';
                accent.style.boxShadow = '0 0 10px #0ea5e9';
            } else {
                document.getElementById('panel-mode-default').classList.remove('hidden');
                badge.textContent = 'DSS';
                badge.className = 'text-[9px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-full bg-slate-700 text-slate-400';
                accent.style.background = '#0ea5e9';
                accent.style.boxShadow = '0 0 10px #0ea5e9';
            }
        };

        // ─── Status Dot Online/Offline ───────────────────────────────────
        const setStatusDot = (isOnline) => {
            const dot = document.getElementById('panel-status-dot');
            const text = document.getElementById('panel-status-text');
            dot.classList.remove('hidden');
            text.classList.remove('hidden');
            if (isOnline) {
                dot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
                text.textContent = 'ONLINE';
                text.className = 'text-[9px] text-emerald-400 font-semibold';
            } else {
                dot.className = 'w-2 h-2 rounded-full bg-red-500';
                text.textContent = 'OFFLINE';
                text.className = 'text-[9px] text-red-400 font-semibold';
            }
        };

        // ─── showInfoPanel — Conditional Rendering ───────────────────────
        const showInfoPanel = (node) => {
            if(node.type === 'title' || node.type === 'label_text') return;

            activeNodeData = node;

            // Isi header yang selalu ada
            document.getElementById('info-title').textContent = node.label || node.nama_logger || node.id;
            document.getElementById('info-source').textContent = node.source_name || 'Saluran Irigasi';

            // Online/offline indicator
            if (node.id_logger) {
                setStatusDot(node.is_online !== false);
            } else {
                document.getElementById('panel-status-dot').classList.add('hidden');
                document.getElementById('panel-status-text').classList.add('hidden');
            }

            // ── Mode AWLR ──────────────────────────────────────────────
            if (node.jenis_alat === 'AWLR') {
                switchPanelMode('AWLR');

                const tma = node.tma ?? 0;
                document.getElementById('awlr-tma-value').textContent = tma.toFixed(1);
                document.getElementById('awlr-id-logger').textContent = node.id_logger || '-';
                document.getElementById('awlr-last-time').textContent = node.last_time
                    ? new Date(node.last_time).toLocaleString('id-ID', { hour12: false })
                    : '-';

                // Badge status siaga
                const badge = document.getElementById('awlr-status-badge');
                const siaga = node.status_siaga || 'Normal';
                const siagaConfig = {
                    'Normal':  { bg: 'bg-emerald-900/60', text: 'text-emerald-400', border: 'border-emerald-700/50' },
                    'Siaga 1': { bg: 'bg-yellow-900/60',  text: 'text-yellow-400',  border: 'border-yellow-700/50' },
                    'Siaga 2': { bg: 'bg-orange-900/60',  text: 'text-orange-400',  border: 'border-orange-700/50' },
                    'Banjir':  { bg: 'bg-red-900/60',     text: 'text-red-400',     border: 'border-red-700/50'    },
                    'Kering':  { bg: 'bg-slate-800',      text: 'text-slate-400',   border: 'border-slate-700'     },
                };
                const cfg = siagaConfig[siaga] || siagaConfig['Normal'];
                badge.textContent = siaga;
                badge.className = `text-[10px] font-bold px-3 py-1 rounded-full ${cfg.bg} ${cfg.text} border ${cfg.border}`;

                // Load chart TMA
                loadAwlrChart(node.id);

            // ── Mode AWGC ──────────────────────────────────────────────
            } else if (node.jenis_alat === 'AWGC') {
                switchPanelMode('AWGC');

                const bukaan = node.bukaan_persen ?? 0;
                document.getElementById('awgc-bukaan-value').textContent = Math.round(bukaan);
                document.getElementById('awgc-bukaan-bar').style.width = Math.round(bukaan) + '%';
                document.getElementById('awgc-id-logger').textContent = node.id_logger || '-';
                document.getElementById('awgc-last-time').textContent = node.last_time
                    ? new Date(node.last_time).toLocaleString('id-ID', { hour12: false })
                    : '-';

                // Set slider ke posisi bukaan saat ini
                const slider = document.getElementById('awgc-slider');
                slider.value = Math.round(bukaan);
                document.getElementById('awgc-target-display').textContent = Math.round(bukaan);

                // Reset tombol dan status
                resetAwgcButton();

            // ── Mode Default (DSS / persimpangan biasa) ────────────────
            } else {
                switchPanelMode('default');
                document.getElementById('info-area').textContent = '-';
            }

            infoPanel.classList.remove('hidden');
        };

        // ─── Slider live update ──────────────────────────────────────────
        document.getElementById('awgc-slider').addEventListener('input', (e) => {
            document.getElementById('awgc-target-display').textContent = e.target.value;
        });

        // ─── Fetch Data Topology ─────────────────────────────────────────
        fetch("{{ route('skema-irigasi.api') }}")
            .then(res => res.json())
            .then(data => {
                currentTopologyData = data;
                drawNetwork(data.nodes, data.edges);
            })
            .catch(err => console.error('[Skema] Gagal fetch topologi:', err));

        function drawNetwork(nodes, edges) {
            edgesLayerBg.innerHTML = '';
            edgesLayerFg.innerHTML = '';
            nodesLayer.innerHTML = '';
            
            // Logika BFS: Salurkan AIr dari Hulu ke Hilir dengan Status: full, trickle, dry
            const nodeFlow = {}; 
            const edgeFlow = {}; 
            nodes.forEach(n => nodeFlow[n.id] = 'dry');
            edges.forEach(e => edgeFlow[`${e.source}-${e.target}`] = 'dry');

            // Sumber utama Bendung selalu punya air
            nodeFlow['WEIR_COPONG'] = 'full'; 
            
            const q = [{id: 'WEIR_COPONG', flow: 'full'}];
            while (q.length > 0) {
                const {id: currId, flow: currFlow} = q.shift();
                const currNode = nodes.find(n => n.id === currId);
                
                // Jika pintu tertutup, aliran menjadi trickle (rembesan)
                let outgoingFlow = currFlow;
                if (currNode && currNode.status === 'closed') {
                    outgoingFlow = 'trickle'; 
                } else if (currNode && currNode.status === 'broken') {
                    outgoingFlow = 'dry';
                }

                if (currNode && currNode.status === 'overflow') { outgoingFlow = 'overflow'; }
                else if (currNode && currNode.status === 'high') { outgoingFlow = 'high'; }

                if (outgoingFlow === 'dry') {
                    continue; 
                }

                edges.filter(e => e.source === currId).forEach(edge => {
                    const eId = `${edge.source}-${edge.target}`;
                    
                    let eFlow = outgoingFlow;
                    if(edge.status === 'closed') { eFlow = 'dry'; }
                    else if(edge.status === 'trickle') { eFlow = 'trickle'; }
                    else if(edge.status === 'high') { eFlow = 'high'; }
                    else if(edge.status === 'overflow') { eFlow = 'overflow'; }

                    const flowRank = { 'overflow': 5, 'high': 4, 'full': 3, 'trickle': 2, 'dry': 1 };
                    
                    if (flowRank[eFlow] > flowRank[edgeFlow[eId]]) {
                        edgeFlow[eId] = eFlow;
                    }
                    
                    if (flowRank[eFlow] > flowRank[nodeFlow[edge.target]]) {
                        nodeFlow[edge.target] = eFlow;
                        if (eFlow !== 'dry') {
                            q.push({id: edge.target, flow: eFlow});
                        }
                    }
                });
            }

            // Design Colors matching Tabo Tabo exactly (Utama/Primer)
            const COLOR_LINE_BORDER = "#303481"; // Biru Tua Pekat
            const COLOR_LINE_INNER = "#00a2ffff"; // Biru Cerah
            const COLOR_WEIR_INNER = "#58c0fdff";
            
            // Design Colors khusus Sekunder (Dikembalikan ke orientasi warna orisinilnya)
            const SEC_LINE_BORDER = "#595D9A"; 
            const SEC_LINE_INNER = "#86cfffff"; 

            // Design Colors khusus Tersier (Biru Abu-abu Pucat)
            const TER_LINE_BORDER = "#4a7a9c"; 
            const TER_LINE_INNER = "#a3dfff"; 

            // Design Colors untuk Rembesan/Genangan (Coklat)
            const TRICKLE_LINE_BORDER = "#8B4513";
            const TRICKLE_LINE_INNER = "#cd9959";
            const TRICKLE_WEIR_INNER = "#deb887";

            // Design Colors untuk Siaga/Deras (Oranye)
            const HIGH_LINE_BORDER = "#B26B00";
            const HIGH_LINE_INNER = "#FFAA00";
            const HIGH_WEIR_INNER = "#FFB84D";

            // Design Colors untuk Overflow/Banjir (Magenta/Ungu)
            const OVERFLOW_LINE_BORDER = "#800080";
            const OVERFLOW_LINE_INNER = "#FF00FF";
            const OVERFLOW_WEIR_INNER = "#FFA6FF";

            const COLOR_LABEL_BG = "#ffcb4d";
            const COLOR_TEXT_DARK = "#1a3656";
            
            // Warnaan Pipa Kosong / Mati / Tertutup
            const DRY_LINE_BORDER = "#8f9fb0";
            const DRY_LINE_INNER = "#cad6e0";
            const CLOSED_NODE_BORDER = "#dc2626"; // Merah
            const CLOSED_NODE_INNER = "#f87171"; // Pink
            
            // 1. Draw Edges
            // We draw edges twice: first the thick background border, then the slightly thinner inner color
            edges.forEach(edge => {
                const sourceNode = nodes.find(n => n.id === edge.source);
                const targetNode = nodes.find(n => n.id === edge.target);
                
                if(sourceNode && targetNode) {
                    const eId = `${edge.source}-${edge.target}`;
                    const flowState = edgeFlow[eId];
                    const isPrimary = edge.type === 'primary';
                    const isTertiary = edge.type === 'tertiary';
                    
                    let borderColor, innerColor, weirInnerColor;
                    if (flowState === 'overflow') {
                        borderColor = OVERFLOW_LINE_BORDER;
                        innerColor = OVERFLOW_LINE_INNER;
                        weirInnerColor = OVERFLOW_WEIR_INNER;
                    } else if (flowState === 'high') {
                        borderColor = HIGH_LINE_BORDER;
                        innerColor = HIGH_LINE_INNER;
                        weirInnerColor = HIGH_WEIR_INNER;
                    } else if (flowState === 'full') {
                        if (isPrimary) {
                            borderColor = COLOR_LINE_BORDER;
                            innerColor = COLOR_LINE_INNER;
                        } else if (isTertiary) {
                            borderColor = TER_LINE_BORDER;
                            innerColor = TER_LINE_INNER;
                        } else { // secondary
                            borderColor = SEC_LINE_BORDER;
                            innerColor = SEC_LINE_INNER;
                        }
                        weirInnerColor = COLOR_WEIR_INNER;
                    } else if (flowState === 'trickle') {
                        borderColor = TRICKLE_LINE_BORDER;
                        innerColor = TRICKLE_LINE_INNER;
                        weirInnerColor = TRICKLE_WEIR_INNER;
                    } else {
                        borderColor = DRY_LINE_BORDER;
                        innerColor = DRY_LINE_INNER;
                        weirInnerColor = DRY_LINE_INNER;
                        if (edge.status === 'closed') {
                            borderColor = CLOSED_NODE_BORDER;
                            innerColor = CLOSED_NODE_INNER;
                        }
                    }

                    const drawLine = (p1, p2, layer, width, stroke, isFlowAnim = false) => {
                        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                        const d = `M ${p1.x} ${p1.y} L ${p2.x} ${p2.y}`;
                        path.setAttribute("d", d);
                        path.setAttribute("stroke", stroke);
                        path.setAttribute("stroke-width", width);
                        path.setAttribute("fill", "none");
                        
                        // Deteksi kemiringan pipa (jika x dan y dua-duanya berubah)
                        const dx = Math.abs(p1.x - p2.x);
                        const dy = Math.abs(p1.y - p2.y);
                        const isDiagonal = dx > 1 && dy > 1;

                        // Alteri line-cap:
                        // Pipa Lurus (Horizontal/Vertikal): 'round' 
                        // Pipa Miring (Diagonal): 'butt' 
                        let strokeType = isDiagonal ? "butt" : "round";

                        path.setAttribute("stroke-linecap", isFlowAnim ? "round" : strokeType);
                        path.setAttribute("stroke-linejoin", "miter");
                        
                        if (isFlowAnim) {
                            if (flowState === 'dry') return; 
                            
                            if (flowState === 'overflow') {
                                path.setAttribute("class", "flow-line-overflow");
                                path.style.pointerEvents = "none";
                                path.setAttribute("stroke", "#ffffff");
                                layer.appendChild(path);
                            } else if (flowState === 'high') {
                                path.setAttribute("class", "flow-line-high");
                                path.style.pointerEvents = "none";
                                path.setAttribute("stroke", "#ffffff");
                                layer.appendChild(path);
                            } else if (flowState === 'full') {
                                path.setAttribute("class", "flow-line-1");
                                path.style.pointerEvents = "none";
                                layer.appendChild(path);

                                const path2 = document.createElementNS("http://www.w3.org/2000/svg", "path");
                                path2.setAttribute("d", d);
                                path2.setAttribute("stroke", stroke);
                                path2.setAttribute("stroke-width", width);
                                path2.setAttribute("fill", "none");
                                path2.setAttribute("stroke-linecap", "round");
                                path2.setAttribute("stroke-linejoin", "round");
                                path2.setAttribute("class", "flow-line-2");
                                path2.style.pointerEvents = "none";
                                layer.appendChild(path2);
                            } else if (flowState === 'trickle') {
                                path.setAttribute("class", "flow-line-trickle");
                                path.style.pointerEvents = "none";
                                path.setAttribute("stroke", "rgba(255, 255, 255, 0.4)");
                                layer.appendChild(path);
                            }

                            return; 
                        } else {
                            // Membuat saluran air bisa diklik 
                            path.style.cursor = 'pointer';
                            path.addEventListener('click', (e) => {
                                e.stopPropagation();
                                let labelTarget = targetNode.label ? targetNode.label : targetNode.id;
                                let edgeName = edge.type === 'primary' 
                                    ? 'Saluran Primer Copong' 
                                    : (targetNode.source_name || 'Saluran Sekunder');
                                    
                                let statusInfo = '';
                                if (flowState === 'overflow') statusInfo = ' (Meluap/Banjir!)';
                                else if (flowState === 'high') statusInfo = ' (Siaga/Deras)';
                                else if (flowState === 'trickle') statusInfo = ' (Arus Lemah)';
                                else if (flowState === 'dry') statusInfo = ' (Kering)';

                                showInfoPanel({
                                    type: 'edge',
                                    label: `Ruas ${labelTarget}${statusInfo}`,
                                    source_name: edgeName
                                });
                            });
                        }
                        layer.appendChild(path);
                    };

                    if (isTertiary) {
                        drawLine(sourceNode, targetNode, edgesLayerBg, 16, borderColor);
                        drawLine(sourceNode, targetNode, edgesLayerFg, 10, innerColor);
                        if(flowState !== 'dry') drawLine(sourceNode, targetNode, edgesLayerAnim, 6, flowState === 'full' ? "rgba(255, 255, 255, 0.45)" : innerColor, true);
                    } else {
                        const bgWidth = isPrimary ? 36 : 24;
                        const fgWidth = isPrimary ? 26 : 16;
                        drawLine(sourceNode, targetNode, edgesLayerBg, bgWidth, borderColor);
                        drawLine(sourceNode, targetNode, edgesLayerFg, fgWidth, innerColor);
                        if(flowState !== 'dry') drawLine(sourceNode, targetNode, edgesLayerAnim, fgWidth - 4, flowState === 'full' ? "rgba(255, 255, 255, 0.45)" : innerColor, true);
                    }
                }
            });

            // 2. Draw Nodes
            nodes.forEach(node => {
                const group = document.createElementNS("http://www.w3.org/2000/svg", "g");
                group.setAttribute("transform", `translate(${node.x}, ${node.y})`);
                
                // Add click behavior to interactive nodes
                if(node.type !== 'title' && node.type !== 'label_text') {
                    group.style.cursor = 'pointer';
                    group.addEventListener('click', (e) => {
                        e.stopPropagation(); // Stop panzoom from taking the click
                        showInfoPanel(node);
                    });
                }

                const flowState = nodeFlow[node.id];
                const isClosed = node.status === 'closed';
                
                let borderColor, innerColor;
                if (isClosed) {
                    borderColor = CLOSED_NODE_BORDER;
                    innerColor = CLOSED_NODE_INNER;
                } else if (flowState === 'overflow') {
                    borderColor = OVERFLOW_LINE_BORDER;
                    innerColor = OVERFLOW_LINE_INNER;
                } else if (flowState === 'high') {
                    borderColor = HIGH_LINE_BORDER;
                    innerColor = HIGH_LINE_INNER;
                } else if (flowState === 'full') {
                    borderColor = COLOR_LINE_BORDER;
                    innerColor = COLOR_LINE_INNER;
                } else if (flowState === 'trickle') {
                    borderColor = TRICKLE_LINE_BORDER;
                    innerColor = TRICKLE_LINE_INNER;
                } else {
                    borderColor = DRY_LINE_BORDER;
                    innerColor = DRY_LINE_INNER;
                }
                
                if(node.type === 'title') {
                    const texts = node.label.split('\n');
                    texts.forEach((textLine, i) => {
                        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        text.setAttribute("text-anchor", "middle");
                        text.setAttribute("y", i * 40); // Spasi antar baris diperbesar
                        text.setAttribute("font-size", i === 0 ? "24" : "36"); // Ukuran teks jauh lebih besar
                        text.setAttribute("font-weight", "bold");
                        text.setAttribute("fill", COLOR_LINE_BORDER);
                        text.textContent = textLine;
                        group.appendChild(text);
                    });
                } 
                else if(node.type === 'label_text') {
                    const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    text.setAttribute("text-anchor", "middle");
                    text.setAttribute("font-size", "16");
                    text.setAttribute("font-weight", "bold");
                    text.setAttribute("fill", COLOR_TEXT_DARK);
                    
                    // Fitur baru: Memutar teks (miring atau vertikal)
                    if(node.rotation) {
                        text.setAttribute("transform", `rotate(${node.rotation})`);
                    }
                    
                    text.textContent = node.label;
                    group.appendChild(text);
                }
                else if(node.type === 'weir_large') {
                    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    rect.setAttribute("x", "-30");
                    rect.setAttribute("y", "-30");
                    rect.setAttribute("width", "60");
                    rect.setAttribute("height", "60");
                    rect.setAttribute("fill", innerColor);
                    rect.setAttribute("stroke", borderColor);
                    rect.setAttribute("stroke-width", "5");
                    group.appendChild(rect);

                    const innerRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    innerRect.setAttribute("x", "-15");
                    innerRect.setAttribute("y", "-15");
                    innerRect.setAttribute("width", "30");
                    innerRect.setAttribute("height", "30");
                    innerRect.setAttribute("fill", "white");
                    group.appendChild(innerRect);

                    if (node.label) {
                        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        text.setAttribute("x", "40");
                        text.setAttribute("y", "5");
                        text.setAttribute("font-weight", "bold");
                        text.setAttribute("font-size", "14");
                        text.setAttribute("fill", COLOR_TEXT_DARK);
                        text.textContent = node.label;
                        group.appendChild(text);
                    }
                }
                else if(node.type === 'weir_main') {
                    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    rect.setAttribute("x", "-20");
                    rect.setAttribute("y", "-20");
                    rect.setAttribute("width", "40");
                    rect.setAttribute("height", "40");
                    rect.setAttribute("fill", innerColor);
                    rect.setAttribute("stroke", borderColor);
                    rect.setAttribute("stroke-width", "4");
                    group.appendChild(rect);

                    const innerRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    innerRect.setAttribute("x", "-10");
                    innerRect.setAttribute("y", "-10");
                    innerRect.setAttribute("width", "20");
                    innerRect.setAttribute("height", "20");
                    innerRect.setAttribute("fill", "white");
                    group.appendChild(innerRect);

                    const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    text.setAttribute("x", "30");
                    text.setAttribute("y", "5");
                    text.setAttribute("font-weight", "bold");
                    text.setAttribute("font-size", "14");
                    text.setAttribute("fill", COLOR_TEXT_DARK);
                    text.textContent = node.label;
                    group.appendChild(text);
                }
                else if(node.type === 'junction') {
                    if (node.id.includes('BM')) {
                        // Jika ID mengandung 'BM', gambar kotak kecil menyesuaikan lebar pipa
                        const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                        rect.setAttribute("x", "-8");
                        rect.setAttribute("y", "-8");
                        rect.setAttribute("width", "16");
                        rect.setAttribute("height", "16");
                        rect.setAttribute("fill", "white");
                        rect.setAttribute("stroke", borderColor);
                        rect.setAttribute("stroke-width", "2");
                        group.appendChild(rect);
                    } else {
                        // Gambar lingkaran persimpangan mungil yang seolah "masuk" di dalam pipa
                        const circleOuter = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                        circleOuter.setAttribute("r", "8");
                        circleOuter.setAttribute("fill", "white");
                        circleOuter.setAttribute("stroke", borderColor);
                        circleOuter.setAttribute("stroke-width", "2");
                        group.appendChild(circleOuter);

                        // Hitung jumlah saluran (edges) yang terhubung ke titik ini
                        let connectedEdgesCount = 0;
                        edges.forEach(edge => {
                            if (edge.source === node.id || edge.target === node.id) {
                                connectedEdgesCount++;
                            }
                        });

                        // Tampilkan titik tengah (inner dot) HANYA jika saluran terhubung >= 3
                        if (connectedEdgesCount >= 3) {
                            const circleInner = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                            circleInner.setAttribute("r", "2.5");
                            circleInner.setAttribute("fill", borderColor);
                            group.appendChild(circleInner);
                        }
                    }
                }
                else if(node.type === 'label_yellow') {
                    const textLength = node.label.length * 6.5 + 10;
                    const isNodeActive = flowState !== 'dry';
                    
                    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    rect.setAttribute("x", "-10");
                    rect.setAttribute("y", "-10");
                    rect.setAttribute("width", Math.max(textLength, 40));
                    rect.setAttribute("height", "20");
                    rect.setAttribute("fill", isNodeActive ? COLOR_LABEL_BG : "#cbd5e1");
                    rect.setAttribute("stroke", isNodeActive ? COLOR_LINE_BORDER : DRY_LINE_BORDER);
                    rect.setAttribute("stroke-width", "1.5");
                    
                    const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    text.textContent = node.label;
                    text.setAttribute("fill", COLOR_TEXT_DARK);
                    text.setAttribute("font-size", "10px");
                    text.setAttribute("font-weight", "bold");
                    text.setAttribute("x", "-2");
                    text.setAttribute("y", "3");
                    
                    group.appendChild(rect);
                    group.appendChild(text);
                }
                // ─────────────────────────────────────────────────────────
                // NODE TYPE: SENSOR_AWLR (Sensor Tinggi Muka Air)
                // Bentuk: Lingkaran berdenyut dengan ikon gelombang
                // ─────────────────────────────────────────────────────────
                else if(node.type === 'sensor_awlr') {
                    const isOnline = node.is_online !== false;
                    const siaga   = node.status_siaga || 'Normal';
                    const sensorColor = {
                        'Normal':  '#22c55e',
                        'Siaga 1': '#eab308',
                        'Siaga 2': '#f97316',
                        'Banjir':  '#ef4444',
                        'Kering':  '#94a3b8',
                    }[siaga] || '#22c55e';

                    // Cincin luar berdenyut (hanya jika online)
                    if (isOnline) {
                        const pulse = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                        pulse.setAttribute("r", "18");
                        pulse.setAttribute("fill", sensorColor);
                        pulse.setAttribute("opacity", "0.2");
                        pulse.innerHTML = `<animate attributeName="r" values="14;20;14" dur="2s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.3;0;0.3" dur="2s" repeatCount="indefinite"/>`;
                        group.appendChild(pulse);
                    }

                    // Lingkaran utama
                    const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    circle.setAttribute("r", "13");
                    circle.setAttribute("fill", isOnline ? sensorColor + '25' : '#334155');
                    circle.setAttribute("stroke", isOnline ? sensorColor : '#64748b');
                    circle.setAttribute("stroke-width", "2.5");
                    group.appendChild(circle);

                    // Teks label AWLR
                    const lbl = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    lbl.setAttribute("text-anchor", "middle");
                    lbl.setAttribute("y", "4");
                    lbl.setAttribute("font-size", "7");
                    lbl.setAttribute("font-weight", "bold");
                    lbl.setAttribute("fill", isOnline ? sensorColor : '#64748b');
                    lbl.textContent = 'AWLR';
                    group.appendChild(lbl);

                    // TMA value di bawah node
                    if (node.tma !== undefined) {
                        const tmaText = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        tmaText.setAttribute("text-anchor", "middle");
                        tmaText.setAttribute("y", "27");
                        tmaText.setAttribute("font-size", "9");
                        tmaText.setAttribute("font-weight", "bold");
                        tmaText.setAttribute("fill", sensorColor);
                        tmaText.textContent = parseFloat(node.tma).toFixed(1) + ' cm';
                        group.appendChild(tmaText);
                    }
                }
                // ─────────────────────────────────────────────────────────
                // NODE TYPE: GATE_AWGC (Pintu Air Bermotor)
                // Bentuk: Segi enam / ikon pintu air
                // ─────────────────────────────────────────────────────────
                else if(node.type === 'gate_awgc') {
                    const bukaan    = node.bukaan_persen ?? 0;
                    const isOnline  = node.is_online !== false;
                    const gateColor = bukaan <= 0  ? '#ef4444'   // Merah = Tertutup
                                    : bukaan < 30  ? '#f97316'   // Oranye = Hampir tutup
                                    : bukaan < 80  ? '#0ea5e9'   // Biru = Normal
                                    : '#8b5cf6';                  // Ungu = Bukaan Besar

                    // Background segiempat dengan sudut tumpul
                    const gateRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    gateRect.setAttribute("x", "-16");
                    gateRect.setAttribute("y", "-16");
                    gateRect.setAttribute("width", "32");
                    gateRect.setAttribute("height", "32");
                    gateRect.setAttribute("rx", "4");
                    gateRect.setAttribute("fill", isOnline ? gateColor + '20' : '#1e293b');
                    gateRect.setAttribute("stroke", isOnline ? gateColor : '#475569');
                    gateRect.setAttribute("stroke-width", "2.5");
                    group.appendChild(gateRect);

                    // Simbol pintu (garis vertikal = palang pintu)
                    const gateBar = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    const barHeight = Math.round(20 * (1 - bukaan / 100)); // Makin tutup makin tinggi
                    gateBar.setAttribute("x", "-3");
                    gateBar.setAttribute("y", String(-8));
                    gateBar.setAttribute("width", "6");
                    gateBar.setAttribute("height", String(barHeight + 4));
                    gateBar.setAttribute("rx", "2");
                    gateBar.setAttribute("fill", isOnline ? gateColor : '#475569');
                    group.appendChild(gateBar);

                    // Label AWGC
                    const lbl = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    lbl.setAttribute("text-anchor", "middle");
                    lbl.setAttribute("y", "26");
                    lbl.setAttribute("font-size", "7");
                    lbl.setAttribute("font-weight", "bold");
                    lbl.setAttribute("fill", isOnline ? gateColor : '#64748b');
                    lbl.textContent = 'AWGC';
                    group.appendChild(lbl);

                    // Persentase bukaan
                    const pctText = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    pctText.setAttribute("text-anchor", "middle");
                    pctText.setAttribute("y", "36");
                    pctText.setAttribute("font-size", "8");
                    pctText.setAttribute("fill", '#94a3b8');
                    pctText.textContent = Math.round(bukaan) + '%';
                    group.appendChild(pctText);
                }

                nodesLayer.appendChild(group);
            });
        }

        // ─────────────────────────────────────────────────────────────────
        // AWLR CHART — Load historis TMA 6 jam via API
        // ─────────────────────────────────────────────────────────────────
        async function loadAwlrChart(nodeId) {
            const loading = document.getElementById('awlr-chart-loading');
            const canvas = document.getElementById('awlr-chart');
            loading.classList.remove('hidden');

            // Destroy chart lama jika ada
            if (awlrChartInstance) { awlrChartInstance.destroy(); awlrChartInstance = null; }

            try {
                const res = await fetch(`/api/skema/node/${nodeId}/history`);
                const json = await res.json();

                if (!json.success || !json.data.length) {
                    loading.querySelector('span').textContent = 'Belum ada data historis.';
                    return;
                }

                loading.classList.add('hidden');

                // Dynamically load Chart.js jika belum ada
                if (!window.Chart) {
                    await new Promise((resolve) => {
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
                        s.onload = resolve;
                        document.head.appendChild(s);
                    });
                }

                const labels = json.data.map(d => new Date(d.waktu).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
                const values = json.data.map(d => parseFloat(d.s1 ?? d.s2 ?? 0));

                awlrChartInstance = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{ label: 'TMA (cm)', data: values, borderColor: '#38bdf8', backgroundColor: 'rgba(56,189,248,0.1)', borderWidth: 1.5, pointRadius: 0, tension: 0.4, fill: true }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { display: false },
                            y: { ticks: { color: '#94a3b8', font: { size: 9 } }, grid: { color: '#1e293b' } }
                        }
                    }
                });
            } catch (e) {
                loading.querySelector('span').textContent = 'Gagal memuat data.';
                console.error('[Skema AWLR] Chart error:', e);
            }
        }

        // ─────────────────────────────────────────────────────────────────
        // AWGC COMMAND — Kirim perintah buka/tutup ke logger fisik
        // ─────────────────────────────────────────────────────────────────
        function resetAwgcButton() {
            const btn = document.getElementById('awgc-btn-send');
            btn.disabled = false;
            btn.className = 'w-full py-2.5 rounded-lg font-bold text-sm transition-all duration-200 bg-[#0ea5e9] hover:bg-[#0284c7] active:scale-95 text-white shadow-lg shadow-cyan-900/50';
            document.getElementById('awgc-btn-text').textContent = '⚡ Terapkan Posisi Pintu';
            document.getElementById('awgc-command-status').classList.add('hidden');
            if (commandPollInterval) { clearInterval(commandPollInterval); commandPollInterval = null; }
        }

        window.sendAwgcCommand = async function() {
            if (!activeNodeData || activeNodeData.jenis_alat !== 'AWGC') return;

            const target = parseInt(document.getElementById('awgc-slider').value);
            const btn    = document.getElementById('awgc-btn-send');
            const txtEl  = document.getElementById('awgc-btn-text');
            const statusBox  = document.getElementById('awgc-command-status');
            const statusText = document.getElementById('awgc-command-status-text');

            // Kunci tombol → loading state
            btn.disabled = true;
            btn.className = 'w-full py-2.5 rounded-lg font-bold text-sm bg-slate-700 text-slate-400 cursor-not-allowed';
            txtEl.textContent = '⏳ Mengirim perintah...';
            statusBox.classList.remove('hidden');
            statusText.textContent = 'Menyambung ke MQTT broker...';
            statusText.className = 'text-slate-400';

            try {
                const res  = await fetch('/api/awgc/command', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({
                        node_skema_id: activeNodeData.id,
                        id_logger:     activeNodeData.id_logger,
                        target_bukaan_persen: target
                    })
                });

                const json = await res.json();

                if (!json.success) {
                    txtEl.textContent = '❌ Gagal Dikirim';
                    statusText.textContent = json.message || 'Terjadi kesalahan.';
                    statusText.className = 'text-red-400';
                    setTimeout(resetAwgcButton, 4000);
                    return;
                }

                activeCommandId = json.command_id;
                txtEl.textContent = '📡 Menunggu Konfirmasi Alat...';
                statusText.textContent = `Perintah terkirim (ID: ${activeCommandId}). Menunggu respons...`;
                statusText.className = 'text-yellow-400';

                // Polling status setiap 3 detik
                commandPollInterval = setInterval(async () => {
                    try {
                        const pRes  = await fetch(`/api/awgc/status/${activeCommandId}`);
                        const pJson = await pRes.json();

                        if (pJson.is_finished) {
                            clearInterval(commandPollInterval);
                            commandPollInterval = null;

                            if (pJson.status === 'success') {
                                txtEl.textContent = '✅ Berhasil Dieksekusi';
                                btn.className = 'w-full py-2.5 rounded-lg font-bold text-sm bg-emerald-700 text-white cursor-not-allowed';
                                statusText.textContent = 'Pintu bergerak ke posisi ' + target + '%';
                                statusText.className = 'text-emerald-400';
                                // Update gauge bar secara visual
                                document.getElementById('awgc-bukaan-value').textContent = target;
                                document.getElementById('awgc-bukaan-bar').style.width = target + '%';
                            } else {
                                txtEl.textContent = '⚠️ Gagal di Alat';
                                btn.className = 'w-full py-2.5 rounded-lg font-bold text-sm bg-red-900/60 text-red-300 cursor-not-allowed';
                                statusText.textContent = pJson.pesan_error || 'Alat melaporkan error.';
                                statusText.className = 'text-red-400';
                            }
                            setTimeout(resetAwgcButton, 5000);
                        }
                    } catch (e) { console.error('[AWGC Poll]', e); }
                }, 3000);

            } catch (e) {
                txtEl.textContent = '❌ Error Jaringan';
                statusText.textContent = 'Periksa koneksi internet Anda.';
                statusText.className = 'text-red-400';
                setTimeout(resetAwgcButton, 4000);
            }
        };

        // ─────────────────────────────────────────────────────────────────
        // WEBSOCKET — Laravel Echo Real-Time Listener
        // Aktif hanya jika VITE_ENABLE_ECHO=true di .env
        // ─────────────────────────────────────────────────────────────────
        @if(env('VITE_ENABLE_ECHO') === 'true')
        if (window.Echo) {
            window.Echo.channel('sensor.data')
                .listen('SensorDataUpdated', (payload) => {
                    console.log('[Skema WS] Event diterima:', payload);

                    // Update node di cache data topologi
                    const nodeIdx = currentTopologyData.nodes.findIndex(n => n.id === payload.node_id);
                    if (nodeIdx !== -1) {
                        // Merge payload ke node data
                        Object.assign(currentTopologyData.nodes[nodeIdx], {
                            tma:          payload.tma,
                            bukaan_persen: payload.bukaan_persen,
                            status_siaga:  payload.status_siaga,
                            flow_state:    payload.flow_state,
                            is_online:     true,
                            last_time:     payload.waktu,
                        });

                        if (payload.flow_state) {
                            // Re-render skema dengan data baru
                            drawNetwork(currentTopologyData.nodes, currentTopologyData.edges);
                        }

                        // Jika panel yang terbuka adalah node ini, refresh panelnya
                        if (activeNodeData && activeNodeData.id === payload.node_id) {
                            showInfoPanel(currentTopologyData.nodes[nodeIdx]);
                        }
                    }

                    // Handle konfirmasi perintah AWGC
                    if (payload.event_type === 'command_confirmed' && payload.command_id == activeCommandId) {
                        if (commandPollInterval) { clearInterval(commandPollInterval); commandPollInterval = null; }
                        const isSuccess = payload.status_command === 'success';
                        const txtEl = document.getElementById('awgc-btn-text');
                        const statusText = document.getElementById('awgc-command-status-text');
                        if (isSuccess) {
                            txtEl.textContent = '✅ Berhasil (via WebSocket)';
                            statusText.textContent = 'Pintu bergerak ke posisi ' + payload.bukaan_persen + '%';
                            statusText.className = 'text-emerald-400';
                        } else {
                            txtEl.textContent = '⚠️ Gagal (via WebSocket)';
                            statusText.className = 'text-red-400';
                        }
                        setTimeout(resetAwgcButton, 5000);
                    }
                });

            // Deteksi koneksi terputus
            window.Echo.connector.socket.on('disconnect', () => {
                console.warn('[Skema WS] WebSocket disconnected');
            });
        }
        @endif

    });
</script>
@endpush
