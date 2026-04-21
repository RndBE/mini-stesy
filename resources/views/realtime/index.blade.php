@extends('layouts.app')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            border: 0;
            height: auto;
            min-height: 28px;
            padding: 0;
            background: transparent;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b;
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.4;
            padding-left: 0;
            padding-right: 1.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 0;
        }

        .select2-dropdown {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            font-size: 0.875rem;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            padding: 6px 10px;
            font-size: 0.8rem;
            outline: none;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.4);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #303481;
        }
    </style>
@endpush

@section('content')
    @php
        $firstDevice = $devices->first();
    @endphp
    <div x-data="realtimeHandler()" x-init="initData()" class="space-y-3">

        <!-- Header -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                    <img src="{{ asset('logo/logo-awlr.svg') }}" alt="Logo" class="w-6 h-6">
                </div>
                <div>
@if ($devices->isNotEmpty())
                        <select id="loggerSelect" x-model="selectedDeviceId"
                            @change="switchDevice()"
                            class="appearance-none bg-transparent text-lg font-bold text-slate-800 border-none focus:ring-0 p-0 pr-8 cursor-pointer w-full md:w-auto">
                            @foreach ($devices as $d)
                                <option value="{{ $d->id_logger }}">
                                    {{ $d->id_logger }} - {{ $d->nama_logger ?? 'Logger' }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div class="text-lg font-bold text-slate-800">Belum ada device</div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <div class="flex items-center justify-end gap-2 text-sm font-semibold"
                        :class="dataOnline ? 'text-emerald-600' : 'text-rose-600'">
                        <span class="w-2 h-2 rounded-full"
                            :class="dataOnline ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
<span x-text="dataOnline ? 'Koneksi Terhubung' : 'Koneksi Terputus'"></span>
                    </div>
                    <div class="text-xs text-slate-500">Terakhir diperbarui <span x-text="lastUpdate"></span></div>
                </div>
                <button @click="loadDeviceData()" @disabled($devices->isEmpty())
                    class="hidden sm:block p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-h-[500px]">

            <!-- Tabs -->
            <div class="flex border-b border-slate-200 overflow-x-auto">
                <template x-for="tab in tabs" :key="tab.id">
                    <button @click="activeTab = tab.id; updateChart()"
                        :class="activeTab === tab.id ? 'border-indigo-600 text-indigo-600' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                        x-text="(tab.label || '').replaceAll('_', ' ')">
                    </button>
                </template>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Chart Header -->
                <div class="text-center mb-6">
                    <h2 class="text-lg font-bold text-slate-900">Data Realtime <span x-text="getTabLabel()"></span></h2>
<p class="text-slate-500 text-sm" x-text="moment().format('DD MMMM YYYY')"></p>
                </div>

                <!-- Chart Canvas -->
                <div class="relative h-[400px] w-full" x-show="selectedDeviceId">
                    <canvas id="realtimeChart"></canvas>
                </div>
                <div x-show="!selectedDeviceId" class="h-[400px] w-full grid place-items-center text-slate-400">
                    Tidak ada device yang bisa ditampilkan
                </div>

            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-800">Log Data Realtime (60 Menit Terakhir)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="bg-neutral-200 text-neutral-950 font-semibold uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-center">Waktu</th>
                            <th class="px-6 py-3 text-center">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
<template x-for="(row, i) in dataHistory" :key="`${row.waktu}-${i}`">

                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-center text-slate-900" x-text="row.waktu"></td>
                                <td class="px-6 py-4 text-center font-medium text-slate-900"
                                    x-text="row.value + ' ' + getUnit()"></td>
                            </tr>
                        </template>
                        <tr x-show="dataHistory.length === 0">
                            <td colspan="2" class="px-6 py-8 text-center text-slate-400">Tidak ada data realtime</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

        <script>
            function realtimeHandler() {
                const mqttCfg = @json($mqttConfig ?? []);

                return {
                    selectedDeviceId: @json(optional($firstDevice)->id_logger ?? ''),
                    selectedDeviceName: '',
                    lastUpdate: '-',
                    activeTab: null,
                    tabs: [],
                    chartInstance: null,
                    rawData: [],
                    dataHistory: [],
                    isLoadingData: false,
                    pollIntervalMs: 5000,
                    pollTimer: null,
                    reconnectDelayMs: 3000,
                    reconnectTimer: null,
                    lastDataAt: null,
                    dataOnline: false,
                    _clockTimer: null,
                    _pahoWaitTimer: null,

                    mqtt: {
                        // Pakai domain sendiri sebagai WSS proxy via Nginx
                        broker: String(mqttCfg.broker || window.location.hostname),
                        port: Number(mqttCfg.port || (window.location.protocol === 'https:' ? 443 : 80)),
                        path: String(mqttCfg.path || '/mqtt'),
                        user: String(mqttCfg.user || 'beacon'),
                        pass: String(mqttCfg.pass || 'be_jogja'),
                        // Otomatis WSS jika HTTPS
                        useSSL: window.location.protocol === 'https:',
                        client: null,
                        connected: false,
                        connecting: false,
                        currentTopic: null
                    },

                    switchDevice() {
                        this.activeTab = null;
                        this.rawData = [];
                        this.dataHistory = [];
                        this.tabs = [];
                        this.selectedDeviceName = '';
                        this.lastDataAt = null;
                        this.dataOnline = false;
                        if (this.mqtt.connected) {
                            this.subscribeMqttTopic(String(this.selectedDeviceId));
                        }
                        this.loadDeviceData({
                            force: true
                        });
                    },

                    startClock() {
                        if (this._clockTimer) clearInterval(this._clockTimer);
                        this._clockTimer = setInterval(() => {
                            this.lastUpdate = moment().format('HH:mm:ss');
                        }, 1000);
                    },

                    initData() {
                        this.startClock();

                        this.$nextTick(async () => {
                            this.initLoggerSelect2();

                            if (!this.selectedDeviceId) {
                                const firstOption = this.$el.querySelector('select option');
                                if (firstOption) this.selectedDeviceId = String(firstOption.value);
                            }

                            if (window.$ && $.fn && $.fn.select2 && this.selectedDeviceId) {
                                $('#loggerSelect').val(String(this.selectedDeviceId)).trigger('change.select2');
                            }

                            if (this.selectedDeviceId) {
                                await this.loadDeviceData();
                                this.startAutoRefresh();
                                this.waitPahoThenConnect();
                            }
                        });
                    },

                    initLoggerSelect2() {
                        if (!(window.$ && $.fn && $.fn.select2)) return;
                        const $select = $('#loggerSelect');
                        if (!$select.length) return;
                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }
                        $select.select2({
                            width: '100%',
                            placeholder: 'Cari ID/Nama logger...',
                            allowClear: false,
                            language: {
                                searching: function() {
                                    return 'Mencari...';
                                },
                                noResults: function() {
                                    return 'Logger tidak ditemukan';
                                },
                            }
                        });

                        const self = this;
                        $select.off('change.realtimeLogger').on('change.realtimeLogger', function() {
                            const next = String($(this).val() || '');
                            if (!next || next === String(self.selectedDeviceId || '')) return;
                            self.selectedDeviceId = next;
                            self.switchDevice();
                        });
                    },

                    startAutoRefresh() {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        this.pollTimer = setInterval(() => {
                            this.loadDeviceData({
                                silent: true,
                                merge: true
                            });
                        }, this.pollIntervalMs);
                    },
                    loadScriptOnce(src, key) {
                        return new Promise((resolve, reject) => {
                            const attr = 'data-lib-' + key;
                            const selector = 'script[' + attr + '="1"]';
                            const existing = document.querySelector(selector);

                            if (existing) {
                                if (window.Paho && (window.Paho.Client || (window.Paho.MQTT && window.Paho.MQTT.Client))) {
                                    resolve();
                                    return;
                                }
                                existing.addEventListener('load', () => resolve(), { once: true });
                                existing.addEventListener('error', () => reject(new Error('script load error')), { once: true });
                                return;
                            }

                            const script = document.createElement('script');
                            script.src = src;
                            script.async = true;
                            script.setAttribute(attr, '1');
                            script.onload = () => resolve();
                            script.onerror = () => reject(new Error('gagal load ' + src));
                            document.head.appendChild(script);
                        });
                    },

                    async ensurePahoLoaded() {
                        const ready = () => !!(window.Paho && (window.Paho.Client || (window.Paho.MQTT && window.Paho.MQTT.Client)));
                        if (ready()) return true;

                        const sources = [
                            'https://unpkg.com/paho-mqtt@1.1.0/mqttws31.min.js',
                            'https://cdn.jsdelivr.net/npm/paho-mqtt@1.1.0/mqttws31.min.js'
                        ];

                        for (const src of sources) {
                            try {
                                await this.loadScriptOnce(src, 'paho');
                                if (ready()) return true;
                            } catch (e) {}
                        }

                        return ready();
                    },

                    waitPahoThenConnect() {
                        if (this._pahoWaitTimer) clearInterval(this._pahoWaitTimer);

                        this.ensurePahoLoaded().then((loaded) => {
                            if (!loaded) {
                                console.warn('Paho gagal dimuat, MQTT dimatikan untuk halaman ini');
                                return;
                            }

                            let tries = 0;
                            this._pahoWaitTimer = setInterval(() => {
                                tries++;
                                const ok = !!(window.Paho && (window.Paho.Client || (window.Paho.MQTT && window.Paho.MQTT.Client)));

                                if (ok) {
                                    clearInterval(this._pahoWaitTimer);
                                    this._pahoWaitTimer = null;
                                    this.connectMqtt();
                                    this.subscribeMqttTopic(String(this.selectedDeviceId));
                                    return;
                                }

                                if (tries >= 60) {
                                    clearInterval(this._pahoWaitTimer);
                                    this._pahoWaitTimer = null;
                                    console.warn('Paho belum siap, MQTT dimatikan untuk halaman ini');
                                }
                            }, 200);
                        });
                    },
                    getTabLabel() {
                        const tab = this.tabs.find(t => t.id === this.activeTab);
                        return tab ? tab.label : '-';
                    },

                    getUnit() {
                        const tab = this.tabs.find(t => t.id === this.activeTab);
                        return tab ? tab.unit : '';
                    },

                    refreshDataOnline() {
                        if (!this.lastDataAt) {
                            this.dataOnline = false;
                            return;
                        }
                        const diffMs = Date.now() - this.lastDataAt.getTime();
                        this.dataOnline = (diffMs / 60000) < 60;
                    },

                    parseLoggerMoment(rawWaktu) {
                        if (!rawWaktu) return null;
                        const raw = String(rawWaktu).trim();
                        let ts = moment.parseZone(raw, moment.ISO_8601, true);
                        if (!ts.isValid()) {
                            ts = moment.parseZone(raw, 'YYYY-MM-DD HH:mm:ss', true);
                        }
                        return ts.isValid() ? ts : null;
                    },

                    extractLoggerTime(rawWaktu, withSeconds = true) {
                        const raw = String(rawWaktu || '').trim();
                        const withSec = raw.match(/(\d{2}:\d{2}:\d{2})/);
                        if (withSec) return withSeconds ? withSec[1] : withSec[1].slice(0, 5);

                        const noSec = raw.match(/(\d{2}:\d{2})/);
                        if (noSec) return noSec[1];

                        const parsed = this.parseLoggerMoment(raw);
                        if (!parsed) return raw;
                        return withSeconds ? parsed.format('HH:mm:ss') : parsed.format('HH:mm');
                    },

                    normalizeRealtimeRow(row) {
                        if (!row || !row.waktu) return null;
                        const rawWaktu = String(row.waktu).trim();
                        const ts = this.parseLoggerMoment(rawWaktu);
                        if (!ts) return null;
                        return {
                            ...row,
                            waktu: rawWaktu,
                            waktu_ts: ts.valueOf(),
                            waktu_label: this.extractLoggerTime(rawWaktu, true),
                            chart_label: this.extractLoggerTime(rawWaktu, false)
                        };
                    },

                    sortRealtimeRows(rows) {
                        return [...rows].sort((a, b) => Number(b.waktu_ts || 0) - Number(a.waktu_ts || 0));
                    },

                    getWindowedRows() {
                        if (!this.rawData.length) return [];
                        const rows = this.sortRealtimeRows(this.rawData);
                        const latestTs = Number(rows[0].waktu_ts || 0);
                        if (!latestTs) return rows;
                        const sixtyMinutesAgoTs = latestTs - (60 * 60 * 1000);
                        return rows.filter(d => {
                            const ts = Number(d.waktu_ts || 0);
                            return ts >= sixtyMinutesAgoTs && ts <= latestTs;
                        });
                    },

                    upsertRealtimeRow(row) {
                        const normalized = this.normalizeRealtimeRow(row);
                        if (!normalized) return null;

                        const newTs = Number(normalized.waktu_ts || 0);
                        const existingIdx = this.rawData.findIndex(r => Number(r.waktu_ts || 0) === newTs);

                        if (existingIdx >= 0) {
                            this.rawData[existingIdx] = {
                                ...this.rawData[existingIdx],
                                ...normalized
                            };
                        } else {
                            this.rawData.push(normalized);
                        }

                        this.rawData = this.sortRealtimeRows(this.rawData).slice(0, 2000);
                        return normalized;
                    },

                    buildFallbackTabs(sampleRow) {
                        if (!sampleRow) return [];
                        const deviceId = String(this.selectedDeviceId || '');
                        const sensorKeys = Object.keys(sampleRow)
                            .filter(k => /^sensor\d+$/i.test(k))
                            .sort((a, b) => Number(a.replace(/[^0-9]/g, '')) - Number(b.replace(/[^0-9]/g, '')));

                        return sensorKeys.map(key => ({
                            id: `${deviceId}:${key}`,
                            label: key.replace(/^sensor/i, 'Sensor '),
                            unit: '',
                            column: key
                        }));
                    },

                    async loadDeviceData(options = {}) {
                        const silent = !!options.silent;
                        const merge = !!options.merge;
                        const force = !!options.force;
                        const requestedDeviceId = String(this.selectedDeviceId || '');

                        if (!requestedDeviceId) {
                            this.rawData = [];
                            this.dataHistory = [];
                            this.tabs = [];
                            this.activeTab = null;
                            this.lastDataAt = null;
                            this.dataOnline = false;
                            return;
                        }

                        if (this.isLoadingData && !force) return;
                        this.isLoadingData = true;
                        if (!silent) this.lastUpdate = 'Updating...';

                        try {
                            const response = await fetch(`{{ route('realtime.index') }}/data/${requestedDeviceId}`);
                            const result = await response.json();
                            if (requestedDeviceId !== String(this.selectedDeviceId || '')) {
                                return;
                            }

                            if (result.success) {
                                const incomingRows = (result.data || [])
                                    .map(row => this.normalizeRealtimeRow(row))
                                    .filter(Boolean);

                                if (merge) {
                                    incomingRows.forEach(row => this.upsertRealtimeRow(row));
                                } else {
                                    this.rawData = this.sortRealtimeRows(incomingRows);
                                }

                                this.selectedDeviceName = result.device?.nama_logger || '';
                                this.subscribeMqttTopic(requestedDeviceId);
                                this.lastDataAt = this.rawData?.[0]?.waktu_ts ? new Date(this.rawData[0].waktu_ts) : null;
                                this.refreshDataOnline();

                                if (result.params && result.params.length > 0) {
                                    this.tabs = result.params
                                        .filter(p => p && p.kolom_sensor)
                                        .map(p => ({
                                            id: `${requestedDeviceId}:${p.kolom_sensor}`,
                                            label: p.nama_parameter || p.kolom_sensor.replace(/^sensor/i,
                                                'Sensor '),
                                            unit: p.satuan || '',
                                            column: p.kolom_sensor
                                        }))
                                        .sort((a, b) => {
                                            const matchA = String(a.column || '').match(/\d+/);
                                            const matchB = String(b.column || '').match(/\d+/);
                                            const numA = matchA ? parseInt(matchA[0], 10) : 9999;
                                            const numB = matchB ? parseInt(matchB[0], 10) : 9999;
                                            return numA - numB;
                                        });
                                } else {
                                    this.tabs = this.buildFallbackTabs(this.rawData[0] || incomingRows[0] || null);
                                }

                                if (this.tabs.length > 0 && (!this.activeTab || !this.tabs.find(t => t.id === this
                                        .activeTab))) {
                                    this.activeTab = this.tabs[0].id;
                                }

                                this.updateChart();
                                if (!silent) this.lastUpdate = moment().format('HH:mm:ss');
                            } else {
                                if (!silent) console.warn(result.message);
                                this.dataHistory = [];
                                this.rawData = [];
                                this.tabs = [];
                                if (this.chartInstance) {
                                    this.chartInstance.destroy();
                                    this.chartInstance = null;
                                }
                            }

                        } catch (error) {
                            console.error('Error fetching data:', error);
                        } finally {
                            this.isLoadingData = false;
                        }
                    },

                    updateChart() {
                        this.$nextTick(() => {
                            const canvas = document.getElementById('realtimeChart');
                            if (!canvas) return;

                            const ctx = canvas.getContext('2d');
                            if (!ctx) return;

                            let currentTab = this.tabs.find(t => t.id === this.activeTab);
                            if (!currentTab && this.tabs.length > 0) {
                                currentTab = this.tabs[0];
                                this.activeTab = currentTab.id;
                            }
                            if (!currentTab) {
                                this.dataHistory = [];
                                if (this.chartInstance) {
                                    this.chartInstance.destroy();
                                    this.chartInstance = null;
                                }
                                return;
                            }

                            const dbColumn = currentTab.column;

                            const windowedRows = this.getWindowedRows();
                            const sortedDocs = [...windowedRows].sort((a, b) => Number(a.waktu_ts || 0) - Number(b.waktu_ts || 0));

                            const chartLabels = sortedDocs.map(d => d.chart_label || this.extractLoggerTime(d.waktu, false));
                            const chartData = sortedDocs.map(d => {
                                const v = d[dbColumn];
                                return (v !== null && v !== undefined && v !== '' && !isNaN(v)) ? parseFloat(
                                    v) : null;
                            });

                            if (this.chartInstance) {
                                this.chartInstance.destroy();
                                this.chartInstance = null;
                            }

                            this.chartInstance = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: chartLabels,
                                    datasets: [{
                                        label: currentTab.label,
                                        data: chartData,
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        borderWidth: 2,
                                        tension: 0.4,
                                        cubicInterpolationMode: 'monotone',
                                        fill: true,
                                        pointRadius: 2,
                                        pointHoverRadius: 4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    animation: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            callbacks: {
                                                label: (context) => {
                                                    let label = context.dataset.label || '';
                                                    if (label) label += ': ';
                                                    if (context.parsed.y !== null) label += context.parsed
                                                        .y + ' ' + currentTab.unit;
                                                    return label;
                                                }
                                            }
                                        }
                                    },
                                    interaction: {
                                        mode: 'nearest',
                                        axis: 'x',
                                        intersect: false
                                    },
                                    scales: {
                                        x: {
                                            grid: {
                                                display: false
                                            },
                                            ticks: {
                                                maxTicksLimit: 10
                                            }
                                        },
                                        y: {
                                            beginAtZero: false
                                        }
                                    }
                                }
                            });

                            this.updateTable(dbColumn);
                        });
                    },

                    updateTable(column) {
                        const windowedRows = this.getWindowedRows();
                        const latestFirstRows = [...windowedRows].sort((a, b) => Number(b.waktu_ts || 0) - Number(a.waktu_ts || 0));

                        this.dataHistory = latestFirstRows.map(row => {
                            const num = Number(row[column]);
                            return {
                                waktu: row.waktu_label || this.extractLoggerTime(row.waktu, true),
                                value: Number.isFinite(num) ? num.toFixed(2) : '-'
                            };
                        });
                    },

                    connectMqtt() {
                        if (this.mqtt.connected || this.mqtt.connecting) return;

                        const clientID = "web_" + Math.floor(Math.random() * 1000000);
                        const PahoClient = window.Paho?.Client || window.Paho?.MQTT?.Client;
                        if (!PahoClient) {
                            console.warn('Paho Client constructor tidak ditemukan');
                            return;
                        }
                        this.mqtt.client = new PahoClient(
                            this.mqtt.broker,
                            Number(this.mqtt.port),
                            String(this.mqtt.path || '/mqtt'),
                            clientID
                        );

                        this.mqtt.connecting = true;

                        this.mqtt.client.onConnectionLost = () => {
                            this.mqtt.connected = false;
                            this.mqtt.connecting = false;
                            this.scheduleMqttReconnect();
                        };

                        this.mqtt.client.onMessageArrived = (message) => {
                            this.handleMqttMessage(message);
                        };

                        this.mqtt.client.connect({
                            timeout: 3,
                            useSSL: this.mqtt.useSSL,
                            userName: this.mqtt.user,
                            password: this.mqtt.pass,
                            onSuccess: () => {
                                this.mqtt.connected = true;
                                this.mqtt.connecting = false;
                                this.subscribeMqttTopic(String(this.selectedDeviceId));
                            },
                            onFailure: (err) => {
                                this.mqtt.connected = false;
                                this.mqtt.connecting = false;
                                console.log('MQTT connect failed:', err);
                                this.scheduleMqttReconnect();
                            }
                        });
                    },

                    scheduleMqttReconnect() {
                        if (this.reconnectTimer) return;
                        this.reconnectTimer = setTimeout(() => {
                            this.reconnectTimer = null;
                            this.connectMqtt();
                        }, this.reconnectDelayMs);
                    },

                    subscribeMqttTopic(topic) {
                        if (!this.mqtt.client || !this.mqtt.connected || !topic) return;

                        const nextTopic = String(topic);

                        if (this.mqtt.currentTopic && this.mqtt.currentTopic !== nextTopic) {
                            try {
                                this.mqtt.client.unsubscribe(this.mqtt.currentTopic);
                            } catch (e) {}
                        }

                        this.mqtt.currentTopic = nextTopic;
                        this.mqtt.client.subscribe(this.mqtt.currentTopic, {
                            qos: 0
                        });
                    },

                    handleMqttMessage(message) {
                        const topic = String(message.destinationName || '');
                        if (topic !== String(this.selectedDeviceId)) return;

                        let data;
                        try {
                            data = JSON.parse(message.payloadString || '{}');
                        } catch (e) {
                            return;
                        }

                        if (!data || !data.waktu) return;
                        const normalized = this.upsertRealtimeRow(data);
                        if (!normalized) return;

                        this.lastDataAt = normalized.waktu_ts ? new Date(normalized.waktu_ts) : null;
                        this.refreshDataOnline();
                        this.updateChart();
                    }
                }
            }
        </script>
    @endpush

@endsection
