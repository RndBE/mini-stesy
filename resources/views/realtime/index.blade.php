@extends('layouts.app')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
@endpush

@section('content')
    <div x-data="realtimeHandler()" x-init="initData()" class="space-y-6">

        <!-- Header -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                    <img src="{{ asset('logo/logo-awlr.svg') }}" alt="Logo" class="w-6 h-6">
                    {{-- <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg> --}}
                </div>
                <div>
                    {{-- <h1 class="text-xl font-bold text-slate-800" x-text="selectedDeviceName || 'Pilih Pos'"></h1> --}}
                    <select x-model="selectedDeviceId" @change="loadDeviceData()"
                        class="appearance-none bg-transparent text-lg font-bold text-slate-800 border-none focus:ring-0 p-0 pr-8 cursor-pointer w-full md:w-auto">
                        @foreach ($devices as $d)
                            <option value="{{ $d->id_logger }}">{{ $d->nama_logger }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <div class="flex items-center justify-end gap-2 text-emerald-600 text-sm font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ $devices->first()->status_logger === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus' }}
                    </div>
                    <div class="text-xs text-slate-500">Terakhir diperbarui <span x-text="lastUpdate"></span></div>
                </div>
                <button @click="loadDeviceData()"
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
                <div class="relative h-[400px] w-full">
                    <canvas id="realtimeChart"></canvas>
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

    <script>
        function realtimeHandler() {
            return {
                selectedDeviceId: '{{ $devices->first()->id_logger ?? '' }}',
                selectedDeviceName: '',
                lastUpdate: '-',
                activeTab: null, // Will be set after loading params
                tabs: [],
                chartInstance: null,
                rawData: [], // Full API response data
                dataHistory: [], // Processed for table/chart

                initData() {
                    this.lastUpdate = moment().format('HH:mm:ss');
                    if (this.selectedDeviceId) {
                        this.loadDeviceData();
                    }
                },

                getTabLabel() {
                    const tab = this.tabs.find(t => t.id === this.activeTab);
                    return tab ? tab.label : '-';
                },

                getUnit() {
                    const tab = this.tabs.find(t => t.id === this.activeTab);
                    return tab ? tab.unit : '';
                },

                async loadDeviceData() {
                    this.lastUpdate = 'Updating...';

                    try {
                        const response = await fetch(`{{ route('realtime.index') }}/data/${this.selectedDeviceId}`);
                        const result = await response.json();

                        if (result.success) {
                            this.rawData = result.data;
                            this.selectedDeviceName = result.device.nama_logger;

                            // Dynamically build tabs from params
                            if (result.params && result.params.length > 0) {
                                this.tabs = result.params.map(p => ({
                                    id: p.nama_parameter, // Unique ID
                                    label: p.nama_parameter,
                                    unit: p.satuan || '', // Assuming 'satuan' exists in DB, else empty
                                    column: p.kolom_sensor
                                }));

                                // Set active tab if not set or invalid
                                if (!this.activeTab || !this.tabs.find(t => t.id === this.activeTab)) {
                                    this.activeTab = this.tabs[0].id;
                                }
                            } else {
                                this.tabs = [];
                            }

                            this.updateChart();

                            this.lastUpdate = moment().format('HH:mm:ss');
                        } else {
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
                    }
                },

                updateChart() {
                    this.$nextTick(() => {
                        const canvas = document.getElementById('realtimeChart');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        if (!ctx) return;

                        const currentTab = this.tabs.find(t => t.id === this.activeTab);
                        if (!currentTab) return;

                        const dbColumn = currentTab.column;
                        let chartLabels = [];
                        let chartData = [];

                        if (this.rawData.length > 0) {
                            // Get the most recent timestamp
                            const latestTime = moment(this.rawData[0].waktu);
                            const sixtyMinutesAgo = latestTime.clone().subtract(60, 'minutes');

                            // Filter data to only show last 60 minutes from the latest update
                            const filtered60Min = this.rawData.filter(d => {
                                const dataTime = moment(d.waktu);
                                return dataTime.isSameOrAfter(sixtyMinutesAgo) && dataTime.isSameOrBefore(latestTime);
                            });

                            // Sort ascending for chart
                            const sortedDocs = [...filtered60Min].sort((a, b) => new Date(a.waktu) - new Date(b.waktu));
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
                    if (this.rawData.length > 0) {
                        // Table shows raw data (descending order usually preferred for log, which API returns)
                        // API returns orderBy('waktu', 'desc'), so rawData is already desc?
                        // Let's verify: yes, api uses orderBy('waktu', 'desc').

                        this.dataHistory = this.rawData.map(row => ({
                            waktu: moment(row.waktu).format('HH:mm:ss'),
                            value: (row[column] !== null && row[column] !== undefined) ? Number(row[column])
                                .toFixed(2) : '-'
                        }));
                    } else {
                        this.dataHistory = [];
                    }
                }
            }
        }
    </script>
@endsection
