@extends('layouts.app')

@section('title', $title)

@push('head')
    <style>
        .analysis-container {
            display: flex;
            gap: 0;
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .sidebar-left {
            width: 250px;
            min-width: 250px;
            background: #f8f9fa;
            padding: 24px 20px;
            border-right: 1px solid #e5e7eb;
        }

        .section-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .radio-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 20px;
        }

        .radio-options label {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .calendar-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #3730a3;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 12px;
        }

        .btn-primary:hover {
            background: #312e81;
        }

        .btn-success {
            width: 100%;
            padding: 10px;
            background: white;
            color: #059669;
            border: 1.5px solid #059669;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-success:hover {
            background: #ecfdf5;
        }

        .btn-outline {
            width: 100%;
            padding: 10px;
            background: white;
            color: #374151;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        .content-main {
            flex: 1;
            padding: 24px 32px;
            min-width: 0;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 8px;
        }

        .btn-header {
            padding: 8px 16px;
            background: #3730a3;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-header:hover {
            background: #312e81;
        }

        .chart-section {
            margin-bottom: 24px;
        }

        .chart-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }

        .chart-legend {
            position: absolute;
            top: 30px;
            right: 40px;
            background: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            z-index: 10;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .legend-item:last-child {
            margin-bottom: 0;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .data-table-section {
            background: white;
        }

        .table-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table thead {
            background: #f3f4f6;
        }

        .data-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        .data-table th:not(:first-child) {
            text-align: right;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
        }

        .data-table td:not(:first-child) {
            text-align: right;
        }

        .data-table tbody tr:hover {
            background: #f9fafb;
        }

        /* Info Panel */
        .info-panel {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .info-panel.show {
            right: 0;
        }

        .info-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: 999;
        }

        .info-panel-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .info-panel-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #261cee;
            color: rgb(255, 255, 255);
        }

        .info-panel-title {
            font-size: 18px;
            font-weight: 600;
        }

        .info-panel-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-panel-body {
            padding: 24px;
        }

        .info-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 15px;
            color: #111827;
            font-weight: 500;
        }

        /* Documentation Modal */
        .doc-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .doc-modal.show {
            display: flex;
        }

        .doc-modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .doc-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #3730a3;
            color: white;
        }

        .doc-modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .doc-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .doc-modal-body {
            padding: 24px;
            overflow-y: auto;
        }

        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .photo-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .photo-item:hover {
            transform: scale(1.05);
        }

        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .photo-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #3730a3;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .no-photos {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
    </style>
@endpush

