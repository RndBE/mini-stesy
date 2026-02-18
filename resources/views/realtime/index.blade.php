@extends('layouts.app')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://unpkg.com/paho-mqtt/mqttws31.min.js"></script>
@endpush

@section('content')
    @php
        $firstDevice = $devices->first();
    @endphp
    <div x-data="realtimeHandler()" x-init="initData()" class="space-y-6">

        <!-- Header -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                    <img src="{{ asset('logo/logo-awlr.svg') }}" alt="Logo" class="w-6 h-6">
                </div>
                <div>
                    {{-- <h1 class="text-xl font-bold text-slate-800" x-text="selectedDeviceName || 'Pilih Pos'"></h1> --}}
                    @if ($devices->isNotEmpty())
                        <select x-model="selectedDeviceId"
                            @change="activeTab = null; rawData = []; dataHistory = []; loadDeviceData()"
                            class="appearance-none bg-transparent text-lg font-bold text-slate-800 border-none focus:ring-0 p-0 pr-8 cursor-pointer w-full md:w-auto">
                            @foreach ($devices as $d)
                                <option value="{{ $d->id_logger }}">{{ $d->nama_logger }}</option>
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
                        {{-- {{ optional($firstDevice)->status_logger === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus' }} --}}
                        <span x-text="dataOnline ? 'Koneksi Terhubung' : 'Koneksi Terputus'"></span>
                    </div>
                    <div class="text-xs text-slate-500">Terakhir diperbarui <span x-text="lastUpdate"></span></div>
                </div>
                <button @click="loadDeviceData()" @disabled($devices->isEmpty())
                    class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-colors">
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
                        x-text="tab.label">
                    </button>
                </template>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Chart Header -->
                <div class="text-center mb-6">
                    <h2 class="text-lg font-bold text-slate-900">Data Realtime <span x-text="getTabLabel()"></span></h2>
                    {{-- <p class="text-slate-500 text-sm">{{ \Carbon\Carbon::now()->format('d F Y') }}</p> --}}
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
                        <template x-for="row in dataHistory" :key="row.waktu">
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

    {{-- @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        <script>
            function realtimeHandler() {
                return {
                    selectedDeviceId: @json(optional($firstDevice)->id_logger ?? ''),
                    selectedDeviceName: '',
                    lastUpdate: '-',
                    activeTab: null, // Will be set after loading params
                    tabs: [],
                    chartInstance: null,
                    rawData: [], // Full API response data
                    dataHistory: [], // Processed for table/chart
                    isLoadingData: false,
                    pollIntervalMs: 5000,
                    pollTimer: null,
                    reconnectDelayMs: 3000,
                    reconnectTimer: null,
                    lastDataAt: null,
                    dataOnline: false,

                    mqtt: {
                        broker: 'mqtt.beacontelemetry.com',
                        port: 8083,
                        user: 'userlog',
                        pass: 'b34c0n',
                        useSSL: false,
                        client: null,
                        connected: false,
                        connecting: false,
                        currentTopic: null
                    },

                    // initData() {
                    //     this.lastUpdate = moment().format('HH:mm:ss');
                    //     if (this.selectedDeviceId) {
                    //         this.loadDeviceData();
                    //     }
                    // },

                    startClock() {
                        if (this._clockTimer) clearInterval(this._clockTimer);
                        this._clockTimer = setInterval(() => {
                            this.lastUpdate = moment().format('HH:mm:ss');
                        }, 1000);
                    },

                    initData() {
                        this.startClock();

                        this.$nextTick(async () => {
                            if (!this.selectedDeviceId) {
                                const firstOption = this.$el.querySelector('select option');
                                if (firstOption) this.selectedDeviceId = String(firstOption.value);
                            }

                            if (this.selectedDeviceId) {
                                await this.loadDeviceData();
                            }

                            this.startAutoRefresh();

                            setTimeout(() => {
                                if (window.Paho && window.Paho.MQTT && window.Paho.MQTT.Client) {
                                    this.connectMqtt();
                                    this.subscribeMqttTopic(String(this.selectedDeviceId));
                                } else {
                                    console.warn('Paho belum siap, MQTT skip dulu');
                                }
                            }, 0);
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
                            this.dataOnline = false
                            return
                        }
                        const diffMs = Date.now() - this.lastDataAt.getTime()
                        this.dataOnline = (diffMs / 60000) < 60
                    },


                    normalizeRealtimeRow(row) {
                        if (!row || !row.waktu) return null;
                        const ts = moment(row.waktu);
                        if (!ts.isValid()) return null;
                        return {
                            ...row,
                            waktu: ts.format('YYYY-MM-DD HH:mm:ss')
                        };
                    },

                    sortRealtimeRows(rows) {
                        return [...rows].sort((a, b) => new Date(b.waktu) - new Date(a.waktu));
                    },

                    getWindowedRows() {
                        if (!this.rawData.length) return [];
                        const rows = this.sortRealtimeRows(this.rawData);
                        const latestTime = moment(rows[0].waktu);
                        if (!latestTime.isValid()) return rows;
                        const sixtyMinutesAgo = latestTime.clone().subtract(60, 'minutes');
                        return rows.filter(d => {
                            const dataTime = moment(d.waktu);
                            return dataTime.isValid() && dataTime.isSameOrAfter(sixtyMinutesAgo) && dataTime
                                .isSameOrBefore(latestTime);
                        });
                    },

                    upsertRealtimeRow(row) {
                        const normalized = this.normalizeRealtimeRow(row);
                        if (!normalized) return false;

                        const newTs = moment(normalized.waktu).valueOf();
                        const existingIdx = this.rawData.findIndex(r => moment(r.waktu).valueOf() === newTs);

                        if (existingIdx >= 0) {
                            this.rawData[existingIdx] = {
                                ...this.rawData[existingIdx],
                                ...normalized
                            };
                        } else {
                            this.rawData.push(normalized);
                        }

                        this.rawData = this.sortRealtimeRows(this.rawData).slice(0, 2000);
                        return true;
                    },

                    buildFallbackTabs(sampleRow) {
                        if (!sampleRow) return [];
                        const sensorKeys = Object.keys(sampleRow)
                            .filter(k => /^sensor\d+$/i.test(k))
                            .sort((a, b) => Number(a.replace(/[^0-9]/g, '')) - Number(b.replace(/[^0-9]/g, '')));

                        return sensorKeys.map(key => ({
                            id: key,
                            label: key.replace(/^sensor/i, 'Sensor '),
                            unit: '',
                            column: key
                        }));
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

                    async loadDeviceData(options = {}) {
                        const silent = !!options.silent;
                        const merge = !!options.merge;

                        if (!this.selectedDeviceId) {
                            this.rawData = [];
                            this.dataHistory = [];
                            this.tabs = [];
                            this.activeTab = null;
                            return;
                        }

                        if (this.isLoadingData) return;
                        this.isLoadingData = true;
                        if (!silent) this.lastUpdate = 'Updating...';

                        try {
                            const response = await fetch(`{{ route('realtime.index') }}/data/${this.selectedDeviceId}`);
                            const result = await response.json();

                            if (result.success) {
                                const incomingRows = (result.data || [])
                                    .map(row => this.normalizeRealtimeRow(row))
                                    .filter(Boolean);

                                if (merge) {
                                    incomingRows.forEach(row => this.upsertRealtimeRow(row));
                                } else {
                                    this.rawData = this.sortRealtimeRows(incomingRows);
                                }

                                this.selectedDeviceName = result.device.nama_logger;
                                this.lastDataAt = this.rawData?.[0]?.waktu ? new Date(this.rawData[0].waktu) : null
                                this.refreshDataOnline()
                                this.subscribeMqttTopic(String(this.selectedDeviceId));

                                // Dynamically build tabs from params
                                if (result.params && result.params.length > 0) {
                                    this.tabs = result.params
                                        .filter(p => p && p.kolom_sensor)
                                        .map(p => ({
                                            id: p.nama_parameter || p.kolom_sensor,
                                            label: p.nama_parameter || p.kolom_sensor.replace(/^sensor/i,
                                                'Sensor '),
                                            unit: p.satuan || '',
                                            column: p.kolom_sensor
                                        }));

                                    if (this.tabs.length === 0) {
                                        const fallbackTabs = this.buildFallbackTabs(this.rawData[0] || incomingRows[0] ||
                                            null);
                                        this.tabs = fallbackTabs;
                                        this.activeTab = fallbackTabs.length ? fallbackTabs[0].id : null;
                                    }

                                    // Set active tab if not set or invalid
                                    if (this.tabs.length > 0 && (!this.activeTab || !this.tabs.find(t => t.id === this
                                            .activeTab))) {
                                        this.activeTab = this.tabs[0].id;
                                    }
                                } else {
                                    const fallbackTabs = this.buildFallbackTabs(this.rawData[0] || incomingRows[0] ||
                                        null);
                                    this.tabs = fallbackTabs;
                                    this.activeTab = fallbackTabs.length ? fallbackTabs[0].id : null;
                                }

                                this.updateChart();

                                this.lastUpdate = moment().format('HH:mm:ss');
                            } else if (!silent) {
                                console.warn(result.message);
                                this.dataHistory = [];
                                this.rawData = [];
                                this.tabs = [];
                                if (this.chartInstance) {
                                    this.chartInstance.data.labels = [];
                                    this.chartInstance.data.datasets[0].data = [];
                                    this.chartInstance.update();
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
                            let chartLabels = [];
                            let chartData = [];

                            const windowedRows = this.getWindowedRows();
                            if (windowedRows.length > 0) {
                                // Sort ascending for chart
                                const sortedDocs = [...windowedRows].sort((a, b) => new Date(a.waktu) - new Date(b
                                    .waktu));
                                chartLabels = sortedDocs.map(d => moment(d.waktu).format('HH:mm'));

                                chartData = sortedDocs.map(d => {
                                    const v = d[dbColumn];
                                    return (v !== null && v !== undefined && v !== '' && !isNaN(v)) ?
                                        parseFloat(v) : null;
                                });
                            }

                            if (this.chartInstance) {
                                this.chartInstance.destroy(); // Always destroy to ensure clean state
                                this.chartInstance = null;
                            }
                            if (!this.chartInstance) {
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
                                                    label: function(context) {
                                                        let label = context.dataset.label || '';
                                                        if (label) label += ': ';
                                                        if (context.parsed.y !== null) {
                                                            label += context.parsed.y + ' ' + currentTab
                                                                .unit;
                                                        }
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
                            } else {
                                this.chartInstance.data.labels = chartLabels;
                                this.chartInstance.data.datasets[0].data = chartData;
                                this.chartInstance.data.datasets[0].label = currentTab.label;
                                this.chartInstance.update('none');
                            }

                            this.updateTable(dbColumn);
                        });
                    },


                    updateTable(column) {
                        const windowedRows = this.getWindowedRows();
                        if (windowedRows.length > 0) {
                            const latestFirstRows = [...windowedRows].sort((a, b) => new Date(b.waktu) - new Date(a
                                .waktu));
                            this.dataHistory = latestFirstRows.map(row => {
                                const num = Number(row[column]);
                                return {
                                    waktu: moment(row.waktu).format('HH:mm:ss'),
                                    value: Number.isFinite(num) ? num.toFixed(2) : '-'
                                };
                            });
                        } else {
                            this.dataHistory = [];
                        }
                    },
                    connectMqtt() {
                        if (this.mqtt.connected || this.mqtt.connecting) return;

                        const clientID = "web_" + Math.floor(Math.random() * 1000000);
                        this.mqtt.client = new Paho.MQTT.Client(this.mqtt.broker, Number(this.mqtt.port), clientID);
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
                        if (!this.upsertRealtimeRow(data)) return;

                        this.lastDataAt = new Date(data.waktu)
                        this.refreshDataOnline()

                        this.lastUpdate = moment().format('HH:mm:ss');
                        this.updateChart();
                    },

                }
            }
        </script>
    @endpush --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

        <script>
            function realtimeHandler() {
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
                        broker: 'mqtt.beacontelemetry.com',
                        port: 8083,
                        user: 'userlog',
                        pass: 'b34c0n',
                        useSSL: false,
                        client: null,
                        connected: false,
                        connecting: false,
                        currentTopic: null
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
                            if (!this.selectedDeviceId) {
                                const firstOption = this.$el.querySelector('select option');
                                if (firstOption) this.selectedDeviceId = String(firstOption.value);
                            }

                            if (this.selectedDeviceId) {
                                await this.loadDeviceData();
                                this.startAutoRefresh();
                                this.waitPahoThenConnect();
                            }
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

                    waitPahoThenConnect() {
                        if (this._pahoWaitTimer) clearInterval(this._pahoWaitTimer);

                        let tries = 0;
                        this._pahoWaitTimer = setInterval(() => {
                            tries++;

                            const ok = window.Paho && window.Paho.MQTT && window.Paho.MQTT.Client;
                            if (ok) {
                                clearInterval(this._pahoWaitTimer);
                                this._pahoWaitTimer = null;
                                this.connectMqtt();
                                this.subscribeMqttTopic(String(this.selectedDeviceId));
                                return;
                            }

                            if (tries >= 20) {
                                clearInterval(this._pahoWaitTimer);
                                this._pahoWaitTimer = null;
                                console.warn('Paho belum siap, MQTT dimatikan untuk halaman ini');
                            }
                        }, 150);
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

                    normalizeRealtimeRow(row) {
                        if (!row || !row.waktu) return null;
                        const ts = moment(row.waktu);
                        if (!ts.isValid()) return null;
                        return {
                            ...row,
                            waktu: ts.format('YYYY-MM-DD HH:mm:ss')
                        };
                    },

                    sortRealtimeRows(rows) {
                        return [...rows].sort((a, b) => new Date(b.waktu) - new Date(a.waktu));
                    },

                    getWindowedRows() {
                        if (!this.rawData.length) return [];
                        const rows = this.sortRealtimeRows(this.rawData);
                        const latestTime = moment(rows[0].waktu);
                        if (!latestTime.isValid()) return rows;
                        const sixtyMinutesAgo = latestTime.clone().subtract(60, 'minutes');
                        return rows.filter(d => {
                            const dataTime = moment(d.waktu);
                            return dataTime.isValid() && dataTime.isSameOrAfter(sixtyMinutesAgo) && dataTime
                                .isSameOrBefore(latestTime);
                        });
                    },

                    upsertRealtimeRow(row) {
                        const normalized = this.normalizeRealtimeRow(row);
                        if (!normalized) return false;

                        const newTs = moment(normalized.waktu).valueOf();
                        const existingIdx = this.rawData.findIndex(r => moment(r.waktu).valueOf() === newTs);

                        if (existingIdx >= 0) {
                            this.rawData[existingIdx] = {
                                ...this.rawData[existingIdx],
                                ...normalized
                            };
                        } else {
                            this.rawData.push(normalized);
                        }

                        this.rawData = this.sortRealtimeRows(this.rawData).slice(0, 2000);
                        return true;
                    },

                    buildFallbackTabs(sampleRow) {
                        if (!sampleRow) return [];
                        const sensorKeys = Object.keys(sampleRow)
                            .filter(k => /^sensor\d+$/i.test(k))
                            .sort((a, b) => Number(a.replace(/[^0-9]/g, '')) - Number(b.replace(/[^0-9]/g, '')));

                        return sensorKeys.map(key => ({
                            id: key,
                            label: key.replace(/^sensor/i, 'Sensor '),
                            unit: '',
                            column: key
                        }));
                    },

                    async loadDeviceData(options = {}) {
                        const silent = !!options.silent;
                        const merge = !!options.merge;

                        if (!this.selectedDeviceId) {
                            this.rawData = [];
                            this.dataHistory = [];
                            this.tabs = [];
                            this.activeTab = null;
                            return;
                        }

                        if (this.isLoadingData) return;
                        this.isLoadingData = true;
                        if (!silent) this.lastUpdate = 'Updating...';

                        try {
                            const response = await fetch(`{{ route('realtime.index') }}/data/${this.selectedDeviceId}`);
                            const result = await response.json();

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
                                this.lastDataAt = this.rawData?.[0]?.waktu ? new Date(this.rawData[0].waktu) : null;
                                this.refreshDataOnline();

                                if (result.params && result.params.length > 0) {
                                    this.tabs = result.params
                                        .filter(p => p && p.kolom_sensor)
                                        .map(p => ({
                                            id: p.nama_parameter || p.kolom_sensor,
                                            label: p.nama_parameter || p.kolom_sensor.replace(/^sensor/i,
                                                'Sensor '),
                                            unit: p.satuan || '',
                                            column: p.kolom_sensor
                                        }));
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
                            const sortedDocs = [...windowedRows].sort((a, b) => new Date(a.waktu) - new Date(b.waktu));

                            const chartLabels = sortedDocs.map(d => moment(d.waktu).format('HH:mm'));
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
                        const latestFirstRows = [...windowedRows].sort((a, b) => new Date(b.waktu) - new Date(a.waktu));

                        this.dataHistory = latestFirstRows.map(row => {
                            const num = Number(row[column]);
                            return {
                                waktu: moment(row.waktu).format('HH:mm:ss'),
                                value: Number.isFinite(num) ? num.toFixed(2) : '-'
                            };
                        });
                    },

                    connectMqtt() {
                        if (this.mqtt.connected || this.mqtt.connecting) return;

                        const clientID = "web_" + Math.floor(Math.random() * 1000000);
                        this.mqtt.client = new Paho.MQTT.Client(this.mqtt.broker, Number(this.mqtt.port), clientID);
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
                        if (!this.upsertRealtimeRow(data)) return;

                        this.lastDataAt = new Date(data.waktu);
                        this.refreshDataOnline();
                        this.updateChart();
                    }
                }
            }
        </script>
    @endpush

@endsection
