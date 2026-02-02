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
                            <option value="{{ $param['nama_parameter'] }}">{{ $param['nama_parameter'] }}</option>
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

                    <button class="btn-primary" onclick="loadData()">Tampil Data</button>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <button class="btn-success" onclick="downloadExcel()">
                        📥 Download Excel
                    </button>

                    <button class="btn-outline" onclick="alert('Data Masuk')">
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
                    <span class="status-badge">
                        <span
                            style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; display: inline-block;"></span>
                        Koneksi Terhubung
                    </span>
                </div>
                <div class="header-actions">
                    <button class="btn-header" onclick="openInfoPanel()">📊 Informasi</button>
                    <button class="btn-header" onclick="openDocModal()">📄 Dokumentasi</button>
                </div>
            </div>

            <div class="chart-section">
                <div class="chart-title" id="chartTitle">Rerata Muka Air Tanah Pada {{ date('F Y') }}</div>
                <div class="chart-wrapper">
                    {{-- <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-dot" style="background: #1e40af;"></span>
                            <span>Rerata: <strong id="legendRerata">-</strong></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background: #3b82f6;"></span>
                            <span>Maksimum: <strong id="legendMax">-</strong></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background: #93c5fd;"></span>
                            <span>Minimum: <strong id="legendMin">-</strong></span>
                        </div>
                    </div> --}}
                    <canvas id="dataChart" height="300"></canvas>
                </div>
            </div>

            <div class="data-table-section">
                <div class="table-title" id="tableTitle">Tabel Rerata Muka Air Tanah Pada {{ date('F Y') }}</div>
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
    <script>
        let chart = null;
        const loggerId = '{{ $logger->id_logger }}';

        // Initialize static chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            initStaticChart();
            initStaticTable();
            updateChartTitle(); // Set initial title
            
            // Add event listeners for dynamic title updates
            document.getElementById('dateInput').addEventListener('change', updateChartTitle);
            document.querySelectorAll('input[name="range"]').forEach(radio => {
                radio.addEventListener('change', updateChartTitle);
            });
        });

        function updateChartTitle() {
            const dateInput = document.getElementById('dateInput').value;
            const range = document.querySelector('input[name="range"]:checked').value;
            
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const date = new Date(dateInput);
            const day = date.getDate();
            const month = monthNames[date.getMonth()];
            const year = date.getFullYear();
            
            let titleText = 'Rerata Muka Air Tanah ';
            let tableTitleText = 'Tabel Rerata Muka Air Tanah ';
            
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

        function initStaticTable() {
            // Static data matching chart
            const staticData = [
                { hour: '00:00', rerata: 6.92, minimum: 6.87, maksimum: 6.97 },
                { hour: '01:00', rerata: 6.91, minimum: 6.86, maksimum: 6.96 },
                { hour: '02:00', rerata: 6.90, minimum: 6.85, maksimum: 6.95 },
                { hour: '03:00', rerata: 6.89, minimum: 6.84, maksimum: 6.94 },
                { hour: '04:00', rerata: 6.88, minimum: 6.83, maksimum: 6.93 },
                { hour: '05:00', rerata: 6.87, minimum: 6.82, maksimum: 6.92 },
                { hour: '06:00', rerata: 6.89, minimum: 6.84, maksimum: 6.94 },
                { hour: '07:00', rerata: 6.92, minimum: 6.87, maksimum: 6.97 },
                { hour: '08:00', rerata: 6.94, minimum: 6.89, maksimum: 6.99 },
                { hour: '09:00', rerata: 6.96, minimum: 6.91, maksimum: 7.01 },
                { hour: '10:00', rerata: 6.97, minimum: 6.92, maksimum: 7.02 },
                { hour: '11:00', rerata: 6.95, minimum: 6.90, maksimum: 7.00 },
                { hour: '12:00', rerata: 6.94, minimum: 6.89, maksimum: 6.99 },
                { hour: '13:00', rerata: 6.93, minimum: 6.88, maksimum: 6.98 },
                { hour: '14:00', rerata: 6.92, minimum: 6.87, maksimum: 6.97 },
                { hour: '15:00', rerata: 6.91, minimum: 6.86, maksimum: 6.96 },
                { hour: '16:00', rerata: 6.93, minimum: 6.88, maksimum: 6.98 },
                { hour: '17:00', rerata: 6.95, minimum: 6.90, maksimum: 7.00 },
                { hour: '18:00', rerata: 6.96, minimum: 6.91, maksimum: 7.01 },
                { hour: '19:00', rerata: 6.94, minimum: 6.89, maksimum: 6.99 },
                { hour: '20:00', rerata: 6.93, minimum: 6.88, maksimum: 6.98 },
                { hour: '21:00', rerata: 6.92, minimum: 6.87, maksimum: 6.97 },
                { hour: '22:00', rerata: 6.91, minimum: 6.86, maksimum: 6.96 },
                { hour: '23:00', rerata: 6.90, minimum: 6.85, maksimum: 6.95 }
            ];

            const tbody = document.getElementById('dataTableBody');
            let html = '';
            
            staticData.forEach(row => {
                html += `
                    <tr>
                        <td>${row.hour}</td>
                        <td>${row.rerata.toFixed(2)} m</td>
                        <td>${row.minimum.toFixed(2)} m</td>
                        <td>${row.maksimum.toFixed(2)} m</td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        function initStaticChart() {
            const ctx = document.getElementById('dataChart').getContext('2d');

            // Static data for demonstration
            const staticLabels = ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
            const staticData = [6.92, 6.91, 6.90, 6.89, 6.88, 6.87, 6.89, 6.92, 6.94, 6.96, 6.97, 6.95, 6.94, 6.93, 6.92, 6.91, 6.93, 6.95, 6.96, 6.94, 6.93, 6.92, 6.91, 6.90];

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: staticLabels,
                    datasets: [
                        {
                            label: 'Minimum',
                            data: staticData.map(v => v - 0.05),
                            borderColor: '#93c5fd',
                            backgroundColor: 'rgba(147, 197, 253, 0.2)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4
                        },
                        {
                            label: 'Rerata',
                            data: staticData,
                            borderColor: '#1e40af',
                            backgroundColor: 'rgba(30, 64, 175, 0.1)',
                            tension: 0.4,
                            fill: false,
                            borderWidth: 3,
                            pointRadius: 0,
                            pointHoverRadius: 4
                        },
                        {
                            label: 'Maksimum',
                            data: staticData.map(v => v + 0.05),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#111827',
                            bodyColor: '#4b5563',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' m';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: '#e5e7eb',
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2) + ' m';
                                },
                                font: {
                                    size: 11
                                },
                                color: '#6b7280'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                color: '#6b7280'
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });
        }

        function loadData() {
            const selectedParam = document.getElementById('parameterSelect').value;

            if (!selectedParam) {
                alert('Pilih parameter terlebih dahulu');
                return;
            }

            const range = document.querySelector('input[name="range"]:checked').value;
            const date = document.getElementById('dateInput').value;

            // Show loading state
            document.getElementById('chartTitle').textContent = 'Memuat data...';
            document.getElementById('tableTitle').textContent = 'Memuat data...';

            fetch(`/api/peta/data/${loggerId}?parameter=${selectedParam}&range=${range}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    updateChart(data, selectedParam, date);
                    updateTable(data, selectedParam, date);
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                    alert('Gagal memuat data. Silakan coba lagi.');
                    // Restore static data on error
                    initStaticChart();
                    initStaticTable();
                    updateChartTitle();
                });
        }

        function updateChart(data, parameter, date) {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const dateObj = new Date(date);
            const day = dateObj.getDate();
            const month = monthNames[dateObj.getMonth()];
            const year = dateObj.getFullYear();
            
            const range = document.querySelector('input[name="range"]:checked').value;

            // Update titles
            let titleText = `Rerata ${parameter} `;
            if (range === 'day') {
                titleText += `Pada ${day} ${month} ${year}`;
            } else if (range === 'month') {
                titleText += `Pada ${month} ${year}`;
            } else if (range === 'year') {
                titleText += `Pada Tahun ${year}`;
            }

            document.getElementById('chartTitle').textContent = titleText;
            document.getElementById('tableTitle').textContent = 'Tabel ' + titleText;

            // Update chart data
            if (chart) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.data.map(v => v ? v - 0.05 : null);
                chart.data.datasets[1].data = data.data;
                chart.data.datasets[2].data = data.data.map(v => v ? v + 0.05 : null);
                chart.update();
            } else {
                // If chart doesn't exist, create it
                initStaticChart();
            }
        }

        function updateTable(data, parameter, date) {
            const tbody = document.getElementById('dataTableBody');

            if (!data.tableData || data.tableData.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align: center; padding: 40px; color: #9ca3af;">Tidak ada data</td></tr>';
                return;
            }

            let html = '';
            data.tableData.forEach(row => {
                const val = parseFloat(row.value);
                html += `
                    <tr>
                        <td>${row.waktu}</td>
                        <td>${val.toFixed(2)} m</td>
                        <td>${(val - 0.05).toFixed(2)} m</td>
                        <td>${(val + 0.05).toFixed(2)} m</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function downloadExcel() {
            alert('Export to Excel functionality coming soon!');
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
            // If event is provided and target is not the modal background, don't close
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
@endpush
