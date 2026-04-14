@extends('layouts.app')

@section('content')
    <div class="h-[calc(100vh-4rem)] w-full overflow-hidden relative shadow-inner flex" style="background-color: #ffffff;">

        <!-- Informasi DSS Floating Panel -->
        <div id="info-panel"
            class="hidden absolute top-4 left-4 bg-[#1e293b] shadow-2xl shadow-black/40 border border-slate-700/60 overflow-hidden flex flex-col"
            style="width: 340px; max-height: calc(100vh - 6rem); border-radius: 8px; z-index: 50; backdrop-filter: blur(12px);">
            <!-- Header -->
            <div class="flex justify-between items-center px-4 py-2.5 border-b border-slate-700/80 bg-slate-800/80 flex-shrink-0">
                <div class="flex items-center gap-2">
                    
                    <span id="panel-type-badge"
                        class="text-[9px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-full bg-slate-700 text-slate-400">DSS</span>
                    <span class="text-slate-300 text-[11px] font-bold tracking-widest uppercase">Informasi Panel</span>
                </div>
                <div class="flex items-center gap-2">

                    <span id="panel-status-dot" class="hidden w-2 h-2 rounded-full bg-slate-500"></span>
                    <span id="panel-status-text" class="hidden text-[9px] text-slate-500 font-semibold"></span>
                    <button id="close-info"
                        class="text-slate-400 hover:text-white transition-colors text-xl leading-none">&times;</button>
                </div>
            </div>

            <div class="px-4 py-3.5 flex relative bg-slate-800/90 text-white overflow-hidden flex-shrink-0">
                <div class="absolute inset-0 bg-gradient-to-r from-[#0ea5e9]/10 to-transparent"></div>
                <div id="panel-left-accent"
                    class="absolute left-0 top-0 bottom-0 w-1 bg-[#0ea5e9] shadow-[0_0_10px_#0ea5e9]"></div>
                <div class="pl-2 relative z-10 w-full">
                    <div class="text-[11px] mb-1 font-semibold text-[#38bdf8] uppercase tracking-wider" id="info-source">
                        Saluran Irigasi</div>
                    <div class="text-lg font-extrabold tracking-tight text-white drop-shadow-md truncate" id="info-title">-
                    </div>
                </div>
            </div>

            <div id="panel-mode-default" class="bg-slate-900/90 text-sm flex flex-col flex-1 min-h-0">
                <div id="panel-page-main" class="flex flex-col flex-1 min-h-0">
                    <!-- Area Konten Bisa Digulir -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar pb-2">

                    {{-- STATISTIK IRIGASI SESUAI KEBUTUHAN LAHAN --}}
                    <div id="panel-statistik-wrap" class="hidden">
                        <div class="flex justify-between items-center px-4 py-3 border-b border-slate-800/80 text-slate-400">
                            <span class="text-xs font-medium">Luas Area Layanan</span>
                            <div><span class="font-bold text-[#38bdf8] text-base" id="panel-luas-area">-</span> <span class="text-[10px]">Ha.</span></div>
                        </div>
                        <div class="px-4 py-2.5 border-b border-slate-800/80 text-slate-400 text-xs">
                            <div class="flex justify-between items-center mb-1.5">
                                <span>Kebutuhan Air Irigasi</span>
                                <span class="text-slate-300 font-mono"><span id="panel-kb-irigasi">0</span> <span class="text-[9px] text-slate-500">lt/dt</span></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Nilai kehilangan 0.20%</span>
                                <span class="text-slate-300 font-mono"><span id="panel-kb-kehilangan">0</span> <span class="text-[9px] text-slate-500">lt/dt</span></span>
                            </div>
                        </div>
                        <div class="px-4 py-2.5 border-b border-slate-800/80 text-slate-400 text-xs">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-slate-300">Total Kebutuhan Air</span>
                                <span class="text-white font-mono font-medium"><span id="panel-kb-total">0</span> <span class="text-[9px] text-slate-500">lt/dt</span></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Faktor K</span>
                                <span class="text-[#38bdf8] font-bold" id="panel-faktor-k">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- AWLR Sensor Info --}}
                    <div id="panel-awlr-wrap" class="hidden px-4 py-4 border-b border-slate-800/80 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-b from-fuchsia-900/10 to-transparent"></div>
                        <div class="relative z-10 w-full">
                            <div class="text-[9px] font-bold text-fuchsia-400 uppercase tracking-widest mb-1.5 flex items-center justify-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Sensor Tinggi Muka Air
                            </div>
                            <div class="flex items-baseline justify-center gap-2 mt-2">
                                <span class="font-black text-4xl text-fuchsia-300 drop-shadow-[0_0_8px_rgba(217,70,239,0.5)]" id="panel-awlr-tma">-</span>
                                <span class="text-sm font-semibold text-fuchsia-500">cm</span>
                            </div>
                            <div id="panel-awlr-status" class="mt-3 inline-flex px-3 py-1 rounded-sm text-[10px] font-black tracking-widest uppercase mb-4"></div>
                            
                            {{-- Chart Historis TMA --}}
                            <div class="mt-2 w-full h-28 relative bg-slate-900/50 border border-slate-700/50 rounded-lg p-2 shadow-inner">
                                <div id="awlr-chart-loading" class="absolute inset-0 flex items-center justify-center hidden bg-slate-900/80 z-20 rounded-lg backdrop-blur-sm">
                                    <span class="text-[10px] text-fuchsia-400 animate-pulse font-bold tracking-widest uppercase">Memuat Grafik...</span>
                                </div>
                                <canvas id="awlr-chart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Saluran info --}}
                    <div id="panel-saluran-wrap" class="hidden px-4 py-2.5 border-b border-slate-800/80">
                        <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Saluran</div>
                        <div id="panel-saluran-name" class="text-xs font-semibold text-slate-200"></div>
                        <div id="panel-elevasi-val" class="text-[9px] text-slate-400 mt-0.5"></div>
                    </div>

                    {{-- TMA Hulu / Hilir --}}
                    <div id="panel-tma-wrap" class="hidden px-4 py-2.5 border-b border-slate-800/80">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="text-center">
                                <div class="text-[9px] font-bold text-sky-400 uppercase tracking-wide mb-1">TMA Hulu</div>
                                <div class="text-lg font-black text-sky-300 leading-none"><span id="panel-tma-hulu">-</span></div>
                                <div class="text-[9px] text-slate-500 mt-0.5">cm</div>
                            </div>
                            <div class="text-center border-l border-r border-slate-700">
                                <div class="text-[9px] font-bold text-violet-400 uppercase tracking-wide mb-1">Selisih</div>
                                <div class="text-lg font-black text-violet-300 leading-none"><span id="panel-tma-selisih">-</span></div>
                                <div class="text-[9px] text-slate-500 mt-0.5">cm</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[9px] font-bold text-cyan-400 uppercase tracking-wide mb-1">TMA Hilir</div>
                                <div class="text-lg font-black text-cyan-300 leading-none"><span id="panel-tma-hilir">-</span></div>
                                <div class="text-[9px] text-slate-500 mt-0.5">cm</div>
                            </div>
                        </div>
                    </div>

                    {{-- Debit & Pemenuhan --}}
                    <div id="panel-debit-wrap" class="hidden px-4 py-2.5 border-b border-slate-800/80">
                        <div class="flex justify-between items-baseline mb-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Debit Aktual</span>
                            <div>
                                <span class="font-black text-xl text-[#0ea5e9] drop-shadow-[0_0_5px_#0ea5e9]" id="panel-debit-val">0</span>
                                <span class="text-[10px] text-slate-500"> m³/dtk</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-baseline mb-1.5">
                            <span class="text-[9px] text-slate-300">Kapasitas Rencana</span>
                            <span class="text-xs text-slate-300 font-mono"><span id="panel-kapasitas-val">-</span> m³/dtk</span>
                        </div>
                        {{-- Progress bar pemenuhan --}}
                        <div class="w-full bg-slate-700 rounded-full h-1.5 mb-1">
                            <div id="panel-debit-bar" class="h-1.5 rounded-full bg-emerald-400 transition-all duration-500" style="width:0%"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[9px] text-slate-300">Tingkat Pemenuhan</span>
                            <span id="panel-debit-pct" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-900/60 text-emerald-400">-</span>
                        </div>
                    </div>

                    {{-- Simulasi Water Balance --}}
                    <div id="panel-wb-wrap" class="hidden px-4 py-3 border-b border-slate-800/80 bg-slate-900/40">
                        <div class="text-[9px] font-bold text-orange-400 border-b border-orange-900/30 pb-1 mb-2 uppercase tracking-widest flex items-center gap-1.5">
                            📊 Simulasi Kinerja (Water Balance)
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-2">
                            <div class="bg-slate-800/70 p-1.5 rounded border border-slate-700/50">
                                <span class="block text-[9px] text-slate-400 mb-0.5">Q Perintah</span>
                                <span class="font-mono text-sm text-slate-200"><span id="panel-wb-perintah">0</span> <span class="text-[9px] text-slate-500">m³/s</span></span>
                            </div>
                            <div class="bg-slate-800/70 p-1.5 rounded border border-emerald-900/30">
                                <span class="block text-[9px] text-emerald-500/80 mb-0.5">Q Terukur</span>
                                <span class="font-mono text-sm text-emerald-400"><span id="panel-wb-terukur">0</span> <span class="text-[9px] text-emerald-700">m³/s</span></span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5 p-2 bg-slate-800/40 rounded border border-slate-800">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-slate-400">Error Deviasi</span>
                                <span class="font-mono text-xs font-bold" id="panel-wb-err-parent"><span id="panel-wb-err-val">0</span> m³/s (<span id="panel-wb-err-pct">0</span>%)</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-slate-400">Selisih Vol. (1 Jam)</span>
                                <span class="font-mono text-xs font-bold" id="panel-wb-vol-parent"><span id="panel-wb-vol">0</span> m³</span>
                            </div>
                            <div id="panel-wb-status" class="mt-1.5 px-2 py-1.5 rounded text-[10px] font-bold tracking-wider uppercase text-center border">
                                STABIL
                            </div>
                        </div>
                    </div>

                    {{-- Status Pintu (AWGC gates) --}}
                    <div id="panel-gates-wrap" class="hidden px-4 py-2.5 border-b border-slate-800/80">
                        <div class="text-[9px] font-bold text-slate-300 uppercase tracking-widest mb-2">Status Pintu Air</div>
                        <div id="panel-gates-list" class="space-y-1.5"></div>
                    </div>

                    {{-- Saluran Hilir --}}
                    <div id="panel-destinations-wrap" class="px-4 pt-2.5 pb-4 text-slate-400 text-xs bg-slate-900 border-b border-slate-800/80">
                        <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-2">Menuju Selanjutnya</div>
                        <div id="panel-destinations-list" class="space-y-1.5"></div>
                    </div>

                    </div> <!-- Penutup area scroll -->

                    {{-- Tombol Bawah Tetap --}}
                    <div id="container-btn-control" class="hidden px-4 py-3 bg-slate-800 z-10 border-t border-slate-700/80 flex-shrink-0 shadow-[0_-5px_15px_rgba(0,0,0,0.3)]">
                        <button id="btn-show-control" class="w-full py-2.5 rounded shadow flex items-center justify-center gap-2 font-bold text-xs transition-colors bg-cyan-900/40 hover:bg-cyan-800/60 text-cyan-300 border border-cyan-700/50 hover:shadow-[0_0_10px_rgba(6,182,212,0.3)]">
                            ⚙️ Buka Kontrol Mesin Pintu
                        </button>
                    </div>
                </div>

                <!-- Halaman Drill Down sudah dipindah ke halaman /kontrol-pintu terpisah -->
            </div> <!-- Penutup info-panel layout -->
        </div>

        <!-- The Panzoom Container -->
        <div id="panzoom-container" class="w-full h-full !cursor-default active:!cursor-grabbing">
            <svg id="network-svg" width="3000" height="2000" class="origin-top-left font-sans">
                <!-- Layers -->
                <g id="edges-layer-bg"></g> <!-- Border lines -->
                <g id="edges-layer-fg"></g> <!-- Inner lines -->
                <g id="edges-layer-anim"></g> <!-- Flow Animations -->
                <g id="nodes-layer"></g> <!-- Nodes and Texts -->
            </svg>
        </div>

        <!-- Panel Legenda Warna Status -->
        <div id="legend-panel" class="absolute bottom-6 right-6 bg-[#1e293b]/95 shadow-2xl shadow-black/50 border border-slate-700/60 rounded-lg overflow-hidden backdrop-blur-md" style="z-index: 40; width: 220px;">
            <div class="px-3 py-2 bg-slate-800 border-b border-slate-700/80 flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Keterangan Aliran</span>
                <button onclick="document.getElementById('legend-content').classList.toggle('hidden')" class="text-slate-400 hover:text-white transition-colors text-xs p-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>
            <div id="legend-content" class="hidden px-3 py-3 space-y-2.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-3 h-3 rounded-full bg-[#d946ef] shadow-[0_0_8px_#d946ef]"></div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-200 leading-none mb-0.5">Meluap (Banjir)</span>
                        <span class="text-[9px] text-slate-500 font-mono">> 134% kapasitas</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-3 h-3 rounded-full bg-[#ea580c] shadow-[0_0_8px_#ea580c]"></div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-200 leading-none mb-0.5">Deras (Siaga)</span>
                        <span class="text-[9px] text-slate-500 font-mono">101% - 134% kapasitas</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-3 h-3 rounded-full bg-[#0ea5e9] shadow-[0_0_8px_#0ea5e9]"></div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-200 leading-none mb-0.5">Normal (Stabil)</span>
                        <span class="text-[9px] text-slate-500 font-mono">50% - 100% kapasitas</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-3 h-3 rounded-full bg-[#b45309] shadow-[0_0_8px_#b45309]"></div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-200 leading-none mb-0.5">Surut (Kritis)</span>
                        <span class="text-[9px] text-slate-500 font-mono">1% - 49% kapasitas</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-3 h-3 rounded-full bg-slate-600 border border-slate-500 border-dashed bg-transparent"></div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-400 leading-none mb-0.5">Kering / Terputus</span>
                        <span class="text-[9px] text-slate-500 font-mono">Tidak ada aliran (0%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Bar Koneksi API (kanan atas) -->
        <div id="api-status-bar" class="absolute top-4 right-4 flex items-center gap-2 bg-[#1e293b]/90 border border-slate-700/60 rounded-full px-3 py-1.5 shadow-lg backdrop-blur-md" style="z-index: 45;">
            <span id="api-status-dot" class="w-2 h-2 rounded-full bg-slate-500"></span>
            <span id="api-status-text" class="text-[10px] font-semibold text-slate-400">Menghubungkan...</span>
            <span class="text-slate-700">|</span>
            <span class="text-[10px] text-slate-500">Update: <span id="api-last-update" class="text-slate-400 font-mono">-</span></span>
            <span class="text-slate-700">|</span>
            <span class="text-[10px] text-slate-500">Aktif: <span id="api-node-count" class="text-emerald-400 font-mono font-bold">-</span> node</span>
        </div>

        <!-- Banner Error Koneksi (muncul dari atas saat API gagal) -->
        <div id="api-error-banner" class="hidden absolute top-0 left-0 right-0 flex items-center justify-center gap-3 py-2 px-4 bg-red-900/95 border-b border-red-700 shadow-lg" style="z-index: 60;">
            <svg class="w-4 h-4 text-red-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
            <span class="text-sm font-semibold text-red-200">Koneksi ke server terputus — Data sensor tidak dapat diperbarui</span>
            <button onclick="document.getElementById('api-error-banner').classList.add('hidden')" class="ml-auto text-red-400 hover:text-white text-lg leading-none">&times;</button>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        /* SCROLLBAR ELEGAN UNTUK PANEL */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5); /* Warna dasar track lebih transparan */
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(56, 189, 248, 0.3); /* Warna thumb / scroll biru cerah transparan */
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(56, 189, 248, 0.6);
        }

        @verbatim @keyframes flow-1 {
                from {
                    stroke-dashoffset: 54;
                }

                to {
                    stroke-dashoffset: 0;
                }
            }

            @keyframes flow-2 {
                from {
                    stroke-dashoffset: 67;
                }

                to {
                    stroke-dashoffset: 0;
                }
            }

            @keyframes flow-trickle {
                from {
                    stroke-dashoffset: 40;
                }

                to {
                    stroke-dashoffset: 0;
                }
            }

            @keyframes flow-high {
                from {
                    stroke-dashoffset: 26;
                }

                to {
                    stroke-dashoffset: 0;
                }
            }

            @keyframes flow-overflow {
                from {
                    stroke-dashoffset: 25;
                }

                to {
                    stroke-dashoffset: 0;
                }
            }
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

            @keyframes radar-ping {
                0% { transform: scale(0.8); opacity: 1; stroke-width: 4px; }
                100% { transform: scale(var(--ping-scale, 3.5)); opacity: 0; stroke-width: 1px; }
            }
            .active-ping-circle {
                animation: radar-ping 1.5s cubic-bezier(0.2, 0.8, 0.2, 1) infinite;
                fill: none;
                stroke: #0400ffb4;
                transform-origin: 0px 0px; /* Fix scaling miring di SVG */
                pointer-events: none; /* Supaya tidak menghalangi event klik pada pintu aslinya */
            }

            /* Efek Hover Ping Pratinjau Jarak Jauh (Kuning/Putih Terang) */
            @keyframes hover-ping {
                0% { transform: scale(0.8); opacity: 1; stroke-width: 4px; }
                100% { transform: scale(var(--hover-scale, 2.5)); opacity: 0; stroke-width: 1px; }
            }
            .hover-ping-circle {
                animation: hover-ping 1s cubic-bezier(0.2, 0.8, 0.2, 1) infinite;
                fill: none;
                stroke: #ffb700ff; /* Warnai kuning radar hover-nya */
                transform-origin: 0px 0px;
                pointer-events: none;
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
                    // Bersihkan efek ping 
                    document.querySelectorAll('.active-ping-circle').forEach(el => el.remove());
                    if (typeof removeHoverPing === 'function') removeHoverPing(); // Hapus jejak kuning hover
                    activeNodeData = null;
                });

                // Mencegah scroll di dalam panel malah membuat SVG background zoom out/in
                infoPanel.addEventListener('wheel', (e) => {
                    e.stopPropagation();
                }, { passive: false });

                // Helper memunculkan Radar Ping
                const drawRadarPing = (group, nodeType = 'sensor_awlr') => {
                    document.querySelectorAll('.active-ping-circle').forEach(el => el.remove());
                    if (typeof removeHoverPing === 'function') removeHoverPing(); // Langsung matikan hover kuning jika node jadi "aktif"
                    
                    if (!group) return;

                    // Cek tipe kotak: Pintu air, Bendung, atau Junction yang memiliki kode BM (Bagi Mutap)
                    const isRectShape = ['gate_awgc', 'weir_main', 'weir_large', 'weir_small'].includes(nodeType) || group.id.includes('-BM');

                    const createPing = () => {
                        const ping = document.createElementNS("http://www.w3.org/2000/svg", isRectShape ? "rect" : "circle");
                        ping.setAttribute("class", "active-ping-circle");
                        
                        if (isRectShape) {
                            // Sesuaikan ukuran dinamis berdasarkan jenis pintu/bendung
                            let bx = "-16", by = "-16", bw = "32", bh = "32"; // default gate_awgc
                            if (nodeType === 'weir_large') {
                                bx = "-25"; by = "-25"; bw = "50"; bh = "50";
                            } else if (nodeType === 'weir_main') {
                                bx = "-20"; by = "-20"; bw = "40"; bh = "40";
                            } else if (group.id.includes('-BM')) {
                                bx = "-11"; by = "-11"; bw = "22"; bh = "22";
                            }
                            
                            ping.setAttribute("x", bx);
                            ping.setAttribute("y", by);
                            ping.setAttribute("width", bw);
                            ping.setAttribute("height", bh);    
                            ping.setAttribute("rx", "1"); // Sudut ketat
                            
                            // Hitung rasio scale berdasarkan daya jangkau rambatan keluar (padding yang seragam).
                            // Untuk radar (biru), rambatan mekar keluar sejauh kurang lebih 40px dari setiap sisi.
                            const targetSize = parseFloat(bw) + 52; // bw + (26px * 2 sisi)
                            const scaleFactor = targetSize / parseFloat(bw);
                            ping.style.setProperty('--ping-scale', scaleFactor.toFixed(2));
                        } else {
                            ping.setAttribute("cx", "0");
                            ping.setAttribute("cy", "0");
                            ping.setAttribute("r", "16"); 
                        }
                        return ping;
                    };

                    const ping1 = createPing();
                    const ping2 = createPing();
                    ping2.style.animationDelay = "0.75s"; // Setengah jeda (biar 2 riak bergantian)

                    // Selipkan di belakang (anak elemen pertama di group)
                    group.insertBefore(ping2, group.firstChild);
                    group.insertBefore(ping1, group.firstChild);
                };

                // Helper memunculkan Radar Hover Pratinjau
                const drawHoverPing = (group, nodeType = 'sensor_awlr') => {
                    document.querySelectorAll('.hover-ping-circle').forEach(el => el.remove());
                    if (!group) return;

                    // Sama dengan di atas, cek status kotak
                    const isRectShape = ['gate_awgc', 'weir_main', 'weir_large', 'weir_small'].includes(nodeType) || group.id.includes('-BM');

                    const ping = document.createElementNS("http://www.w3.org/2000/svg", isRectShape ? "rect" : "circle");
                    ping.setAttribute("class", "hover-ping-circle");
                    
                    if (isRectShape) {
                        let bx = "-16", by = "-16", bw = "32", bh = "32";
                        if (nodeType === 'weir_large') {
                            bx = "-30"; by = "-30"; bw = "60"; bh = "60";
                        } else if (nodeType === 'weir_main') {
                            bx = "-20"; by = "-20"; bw = "40"; bh = "40";
                        } else if (group.id.includes('-BM')) {
                            bx = "-11"; by = "-11"; bw = "22"; bh = "22";
                        }
                        
                        ping.setAttribute("x", bx);
                        ping.setAttribute("y", by);
                        ping.setAttribute("width", bw);
                        ping.setAttribute("height", bh);
                        ping.setAttribute("rx", "1");
                        
                        // Ramabatan untuk hover (kuning) mekar keluar sejauh kurang lebih 24px dari setiap sisi.
                        const targetSize = parseFloat(bw) + 48; // bw + (24px * 2 sisi)
                        const scaleFactor = targetSize / parseFloat(bw);
                        ping.style.setProperty('--hover-scale', scaleFactor.toFixed(2));
                    } else {
                        ping.setAttribute("cx", "0");
                        ping.setAttribute("cy", "0");
                        ping.setAttribute("r", "16");
                    }

                    group.insertBefore(ping, group.firstChild);
                };

                const removeHoverPing = () => {
                    document.querySelectorAll('.hover-ping-circle').forEach(el => el.remove());
                };

                // ─── State Global ────────────────────────────────────────────────
                let currentTopologyData = {
                    nodes: [],
                    edges: []
                }; // Cache data topologi
                let activeNodeData = null; // Node yang sedang dibuka di panel
                let activeCommandId = null; // ID perintah AWGC yang sedang berjalan
                let awlrChartInstance = null; // Instance Chart.js untuk panel AWLR
                let commandPollInterval = null; // Interval polling status AWGC

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

                // ─── Number Animation Helper ────────────────────────────────────
                const animateNumber = (elementId, newValue, isFloat = false, formatFn = null) => {
                    const el = document.getElementById(elementId);
                    if (!el || newValue === undefined || newValue === null) return;
                    
                    let startVal = parseFloat(el.dataset.val || el.textContent) || 0;
                    const endVal = parseFloat(newValue);
                    
                    if (startVal === endVal) {
                        const valStr = isFloat ? endVal.toFixed(2) : endVal;
                        el.textContent = formatFn ? formatFn(valStr) : valStr;
                        el.dataset.val = endVal;
                        return;
                    }

                    if (el.animFrame) cancelAnimationFrame(el.animFrame);

                    const duration = 1000; // ms
                    let startTimestamp = null;
                    
                    const step = (timestamp) => {
                        if (!startTimestamp) startTimestamp = timestamp;
                        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                        const easeOut = 1 - Math.pow(1 - progress, 4);
                        const currentVal = startVal + (easeOut * (endVal - startVal));
                        
                        const strVal = isFloat ? currentVal.toFixed(2) : Math.round(currentVal);
                        el.textContent = formatFn ? formatFn(strVal) : strVal;
                        
                        if (progress < 1) {
                            el.animFrame = requestAnimationFrame(step);
                        } else {
                            const finalStr = isFloat ? endVal.toFixed(2) : endVal;
                            el.textContent = formatFn ? formatFn(finalStr) : finalStr;
                            el.dataset.val = endVal;
                        }
                    };
                    
                    el.animFrame = requestAnimationFrame(step);
                };

                // ─── showInfoPanel — Conditional Rendering ───────────────────────
                const showInfoPanel = (node) => {
                    if (node.type === 'title' || node.type === 'label_text') return;

                    activeNodeData = node;

                    if (node.type !== 'edge') {
                        const targetGroup = document.getElementById(`node-g-${node.id}`);
                        drawRadarPing(targetGroup, node.type);
                    } else {
                        document.querySelectorAll('.active-ping-circle').forEach(el => el.remove());
                    }

                    // Isi header yang selalu ada
                    document.getElementById('info-title').textContent = node.label || node.nama_logger || node.id;
                    document.getElementById('info-source').textContent = node.source_name || 'Saluran Irigasi';

                    // Online/offline indicator
                    if (node.id_logger) {
                        // Khusus AWGC, kita tetap tampilkan UI "ONLINE" sesuai node.is_online
                        // (Yang telah di-set true terus menerus dari backend)
                        setStatusDot(node.is_online !== false);
                    } else {
                        document.getElementById('panel-status-dot').classList.add('hidden');
                        document.getElementById('panel-status-text').classList.add('hidden');
                    }

                    // Badge selalu DSS — menggunakan tema warna cyan terang yang lebih hidup (tidak hitam putih)
                    const badge = document.getElementById('panel-type-badge');
                    badge.textContent = 'DSS';
                        badge.className = 'text-[9px] font-bold tracking-widest uppercase px-2.5 py-0.5 rounded-full bg-cyan-900/80 text-cyan-300 border border-cyan-500/50 shadow-[0_0_8px_rgba(6,182,212,0.3)]';

                    // Selalu pastikan tampilan panel Info Utama muncul
                    document.getElementById('panel-page-main').classList.remove('hidden');

                    // Tampilkan tombol "Buka Kontrol Pintu" untuk semua node yang punya saluran hilir
                    // (akan diupdate setelah destList dirender di bawah)
                    // Hapus inisialisasi data kontrol modal lama karena sekarang pindah halaman

                    // Selalu render Info Default
                    const infoAreaEl = document.getElementById('info-area');
                    if (infoAreaEl) infoAreaEl.textContent = '-';

                    // ── Render data panel dinamis berdasarkan node ──────────────
                    
                    // Statistik Lahan
                    const statWrap = document.getElementById('panel-statistik-wrap');
                    if (node.panel_luas_area !== undefined && node.panel_luas_area > 0) {
                        document.getElementById('panel-luas-area').textContent = node.panel_luas_area;
                        document.getElementById('panel-kb-irigasi').textContent = node.panel_kb_irigasi;
                        document.getElementById('panel-kb-kehilangan').textContent = node.panel_kb_kehilangan;
                        document.getElementById('panel-kb-total').textContent = node.panel_kb_total;
                        document.getElementById('panel-faktor-k').textContent = node.panel_faktor_k;
                        statWrap.classList.remove('hidden');
                    } else {
                        statWrap.classList.add('hidden');
                    }

                    // Saluran info
                    const saluranWrap = document.getElementById('panel-saluran-wrap');
                    if (node.panel_saluran) {
                        document.getElementById('panel-saluran-name').textContent = node.panel_saluran;
                        document.getElementById('panel-elevasi-val').textContent  = node.panel_elevasi_m ? `Elevasi: ${node.panel_elevasi_m} m dpl` : '';
                        saluranWrap.classList.remove('hidden');
                    } else {
                        saluranWrap.classList.add('hidden');
                    }

                    // ── AWLR Sensor Info Dinamis
                    const awlrWrap = document.getElementById('panel-awlr-wrap');
                    if (node.jenis_alat === 'AWLR' && node.tma !== undefined) {
                        animateNumber('panel-awlr-tma', node.tma, true);
                        const stsBadge = document.getElementById('panel-awlr-status');
                        
                        if (node.status === 'overflow' || (node.status_siaga && node.status_siaga.toLowerCase().includes('banjir'))) {
                            stsBadge.className = 'mt-3 inline-flex px-3 py-1 rounded-sm text-[10px] font-black tracking-widest uppercase bg-red-900/60 text-red-400 border border-red-700/50 shadow-[0_0_8px_rgba(239,68,68,0.4)]';
                        } else if (node.status === 'high' || (node.status_siaga && node.status_siaga.toLowerCase().includes('siaga'))) {
                            stsBadge.className = 'mt-3 inline-flex px-3 py-1 rounded-sm text-[10px] font-black tracking-widest uppercase bg-yellow-900/60 text-yellow-400 border border-yellow-700/50 shadow-[0_0_8px_rgba(234,179,8,0.4)]';
                        } else {
                            stsBadge.className = 'mt-3 inline-flex px-3 py-1 rounded-sm text-[10px] font-black tracking-widest uppercase bg-emerald-900/60 text-emerald-400 border border-emerald-700/50 shadow-[0_0_8px_rgba(16,185,129,0.4)]';
                        }
                        
                        stsBadge.textContent = node.status_siaga ? node.status_siaga.toUpperCase() : 'NORMAL';
                        
                        awlrWrap.classList.remove('hidden');

                        // Panggil render chart historis 6 jam
                        if (typeof loadAwlrChart === 'function') {
                            loadAwlrChart(node.id);
                        }
                    } else {
                        awlrWrap.classList.add('hidden');
                    }

                    // TMA Hulu / Hilir / Selisih
                    const tmaWrap = document.getElementById('panel-tma-wrap');
                    if (node.panel_tma_hulu !== undefined) {
                        document.getElementById('panel-tma-hulu').textContent    = node.panel_tma_hulu;
                        document.getElementById('panel-tma-hilir').textContent   = node.panel_tma_hilir;
                        const sel = node.panel_selisih_tma;
                        document.getElementById('panel-tma-selisih').textContent = (sel > 0 ? '+' : '') + sel;
                        tmaWrap.classList.remove('hidden');
                    } else {
                        tmaWrap.classList.add('hidden');
                    }

                    // Debit & Progress bar pemenuhan
                    const debitWrap = document.getElementById('panel-debit-wrap');
                    if (node.jenis_alat !== 'AWLR' && node.panel_debit !== undefined) {
                        const pct     = node.panel_pct_debit ?? 0;
                        const barEl   = document.getElementById('panel-debit-bar');
                        const pctEl   = document.getElementById('panel-debit-pct');
                        animateNumber('panel-debit-val', node.panel_debit, true);
                        animateNumber('panel-kapasitas-val', node.panel_kapasitas, true);
                        barEl.style.width = Math.min(pct, 100) + '%';
                        
                        let suffix = '';
                        if (pct >= 80) {
                            barEl.className = 'h-1.5 rounded-full bg-emerald-400 transition-all duration-500 ease-out';
                            pctEl.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-900/60 text-emerald-400';
                            suffix = ' — Normal';
                        } else if (pct >= 50) {
                            barEl.className = 'h-1.5 rounded-full bg-yellow-400 transition-all duration-500 ease-out';
                            pctEl.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-yellow-900/60 text-yellow-400';
                            suffix = ' — Kurang';
                        } else {
                            barEl.className = 'h-1.5 rounded-full bg-red-500 transition-all duration-500 ease-out';
                            pctEl.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-red-900/60 text-red-400';
                            suffix = ' — Kritis';
                        }
                        
                        // Animate text persentase pemenuhan beserta imbuhan
                        animateNumber('panel-debit-pct', pct, false, (val) => `${val}%${suffix}`);
                        
                        debitWrap.classList.remove('hidden');
                    } else {
                        debitWrap.classList.add('hidden');
                    }

                    // Water Balance Simulation Panel Update
                    const wbWrap = document.getElementById('panel-wb-wrap');
                    if (node.jenis_alat !== 'AWLR' && node.panel_q_perintah !== undefined) {
                        document.getElementById('panel-wb-perintah').textContent = node.panel_q_perintah;
                        animateNumber('panel-wb-terukur', node.panel_q_terukur, true);
                        
                        const errVal = node.panel_wb_error_val;
                        const errPct = node.panel_wb_error_pct;
                        document.getElementById('panel-wb-err-val').textContent = errVal > 0 ? '+' + errVal : errVal;
                        document.getElementById('panel-wb-err-pct').textContent = errPct > 0 ? '+' + errPct : errPct;
                        const errParent = document.getElementById('panel-wb-err-parent');
                        errParent.className = 'font-mono text-xs font-bold ' + (errVal > 0 ? 'text-amber-400' : (errVal < 0 ? 'text-rose-400' : 'text-emerald-400'));
                        
                        document.getElementById('panel-wb-vol').textContent = node.panel_wb_selisih_vol;
                        const volParent = document.getElementById('panel-wb-vol-parent');
                        volParent.className = 'font-mono text-xs font-bold ' + (node.panel_wb_selisih_vol > 0 ? 'text-amber-400' : (node.panel_wb_selisih_vol < 0 ? 'text-rose-400' : 'text-emerald-400'));
                        
                        const statEl = document.getElementById('panel-wb-status');
                        statEl.textContent = node.panel_wb_status;
                        if(node.panel_wb_status === 'Stabil' || node.panel_wb_status === 'Mendekati target') {
                            statEl.className = 'mt-1.5 px-2 py-1.5 rounded text-[10px] font-bold tracking-wider uppercase text-center bg-emerald-900/30 text-emerald-400 border border-emerald-800/50';
                        } else if(node.panel_wb_status === 'Kurang aliran') {
                            statEl.className = 'mt-1.5 px-2 py-1.5 rounded text-[10px] font-bold tracking-wider uppercase text-center bg-rose-900/30 text-rose-400 border border-rose-800/50';
                        } else {
                            statEl.className = 'mt-1.5 px-2 py-1.5 rounded text-[10px] font-bold tracking-wider uppercase text-center bg-amber-900/30 text-amber-500 border border-amber-800/50';
                        }
                        
                        if (wbWrap) wbWrap.classList.remove('hidden');
                    } else {
                        if (wbWrap) wbWrap.classList.add('hidden');
                    }

                    // Status Pintu (AWGC gates)
                    const gatesWrap = document.getElementById('panel-gates-wrap');
                    const gatesList = document.getElementById('panel-gates-list');
                    gatesList.innerHTML = '';
                    if (node.jenis_alat === 'AWGC' && node.gates && node.gates.length > 0) {
                        node.gates.forEach(g => {
                            const bukaan  = g.bukaan_persen ?? 0;
                            const maxCm   = g.max_cm ?? 100;
                            const pctGate = maxCm > 0 ? Math.round((bukaan / maxCm) * 100) : 0;
                            let gateColor = pctGate > 0 ? 'text-emerald-400 bg-emerald-900/50 border-emerald-700/50' : 'text-red-400 bg-red-900/50 border-red-700/50';
                            const row = document.createElement('div');
                            row.className = 'flex items-center justify-between py-1 px-2 rounded border ' + gateColor;
                            row.innerHTML = `
                                <span class="text-[10px] font-semibold">${g.name}</span>
                                <div class="text-right">
                                    <span class="text-xs font-black">${bukaan} cm</span>
                                    <span class="text-[9px] ml-1 opacity-70">(${pctGate}%)</span>
                                </div>`;
                            gatesList.appendChild(row);
                        });
                        gatesWrap.classList.remove('hidden');
                    } else {
                        gatesWrap.classList.add('hidden');
                    }

                        // ── Render daftar tujuan aliran (outgoing edges) ────────
                        const destList = document.getElementById('panel-destinations-list');
                        const destWrap = document.getElementById('panel-destinations-wrap');
                        destList.innerHTML = '';

                        if (node.jenis_alat === 'AWLR') {
                            if (destWrap) destWrap.classList.add('hidden');
                        } else {
                            if (destWrap) destWrap.classList.remove('hidden');
                            
                            // Filter edge keluar dari node ini (termasuk saluran primer, sekunder, dan tersier)
                            const outgoing = (currentTopologyData.edges || []).filter(e =>
                                e.source === node.id
                            );

                            if (outgoing.length === 0) {
                                destList.innerHTML = '<span class="text-slate-600 italic">Tidak ada saluran hilir</span>';
                            } else {
                            outgoing.forEach(edge => {
                                const targetNode = (currentTopologyData.nodes || []).find(n => n.id === edge.target);
                                if (!targetNode) return;
                                // Skip jika target hanya node label visual (Kecuali untuk saluran Tersier yang ujungnya memang ke arah hamparan areal)
                                if (edge.type !== 'tertiary' && (targetNode.type === 'label_yellow' || targetNode.type === 'label_text')) return;

                                const lblNode = (currentTopologyData.nodes || []).find(n =>
                                    n.id === 'LBL_' + edge.target &&
                                    (n.type === 'label_text' || n.type === 'label_yellow')
                                );
                                const displayName = (lblNode?.label && lblNode.label.trim())
                                    ? lblNode.label
                                    : (targetNode.label && targetNode.label.trim())
                                        ? targetNode.label
                                        : targetNode.id;
                                const saluran = targetNode.source_name || '';

                                // Badge warna berdasarkan tipe edge
                                let typeBadge;
                                if (edge.type === 'primary') typeBadge = { cls: 'bg-blue-900/80 text-blue-300 border-blue-500/50 shadow-[0_0_5px_rgba(59,130,246,0.3)]', txt: 'Primer' };
                                else if (edge.type === 'tertiary') typeBadge = { cls: 'bg-teal-900/80 text-teal-300 border-teal-500/50 shadow-[0_0_5px_rgba(20,184,166,0.3)]', txt: 'Tersier' };
                                else typeBadge = { cls: 'bg-indigo-900/80 text-indigo-300 border-indigo-500/50 shadow-[0_0_5px_rgba(99,102,241,0.3)]', txt: 'Sekunder' };

                                const hasGateControl = targetNode && targetNode.jenis_alat === 'AWGC';

                                const row = document.createElement('div');
                                row.className = 'flex items-center gap-2 py-1.5 px-2.5 rounded-md bg-slate-800/70 border border-slate-700/40 hover:border-slate-500 hover:bg-slate-700/90 transition-all cursor-pointer hover:-translate-y-0.5 active:scale-95 shadow-sm';
                                row.innerHTML = `
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0ea5e9] flex-shrink-0 shadow-[0_0_4px_#0ea5e9]"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-slate-100 truncate">${displayName}</div>
                                        ${saluran ? `<div class="text-[9px] text-slate-500 truncate">${saluran}</div>` : ''}
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border flex-shrink-0 ${typeBadge.cls}">${typeBadge.txt}</span>
                                `;

                                row.addEventListener('click', () => {
                                    // BERSUHKAN EFEK KUNING SEBELUM HTML DIHANCURKAN
                                    removeHoverPing();

                                    // 1. Ganti tampilan panel 
                                    showInfoPanel(targetNode);

                                    // 2. Geser kamera perlahan tepat ke koordinat target
                                    if (typeof panzoomInstance !== 'undefined') {
                                        const svgContainer = document.getElementById('panzoom-container');
                                        const transform = panzoomInstance.getTransform();
                                        
                                        // Posisikan node persis di tengah layar
                                        const targetX = (svgContainer.clientWidth / 2) - (targetNode.x * transform.scale);
                                        const targetY = (svgContainer.clientHeight / 2) - (targetNode.y * transform.scale) + 50; // +50 agar tidak tertutup judul
                                        
                                        panzoomInstance.smoothMoveTo(targetX, targetY);
                                    }
                                });

                                // Event Preview Hover
                                row.addEventListener('mouseenter', () => {
                                    const targetGroup = document.getElementById(`node-g-${targetNode.id}`);
                                    drawHoverPing(targetGroup, targetNode.type);
                                });

                                row.addEventListener('mouseleave', () => {
                                    removeHoverPing();
                                });

                                destList.appendChild(row);
                            });

                            // Jika semua target adalah label, tampilkan pesan kosong
                            if (destList.children.length === 0) {
                                destList.innerHTML = '<span class="text-slate-600 italic">Tidak ada saluran hilir</span>';
                            }
                        }
                        }

                        // Tampilkan tombol kontrol pintu jika ada saluran hilir (outgoing > 0 dan hasil render list > 0)
                        if (node.jenis_alat !== 'AWLR' && destList.children.length > 0 && !destList.querySelector('.italic')) {
                            document.getElementById('container-btn-control').classList.remove('hidden');
                        } else {
                            document.getElementById('container-btn-control').classList.add('hidden');
                        }

                    infoPanel.classList.remove('hidden');
                };

                // ─── Eksekusi Navigasi Kontrol Pintu Air ───────────────────────
                document.getElementById('btn-show-control').addEventListener('click', () => {
                    if (activeNodeData && activeNodeData.id) {
                        window.location.href = `/skema-irigasi/kontrol/${activeNodeData.id}`;
                    }
                });

                // ─── Fetch Data Topology ─────────────────────────────────────────
                let lastDataHash = "";

                function fetchTopologyData() {
                    fetch("{{ route('skema-irigasi.api') }}")
                        .then(res => res.json())
                        .then(data => {
                            currentTopologyData = data;
                            
                            // Hitung riwayat status grafik (hanya refresh vektor saat status simulasi/fisik pipa berubah)
                            // Ini menghindari efek glitch animasi air me-reset setiap 3 detik
                            const currentHash = data.nodes.map(n => {
                                const gatePct = (n.gates && n.gates.length > 0) ? n.gates[0].bukaan_persen : 0;
                                // Sertakan TMA agar perubahan sensor AWLR otomatis me-refresh ikon di SVG
                                const tmaVal  = n.tma !== undefined ? parseFloat(n.tma).toFixed(1) : '0';
                                return `${n.id}_${n.status}_${gatePct}_${tmaVal}`;
                            }).join('|');

                            if (currentHash !== lastDataHash) {
                                drawNetwork(data.nodes, data.edges);
                                lastDataHash = currentHash;
                            }

                            // ── Update Status Bar Koneksi ──
                            const dotEl  = document.getElementById('api-status-dot');
                            const txtEl  = document.getElementById('api-status-text');
                            const timeEl = document.getElementById('api-last-update');
                            const cntEl  = document.getElementById('api-node-count');
                            if (dotEl)  { dotEl.className = 'w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_6px_#4ade80]'; dotEl.style.animation = 'ping 1.5s ease-in-out infinite'; }
                            if (txtEl)  txtEl.textContent = 'Terhubung';
                            if (timeEl) timeEl.textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
                            if (cntEl)  cntEl.textContent = data.nodes.filter(n => n.is_online).length;

                            // Sembunyikan banner error jika server kembali online
                            document.getElementById('api-error-banner')?.classList.add('hidden');
                            apiFailCount = 0;

                            // Jika panel sedang terbuka, perbarui info angkanya (TMA, Debit)
                            if (activeNodeData) {
                                if (activeNodeData.type === 'edge') {
                                    const updatedEdge = data.edges.find(e =>
                                        e.source === activeNodeData.edge_source_id &&
                                        e.target === activeNodeData.edge_target_id
                                    );
                                    const targetInfoNode = data.nodes.find(n => n.id === activeNodeData.edge_target_info_id);

                                    if (updatedEdge && targetInfoNode) {
                                        showInfoPanel({
                                            ...activeNodeData,
                                            panel_debit: updatedEdge.panel_debit !== undefined ? updatedEdge.panel_debit : targetInfoNode.panel_debit,
                                            panel_kapasitas: targetInfoNode.panel_kapasitas,
                                            panel_pct_debit: updatedEdge.panel_pct_debit !== undefined ? updatedEdge.panel_pct_debit : targetInfoNode.panel_pct_debit,
                                            panel_saluran: targetInfoNode.panel_saluran,
                                            panel_elevasi_m: targetInfoNode.panel_elevasi_m,
                                            panel_q_perintah: targetInfoNode.panel_q_perintah,
                                            panel_q_terukur: updatedEdge.panel_debit !== undefined ? updatedEdge.panel_debit : (targetInfoNode.panel_q_terukur ?? targetInfoNode.panel_debit),
                                            panel_wb_error_val: targetInfoNode.panel_wb_error_val ?? 0,
                                            panel_wb_error_pct: targetInfoNode.panel_wb_error_pct ?? 0,
                                            panel_wb_selisih_vol: targetInfoNode.panel_wb_selisih_vol ?? '-',
                                            panel_wb_status: targetInfoNode.panel_wb_status ?? '-'
                                        });
                                    }
                                    return;
                                }

                                const updatedNode = data.nodes.find(n => n.id === activeNodeData.id);
                                if (updatedNode) { // Cukup perbarui angka tanpa merusak elemen HTML berulang-ulang
                                    Object.assign(activeNodeData, updatedNode);
                                    if (document.getElementById('panel-tma-hulu')) document.getElementById('panel-tma-hulu').textContent = updatedNode.panel_tma_hulu ?? '-';
                                    if (document.getElementById('panel-tma-hilir')) document.getElementById('panel-tma-hilir').textContent = updatedNode.panel_tma_hilir ?? '-';
                                    
                                    // Sinkronisasikan Animasi Debit, Kapasitas, Bar, & Persentase Pemenuhan secara realtime
                                    if (updatedNode.panel_debit !== undefined) {
                                        const pct     = updatedNode.panel_pct_debit ?? 0;
                                        const barEl   = document.getElementById('panel-debit-bar');
                                        const pctEl   = document.getElementById('panel-debit-pct');
                                        if (document.getElementById('panel-debit-val')) animateNumber('panel-debit-val', updatedNode.panel_debit, true);
                                        if (document.getElementById('panel-kapasitas-val')) animateNumber('panel-kapasitas-val', updatedNode.panel_kapasitas, true);
                                        if (barEl) barEl.style.width = Math.min(pct, 100) + '%';
                                        
                                        let suffix = '';
                                        if (pct >= 80) {
                                            if (barEl) barEl.className = 'h-1.5 rounded-full bg-emerald-400 transition-all duration-500 ease-out';
                                            if (pctEl) pctEl.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-900/60 text-emerald-400';
                                            suffix = ' — Normal';
                                        } else if (pct >= 50) {
                                            if (barEl) barEl.className = 'h-1.5 rounded-full bg-yellow-400 transition-all duration-500 ease-out';
                                            if (pctEl) pctEl.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-yellow-900/60 text-yellow-400';
                                            suffix = ' — Kurang';
                                        } else {
                                            if (barEl) barEl.className = 'h-1.5 rounded-full bg-red-500 transition-all duration-500 ease-out';
                                            if (pctEl) pctEl.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-red-900/60 text-red-400';
                                            suffix = ' — Kritis';
                                        }
                                        if (pctEl) animateNumber('panel-debit-pct', pct, false, (val) => `${val}%${suffix}`);
                                    }

                                    // ── Sinkronisasi Panel AWLR (TMA & Siaga Badge) secara realtime ──
                                    if (updatedNode.jenis_alat === 'AWLR' && updatedNode.tma !== undefined) {
                                        animateNumber('panel-awlr-tma', updatedNode.tma, true);

                                        // Update badge status siaga
                                        const siaga   = updatedNode.status_siaga || 'Normal';
                                        const classBySiaga = {
                                            'Normal' : 'bg-emerald-900/60 text-emerald-400 border-emerald-700/50',
                                            'Siaga 1': 'bg-yellow-900/60  text-yellow-400  border-yellow-700/50',
                                            'Siaga 2': 'bg-orange-900/60  text-orange-400  border-orange-700/50',
                                            'Banjir' : 'bg-red-900/60     text-red-400     border-red-700/50',
                                            'Kering' : 'bg-slate-800      text-slate-400   border-slate-700',
                                        };
                                        const stsBadge = document.getElementById('panel-awlr-status');
                                        if (stsBadge) {
                                            stsBadge.className = 'mt-3 inline-flex px-3 py-1 rounded-sm text-[10px] font-black tracking-widest uppercase border ' +
                                                (classBySiaga[siaga] || classBySiaga['Normal']);
                                            stsBadge.textContent = siaga;
                                        }
                                    }

                                    // ── Sinkronisasi Indikator Status Pintu AWGC di panel ──
                                    const gatesList = document.getElementById('panel-gates-list');
                                    if (updatedNode.jenis_alat === 'AWGC' && updatedNode.gates && gatesList) {
                                        gatesList.innerHTML = '';
                                        updatedNode.gates.forEach(g => {
                                            const bukaan  = g.bukaan_persen ?? 0;
                                            const maxCm   = g.max_cm ?? 100;
                                            const pctGate = maxCm > 0 ? Math.round((bukaan / maxCm) * 100) : 0;
                                            let gateColor = pctGate > 0 ? 'text-emerald-400 bg-emerald-900/50 border-emerald-700/50' : 'text-red-400 bg-red-900/50 border-red-700/50';
                                            const row = document.createElement('div');
                                            row.className = 'flex items-center justify-between py-1 px-2 rounded border ' + gateColor;
                                            row.innerHTML = `<span class="text-[10px] font-semibold">${g.name}</span><div class="text-right"><span class="text-xs font-black">${bukaan} cm</span><span class="text-[9px] ml-1 opacity-70">(${pctGate}%)</span></div>`;
                                            gatesList.appendChild(row);
                                        });
                                    }
                                }
                            }
                        })
                        .catch(err => {
                            console.error('[Skema] Gagal fetch topologi:', err);
                            apiFailCount++;

                            // Update Status Bar ke error
                            const dotEl = document.getElementById('api-status-dot');
                            const txtEl = document.getElementById('api-status-text');
                            if (dotEl)  { dotEl.className = 'w-2 h-2 rounded-full bg-red-500'; dotEl.style.animation = ''; }
                            if (txtEl)  txtEl.textContent = 'Gagal terhubung';

                            // Tampilkan banner error setelah 2x gagal berturut-turut
                            if (apiFailCount >= 2) {
                                document.getElementById('api-error-banner')?.classList.remove('hidden');
                            }
                        });
                }

                let apiFailCount = 0; // Counter kegagalan fetch berturut-turut

                // Panggil pertama kali saat halaman dimuat
                fetchTopologyData();

                // POLLING: Ambil data secara otomatis setiap 3 detik (sebagai fungsi transisi realtime tanpa Websocket)
                setInterval(fetchTopologyData, 3000);

                function drawNetwork(nodes, edges) {
                    // Jangan hapus edge jika sudah ada agar bisa diberi efek perlahan (CSS Transition)
                    if (!window.isSkemaEdgeRendered) {
                        edgesLayerBg.innerHTML = '';
                        edgesLayerFg.innerHTML = '';
                        window.isSkemaEdgeRendered = true;
                    }
                    edgesLayerAnim.innerHTML = '';
                    nodesLayer.innerHTML = '';

                    // Logika BFS: Salurkan AIr dari Hulu ke Hilir dengan Status: full, trickle, dry
                    const nodeFlow = {};
                    const edgeFlow = {};
                    nodes.forEach(n => nodeFlow[n.id] = 'dry');
                    edges.forEach(e => edgeFlow[`${e.source}-${e.target}`] = 'dry');

                    // Sumber utama Bendung selalu punya air
                    nodeFlow['WEIR_COPONG'] = 'full';

                    const q = [{
                        id: 'WEIR_COPONG',
                        flow: 'full'
                    }];
                    while (q.length > 0) {
                        const {
                            id: currId,
                            flow: currFlow
                        } = q.shift();
                        const currNode = nodes.find(n => n.id === currId);

                        // Propagasi warna berdasarkan status yang sudah dihitung di backend (panel_pct_debit)
                        let outgoingFlow = currFlow;
                        if (currNode) {
                            if (currNode.status === 'overflow') {
                                outgoingFlow = 'overflow';
                            } else if (currNode.status === 'high') {
                                outgoingFlow = 'high';
                            } else if (currNode.status === 'full' || currNode.status === 'open') {
                                outgoingFlow = 'full';
                            } else if (currNode.status === 'trickle') {
                                outgoingFlow = 'trickle';
                            } else if (currNode.status === 'closed') {
                                outgoingFlow = 'trickle'; // trickle = rembesan, tidak mati total
                            } else if (currNode.status === 'dry' || currNode.status === 'broken') {
                                outgoingFlow = 'dry';
                            }
                        }

                        if (outgoingFlow === 'dry') {
                            continue;
                        }

                        edges.filter(e => e.source === currId).forEach(edge => {
                            const eId = `${edge.source}-${edge.target}`;

                            let eFlow = outgoingFlow;
                            
                            // OVERRIDE: Gunakan perhitungan persentase aktual PIPA (edge) yang di-inject dari backend
                            // Ini memastikan percabangan memiliki warna animasinya sendiri-sendiri, independen dari pintu lainnya.
                            if (edge.panel_pct_debit !== undefined) {
                                let pct = edge.panel_pct_debit;
                                if (pct >= 135) eFlow = 'overflow';
                                else if (pct >= 101) eFlow = 'high';
                                else if (pct >= 50) eFlow = 'full';
                                else if (pct >= 1) eFlow = 'trickle';
                                else eFlow = 'dry';
                            } else if (edge.status === 'closed') {
                                eFlow = 'dry';
                            } else if (edge.status === 'trickle') {
                                eFlow = 'trickle';
                            } else if (edge.status === 'high') {
                                eFlow = 'high';
                            } else if (edge.status === 'overflow') {
                                eFlow = 'overflow';
                            }

                            const flowRank = {
                                'overflow': 5,
                                'high': 4,
                                'full': 3,
                                'trickle': 2,
                                'dry': 1
                            };

                            if (flowRank[eFlow] > flowRank[edgeFlow[eId]]) {
                                edgeFlow[eId] = eFlow;
                            }

                            if (flowRank[eFlow] > flowRank[nodeFlow[edge.target]]) {
                                nodeFlow[edge.target] = eFlow;
                                if (eFlow !== 'dry') {
                                    q.push({
                                        id: edge.target,
                                        flow: eFlow
                                    });
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

                    const getEdgeEndpointNode = (node, side) => {
                        if (!node) return null;

                        if (node.type !== 'corner') {
                            return node;
                        }

                        let probe = node;
                        let hop = 0;

                        while (probe && probe.type === 'corner' && hop < 10) {
                            const linkedEdge = side === 'source'
                                ? edges.find(e => e.target === probe.id)
                                : edges.find(e => e.source === probe.id);

                            if (!linkedEdge) {
                                break;
                            }

                            probe = nodes.find(n => n.id === (side === 'source' ? linkedEdge.source : linkedEdge.target));
                            hop++;
                        }

                        return probe || node;
                    };

                    const getEdgeEndpointName = (node, side) => {
                        const endpointNode = getEdgeEndpointNode(node, side);
                        if (!endpointNode) return '-';
                        return endpointNode.label ? endpointNode.label.replace('\n', ' ') : endpointNode.id;
                    };

                    // 1. Draw Edges
                    // We draw edges twice: first the thick background border, then the slightly thinner inner color
                    edges.forEach(edge => {
                        const sourceNode = nodes.find(n => n.id === edge.source);
                        const targetNode = nodes.find(n => n.id === edge.target);

                        if (sourceNode && targetNode) {
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
                                const pathId = isFlowAnim ? null : `path-${layer.id}-${p1.id}-${p2.id}`;
                                let path = isFlowAnim ? null : document.getElementById(pathId);
                                const isNew = !path;

                                if (isNew) {
                                    path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                                    if (!isFlowAnim) {
                                        path.setAttribute("id", pathId);
                                        // Efek Fading Color
                                        path.style.transition = "stroke 2s ease, stroke-width 2s ease";
                                    }
                                }

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

                                        const path2 = document.createElementNS("http://www.w3.org/2000/svg",
                                            "path");
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
                                    // Update metadata secara live di pipa agar panel popupnya ikut terupdate saat diklik
                                    path.dataset.statusInfo = flowState === 'overflow' ? ' (Meluap/Banjir!)' :
                                        flowState === 'high' ? ' (Siaga/Deras)' :
                                        flowState === 'trickle' ? ' (Arus Lemah)' :
                                        flowState === 'dry' ? ' (Kering)' : '';
                                        
                                    const sourceDisplayNode = getEdgeEndpointNode(sourceNode, 'source');
                                    const targetDisplayNode = getEdgeEndpointNode(targetNode, 'target');
                                    const sourceName = getEdgeEndpointName(sourceNode, 'source');
                                    const targetName = getEdgeEndpointName(targetNode, 'target');
                                    path.dataset.labelTitle = `${sourceName} - ${targetName}`;
                                    path.dataset.edgeName = edge.type === 'primary' ? 'Saluran Primer Copong' : ((targetDisplayNode?.source_name) || 'Saluran Sekunder');

                                    if (isNew) {
                                        // Membuat saluran air bisa diklik (bind event sekali saja)
                                        path.style.cursor = 'pointer';
                                        path.addEventListener('click', (e) => {
                                            e.stopPropagation();
                                            const targetInfoNode = getEdgeEndpointNode(targetNode, 'target') || targetNode;
                                            showInfoPanel({
                                                id: 'edge_' + edge.source + '_' + edge.target,
                                                type: 'edge',
                                                edge_source_id: edge.source,
                                                edge_target_id: edge.target,
                                                edge_target_info_id: targetInfoNode.id,
                                                label: `Ruas ${path.dataset.labelTitle}${path.dataset.statusInfo}`,
                                                source_name: path.dataset.edgeName,
                                                panel_debit: edge.panel_debit !== undefined ? edge.panel_debit : targetInfoNode.panel_debit,
                                                panel_kapasitas: targetInfoNode.panel_kapasitas,
                                                panel_pct_debit: edge.panel_pct_debit !== undefined ? edge.panel_pct_debit : targetInfoNode.panel_pct_debit,
                                                panel_saluran: targetInfoNode.panel_saluran,
                                                panel_elevasi_m: targetInfoNode.panel_elevasi_m,
                                                panel_q_perintah: targetInfoNode.panel_q_perintah,
                                                panel_q_terukur: edge.panel_debit !== undefined ? edge.panel_debit : (targetInfoNode.panel_q_terukur ?? targetInfoNode.panel_debit),
                                                panel_wb_error_val: targetInfoNode.panel_wb_error_val ?? 0,
                                                panel_wb_error_pct: targetInfoNode.panel_wb_error_pct ?? 0,
                                                panel_wb_selisih_vol: targetInfoNode.panel_wb_selisih_vol ?? '-',
                                                panel_wb_status: targetInfoNode.panel_wb_status ?? '-'
                                            });
                                        });
                                        layer.appendChild(path);
                                    }
                                }
                            };

                            if (isTertiary) {
                                drawLine(sourceNode, targetNode, edgesLayerBg, 16, borderColor);
                                drawLine(sourceNode, targetNode, edgesLayerFg, 10, innerColor);
                                if (flowState !== 'dry') drawLine(sourceNode, targetNode, edgesLayerAnim, 6,
                                    flowState === 'full' ? "rgba(255, 255, 255, 0.45)" : innerColor, true);
                            } else {
                                const bgWidth = isPrimary ? 36 : 24;
                                const fgWidth = isPrimary ? 26 : 16;
                                drawLine(sourceNode, targetNode, edgesLayerBg, bgWidth, borderColor);
                                drawLine(sourceNode, targetNode, edgesLayerFg, fgWidth, innerColor);
                                if (flowState !== 'dry') drawLine(sourceNode, targetNode, edgesLayerAnim,
                                    fgWidth - 4, flowState === 'full' ? "rgba(255, 255, 255, 0.45)" :
                                    innerColor, true);
                            }
                        }
                    });

                    // 2. Draw Nodes
                    nodes.forEach(node => {
                        // FIX: Membuang skip sensor_awlr agar ikonnya muncul!

                        const group = document.createElementNS("http://www.w3.org/2000/svg", "g");
                        group.setAttribute("transform", `translate(${node.x}, ${node.y})`);
                        group.setAttribute("id", `node-g-${node.id}`);

                        // Gambar kembali ping jika node ini sedang terpilih
                        if (activeNodeData && activeNodeData.id === node.id) {
                            setTimeout(() => drawRadarPing(group, node.type), 10);
                        }

                        // Add click behavior to interactive nodes
                        if (node.type !== 'title' && node.type !== 'label_text') {
                            group.style.cursor = 'pointer';
                            group.classList.add('node-hoverable');
                            
                            // Event klik untuk membuka panel
                            group.addEventListener('click', (e) => {
                                e.stopPropagation(); // Stop panzoom from taking the click
                                showInfoPanel(node);
                            });

                            // Event hover untuk memunculkan efek animasi ping kuning
                            group.addEventListener('mouseenter', () => {
                                // Jangan pasang animasi hover kuning jika node ini sedang 'aktif' (sudah diklik biru)
                                if (!activeNodeData || activeNodeData.id !== node.id) {
                                    drawHoverPing(group, node.type);
                                }
                            });
                            group.addEventListener('mouseleave', () => {
                                removeHoverPing();
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

                        if (node.type === 'title') {
                            const texts = node.label.split('\n');
                            texts.forEach((textLine, i) => {
                                const text = document.createElementNS("http://www.w3.org/2000/svg",
                                    "text");
                                text.setAttribute("text-anchor", "middle");
                                text.setAttribute("y", i * 40); // Spasi antar baris diperbesar
                                text.setAttribute("font-size", i === 0 ? "24" :
                                "36"); // Ukuran teks jauh lebih besar
                                text.setAttribute("font-weight", "bold");
                                text.setAttribute("fill", COLOR_LINE_BORDER);
                                
                                text.style.textRendering = "geometricPrecision";
                                text.style.webkitFontSmoothing = "antialiased";

                                text.textContent = textLine;
                                group.appendChild(text);
                            });
                        } else if (node.type === 'label_text') {
                            const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                            text.setAttribute("text-anchor", "middle");
                            text.setAttribute("font-size", "16");
                            text.setAttribute("font-weight", "bold");
                            text.setAttribute("fill", COLOR_TEXT_DARK);
                            
                            // Anti-aliasing fix agar teks tidak blur saat ada animasi hover di dekatnya
                            text.style.textRendering = "geometricPrecision";
                            text.style.webkitFontSmoothing = "antialiased";

                            // Fitur baru: Memutar teks (miring atau vertikal)
                            if (node.rotation) {
                                text.setAttribute("transform", `rotate(${node.rotation})`);
                            }

                            text.textContent = node.label;
                            group.appendChild(text);
                        } else if (node.type === 'weir_large') {
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
                        } else if (node.type === 'weir_main') {
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
                        } else if (node.type === 'junction') {
                            if (node.id.includes('BM')) {
                                // Jika ID mengandung 'BM', gambar kotak kecil menyesuaikan lebar pipa
                                const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                                rect.setAttribute("x", "-14");
                                rect.setAttribute("y", "-14");
                                rect.setAttribute("width", "28");
                                rect.setAttribute("height", "28");
                                rect.setAttribute("fill", "white");
                                rect.setAttribute("stroke", borderColor);
                                rect.setAttribute("stroke-width", "2");
                                group.appendChild(rect);
                            } else {
                                // Gambar lingkaran persimpangan mungil yang seolah "masuk" di dalam pipa
                                const circleOuter = document.createElementNS("http://www.w3.org/2000/svg",
                                    "circle");
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
                                    const circleInner = document.createElementNS("http://www.w3.org/2000/svg",
                                        "circle");
                                    circleInner.setAttribute("r", "2.5");
                                    circleInner.setAttribute("fill", borderColor);
                                    group.appendChild(circleInner);
                                }
                            }
                        } else if (node.type === 'label_yellow') {
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
                        /* else if (node.type === 'sensor_awlr') { */ // blok ini sekarang aktif di bawah
                        // ─────────────────────────────────────────────────────────
                        // NODE TYPE: SENSOR_AWLR (Sensor Tinggi Muka Air)
                        // Bentuk: Lingkaran berdenyut dengan ikon gelombang
                        // ─────────────────────────────────────────────────────────
                        else if (node.type === 'sensor_awlr') {
                            const isOnline = node.is_online !== false;
                            const siaga = node.status_siaga || 'Normal';
                            const sensorColor = {
                                'Normal': '#22c55e',
                                'Siaga 1': '#eab308',
                                'Siaga 2': '#f97316',
                                'Banjir': '#ef4444',
                                'Kering': '#94a3b8',
                            } [siaga] || '#22c55e';

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
                            lbl.style.textRendering = "geometricPrecision";
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
                                tmaText.style.textRendering = "geometricPrecision";
                                tmaText.textContent = parseFloat(node.tma).toFixed(1) + ' cm';
                                group.appendChild(tmaText);
                            }
                        }
                        // ─────────────────────────────────────────────────────────
                        // NODE TYPE: GATE_AWGC (Pintu Air Bermotor)
                        // Bentuk: Segi enam / ikon pintu air
                        // ─────────────────────────────────────────────────────────
                        else if (node.type === 'gate_awgc') {
                            const bukaan = node.bukaan_persen ?? 0;
                            const isOnline = node.is_online !== false;
                            const gateColor = bukaan <= 0 ? '#ef4444' // Merah = Tertutup
                                :
                                bukaan < 30 ? '#f97316' // Oranye = Hampir tutup
                                :
                                bukaan < 80 ? '#0ea5e9' // Biru = Normal
                                :
                                '#8b5cf6'; // Ungu = Bukaan Besar

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
                    if (awlrChartInstance) {
                        awlrChartInstance.destroy();
                        awlrChartInstance = null;
                    }

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

                        const labels = json.data.map(d => new Date(d.waktu).toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        }));
                        const values = json.data.map(d => parseFloat(d.s1 ?? d.s2 ?? 0));

                        awlrChartInstance = new Chart(canvas, {
                            type: 'line',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'TMA (cm)',
                                    data: values,
                                    borderColor: '#38bdf8',
                                    backgroundColor: 'rgba(56,189,248,0.1)',
                                    borderWidth: 1.5,
                                    pointRadius: 0,
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    x: {
                                        display: false
                                    },
                                    y: {
                                        ticks: {
                                            color: '#94a3b8',
                                            font: {
                                                size: 9
                                            }
                                        },
                                        grid: {
                                            color: '#1e293b'
                                        }
                                    }
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
                    btn.className =
                        'w-full py-2.5 rounded-lg font-bold text-sm transition-all duration-200 bg-[#0ea5e9] hover:bg-[#0284c7] active:scale-95 text-white shadow-lg shadow-cyan-900/50';
                    document.getElementById('awgc-btn-text').textContent = '⚡ Terapkan Posisi Pintu';
                    document.getElementById('awgc-command-status').classList.add('hidden');
                    if (commandPollInterval) {
                        clearInterval(commandPollInterval);
                        commandPollInterval = null;
                    }
                }

                window.sendAwgcCommand = async function() {
                    if (!activeNodeData || activeNodeData.jenis_alat !== 'AWGC') return;

                    const target = parseInt(document.getElementById('awgc-slider').value);
                    const btn = document.getElementById('awgc-btn-send');
                    const txtEl = document.getElementById('awgc-btn-text');
                    const statusBox = document.getElementById('awgc-command-status');
                    const statusText = document.getElementById('awgc-command-status-text');

                    // Kunci tombol → loading state
                    btn.disabled = true;
                    btn.className =
                        'w-full py-2.5 rounded-lg font-bold text-sm bg-slate-700 text-slate-400 cursor-not-allowed';
                    txtEl.textContent = '⏳ Mengirim perintah...';
                    statusBox.classList.remove('hidden');
                    statusText.textContent = 'Menyambung ke MQTT broker...';
                    statusText.className = 'text-slate-400';

                    try {
                        const res = await fetch('/api/awgc/command', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content
                            },
                            body: JSON.stringify({
                                node_skema_id: activeNodeData.id,
                                id_logger: activeNodeData.id_logger,
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
                        statusText.textContent =
                            `Perintah terkirim (ID: ${activeCommandId}). Menunggu respons...`;
                        statusText.className = 'text-yellow-400';

                        // Polling status setiap 3 detik
                        commandPollInterval = setInterval(async () => {
                            try {
                                const pRes = await fetch(`/api/awgc/status/${activeCommandId}`);
                                const pJson = await pRes.json();

                                if (pJson.is_finished) {
                                    clearInterval(commandPollInterval);
                                    commandPollInterval = null;

                                    if (pJson.status === 'success') {
                                        txtEl.textContent = '✅ Berhasil Dieksekusi';
                                        btn.className =
                                            'w-full py-2.5 rounded-lg font-bold text-sm bg-emerald-700 text-white cursor-not-allowed';
                                        statusText.textContent = 'Pintu bergerak ke posisi ' +
                                            target + '%';
                                        statusText.className = 'text-emerald-400';
                                        // Update gauge bar secara visual
                                        document.getElementById('awgc-bukaan-value').textContent =
                                            target;
                                        document.getElementById('awgc-bukaan-bar').style.width =
                                            target + '%';
                                    } else {
                                        txtEl.textContent = '⚠️ Gagal di Alat';
                                        btn.className =
                                            'w-full py-2.5 rounded-lg font-bold text-sm bg-red-900/60 text-red-300 cursor-not-allowed';
                                        statusText.textContent = pJson.pesan_error ||
                                            'Alat melaporkan error.';
                                        statusText.className = 'text-red-400';
                                    }
                                    setTimeout(resetAwgcButton, 5000);
                                }
                            } catch (e) {
                                console.error('[AWGC Poll]', e);
                            }
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
                @if (env('VITE_ENABLE_ECHO') === 'true')
                    if (window.Echo) {
                        window.Echo.channel('sensor.data')
                            .listen('SensorDataUpdated', (payload) => {
                                console.log('[Skema WS] Event diterima:', payload);

                                // Update node di cache data topologi
                                const nodeIdx = currentTopologyData.nodes.findIndex(n => n.id === payload.node_id);
                                if (nodeIdx !== -1) {
                                    // Merge payload ke node data
                                    Object.assign(currentTopologyData.nodes[nodeIdx], {
                                        tma: payload.tma,
                                        bukaan_persen: payload.bukaan_persen,
                                        status_siaga: payload.status_siaga,
                                        flow_state: payload.flow_state,
                                        is_online: true,
                                        last_time: payload.waktu,
                                    });

                                    // Tangkap bukaan aktual maupun target dari bypass simulasi
                                    const actualPercent = payload.bukaan_persen !== undefined ? payload.bukaan_persen : payload.target_persen;

                                    // Update struktur gates jika ada
                                    if (actualPercent !== undefined) {
                                        if (currentTopologyData.nodes[nodeIdx].gates && currentTopologyData.nodes[nodeIdx].gates.length > 0) {
                                            currentTopologyData.nodes[nodeIdx].gates[0].bukaan_persen = actualPercent;
                                        }
                                        
                                        // Inject manual "status" pada node dan jalurnya agar dibaca oleh fungsi bfsCalculateFlow
                                        let newStatus = 'open';
                                        if (actualPercent <= 0) {
                                            newStatus = 'closed';
                                        } else if (actualPercent < 30) {
                                            newStatus = 'trickle';
                                        }
                                        currentTopologyData.nodes[nodeIdx].status = newStatus;

                                        // UPDATE STATUS PADA EDGES (Paling Penting untuk merubah aliran downstream)
                                        currentTopologyData.edges.forEach(edge => {
                                            if (edge.source === payload.node_id) {
                                                edge.status = newStatus;
                                            }
                                        });
                                    }

                                    // Selalu re-render (sekarang memakai mode update atribut, bukan innerHTML= '')
                                    drawNetwork(currentTopologyData.nodes, currentTopologyData.edges);

                                    // Jika panel yang terbuka adalah node ini, refresh panelnya
                                    if (activeNodeData && activeNodeData.id === payload.node_id) {
                                        showInfoPanel(currentTopologyData.nodes[nodeIdx]);
                                    }
                                }

                                // Handle konfirmasi perintah AWGC
                                if (payload.event_type === 'command_confirmed' && payload.command_id ==
                                    activeCommandId) {
                                    if (commandPollInterval) {
                                        clearInterval(commandPollInterval);
                                        commandPollInterval = null;
                                    }
                                    const isSuccess = payload.status_command === 'success';
                                    const txtEl = document.getElementById('awgc-btn-text');
                                    const statusText = document.getElementById('awgc-command-status-text');
                                    if (isSuccess) {
                                        txtEl.textContent = '✅ Berhasil (via WebSocket)';
                                        statusText.textContent = 'Pintu bergerak ke posisi ' + payload
                                            .bukaan_persen + '%';
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
