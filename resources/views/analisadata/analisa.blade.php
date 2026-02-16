@extends('layouts.app')
@section('title', $title)
@push('head')
    <style>
        .analysis-container {
            width: 100%;
            background: white;
            overflow: hidden;
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

        .range-input-group {
            margin-bottom: 0;
        }

        .calendar-input {
            width: 100%;
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

        .content-main {}

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
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

        .chart-section {
            margin-bottom: 0;
        }

        .chart-title {
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            background: white;
            padding: 0;
            border-radius: 8px;
        }

        #dataChart {
            display: block;
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
            background: #E6E6E6;
            color: rgb(0, 0, 0);
        }

        .info-panel-title {
            font-size: 18px;
            font-weight: 600;
        }

        .info-panel-close {
            background: none;
            border: none;
            color: rgb(0, 0, 0);
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
            padding: 14px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: rgb(0, 0, 0);
        }

        .doc-modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .doc-modal-close {
            background: none;
            border: none;
            color: rgb(0, 0, 0);
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
            gap: 14px;
        }

        .photo-main {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .photo-main img {
            width: 100%;
            height: min(58vh, 460px);
            object-fit: contain;
            display: block;
            background: #f8fafc;
        }

        .photo-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 9999px;

            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            line-height: 1;
        }

        .photo-nav.prev {
            left: 10px;
        }

        .photo-nav.next {
            right: 10px;
        }

        .photo-counter {
            position: absolute;
            right: 10px;
            bottom: 10px;
            background: rgba(15, 23, 42, 0.7);
            color: #fff;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 9999px;
        }

        .photo-thumbs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 10px;
        }

        .photo-thumb {
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 0;
            overflow: hidden;
            background: #fff;
            cursor: pointer;
        }

        .photo-thumb.active {
            border-color: #303481;
        }

        .photo-thumb img {
            width: 100%;
            height: 78px;
            object-fit: cover;
            display: block;
        }

        .no-photos {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
    </style>
@endpush
@section('content')
    <div class="info-panel-overlay" id="infoPanelOverlay" onclick="closeInfoPanel()"></div>
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
    <div class="doc-modal" id="docModal" onclick="closeDocModal(event)">
        <div class="doc-modal-content" onclick="event.stopPropagation()">
            <div class="doc-modal-header">
                <div class="doc-modal-title">Dokumentasi</div>
                <button class="doc-modal-close" onclick="closeDocModal()">×</button>
            </div>
            <div class="doc-modal-body">
                @if ($photos->count() > 0)
                    @php
                        $firstPhotoRaw = (string) ($photos->first()->url_foto ?? '');
                        $firstPhotoUrl =
                            str_starts_with($firstPhotoRaw, 'http://') || str_starts_with($firstPhotoRaw, 'https://')
                                ? $firstPhotoRaw
                                : asset(ltrim($firstPhotoRaw, '/'));
                    @endphp
                    <div class="photo-gallery">
                        <div class="photo-main">
                            <img id="docMainPhoto" src="{{ $firstPhotoUrl }}" alt="Dokumentasi">
                            <button type="button" class="photo-nav prev" onclick="prevDocPhoto(event)">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.91675 1.16675L4.08341 7.00008L9.91675 12.8334" stroke="#303481"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button" class="photo-nav next text-white" onclick="nextDocPhoto(event)">
                                <svg width="8" height="14" viewBox="0 0 8 14" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.75 0.75L6.58333 6.58333L0.75 12.4167" stroke="#303481" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div class="photo-counter" id="docPhotoCounter">1 / {{ $photos->count() }}</div>
                        </div>
                        <div class="photo-thumbs" id="docThumbs">
                            @foreach ($photos as $photo)
                                @php
                                    $photoRaw = (string) ($photo->url_foto ?? '');
                                    $photoUrl =
                                        str_starts_with($photoRaw, 'http://') || str_starts_with($photoRaw, 'https://')
                                            ? $photoRaw
                                            : asset(ltrim($photoRaw, '/'));
                                @endphp
                                <button type="button" class="photo-thumb {{ $loop->first ? 'active' : '' }}"
                                    data-src="{{ $photoUrl }}" onclick="setDocPhoto({{ $loop->index }})">
                                    <img src="{{ $photoUrl }}" alt="Dokumentasi {{ $loop->iteration }}">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="no-photos">
                        <p>Tidak ada foto dokumentasi untuk logger ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
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
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center mb-3">

            <button type="button"
                onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('peta.lokasi') }}'"
                class="inline-flex items-center justify-center" aria-label="Kembali">
                <svg width="8" height="20" viewBox="0 0 10 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.5 18.5L1 9.75L8.5 1" stroke="#303481" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            <span
                class="ms-6 w-3 h-3 rounded-full {{ $status === 'online' ? 'bg-green-500' : 'bg-red-500' }} me-4"></span>
            <div>
                <div class="text-lg font-bold mb-0 py-0 my-0">{{ $logger->nama_logger }}</div>
                <div
                    class="flex items-center gap-2 text-xs font-semibold {{ $status === 'online' ? 'text-emerald-600' : 'text-rose-600' }}">

                    <small class="text-sm font-medium">
                        {{ $status === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="flex">
            <button
                class="bg-[#303481] items-center rounded-lg flex px-4 text-white py-3 hover:bg-[#10134B] hover:font-semibold text-sm me-3"
                onclick="openInfoPanel()">
                <svg class="me-2" width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 18.3334C14.6024 18.3334 18.3334 14.6025 18.3334 10.0001C18.3334 5.39771 14.6024 1.66675 10 1.66675C5.39765 1.66675 1.66669 5.39771 1.66669 10.0001C1.66669 14.6025 5.39765 18.3334 10 18.3334Z"
                        stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10 13.3334V10.0001M10 6.66675H10.0083" stroke="white" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Informasi
            </button>
            <button
                class="bg-white border  items-center  border-[#303481] text-[#303481] rounded-lg flex px-4 py-3 hover:bg-[#eaebff] hover:font-semibold text-sm "
                onclick="openDocModal()">
                <svg width="20" height="18" viewBox="0 0 20 18" class="me-2" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.91667 13.5833C11.9417 13.5833 13.5833 11.9417 13.5833 9.91667C13.5833 7.89162 11.9417 6.25 9.91667 6.25C7.89162 6.25 6.25 7.89162 6.25 9.91667C6.25 11.9417 7.89162 13.5833 9.91667 13.5833Z"
                        stroke="#303481" stroke-width="1.5" />
                    <path
                        d="M7.87983 17.25H11.9535C14.8144 17.25 16.2453 17.25 17.2729 16.5763C17.7164 16.2858 18.0983 15.9108 18.3967 15.4726C19.0833 14.4643 19.0833 13.059 19.0833 10.2504C19.0833 7.44171 19.0833 6.03646 18.3967 5.02813C18.0983 4.58995 17.7164 4.21491 17.2729 3.92446C16.6129 3.49088 15.7861 3.33596 14.5202 3.28096C13.9161 3.28096 13.3963 2.83179 13.2781 2.24971C13.1877 1.82334 12.9529 1.44124 12.6134 1.168C12.2738 0.894753 11.8503 0.747117 11.4145 0.750043H8.41883C7.51317 0.750043 6.73308 1.37796 6.55525 2.24971C6.437 2.83179 5.91725 3.28096 5.31317 3.28096C4.04817 3.33596 3.22133 3.49179 2.56042 3.92446C2.11724 4.21501 1.73567 4.59004 1.4375 5.02813C0.75 6.03646 0.75 7.44079 0.75 10.2504C0.75 13.06 0.75 14.4634 1.43658 15.4726C1.73358 15.909 2.11492 16.2839 2.56042 16.5763C3.588 17.25 5.01892 17.25 7.87983 17.25Z"
                        stroke="#303481" stroke-width="1.5" />
                    <path d="M16.3333 7.16675H15.4166" stroke="#303481" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                Dokumentasi</button>
        </div>
    </div>
    <div class="analysis-container grid grid-cols-12 gap-3">
        <div class="xl:col-span-3 2xl:col-span-2">
            <div class="border rounded-lg px-4 py-3">
                <div class="grid grid-cols-1 gap-3">
                    <div class="">
                        <div class="text-md font-semibold mb-2 ">Parameter</div>
                        <select id="parameterSelect"
                            class="calendar-input text-sm py-2 rounded-lg border border-slate-300 rounded-lg">
                            <option value="">Pilih Parameter</option>
                            @foreach ($parameters as $param)
                                <option value="{{ $param['nama_parameter'] }}" data-unit="{{ $param['satuan'] ?? '' }}">
                                    {{ str_replace('_', ' ', $param['nama_parameter']) }}</option>
                            @endforeach
                        </select>
                        <div class="text-md font-semibold mb-2 mt-3">Analisa Dalam</div>
                        <div class="radio-options">
                            <label><input type="radio" name="range" value="day" checked> Hari</label>
                            <label><input type="radio" name="range" value="month"> Bulan</label>
                            <label><input type="radio" name="range" value="year"> Tahun</label>
                            <label><input type="radio" name="range" value="custom"> Rentang</label>
                        </div>
                        <div id="rangeDay" class="range-input-group rounded-lg" style="display: block;">
                            <div class="text-md font-semibold mb-2">Tanggal</div>
                            <input type="date" id="dateInput"
                                class="calendar-input text-sm py-2 rounded-lg border border-slate-300"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div id="rangeMonth" class="range-input-group" style="display: none;">
                            <div class="text-md font-semibold mb-2">Bulan-Tahun</div>
                            <input type="month" id="monthInput"
                                class="calendar-input text-sm py-2 rounded-lg border border-slate-300"
                                value="{{ date('Y-m') }}">
                        </div>
                        <div id="rangeYear" class="range-input-group" style="display: none;">
                            <div class="text-md font-semibold mb-2">Tahun</div>
                            <input type="number" id="yearInput"
                                class="calendar-input text-sm py-2 rounded-lg border border-slate-300" min="2000"
                                max="2100" value="{{ date('Y') }}">
                        </div>
                        <div id="rangeCustom" class="range-input-group" style="display: none;">
                            <div class="text-md font-semibold mb-2">Tanggal Mulai</div>
                            <input type="datetime-local" id="startDateTime"
                                class="calendar-input text-sm py-2 rounded-lg border border-slate-300"
                                value="{{ date('Y-m-d\TH:i') }}">
                            <div class="text-md font-semibold mb-2" style="margin-top: 12px;">Tanggal Akhir</div>
                            <input type="datetime-local" id="endDateTime"
                                class="calendar-input text-sm py-2 rounded-lg border border-slate-300"
                                value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn-success" onclick="downloadExcel()">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1023_3171)">
                                    <path
                                        d="M9.33333 0.666748V3.92601C9.33333 4.03301 9.35441 4.13897 9.39536 4.23782C9.4363 4.33668 9.49632 4.42651 9.57199 4.50217C9.64765 4.57783 9.73747 4.63785 9.83633 4.6788C9.93519 4.71975 10.0411 4.74082 10.1481 4.74082H13.4074"
                                        stroke="#06C022" stroke-width="1.2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M11.7778 15.3334H3.62963C3.19743 15.3334 2.78292 15.1617 2.47731 14.8561C2.17169 14.5505 2 14.136 2 13.7038V2.29638C2 1.86417 2.17169 1.44967 2.47731 1.14406C2.78292 0.838441 3.19743 0.666748 3.62963 0.666748H9.33333L13.4074 4.74082V13.7038C13.4074 14.136 13.2357 14.5505 12.9301 14.8561C12.6245 15.1617 12.21 15.3334 11.7778 15.3334Z"
                                        stroke="#06C022" stroke-width="1.2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M4.44444 7.18506H10.963V12.8888H4.44444V7.18506Z" stroke="#06C022"
                                        stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M4.44444 10.4443H10.963" stroke="#06C022" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M6.88889 7.18506V12.8888" stroke="#06C022" stroke-width="1.2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1023_3171">
                                        <rect width="16" height="16" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg> Download Excel
                        </button>
                        <button type="button" class="btn-outline" onclick="openDataMasukModal()">

                            <svg width="16" height="16" viewBox="0 0 16 16" class="me-2" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1023_3203)">
                                    <path d="M1.71429 14.2858L14.2857 1.71436" stroke="#303481" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path
                                        d="M13.2381 15.3333C14.3953 15.3333 15.3333 14.3952 15.3333 13.2381C15.3333 12.0809 14.3953 11.1428 13.2381 11.1428C12.0809 11.1428 11.1429 12.0809 11.1429 13.2381C11.1429 14.3952 12.0809 15.3333 13.2381 15.3333Z"
                                        fill="#303481" stroke="#303481" stroke-width="1.2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M2.76191 4.85722C3.91907 4.85722 4.85714 3.91915 4.85714 2.76199C4.85714 1.60482 3.91907 0.666748 2.76191 0.666748C1.60474 0.666748 0.666668 1.60482 0.666668 2.76199C0.666668 3.91915 1.60474 4.85722 2.76191 4.85722Z"
                                        fill="#303481" stroke="#303481" stroke-width="1.2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1023_3203">
                                        <rect width="16" height="16" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                            Data Masuk
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="xl:col-span-9 2xl:col-span-10">
            <div class="border rounded-lg ">
                <div class="chart-section ps-3 pe-3 pt-3 pb-0 mb-0">
                    <div class="chart-title text-lg font-semibold" id="chartTitle">{{ date('F Y') }}</div>
                    <div class="chart-wrapper mb-3 mt-2">
                        <canvas id="dataChart" height="400"></canvas>
                    </div>
                </div>
                <div class="data-table-section ps-3 pe-3">
                    <div class="table-title" id="tableTitle">{{ date('F Y') }}</div>
                    <div class=" w-full overflow-hidden rounded-lg border  border-slate-300 mb-3">
                        <table class="data-table ">
                            <thead class="bg-neutral-300 text-neutral-950 font-semibold uppercase text-xs">
                                <tr>
                                    <th>WAKTU</th>
                                    <th>RERATA</th>
                                    <th>MINIMUM</th>
                                    <th>MAKSIMUM</th>
                                </tr>
                            </thead>
                            <tbody id="dataTableBody" class="text-sm">
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
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        let chart = null;
        const loggerId = '{{ $logger->id_logger }}';
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            updateChartTitle();
            setupRangeInputs();
            const urlParams = new URLSearchParams(window.location.search);
            const paramFromUrl = urlParams.get('parameter');
            if (paramFromUrl) {
                const paramSelect = document.getElementById('parameterSelect');
                if (paramSelect) {
                    paramSelect.value = paramFromUrl;
                    loadData();
                }
            }
            document.getElementById('dateInput').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param) loadData();
            });
            document.getElementById('monthInput').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param) loadData();
            });
            document.getElementById('yearInput').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param) loadData();
            });
            document.getElementById('startDateTime').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param) loadData();
            });
            document.getElementById('endDateTime').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param) loadData();
            });
            document.querySelectorAll('input[name="range"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    toggleRangeInputs(radio.value);
                    updateChartTitle();
                    const param = document.getElementById('parameterSelect').value;
                    if (param) loadData();
                });
            });
            document.getElementById('parameterSelect').addEventListener('change', () => {
                loadData();
            });
        });

        function setupRangeInputs() {
            toggleRangeInputs('day');
        }

        function toggleRangeInputs(rangeType) {
            document.getElementById('rangeDay').style.display = 'none';
            document.getElementById('rangeMonth').style.display = 'none';
            document.getElementById('rangeYear').style.display = 'none';
            document.getElementById('rangeCustom').style.display = 'none';
            if (rangeType === 'day') {
                document.getElementById('rangeDay').style.display = 'block';
            } else if (rangeType === 'month') {
                document.getElementById('rangeMonth').style.display = 'block';
            } else if (rangeType === 'year') {
                document.getElementById('rangeYear').style.display = 'block';
            } else if (rangeType === 'custom') {
                document.getElementById('rangeCustom').style.display = 'block';
            }
        }

        function updateChartTitle() {
            const range = document.querySelector('input[name="range"]:checked').value;
            const param = document.getElementById('parameterSelect').value;
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            let titleText = `Rerata ${param} `;
            let tableTitleText = `Tabel Rerata ${param} `;
            if (range === 'day') {
                const dateInput = document.getElementById('dateInput').value;
                const date = new Date(dateInput);
                const day = date.getDate();
                const month = monthNames[date.getMonth()];
                const year = date.getFullYear();
                titleText += `Pada ${day} ${month} ${year}`;
                tableTitleText += `Pada ${day} ${month} ${year}`;
            } else if (range === 'month') {
                const monthInput = document.getElementById('monthInput').value;
                const [year, month] = monthInput.split('-');
                const monthName = monthNames[parseInt(month) - 1];
                titleText += `Pada ${monthName} ${year}`;
                tableTitleText += `Pada ${monthName} ${year}`;
            } else if (range === 'year') {
                const yearInput = document.getElementById('yearInput').value;
                titleText += `Pada Tahun ${yearInput}`;
                tableTitleText += `Pada Tahun ${yearInput}`;
            } else if (range === 'custom') {
                const startDateTime = document.getElementById('startDateTime').value;
                const endDateTime = document.getElementById('endDateTime').value;
                titleText += `Dari ${startDateTime} hingga ${endDateTime}`;
                tableTitleText += `Dari ${startDateTime} hingga ${endDateTime}`;
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
                            cubicInterpolationMode: 'monotone',
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
                            cubicInterpolationMode: 'monotone',
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
                            cubicInterpolationMode: 'monotone',
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
                return;
            }
            const range = document.querySelector('input[name="range"]:checked').value;
            let date = '';
            if (range === 'day') {
                date = document.getElementById('dateInput').value;
            } else if (range === 'month') {
                date = document.getElementById('monthInput').value;
            } else if (range === 'year') {
                date = document.getElementById('yearInput').value;
            } else if (range === 'custom') {
                const startDateTime = document.getElementById('startDateTime').value;
                const endDateTime = document.getElementById('endDateTime').value;
                date = `${startDateTime},${endDateTime}`;
            }
            const originalTitle = document.getElementById('chartTitle').textContent;
            document.getElementById('chartTitle').textContent = 'Memuat data...';
            fetch(`{{ route('analisa.data', ':id') }}`.replace(':id', loggerId) +
                    `?parameter=${selectedParam}&range=${range}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('chartTitle').textContent = originalTitle;
                    updateChartTitle();
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

        function isToday(dateStr) {
            const now = new Date();
            const today = now.toISOString().slice(0, 10);
            return dateStr === today;
        }

        function isCurrentMonth(monthStr) {
            const now = new Date();
            const currentMonth = now.toISOString().slice(0, 7);
            return monthStr === currentMonth;
        }

        function isCurrentYear(yearStr) {
            const now = new Date();
            const currentYear = String(now.getFullYear());
            return yearStr === currentYear;
        }

        function filterSeriesToNow(labels, range, ...series) {
            const now = new Date();
            const nowMin = (now.getHours() * 60) + now.getMinutes();
            const today = now.toISOString().slice(0, 10);
            const currentMonth = now.toISOString().slice(0, 7);
            const currentYear = String(now.getFullYear());
            let idx = -1;
            if (range === 'day') {
                const dateInput = document.getElementById('dateInput')?.value || '';
                if (!isToday(dateInput)) {
                    return {
                        labels,
                        series
                    };
                }
                for (let i = 0; i < (labels || []).length; i++) {
                    const t = parseLabelToMinutes(labels[i]);
                    if (t === null) continue;
                    if (t <= nowMin) idx = i;
                }
            } else if (range === 'month') {
                const monthInput = document.getElementById('monthInput')?.value || '';
                if (!isCurrentMonth(monthInput)) {
                    return {
                        labels,
                        series
                    };
                }
                const currentDay = now.getDate();
                for (let i = 0; i < (labels || []).length; i++) {
                    const dayStr = String(labels[i] || '').trim();
                    const day = parseInt(dayStr);
                    if (!isNaN(day) && day <= currentDay) {
                        idx = i;
                    }
                }
            } else if (range === 'year') {
                const yearInput = document.getElementById('yearInput')?.value || '';
                if (!isCurrentYear(yearInput)) {
                    return {
                        labels: [],
                        series: series.map(() => [])
                    };
                }
                const monthNames = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                const currentMonthNum = now.getMonth();
                for (let i = 0; i < (labels || []).length; i++) {
                    const labelLower = String(labels[i] || '').toLowerCase().trim();
                    const monthIdx = monthNames.findIndex(mn => labelLower.includes(mn) || labelLower.startsWith(mn
                        .substring(0, 3)));
                    if (monthIdx !== -1 && monthIdx <= currentMonthNum) {
                        idx = i;
                    }
                }
            } else if (range === 'custom') {
                return {
                    labels,
                    series
                };
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
            if (!hasAnyDataPayload(data)) {
                chart.data.labels = [];
                chart.data.datasets[0].data = [];
                chart.data.datasets[1].data = [];
                chart.data.datasets[2].data = [];
                chart.update();
                return;
            }
            if (range === 'custom') {
                chart.data.labels = labelsRaw;
                chart.data.datasets[0].data = avgRaw;
                chart.data.datasets[1].data = minRaw;
                chart.data.datasets[2].data = maxRaw;
                chart.update();
                return;
            }
            const f = filterSeriesToNow(labelsRaw, range, avgRaw, minRaw, maxRaw);
            chart.data.labels = f.labels;
            chart.data.datasets[0].data = f.series[0];
            chart.data.datasets[1].data = f.series[1];
            chart.data.datasets[2].data = f.series[2];
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
            if (isAllEmpty) {
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">Tidak ada data</td></tr>';
                return;
            }
            if (range === 'custom' || range === 'month') {
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
                return;
            }
            const f = filterSeriesToNow(labelsRaw, range);
            const labelsFiltered = f.labels || [];
            const labelSet = new Set(labelsFiltered);
            const filtered = rows.filter(r => {
                if (!r || r.waktu == null) return false;
                if (!labelSet.has(String(r.waktu))) return false;
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
            let date = '';
            if (range === 'day') {
                date = document.getElementById('dateInput').value;
            } else if (range === 'month') {
                date = document.getElementById('monthInput').value;
            } else if (range === 'year') {
                date = document.getElementById('yearInput').value;
            } else if (range === 'custom') {
                const startDateTime = document.getElementById('startDateTime').value;
                const endDateTime = document.getElementById('endDateTime').value;
                date = `${startDateTime},${endDateTime}`;
            }
            const url = `{{ route('analisa.export', ['id_logger' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', loggerId) +
                `?parameter=${selectedParam}&range=${range}&date=${date}`;
            window.location.href = url;
        }

        function openInfoPanel() {
            document.getElementById('infoPanel').classList.add('show');
            document.getElementById('infoPanelOverlay').classList.add('show');
        }

        function closeInfoPanel() {
            document.getElementById('infoPanel').classList.remove('show');
            document.getElementById('infoPanelOverlay').classList.remove('show');
        }

        let docPhotoUrls = [];
        let docPhotoIndex = 0;

        function updateDocPhoto(index) {
            if (!docPhotoUrls.length) return;

            docPhotoIndex = (index + docPhotoUrls.length) % docPhotoUrls.length;

            const mainPhoto = document.getElementById('docMainPhoto');
            if (mainPhoto) {
                mainPhoto.src = docPhotoUrls[docPhotoIndex];
            }

            const counter = document.getElementById('docPhotoCounter');
            if (counter) {
                counter.textContent = `${docPhotoIndex + 1} / ${docPhotoUrls.length}`;
            }

            document.querySelectorAll('#docThumbs .photo-thumb').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === docPhotoIndex);
            });
        }

        function initDocGallery() {
            const thumbs = Array.from(document.querySelectorAll('#docThumbs .photo-thumb'));
            docPhotoUrls = thumbs.map(t => t.dataset.src).filter(Boolean);
            if (!docPhotoUrls.length) return;
            updateDocPhoto(docPhotoIndex);
        }

        function setDocPhoto(index) {
            updateDocPhoto(Number(index) || 0);
        }

        function prevDocPhoto(event) {
            event?.stopPropagation();
            updateDocPhoto(docPhotoIndex - 1);
        }

        function nextDocPhoto(event) {
            event?.stopPropagation();
            updateDocPhoto(docPhotoIndex + 1);
        }

        function openDocModal() {
            document.getElementById('docModal').classList.add('show');
            docPhotoIndex = 0;
            initDocGallery();
        }

        function closeDocModal(event) {
            if (event && event.target.id !== 'docModal') {
                return;
            }
            document.getElementById('docModal').classList.remove('show');
        }
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
            const barColors = (percentages || []).map(p => (Number(p) < 80 ? '#FED0D0' : '#D0EFFE'));
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