@section('content')
    <!-- Info Panel Overlay -->
    <div class="info-panel-overlay" id="infoPanelOverlay" onclick="closeInfoPanel()"></div>

    <!-- Info Panel -->
    <div class="info-panel" id="infoPanel">
        <div class="info-panel-header">
            <div class="info-panel-title">Informasi Logger</div>
            <button class="info-panel-close" onclick="closeInfoPanel()">×</button>
        </div>
        <div class="info-panel-body">
            <div class="info-item">
                <div class="info-label">ID Logger</div>
                <div class="info-value">{{ $logger->id_logger }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Nama Logger</div>
                <div class="info-value">{{ $logger->nama_logger }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Seri Logger</div>
                <div class="info-value">{{ $logger->informasi->seri_logger ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Sensor</div>
                <div class="info-value">{{ $logger->sensor_count ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Serial Number</div>
                <div class="info-value">{{ $logger->informasi->serial_number ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">No. Seluler</div>
                <div class="info-value">{{ $logger->no_seluler ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Nama Penjaga</div>
                <div class="info-value">{{ $logger->informasi->nama_pic ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Nomor Penjaga</div>
                <div class="info-value">{{ $logger->informasi->no_pic ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Lokasi</div>
                <div class="info-value">{{ $logger->lokasi->nama_lokasi ?? '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Documentation Modal -->
    <div class="doc-modal" id="docModal" onclick="closeDocModal(event)">
        <div class="doc-modal-content" onclick="event.stopPropagation()">
            <div class="doc-modal-header">
                <div class="doc-modal-title">Dokumentasi Foto</div>
                <button class="doc-modal-close" onclick="closeDocModal()">×</button>
            </div>
            <div class="doc-modal-body">
                @if ($photos->count() > 0)
                    <div class="photo-gallery">
                        @foreach ($photos as $photo)
                            <div class="photo-item" onclick="window.open('{{ asset($photo->url_foto) }}', '_blank')">
                                <img src="{{ asset($photo->url_foto) }}" alt="Dokumentasi">
                                {{-- @if ($photo->foto_utama == 1)
                                    <div class="photo-badge">Utama</div>
                                @endif --}}
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-photos">
                        <p>Tidak ada foto dokumentasi untuk logger ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Data Masuk Modal -->
    <div class="doc-modal" id="dataMasukModal" onclick="closeDataMasukModal(event)">
        <div class="doc-modal-content" style="max-width: 900px;" onclick="event.stopPropagation()">
            <div class="doc-modal-header">
                <div class="doc-modal-title">Jumlah Data Masuk 30 Hari Terakhir</div>
                <button class="doc-modal-close" onclick="closeDataMasukModal()">×</button>
            </div>
            <div class="doc-modal-body">
                <div style="height: 400px;">
                    <canvas id="dataMasukChart"></canvas>
                </div>
                <div id="dataMasukLoading" style="display:none;text-align:center;padding:20px;">
                    Memuat data...
                </div>
            </div>
        </div>
    </div>


    <div class="analysis-container">
        {{-- LEFT SIDEBAR --}}
        <div class="sidebar-left">
            <div class="mb-4">
                <a href="{{ route('peta.lokasi') }}" class="text-slate-600 hover:text-slate-900 text-sm">
                    ← Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="section-label">Parameter</div>
                    <select id="parameterSelect" class="calendar-input">
                        <option value="">-- Pilih Parameter --</option>
                        @foreach ($parameters as $param)
                            <option value="{{ $param['nama_parameter'] }}" data-unit="{{ $param['satuan'] ?? '' }}">
                                {{ $param['nama_parameter'] }}</option>
                        @endforeach
                    </select>

                    <div class="section-label">Analisa Dalam</div>
                    <div class="radio-options">
                        <label><input type="radio" name="range" value="day" checked> Hari</label>
                        <label><input type="radio" name="range" value="month"> Bulan</label>
                        <label><input type="radio" name="range" value="year"> Tahun</label>
                        <label><input type="radio" name="range" value="custom"> Rentang</label>
                    </div>

                    <div class="section-label">Tanggal</div>
                    <input type="date" id="dateInput" class="calendar-input" value="{{ date('Y-m-d') }}">

                    {{-- <button type="button" class="btn-primary" onclick="loadData()">Tampil Data</button> --}}
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <button type="button" class="btn-success" onclick="downloadExcel()">
                        📥 Download Excel
                    </button>

                    <button type="button" class="btn-outline" onclick="openDataMasukModal()">
                        📊 Data Masuk
                    </button>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="content-main">
            <div class="page-header">
                <div class="header-info">
                    <h2 class="text-lg font-bold">{{ $logger->nama_logger }}</h2>
                    <div
                        class="flex items-center gap-2 text-xs font-semibold {{ $status === 'online' ? 'text-emerald-600' : 'text-rose-600' }}">
                        <span
                            class="w-2 h-2 rounded-full {{ $status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        <span class="text-sm font-medium">
                            {{ $status === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus' }}
                        </span>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-header" onclick="openInfoPanel()">📊 Informasi</button>
                    <button class="btn-header" onclick="openDocModal()">📄 Dokumentasi</button>
                </div>
            </div>

            <div class="chart-section">
                <div class="chart-title" id="chartTitle">{{ date('F Y') }}</div>
                <div class="chart-wrapper">
                    <canvas id="dataChart" height="300"></canvas>
                </div>
            </div>

            <div class="data-table-section">
                <div class="table-title" id="tableTitle">{{ date('F Y') }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>WAKTU</th>
                            <th>RERATA</th>
                            <th>MINIMUM</th>
                            <th>MAKSIMUM</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #9ca3af;">
                                Pilih parameter dan klik Tampil Data
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        let chart = null;

        const loggerId = '{{ $logger->id_logger }}';

        // Initialize static chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            initChart(); // Init empty chart
            // initStaticTable(); // Removed static table
            updateChartTitle(); // Set initial title

            // Add event listeners for dynamic title updates
            document.getElementById('dateInput').addEventListener('change', () => {
                updateChartTitle();
                // Optional: Auto load on date change if param selected
                const param = document.getElementById('parameterSelect').value;
                if (param) loadData();
            });
            document.querySelectorAll('input[name="range"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    updateChartTitle();
                    const param = document.getElementById('parameterSelect').value;
                    if (param) loadData();
                });
            });

            document.getElementById('parameterSelect').addEventListener('change', () => {
                loadData();
            });
        });

        function updateChartTitle() {
            const dateInput = document.getElementById('dateInput').value;
            const range = document.querySelector('input[name="range"]:checked').value;
            const param = document.getElementById('parameterSelect').value;

            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            const date = new Date(dateInput);
            const day = date.getDate();
            const month = monthNames[date.getMonth()];
            const year = date.getFullYear();

            let titleText = `Rerata ${param} `;
            let tableTitleText = `Tabel Rerata ${param} `;

            if (range === 'day') {
                titleText += `Pada ${day} ${month} ${year}`;
                tableTitleText += `Pada ${day} ${month} ${year}`;
            } else if (range === 'month') {
                titleText += `Pada ${month} ${year}`;
                tableTitleText += `Pada ${month} ${year}`;
            } else if (range === 'year') {
                titleText += `Pada Tahun ${year}`;
                tableTitleText += `Pada Tahun ${year}`;
            } else if (range === 'custom') {
                titleText += `Rentang Custom`;
                tableTitleText += `Rentang Custom`;
            }

            document.getElementById('chartTitle').textContent = titleText;
            document.getElementById('tableTitle').textContent = tableTitleText;
        }

        function initChart() {
            const canvas = document.getElementById('dataChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                            label: 'Rerata',
                            data: [],
                            borderColor: '#1e40af',
                            backgroundColor: 'rgba(30, 64, 175, 0.1)',
                            tension: 0.4,
                            cubicInterpolationMode: 'monotone', // Spline effect
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 2
                        },
                        {
                            label: 'Minimum',
                            data: [],
                            borderColor: '#60a5fa',
                            backgroundColor: 'rgba(96,165,250,0.1)',
                            tension: 0.4,
                            cubicInterpolationMode: 'monotone', // Spline effect
                            fill: false,
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 0
                        },
                        {
                            label: 'Maksimum',
                            data: [],
                            borderColor: '#4338ca',
                            backgroundColor: 'rgba(67,56,202,0.1)',
                            tension: 0.4,
                            cubicInterpolationMode: 'monotone', // Spline effect
                            fill: true,
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        function loadData() {
            const selectedParam = document.getElementById('parameterSelect').value;

            if (!selectedParam) {
                // Alert handled by select change or initial state
                return;
            }

            const range = document.querySelector('input[name="range"]:checked').value;
            const date = document.getElementById('dateInput').value;

            // Show loading state
            const originalTitle = document.getElementById('chartTitle').textContent;
            document.getElementById('chartTitle').textContent = 'Memuat data...';

            fetch(`{{ route('analisa.data', ':id') }}`.replace(':id', loggerId) +
                    `?parameter=${selectedParam}&range=${range}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('chartTitle').textContent = originalTitle; // Restore title or update
                    updateChartTitle(); // update with correct param name

                    updateChart(data);
                    updateTable(data);
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                    alert('Gagal memuat data. Silakan coba lagi.');
                    document.getElementById('chartTitle').textContent = originalTitle;
                });
        }

        function parseLabelToMinutes(label) {
            const s = String(label ?? '').trim();
            if (!s) return null;

            let m = s.match(/(\d{1,2})[:.](\d{2})(?::\d{2})?/);
            if (m) return (Number(m[1]) * 60) + Number(m[2]);

            m = s.match(/\b(\d{1,2})\s*[:.]\s*00\b/);
            if (m) return Number(m[1]) * 60;

            m = s.match(/\b(\d{1,2})\b/);
            if (m && s.length <= 2) return Number(m[1]) * 60;

            return null;
        }

        function filterSeriesToNowIfToday(labels, ...series) {
            const el = document.getElementById('dateInput');
            const selectedDate = el ? el.value : '';
            const now = new Date();
            const today = now.toISOString().slice(0, 10);

            if (!selectedDate || selectedDate !== today) return {
                labels,
                series
            };

            const nowMin = (now.getHours() * 60) + now.getMinutes();

            let idx = -1;
            for (let i = 0; i < (labels || []).length; i++) {
                const t = parseLabelToMinutes(labels[i]);
                if (t === null) continue;
                if (t <= nowMin) idx = i;
            }

            if (idx < 0) idx = 0;

            return {
                labels: (labels || []).slice(0, idx + 1),
                series: series.map(arr => (arr || []).slice(0, idx + 1))
            };
        }

        function hasAnyRealValue(arr) {
            if (!Array.isArray(arr)) return false;
            for (const v of arr) {
                if (v !== null && v !== undefined && v !== '') return true;
            }
            return false;
        }

        function hasAnyDataPayload(data) {
            return hasAnyRealValue(data?.chartData) || hasAnyRealValue(data?.minData) || hasAnyRealValue(data?.maxData);
        }

        function getSelectedUnit() {
            const sel = document.getElementById('parameterSelect');
            if (!sel) return '';
            const opt = sel.options[sel.selectedIndex];
            const u = opt?.dataset?.unit || '';
            return u ? ` ${u}` : '';
        }

        function fmtWithUnit(v, unit) {
            if (v === null || v === undefined || v === '') return '-';
            return `${v}${unit}`;
        }

        function updateChart(data) {
            if (!chart) return;

            const labelsRaw = data.labels || [];
            const avgRaw = data.chartData || [];
            const minRaw = data.minData || [];
            const maxRaw = data.maxData || [];

            const range = document.querySelector('input[name="range"]:checked')?.value;

            if (range === 'day') {
                if (!hasAnyDataPayload(data)) {
                    chart.data.labels = [];
                    chart.data.datasets[0].data = [];
                    chart.data.datasets[1].data = [];
                    chart.data.datasets[2].data = [];
                    chart.update();
                    return;
                }

                const f = filterSeriesToNowIfToday(labelsRaw, avgRaw, minRaw, maxRaw);
                chart.data.labels = f.labels;
                chart.data.datasets[0].data = f.series[0];
                chart.data.datasets[1].data = f.series[1];
                chart.data.datasets[2].data = f.series[2];
                chart.update();
                return;
            }

            if (!hasAnyDataPayload(data)) {
                chart.data.labels = [];
                chart.data.datasets[0].data = [];
                chart.data.datasets[1].data = [];
                chart.data.datasets[2].data = [];
                chart.update();
                return;
            }

            chart.data.labels = labelsRaw;
            chart.data.datasets[0].data = avgRaw;
            chart.data.datasets[1].data = minRaw;
            chart.data.datasets[2].data = maxRaw;
            chart.update();
        }

        function updateTable(data) {
            const tbody = document.getElementById('dataTableBody');
            if (!tbody) return;

            const range = document.querySelector('input[name="range"]:checked')?.value;
            const rows = Array.isArray(data.tableData) ? data.tableData : [];
            const labelsRaw = Array.isArray(data.labels) ? data.labels : [];
            const unit = getSelectedUnit();

            const isAllEmpty = !hasAnyDataPayload(data);

            if (range === 'day') {
                if (isAllEmpty) {
                    tbody.innerHTML =
                        '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">Tidak ada data</td></tr>';
                    return;
                }

                const f = filterSeriesToNowIfToday(labelsRaw);
                const labels = f.labels || [];

                const map = new Map();
                for (const r of rows) {
                    if (r && r.waktu != null) map.set(String(r.waktu), r);
                }

                let html = '';
                for (const t of labels) {
                    const r = map.get(String(t)) || {};
                    html += `
                <tr>
                    <td>${t}</td>
                    <td>${fmtWithUnit(r.rerata, unit)}</td>
                    <td>${fmtWithUnit(r.minimum, unit)}</td>
                    <td>${fmtWithUnit(r.maksimum, unit)}</td>
                </tr>`;
                }

                tbody.innerHTML = html ||
                    '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">Tidak ada data</td></tr>';
                return;
            }

            const filtered = rows.filter(r => {
                if (!r) return false;
                const a = r.rerata;
                const b = r.minimum;
                const c = r.maksimum;
                return !((a == null || a === '') && (b == null || b === '') && (c == null || c === ''));
            });

            if (!filtered.length) {
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">Tidak ada data</td></tr>';
                return;
            }

            let html = '';
            for (const r of filtered) {
                html += `
            <tr>
                <td>${r.waktu ?? '-'}</td>
                <td>${fmtWithUnit(r.rerata, unit)}</td>
                <td>${fmtWithUnit(r.minimum, unit)}</td>
                <td>${fmtWithUnit(r.maksimum, unit)}</td>
            </tr>`;
            }
            tbody.innerHTML = html;
        }

        function downloadExcel() {
            const selectedParam = document.getElementById('parameterSelect').value;
            if (!selectedParam) {
                alert('Pilih parameter terlebih dahulu');
                return;
            }

            const range = document.querySelector('input[name="range"]:checked').value;
            const date = document.getElementById('dateInput').value;
            const url = `{{ route('analisa.export', ['id_logger' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', loggerId) +
                `?parameter=${selectedParam}&range=${range}&date=${date}`;

            window.location.href = url;
        }

        // Info Panel Functions
        function openInfoPanel() {
            document.getElementById('infoPanel').classList.add('show');
            document.getElementById('infoPanelOverlay').classList.add('show');
        }

        function closeInfoPanel() {
            document.getElementById('infoPanel').classList.remove('show');
            document.getElementById('infoPanelOverlay').classList.remove('show');
        }

        // Documentation Modal Functions
        function openDocModal() {
            document.getElementById('docModal').classList.add('show');
        }

        function closeDocModal(event) {
            if (event && event.target.id !== 'docModal') {
                return;
            }
            document.getElementById('docModal').classList.remove('show');
        }

        // Auto-refresh every hour
        setInterval(() => {
            const selectedParam = document.getElementById('parameterSelect').value;
            if (selectedParam) {
                loadData();
            }
        }, 3600000);
    </script>

    <script>
        let dataMasukChartInstance = null;

        function openDataMasukModal() {
            document.getElementById('dataMasukModal').classList.add('show');
            loadDataMasuk();
        }

        function closeDataMasukModal(event) {
            if (event && event.target.id !== 'dataMasukModal') return;
            document.getElementById('dataMasukModal').classList.remove('show');
        }

        async function loadDataMasuk() {
            const loading = document.getElementById('dataMasukLoading');
            loading.style.display = 'block';

            try {
                const url = `{{ route('analisa.dataMasuk', ':id') }}`.replace(':id', loggerId);
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    const txt = await res.text();
                    throw new Error(`HTTP ${res.status} - ${txt.slice(0, 200)}`);
                }

                const rows = await res.json();

                const labels = (rows || []).map(r => r.date);
                const counts = (rows || []).map(r => Number(r.count || 0));
                const percentages = (rows || []).map(r => Number(r.percentage || 0));

                loading.style.display = 'none';
                renderDataMasukChart(labels, counts, percentages);
            } catch (err) {
                loading.style.display = 'none';
                console.error('loadDataMasuk error:', err);
                alert('Gagal memuat data masuk');
            }
        }

        function renderDataMasukChart(labels, counts, percentages) {
            const canvas = document.getElementById('dataMasukChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            if (dataMasukChartInstance) {
                dataMasukChartInstance.destroy();
                dataMasukChartInstance = null;
            }

            const barColors = (percentages || []).map(p => (Number(p) < 90 ? '#dc2626' : '#4f46e5'));

            dataMasukChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Jumlah Data Masuk',
                        data: counts,
                        backgroundColor: barColors,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (c) => {
                                    const i = c.dataIndex;
                                    const count = counts[i] ?? 0;
                                    const pct = percentages[i] ?? 0;
                                    return `Data: ${count} (${pct}%)`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
