@extends('layouts.app')
@section('title', $title)
@push('head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        [x-cloak] { display: none !important; }
        /* ── Select2 Tailwind-style override ── */
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            height: 36px;
            padding: 0 8px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            transition: border-color .15s, box-shadow .15s;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            top: 0;
            right: 6px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding: 0 4px;
            color: #374151;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 4px rgba(147, 197, 253, .4);
            outline: none;
        }

        .select2-dropdown {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .10);
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
            box-shadow: 0 0 0 3px rgba(147, 197, 253, .4);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #303481;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e9eafb;
            color: #303481;
            font-weight: 600;
        }
    </style>
    <style>
        .analysis-container {
            width: 100%;
            background: white;
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
            right: -100vw;
            width: 100vw;
            max-width: 400px;
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
    <div class="info-panel rounded-lg" id="infoPanel">
        <div class="info-panel-header">
            <div class="info-panel-title">Informasi Logger</div>
            <button class="info-panel-close" onclick="closeInfoPanel()">×</button>
        </div>
        <div class="info-panel-body">
            <div class="info-item mb-2 pb-1">
                <div class="info-label">ID Logger</div>
                <div class="info-value">{{ $logger->id_logger }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
                <div class="info-label">Nama Logger</div>
                <div class="info-value">{{ $logger->nama_logger }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
                <div class="info-label">Seri Logger</div>
                <div class="info-value">{{ $logger->informasi->seri_logger ?? '-' }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
                <div class="info-label">Sensor</div>
                <div class="info-value">{{ $logger->sensor_count ?? '-' }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
                <div class="info-label">Serial Number</div>
                <div class="info-value">{{ $logger->informasi->serial_number ?? '-' }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
                <div class="info-label">No. Seluler</div>
                <div class="info-value">{{ $logger->no_seluler ?? '-' }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
                <div class="info-label">Nama Penjaga</div>
                <div class="info-value">{{ $logger->informasi->nama_pic ?? '-' }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
                <div class="info-label">Nomor Penjaga</div>
                <div class="info-value">{{ $logger->informasi->no_pic ?? '-' }}</div>
            </div>
            <div class="info-item mb-2 pb-1">
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
    <div x-data="{ filterOpen: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-2">
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.location.href='{{ route('peta.lokasi') }}'"
                class="inline-flex items-center justify-center flex-shrink-0" aria-label="Kembali">
                <svg width="8" height="20" viewBox="0 0 10 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.5 18.5L1 9.75L8.5 1" stroke="#303481" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            <span
                class="w-3 h-3 rounded-full flex-shrink-0 {{ $status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></span>
            <div class="min-w-0">
                <div class="text-base sm:text-lg font-bold truncate">{{ $logger->nama_logger }}</div>
                <div class="flex items-center gap-2 text-xs font-semibold {{ $status === 'online' ? 'text-emerald-600' : 'text-rose-600' }}">
                    <small class="text-xs sm:text-sm font-medium">
                        {{ $status === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="flex gap-2 flex-shrink-0">
            {{-- Tombol Filter: hanya muncul di mobile --}}
            <button @click="filterOpen = !filterOpen"
                :class="filterOpen ? 'bg-[#303481] text-white border-[#303481]' : 'bg-slate-100 text-slate-700 border-slate-300'"
                class="md:hidden border items-center rounded-lg flex px-3 py-2 text-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                <span x-text="filterOpen ? 'Filter' : 'Filter'"></span>
            </button>
            <button
                class="bg-[#303481] items-center rounded-lg flex px-3 sm:px-4 text-white py-2 sm:py-3 hover:bg-[#10134B] text-sm"
                onclick="openInfoPanel()">
                <svg class="sm:me-2" width="18" height="18" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 18.3334C14.6024 18.3334 18.3334 14.6025 18.3334 10.0001C18.3334 5.39771 14.6024 1.66675 10 1.66675C5.39765 1.66675 1.66669 5.39771 1.66669 10.0001C1.66669 14.6025 5.39765 18.3334 10 18.3334Z"
                        stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10 13.3334V10.0001M10 6.66675H10.0083" stroke="white" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="hidden sm:inline">Informasi</span>
            </button>
            <button
                class="bg-white border items-center border-[#303481] text-[#303481] rounded-lg flex px-3 sm:px-4 py-2 sm:py-3 hover:bg-[#eaebff] text-sm"
                onclick="openDocModal()">
                <svg width="18" height="16" viewBox="0 0 20 18" class="sm:me-2" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.91667 13.5833C11.9417 13.5833 13.5833 11.9417 13.5833 9.91667C13.5833 7.89162 11.9417 6.25 9.91667 6.25C7.89162 6.25 6.25 7.89162 6.25 9.91667C6.25 11.9417 7.89162 13.5833 9.91667 13.5833Z"
                        stroke="#303481" stroke-width="1.5" />
                    <path
                        d="M7.87983 17.25H11.9535C14.8144 17.25 16.2453 17.25 17.2729 16.5763C17.7164 16.2858 18.0983 15.9108 18.3967 15.4726C19.0833 14.4643 19.0833 13.059 19.0833 10.2504C19.0833 7.44171 19.0833 6.03646 18.3967 5.02813C18.0983 4.58995 17.7164 4.21491 17.2729 3.92446C16.6129 3.49088 15.7861 3.33596 14.5202 3.28096C13.9161 3.28096 13.3963 2.83179 13.2781 2.24971C13.1877 1.82334 12.9529 1.44124 12.6134 1.168C12.2738 0.894753 11.8503 0.747117 11.4145 0.750043H8.41883C7.51317 0.750043 6.73308 1.37796 6.55525 2.24971C6.437 2.83179 5.91725 3.28096 5.31317 3.28096C4.04817 3.33596 3.22133 3.49179 2.56042 3.92446C2.11724 4.21501 1.73567 4.59004 1.4375 5.02813C0.75 6.03646 0.75 7.44079 0.75 10.2504C0.75 13.06 0.75 14.4634 1.43658 15.4726C1.73358 15.909 2.11492 16.2839 2.56042 16.5763C3.588 17.25 5.01892 17.25 7.87983 17.25Z"
                        stroke="#303481" stroke-width="1.5" />
                    <path d="M16.3333 7.16675H15.4166" stroke="#303481" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="hidden sm:inline">Dokumentasi</span>
            </button>
        </div>
    </div>
    <div class="analysis-container grid grid-cols-1 md:grid-cols-12 gap-3">

        {{-- Filter Panel: hidden di mobile hingga diklik, selalu tampil di md+ --}}
        <div :class="filterOpen ? 'block' : 'hidden md:block'"
            class="col-span-1 md:col-span-5 xl:col-span-3 2xl:col-span-2">
            <div class="border rounded-lg px-4 py-3">

                <div class="grid grid-cols-1 gap-3">
                    <div class="">
                        <div class="text-md font-semibold mb-2 ">Pilih Logger</div>
                        <select id="loggerSelect" class="calendar-input text-sm py-2 border border-slate-300 rounded-lg">
                            @foreach ($allLoggers as $l)
                                <option value="{{ $l->id_logger }}"
                                    {{ $logger->id_logger == $l->id_logger ? 'selected' : '' }}>
                                    {{ $l->id_logger }} - {{ $l->nama_logger ?? 'Logger' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-md font-semibold mb-2 mt-3">Parameter</div>
                        <select id="parameterSelect"
                            class="calendar-input text-sm py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-700">
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
                        <div id="rangeDay" class="range-input-group rounded-lg" style="display:block;">
                            <div class="text-md font-semibold mb-2">Tanggal</div>

                            <div class="relative w-full" id="dpWrap">
                                <input type="text" id="dateInput"
                                    class="calendar-input text-sm py-2 rounded-lg pr-10 border border-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-700"
                                    value="{{ date('Y-m-d') }}" placeholder="YYYY-MM-DD" autocomplete="off" />

                                <button type="button" id="dpBtn"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </button>

                                <div id="dpPanel"
                                    class="fixed w-[320px] rounded-xl border border-slate-200 bg-white shadow-lg p-3 z-[9999] hidden">
                                    <div class="flex items-center justify-between gap-2">
                                        <button type="button" id="dpPrev"
                                            class="h-8 w-8 rounded-lg border border-slate-50 hover:bg-slate-50 flex items-center justify-center">
                                            <span class="text-slate-600">‹</span>
                                        </button>

                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 rounded-full border border-slate-200 bg-slate-100 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 inline-flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="relative">
                                                <button type="button" id="dpMonthBtn"
                                                    class="h-8 rounded-full border border-slate-200 bg-slate-100 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 inline-flex items-center gap-2">
                                                    <span
                                                        id="dpMonthLabel">{{ ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][(int) date('n') - 1] }}</span>
                                                    <svg width="12" height="12" viewBox="0 0 20 20"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>

                                                <div id="dpMonthMenu"
                                                    class="absolute left-0 mt-2 w-44 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-64 overflow-auto">
                                                    <div id="dpMonthItems" class="text-sm"></div>
                                                </div>
                                            </div>

                                            <div class="relative">
                                                <button type="button" id="dpYearBtn"
                                                    class="h-8 rounded-full border border-slate-200 bg-slate-100 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 inline-flex items-center gap-2">
                                                    <span id="dpYearLabel">{{ date('Y') }}</span>
                                                    <svg width="12" height="12" viewBox="0 0 20 20"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>

                                                <div id="dpYearMenu"
                                                    class="absolute right-0 mt-2 w-28 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-64 overflow-auto">
                                                    <div id="dpYearItems" class="text-sm"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" id="dpNext"
                                            class="h-8 w-8 rounded-lg border border-slate-50 hover:bg-slate-50 flex items-center justify-center">
                                            <span class="text-slate-600">›</span>
                                        </button>
                                    </div>

                                    <div class="mt-3 grid grid-cols-7 text-center text-xs font-semibold text-slate-500">
                                        <div>Min</div>
                                        <div>Sen</div>
                                        <div>Sel</div>
                                        <div>Rab</div>
                                        <div>Kam</div>
                                        <div>Jum</div>
                                        <div>Sab</div>
                                    </div>

                                    <div id="dpGrid" class="mt-2 grid grid-cols-7 gap-1"></div>
                                </div>
                            </div>
                        </div>
                        <div id="rangeMonth" class="range-input-group" style="display:none;">
                            <div class="text-md font-semibold mb-2">Bulan</div>

                            <div class="relative w-full" id="mpWrap">
                                <input type="text" id="monthInputText"
                                    class="calendar-input text-sm py-2 rounded-lg pr-10 border border-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-700"
                                    value="" placeholder="Bulan Tahun" autocomplete="off" readonly />
                                <button type="button" id="mpBtn"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </button>

                                <input type="hidden" id="monthInput" value="{{ date('Y-m') }}" />

                                <div id="mpPanel"
                                    class="fixed w-[320px] rounded-xl border border-slate-200 bg-white shadow-lg p-3 z-[9999] hidden">
                                    <div class="flex items-center justify-center">
                                        <div class="relative">
                                            <button type="button" id="mpYearBtn"
                                                class="h-7 rounded-full bg-slate-100 px-4 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                <span id="mpYearLabel"></span>
                                                <svg width="12" height="12" viewBox="0 0 20 20" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>

                                            <div id="mpYearMenu"
                                                class="absolute left-1/2 -translate-x-1/2 mt-2 w-28 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-64 overflow-auto">
                                                <div id="mpYearItems" class="text-sm"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="mpGrid" class="mt-4 grid grid-cols-3 gap-2"></div>
                                </div>
                            </div>
                        </div>
                        <div id="rangeYear" class="range-input-group" style="display:none;">
                            <div class="text-md font-semibold mb-2">Tahun</div>

                            <div class="relative w-full" id="ypWrap">
                                <input type="text" id="yearInputText"
                                    class="calendar-input text-sm py-2 rounded-lg pr-10 border border-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-700"
                                    value="" placeholder="Tahun" autocomplete="off" readonly />
                                <button type="button" id="ypBtn"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </button>

                                <input type="hidden" id="yearInput" value="{{ date('Y') }}" />

                                <div id="ypPanel"
                                    class="fixed w-[320px] rounded-xl border border-slate-200 bg-white shadow-lg p-3 z-[9999] hidden">
                                    <div class="flex items-center justify-between">
                                        <button type="button" id="ypPrev"
                                            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center">
                                            <span class="text-slate-600">‹</span>
                                        </button>

                                        <div class="text-xs font-semibold text-slate-700 bg-slate-100 rounded-full px-4 py-1"
                                            id="ypRangeLabel"></div>

                                        <button type="button" id="ypNext"
                                            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center">
                                            <span class="text-slate-600">›</span>
                                        </button>
                                    </div>

                                    <div id="ypGrid" class="mt-4 grid grid-cols-3 gap-2"></div>
                                </div>
                            </div>
                        </div>
                        <div id="rangeCustom" class="range-input-group" style="display:none;">
                            <div class="text-md font-semibold mb-2">Rentang</div>

                            <div class="relative w-full" id="rpWrap">
                                <input type="text" id="rangeText"
                                    class="calendar-input text-sm py-2 rounded-lg pr-10 border border-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-700"
                                    value="" placeholder="YYYY/MM/DD - YYYY/MM/DD" autocomplete="off" readonly />
                                <button type="button" id="rpBtn"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </button>

                                <input type="hidden" id="startDateTime" value="{{ date('Y-m-d\T00:00') }}">
                                <input type="hidden" id="endDateTime" value="{{ date('Y-m-d\T23:59') }}">

                                <div id="rpPanel"
                                    class="fixed w-[640px] rounded-xl border border-slate-200 bg-white shadow-lg p-4 z-[9999] hidden">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1">
                                            <div id="rpStartBox"
                                                class="h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-sm text-slate-700">
                                            </div>
                                        </div>
                                        <div class="w-8 flex items-center justify-center text-slate-700">→</div>
                                        <div class="flex-1">
                                            <div id="rpEndBox"
                                                class="h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-sm text-slate-700">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2 text-center text-xs text-slate-600">
                                        <span id="rpDays">0 hari</span>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-4">
                                        <div class="rounded-xl border border-slate-200 p-3">
                                            <div class="flex items-center justify-between">
                                                <button type="button" id="rpPrev"
                                                    class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center">‹</button>

                                                <div class="flex items-center gap-2">
                                                    <div class="relative">
                                                        <button type="button" id="rpMonthBtnL"
                                                            class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                            <span id="rpMonthLabelL"></span>
                                                            <svg width="12" height="12" viewBox="0 0 20 20"
                                                                fill="none">
                                                                <path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round" />
                                                            </svg>
                                                        </button>
                                                        <div id="rpMonthMenuL"
                                                            class="absolute left-1/2 -translate-x-1/2 mt-2 w-44 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-64 overflow-auto">
                                                            <div id="rpMonthItemsL" class="text-sm"></div>
                                                        </div>
                                                    </div>

                                                    <div class="relative">
                                                        <button type="button" id="rpYearBtnL"
                                                            class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                            <span id="rpYearLabelL"></span>
                                                            <svg width="12" height="12" viewBox="0 0 20 20"
                                                                fill="none">
                                                                <path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round" />
                                                            </svg>
                                                        </button>
                                                        <div id="rpYearMenuL"
                                                            class="absolute left-1/2 -translate-x-1/2 mt-2 w-28 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-64 overflow-auto">
                                                            <div id="rpYearItemsL" class="text-sm"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button" id="rpNext"
                                                    class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center">›</button>
                                            </div>

                                            <div class="mt-3 grid grid-cols-7 gap-1 text-xs text-slate-500">
                                                <div class="text-center">Sen</div>
                                                <div class="text-center">Sel</div>
                                                <div class="text-center">Rab</div>
                                                <div class="text-center">Kam</div>
                                                <div class="text-center">Jum</div>
                                                <div class="text-center">Sab</div>
                                                <div class="text-center">Min</div>
                                            </div>
                                            <div id="rpGridL" class="mt-2 grid grid-cols-7"></div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 p-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <div class="relative">
                                                    <button type="button" id="rpMonthBtnR"
                                                        class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                        <span id="rpMonthLabelR"></span>
                                                        <svg width="12" height="12" viewBox="0 0 20 20"
                                                            fill="none">
                                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                    <div id="rpMonthMenuR"
                                                        class="absolute left-1/2 -translate-x-1/2 mt-2 w-44 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-64 overflow-auto">
                                                        <div id="rpMonthItemsR" class="text-sm"></div>
                                                    </div>
                                                </div>

                                                <div class="relative">
                                                    <button type="button" id="rpYearBtnR"
                                                        class="h-7 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-700 inline-flex items-center gap-2 hover:bg-slate-200">
                                                        <span id="rpYearLabelR"></span>
                                                        <svg width="12" height="12" viewBox="0 0 20 20"
                                                            fill="none">
                                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="#64748B"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                    <div id="rpYearMenuR"
                                                        class="absolute left-1/2 -translate-x-1/2 mt-2 w-28 rounded-xl border border-slate-200 bg-white shadow-lg p-1 hidden z-50 max-h-64 overflow-auto">
                                                        <div id="rpYearItemsR" class="text-sm"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 grid grid-cols-7 gap-1 text-xs text-slate-500">
                                                <div class="text-center">Sen</div>
                                                <div class="text-center">Sel</div>
                                                <div class="text-center">Rab</div>
                                                <div class="text-center">Kam</div>
                                                <div class="text-center">Jum</div>
                                                <div class="text-center">Sab</div>
                                                <div class="text-center">Min</div>
                                            </div>
                                            <div id="rpGridR" class="mt-2 grid grid-cols-7"></div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-end gap-3 border-t border-slate-200 pt-3">
                                        <button type="button" id="rpCancel"
                                            class="h-9 px-5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                                        <button type="button" id="rpApply"
                                            class="h-9 px-5 rounded-lg bg-[#303481] text-white text-sm font-semibold hover:bg-[#10134B]">Terapkan</button>
                                    </div>
                                </div>
                            </div>
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
        <div class="col-span-1 md:col-span-7 xl:col-span-9 2xl:col-span-10">
            <div class="border rounded-lg ">
                <div class="chart-section ps-3 pe-3 pt-3 pb-0 mb-0">

                    {{-- Rainfall Summary + Legend (hanya tampil saat tipe_graf = bar) --}}
                    <div id="rainfallHeader" class="hidden mb-3">
                        <div class="flex flex-col md:flex-row gap-3">
                            {{-- Card Akumulasi --}}
                            <div
                                class="relative overflow-hidden flex items-start gap-4 bg-white border border-slate-200 rounded-xl px-5 py-4 shadow-sm min-w-[240px]">
                                <div class="z-10">
                                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1"
                                        id="rainfallCardLabel">AKUMULASI CURAH HUJAN</div>
                                    <div class="text-xs text-slate-400 mb-1" id="rainfallCardDate">—</div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-3xl font-bold text-slate-800"
                                            id="rainfallCardTotal">0.000</span>
                                        <span class="text-sm font-semibold text-slate-500">mm</span>
                                    </div>
                                    <div class="mt-1 text-xs font-medium" id="rainfallCardCategory">—</div>
                                </div>
                                <img id="rainfallCardIcon" src="{{ asset('klasifikasi_hujan/tidak_hujan.png') }}"
                                    onerror="this.onerror=null;this.src='{{ asset('klasifikasi_hujan/tidak_hujan.png') }}';"
                                    alt="Status Hujan"
                                    class="pointer-events-none absolute right-[-0.5rem] top-6 h-24 w-24 object-contain opacity-90">
                            </div>

                            {{-- Legend Intensitas (dirender dinamis dari DB) --}}
                            <div class="flex-1 bg-white border border-slate-200 rounded-xl px-5 py-4 shadow-sm">
                                <div class="text-xl font-bold text-slate-700 mb-2">Keterangan Intensitas Hujan Per Jam:
                                </div>
                                <div id="rainfallLegendItems" class="flex flex-wrap gap-x-5 gap-y-1">
                                    {{-- akan diisi JS dari data.klasifikasi --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $loggerKategoriName = strtoupper(
                            $logger->kategori_logger
                            ?? $logger->kategori?->nama_kategori
                            ?? ''
                        );
                    @endphp
                    @if($loggerKategoriName === 'AWQR')
                    @php
                        $latestAwgrRow = ($logger->temp19 instanceof \Illuminate\Database\Eloquent\Model)
                            ? $logger->temp19 : $logger->temp16;
                        $phParamAwgr = $logger->params->first(fn($p) =>
                            in_array(strtolower(trim($p->nama_parameter)), ['ph_air','ph air','ph']) ||
                            in_array(strtolower(trim($p->kolom_sensor ?? '')), ['ph_air','ph'])
                        );
                        $phKolomAwgr = $phParamAwgr?->kolom_sensor;
                        $latestPhAwgr = $latestAwgrRow && $phKolomAwgr ? ($latestAwgrRow->{$phKolomAwgr} ?? null) : null;
                        $latestPhTimeAwgr = $latestAwgrRow?->waktu ?? null;
                        $phDisplayAwgr = is_numeric($latestPhAwgr) ? number_format((float)$latestPhAwgr, 2) : '-';
                        $phTimeDisplayAwgr = $latestPhTimeAwgr ? date('d-m-Y H:i:s', strtotime($latestPhTimeAwgr)) : '-';
                        $phVal = is_numeric($latestPhAwgr) ? (float)$latestPhAwgr : null;
                        $phClassLabel = $phVal !== null ? ($phVal >= 6 && $phVal <= 9 ? 'Kelas I – III' : ($phVal >= 5 ? 'Kelas IV' : 'Di Luar Baku Mutu')) : '';
                        $phClassColor = $phVal !== null ? ($phVal >= 6 && $phVal <= 9 ? '#3b82f6' : ($phVal >= 5 ? '#ef4444' : '#6b7280')) : '#6b7280';
                    @endphp
                    {{-- Universal AWQR Parameter Panel — konten diupdate JS sesuai parameter dipilih --}}
                    <div id="awqrParamHeader" class="hidden mb-3">
                        <div class="flex flex-col md:flex-row gap-3">
                            {{-- Card Nilai --}}
                            <div class="relative overflow-hidden flex-shrink-0 bg-white border border-slate-200 rounded-xl px-5 py-2 shadow-sm" style="min-width:290px">
                                <div class="z-10 relative">
                                    <div class="flex items-start justify-between gap-4 mt-2">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-700 uppercase tracking-wide" id="awqrParamLabel">NILAI PARAMETER</div>
                                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 16 16" stroke="currentColor"><rect x="1" y="2" width="14" height="13" rx="2" stroke-width="1.5"/><path d="M1 6h14" stroke-width="1.5"/><path d="M5 1v3M11 1v3" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                <span id="awqrParamTimeSpan">—</span>
                                            </div>
                                        </div>
                                        <div class="text-right leading-none">
                                            <span class="text-3xl font-bold text-slate-800" id="awqrParamValue">—</span>
                                            <span class="text-xs text-slate-400 ml-1" id="awqrParamUnit"></span>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center gap-1.5" id="awqrParamBadge">
                                        <span class="inline-block w-3 h-3 rounded-sm flex-shrink-0" id="awqrParamClassDot" style="background:#009CD9"></span>
                                        <span class="text-sm font-semibold" id="awqrParamClass" style="color:#009CD9"></span>
                                    </div>
                                </div>
                                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-12 opacity-60" aria-hidden="true">
                                    <img src="{{ asset('icons/gelombang.svg') }}" class="w-full h-full object-cover object-center" alt="">
                                </div>
                            </div>
                            {{-- Keterangan (diisi JS) --}}
                            <div class="flex-1 bg-white border border-slate-200 rounded-xl px-5 py-2 shadow-sm">
                                <div class="text-base font-bold text-slate-700 mb-2">Keterangan:</div>
                                <div class="flex flex-wrap gap-x-8 gap-y-3" id="awqrKeteranganItems">
                                    {{-- Diisi JS --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif

                    <div class="chart-title text-lg font-semibold" id="chartTitle">{{ date('F Y') }}</div>
                    <div class="chart-wrapper mb-3 mt-2">
                        <canvas id="dataChart" height="400"></canvas>
                    </div>
                </div>
                <div class="data-table-section ps-3 pe-3">
                    <div class="table-title" id="tableTitle">{{ date('F Y') }}</div>

                    {{-- Tabel Utama (line chart) --}}
                    <div id="mainTableWrap" class="w-full overflow-hidden rounded-lg border border-slate-300 mb-3">
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

                    {{-- Tabel Intensitas Curah Hujan (bar chart) --}}
                    <div id="rainfallTableWrap"
                        class="hidden w-full overflow-hidden rounded-lg border border-slate-300 mb-3">
                        <table class="data-table w-full">
                            <thead class="bg-neutral-300 text-neutral-950 font-semibold uppercase text-xs">
                                <tr>
                                    <th class="py-2 px-4 text-left w-1/2">WAKTU</th>
                                    <th class="py-2 px-4 text-left w-1/2">CURAH HUJAN</th>
                                </tr>
                            </thead>
                            <tbody id="rainfallTableBody" class="text-sm">
                                <tr>
                                    <td colspan="2" class="text-center py-10 text-slate-400">
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        let chart = null;
        const loggerId = '{{ $logger->id_logger }}';
        document.addEventListener('DOMContentLoaded', function() {
            const paramSelectEl = document.getElementById('parameterSelect');
            const refreshCurrentParamData = () => {
                const param = paramSelectEl ? String(paramSelectEl.value || '').trim() : '';
                if (!param) return;
                updateChartTitle();
                loadData();
            };
            let parameterUsesSelect2 = false;

            // Init Select2 untuk dropdown logger
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $('#loggerSelect').select2({
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
                }).on('change', function() {
                    const selectedId = $(this).val();
                    if (selectedId) {
                        window.location.href = '{{ url('/analisa') }}/' + selectedId;
                    }
                });

                $('#parameterSelect').select2({
                    width: '100%',
                    placeholder: 'Cari parameter...',
                    allowClear: false,
                    language: {
                        searching: function() {
                            return 'Mencari...';
                        },
                        noResults: function() {
                            return 'Parameter tidak ditemukan';
                        },
                    }
                });
                parameterUsesSelect2 = true;
            }

            initChart();
            updateChartTitle();
            setupRangeInputs();
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
            if (parameterUsesSelect2 && typeof $ !== 'undefined') {
                $('#parameterSelect').on('change', refreshCurrentParamData);
            } else if (paramSelectEl) {
                paramSelectEl.addEventListener('change', refreshCurrentParamData);
            }

            const urlParams = new URLSearchParams(window.location.search);
            const paramFromUrl = String(urlParams.get('parameter') || '').trim();
            const options = Array.from(paramSelectEl?.options || []);

            const hasParamOption = (value) => options.some((opt) => opt.value === value);
            const firstValidParam = options.find((opt) => String(opt.value || '').trim() !== '')?.value || '';

            // Parameter utama dari controller (parameter_utama = 1 di DB)
            const defaultParam = '{{ $defaultParameter ?? '' }}';

            let initialParam = '';
            if (paramFromUrl && hasParamOption(paramFromUrl)) {
                // Prioritas 1: dari URL ?parameter=
                initialParam = paramFromUrl;
            } else if (defaultParam && hasParamOption(defaultParam)) {
                // Prioritas 2: parameter_utama = 1 dari controller
                initialParam = defaultParam;
            } else if (paramSelectEl && String(paramSelectEl.value || '').trim() !== '') {
                initialParam = paramSelectEl.value;
            } else {
                initialParam = firstValidParam;
            }

            if (paramSelectEl && initialParam) {
                paramSelectEl.value = initialParam;
                if (parameterUsesSelect2 && typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined' && $(
                        '#parameterSelect').data(
                        'select2')) {
                    $('#parameterSelect').val(initialParam).trigger('change');
                } else {
                    refreshCurrentParamData();
                }
            }
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
            const paramLabel = param.replace(/_/g, ' ');
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            let titleText = `Rerata ${paramLabel} `;
            let tableTitleText = `Tabel Rerata ${paramLabel} `;
            if (range === 'day') {
                const dateInput = String(document.getElementById('dateInput').value || '').trim();
                const m = dateInput.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (m) {
                    const year = Number(m[1]);
                    const monthIdx = Number(m[2]) - 1;
                    const day = Number(m[3]);
                    const month = monthNames[monthIdx] || '-';
                    titleText += `Pada ${day} ${month} ${year}`;
                    tableTitleText += `Pada ${day} ${month} ${year}`;
                } else {
                    titleText += 'Pada -';
                    tableTitleText += 'Pada -';
                }
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

        let currentChartType = 'line';

        function buildChart(isBar) {
            const type = isBar ? 'bar' : 'line';
            if (chart) {
                chart.destroy();
                chart = null;
            }
            const canvas = document.getElementById('dataChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const datasets = isBar ? [
                { label: 'Curah Hujan', data: [], backgroundColor: [], borderColor: [], borderWidth: 0, borderRadius: 2, minBarLength: 3, barPercentage: 0.6, categoryPercentage: 0.7 },
                { label: '', data: [], hidden: true },
                { label: '', data: [], hidden: true }
            ] : [
                { label: 'Rerata',   data: [], borderColor: '#1e40af', backgroundColor: 'rgba(30,64,175,0.1)',  tension: 0.4, cubicInterpolationMode: 'monotone', fill: true,  borderWidth: 3, pointRadius: 2 },
                { label: 'Minimum',  data: [], borderColor: '#60a5fa', backgroundColor: 'rgba(96,165,250,0.1)', tension: 0.4, cubicInterpolationMode: 'monotone', fill: false, borderWidth: 2, borderDash: [5,5], pointRadius: 0 },
                { label: 'Maksimum', data: [], borderColor: '#4338ca', backgroundColor: 'rgba(67,56,202,0.1)', tension: 0.4, cubicInterpolationMode: 'monotone', fill: true,  borderWidth: 2, borderDash: [5,5], pointRadius: 0 }
            ];

            chart = new Chart(ctx, {
                type: type,
                data: { labels: [], datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, boxHeight: 8, padding: 16, font: { size: 12 } }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(ctx) {
                                    const v = ctx.parsed.y;
                                    if (v === null || v === undefined) return null;
                                    return isBar ? `${ctx.dataset.label}: ${v} mm` : `${ctx.dataset.label}: ${v}`;
                                }
                            }
                        }
                    },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    scales: {
                        x: {
                            offset: isBar,
                            grid: { display: false, offset: isBar },
                            ticks: { font: { size: 11 }, color: '#94a3b8', maxRotation: 0 },
                            border: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            min: isBar ? 0 : undefined,
                            grid: { color: 'rgba(148,163,184,0.15)', lineWidth: 1 },
                            ticks: {
                                font: { size: 11 },
                                color: '#94a3b8',
                                callback: function(value) { return isBar ? value + ' mm' : value; }
                            },
                            border: { display: false }
                        }
                    }
                }
            });
            currentChartType = type;
        }

        function initChart() {
            buildChart(false);
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
            const query = new URLSearchParams({
                parameter: selectedParam,
                range: range,
                date: date
            });
            fetch(`{{ route('analisa.data', ':id') }}`.replace(':id', loggerId) + `?${query.toString()}`)
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
                const dateInputEl = document.getElementById('dateInput');
                const dateInput = dateInputEl ? dateInputEl.value : '';
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
                const monthInputEl = document.getElementById('monthInput');
                const monthInput = monthInputEl ? monthInputEl.value : '';
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
                const yearInputEl = document.getElementById('yearInput');
                const yearInput = yearInputEl ? yearInputEl.value : '';
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
            return hasAnyRealValue(data && data.chartData) || hasAnyRealValue(data && data.minData) || hasAnyRealValue(
                data && data.maxData);
        }

        function getSelectedUnit() {
            const sel = document.getElementById('parameterSelect');
            if (!sel) return '';
            const opt = sel.options[sel.selectedIndex];
            const u = (opt && opt.dataset && opt.dataset.unit) ? opt.dataset.unit : '';
            return u ? ` ${u}` : '';
        }

        function fmtWithUnit(v, unit) {
            if (v === null || v === undefined || v === '') return '-';
            return `${v}${unit}`;
        }

        // ─── Rainfall helpers ────────────────────────────────────────────────
        function getRainfallColor(val) {
            if (val === null || val === undefined) return 'rgba(200,200,200,0.3)';
            if (val <= 0) return '#84C450'; // Tidak Hujan
            if (val < 1) return '#70CDDD'; // Sangat Ringan
            if (val < 5) return '#35549D'; // Ringan
            if (val < 10) return '#FEF216'; // Sedang
            if (val < 20) return '#F47E2C'; // Lebat
            return '#ED1C24'; // Sangat Lebat
        }

        function getRainfallCategory(total) {
            if (total <= 0) return {
                label: 'Tidak Hujan',
                color: '#4b7c1e'
            };
            if (total < 1) return {
                label: 'Hujan Sangat Ringan',
                color: '#1a7ab5'
            };
            if (total < 5) return {
                label: 'Hujan Ringan',
                color: '#1e4db7'
            };
            if (total < 10) return {
                label: 'Hujan Sedang',
                color: '#b08900'
            };
            if (total < 20) return {
                label: 'Hujan Lebat',
                color: '#b85d1f'
            };
            return {
                label: 'Hujan Sangat Lebat',
                color: '#922b21'
            };
        }

        function getRainfallIconState(total, klasifikasi) {
            // Gunakan thresholds dari DB jika tersedia
            if (Array.isArray(klasifikasi) && klasifikasi.length) {
                const sorted = [...klasifikasi].sort((a, b) => a.debit_air - b.debit_air);
                // Mapping intensitas label → icon filename slug
                const slugMap = {
                    'tidak hujan': 'tidak_hujan',
                    'hujan sangat ringan': 'hujan_sangat_ringan',
                    'hujan ringan': 'hujan_ringan',
                    'hujan sedang': 'hujan_sedang',
                    'hujan lebat': 'hujan_lebat',
                    'hujan sangat lebat': 'hujan_sangat_lebat',
                };
                let matched = sorted[0];
                for (const row of sorted) {
                    if (total >= row.debit_air) matched = row;
                }
                const slug = slugMap[(matched.intensitas ?? '').toLowerCase().trim()];
                return slug ?? 'tidak_hujan';
            }
            // Fallback hardcoded
            if (total <= 0) return 'tidak_hujan';
            if (total < 1) return 'hujan_sangat_ringan';
            if (total < 5) return 'hujan_ringan';
            if (total < 10) return 'hujan_sedang';
            if (total < 20) return 'hujan_lebat';
            return 'hujan_sangat_lebat';
        }

        function renderRainfallLegend(klasifikasi) {
            const container = document.getElementById('rainfallLegendItems');
            if (!container) return;
            if (!Array.isArray(klasifikasi) || !klasifikasi.length) {
                container.innerHTML = '<span class="text-sm text-slate-400">Keterangan tidak tersedia</span>';
                return;
            }
            const sorted = [...klasifikasi].sort((a, b) => a.debit_air - b.debit_air);
            let html = '';
            for (let i = 0; i < sorted.length; i++) {
                const row   = sorted[i];
                const next  = sorted[i + 1];
                const color = getRainfallColor(row.debit_air <= 0 ? 0 : row.debit_air);
                let rangeLabel;
                if (row.debit_air <= 0) {
                    rangeLabel = '0 mm';
                } else if (next) {
                    rangeLabel = `${row.debit_air} \u2013 ${next.debit_air} mm`;
                } else {
                    rangeLabel = `\u2265 ${row.debit_air} mm`;
                }
                html += `<div class="flex items-center gap-2">
                    <span class="inline-block w-9 h-9 rounded-sm flex-shrink-0" style="background:${color}"></span>
                    <div>
                        <div class="text-sm font-semibold text-slate-700">${row.intensitas}</div>
                        <div class="text-sm text-slate-400">${rangeLabel}</div>
                    </div>
                </div>`;
            }
            container.innerHTML = html;
        }

        function updateRainfallCard(data) {
            const isBar = (data.tipe_graf === 'bar');
            const header = document.getElementById('rainfallHeader');
            if (!header) return;
            if (!isBar) {
                header.classList.add('hidden');
                return;
            }
            header.classList.remove('hidden');

            // Render legend dinamis dari data DB
            renderRainfallLegend(data.klasifikasi ?? []);

            const total = data.akumulasi ?? 0;
            document.getElementById('rainfallCardTotal').textContent = total.toFixed(3);

            // Update icon image
            const iconEl = document.getElementById('rainfallCardIcon');
            const iconState = getRainfallIconState(total, data.klasifikasi ?? []);
            if (iconEl) {
                iconEl.src = `{{ asset('klasifikasi_hujan') }}/${iconState}.png`;
                iconEl.alt = iconState.replace(/_/g, ' ');
            }

            // Date label
            const range = document.querySelector('input[name="range"]:checked')?.value ?? 'day';
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            let dateLabel = '';
            if (range === 'day') {
                const v = document.getElementById('dateInput')?.value ?? '';
                const m = v.match(/(\d{4})-(\d{2})-(\d{2})/);
                if (m) dateLabel = `${parseInt(m[3])} ${monthNames[parseInt(m[2])-1]} ${m[1]}`;
            } else if (range === 'month') {
                const v = document.getElementById('monthInput')?.value ?? '';
                const [yr, mo] = v.split('-');
                dateLabel = `${monthNames[parseInt(mo)-1]} ${yr}`;
            } else if (range === 'year') {
                dateLabel = document.getElementById('yearInput')?.value ?? '';
            } else {
                dateLabel = 'Rentang Kustom';
            }
            document.getElementById('rainfallCardDate').textContent = dateLabel;

            const cat = getRainfallCategory(total);
            const catEl = document.getElementById('rainfallCardCategory');
            catEl.textContent = cat.label;
            catEl.style.color = cat.color;
        }
        // ─────────────────────────────────────────────────────────────────────

        // ─── AWQR Universal Parameter Panel ────────────────────────────────────
        const _isAwgr = @php
            echo (strtoupper($logger->kategori_logger ?? $logger->kategori?->nama_kategori ?? '') === 'AWQR') ? 'true' : 'false';
        @endphp;

        // Definisi keterangan untuk setiap parameter AWQR
        const _awqrParamDefs = {
            // Tinggi Muka Air
            tinggi_muka_air: {
                aliases: ['tinggi_muka_air','tinggi muka air','water level','tma'],
                label: 'TINGGI MUKA AIR', unit: 'm',
                classify: () => null,
                keterangan: [] // tidak ada klasifikasi baku mutu
            },
            // pH Air
            ph_air: {
                aliases: ['ph_air','ph air','ph','ph_water'],
                label: 'NILAI pH AIR', unit: '',
                classify: v => {
                    if (v >= 6 && v <= 9) return { label: 'Kelas I \u2013 III', color: '#009CD9' };
                    if (v >= 5 && v <= 9) return { label: 'Kelas IV', color: '#E62421' };
                    return { label: 'Di Luar Baku Mutu', color: '#6b7280' };
                },
                keterangan: [
                    { color: '#009CD9', label: 'Kelas I \u2013 III', range: '6 \u2013 9' },
                    { color: '#E62421', label: 'Kelas IV',        range: '5 \u2013 9' },
                ]
            },
            // Suhu Air
            suhu_air: {
                aliases: ['suhu_air','suhu air','suhu','temperature','temp','water_temperature'],
                label: 'SUHU AIR', unit: '\u00b0C',
                classify: v => {
                    if (v >= 25 && v <= 30) return { label: 'Normal', color: '#009CD9' };
                    return { label: 'Di Luar Rentang', color: '#F97316' };
                },
                keterangan: [
                    { color: '#009CD9', label: 'Normal',          range: '25 \u2013 30 \u00b0C' },
                    { color: '#F97316', label: 'Di Luar Rentang', range: '< 25 atau > 30 \u00b0C' },
                ]
            },
            // ORP
            orp: {
                aliases: ['orp'],
                label: 'NILAI ORP', unit: 'mV',
                classify: v => v <= 200
                    ? { label: 'Aman',    color: '#009CD9' }
                    : { label: 'Waspada', color: '#F97316' },
                keterangan: [
                    { color: '#009CD9', label: 'Aman',    range: '0 mV \u2013 200 mV' },
                    { color: '#F97316', label: 'Waspada', range: '\u2265 200 mV' },
                ]
            },
            // Conductivity
            conductivity: {
                aliases: ['conductivity','konduktivitas','electrical_conductivity','ec'],
                label: 'NILAI CONDUCTIVITY', unit: '\u03bcS/cm',
                classify: v => v <= 1000
                    ? { label: 'Kelas I',         color: '#009CD9' }
                    : { label: 'Kelas II \u2013 IV', color: '#F97316' },
                keterangan: [
                    { color: '#009CD9', label: 'Kelas I',           range: '0 \u03bcS/cm \u2013 1000 \u03bcS/cm' },
                    { color: '#F97316', label: 'Kelas II \u2013 IV', range: '\u2265 1000 \u03bcS/cm' },
                ]
            },
            // Salinity
            salinity: {
                aliases: ['salinity','salinitas'],
                label: 'NILAI SALINITY', unit: 'PSU',
                classify: () => ({ label: 'Kelas I \u2013 III', color: '#009CD9' }),
                keterangan: [
                    { color: '#009CD9', label: 'Kelas I \u2013 III', range: 'Mendekati Nol' },
                ]
            },
            // Total Dissolved Solids
            tds: {
                aliases: ['tds','total_dissolved_solids','dissolved_solids','total dissolved solids'],
                label: 'TOTAL DISSOLVED SOLIDS', unit: 'mg/L',
                classify: v => v <= 1000
                    ? { label: 'Kelas I \u2013 III', color: '#009CD9' }
                    : { label: 'Kelas IV',         color: '#F97316' },
                keterangan: [
                    { color: '#009CD9', label: 'Kelas I \u2013 III', range: '0 \u2013 1000 mg/L' },
                    { color: '#F97316', label: 'Kelas IV',           range: '\u2265 1000 mg/L' },
                ]
            },
            // Turbidity
            turbidity: {
                aliases: ['turbidity','kekeruhan'],
                label: 'NILAI TURBIDITY', unit: 'NTU',
                classify: v => v <= 5
                    ? { label: 'Kelas I',           color: '#009CD9' }
                    : { label: 'Kelas II \u2013 IV', color: '#F97316' },
                keterangan: [
                    { color: '#009CD9', label: 'Kelas I',           range: '0 \u2013 5 NTU' },
                    { color: '#F97316', label: 'Kelas II \u2013 IV', range: '\u2265 5 NTU' },
                ]
            },
            // Tinggi Sensor
            tinggi_sensor: {
                aliases: ['tinggi_sensor','tinggi sensor','sensor height','sensor_height'],
                label: 'TINGGI SENSOR', unit: 'm',
                classify: () => null,
                keterangan: []
            },
        };

        function _buildKeteranganHtml(items) {
            if (!items || !items.length) {
                return '<span class="text-sm text-slate-400">Tidak ada klasifikasi baku mutu untuk parameter ini.</span>';
            }
            return items.map(it => `
                <div class="flex items-center gap-3">
                    <span class="inline-block w-8 h-8 rounded-sm flex-shrink-0" style="background:${it.color}"></span>
                    <div>
                        <div class="text-sm font-semibold text-slate-700">${it.label}</div>
                        <div class="text-sm text-slate-400">${it.range}</div>
                    </div>
                </div>`).join('');
        }

        function _getTimeLbl() {
            const mn = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const r  = document.querySelector('input[name="range"]:checked')?.value ?? 'day';
            if (r === 'day') {
                const v = document.getElementById('dateInput')?.value ?? '';
                const m = v.match(/(\d{4})-(\d{2})-(\d{2})/);
                return m ? `Rerata ${parseInt(m[3])} ${mn[parseInt(m[2])-1]} ${m[1]}` : 'Rerata';
            }
            if (r === 'month') {
                const v = document.getElementById('monthInput')?.value ?? '';
                const [yr, mo] = v.split('-');
                return (yr && mo) ? `Rerata ${mn[parseInt(mo)-1]} ${yr}` : 'Rerata';
            }
            if (r === 'year') return `Rerata Tahun ${document.getElementById('yearInput')?.value ?? ''}`;
            return 'Rerata Rentang';
        }

        function updatePhAirPanel(data) {
            // alias ke fungsi universal agar backward-compatible
            updateAwqrParamPanel(data);
        }

        function updateAwqrParamPanel(data) {
            const panel = document.getElementById('awqrParamHeader');
            if (!panel) return;

            if (!_isAwgr) { panel.classList.add('hidden'); return; }

            const sel      = document.getElementById('parameterSelect');
            const paramVal = String(sel ? sel.value : '').toLowerCase().trim();
            if (!paramVal) { panel.classList.add('hidden'); return; }

            // Temukan definisi parameter
            let def = null;
            for (const key of Object.keys(_awqrParamDefs)) {
                const d = _awqrParamDefs[key];
                if (d.aliases.some(a => paramVal === a || paramVal.includes(a) || a.includes(paramVal))) {
                    def = d; break;
                }
            }
            if (!def) { panel.classList.add('hidden'); return; }

            panel.classList.remove('hidden');

            // Hitung rerata
            const chartArr = Array.isArray(data && data.chartData) ? data.chartData : [];
            const valid    = chartArr.filter(v => v !== null && v !== undefined && !isNaN(Number(v))).map(Number);
            const avg      = valid.length > 0 ? valid.reduce((a, b) => a + b, 0) / valid.length : null;

            // Update elemen
            const lblEl  = document.getElementById('awqrParamLabel');
            const valEl  = document.getElementById('awqrParamValue');
            const unitEl = document.getElementById('awqrParamUnit');
            const timeEl = document.getElementById('awqrParamTimeSpan');
            const dotEl  = document.getElementById('awqrParamClassDot');
            const clsEl  = document.getElementById('awqrParamClass');
            const badgeEl= document.getElementById('awqrParamBadge');
            const ktEl   = document.getElementById('awqrKeteranganItems');

            if (lblEl)  lblEl.textContent  = def.label;
            if (unitEl) unitEl.textContent = def.unit;
            if (timeEl) timeEl.textContent = _getTimeLbl();
            if (valEl)  valEl.textContent  = avg !== null ? avg.toFixed(2) : '\u2014';
            if (ktEl)   ktEl.innerHTML     = _buildKeteranganHtml(def.keterangan);

            // Badge klasifikasi
            const cls = avg !== null ? def.classify(avg) : null;
            if (badgeEl) badgeEl.style.display = cls ? '' : 'none';
            if (cls) {
                if (dotEl) dotEl.style.background = cls.color;
                if (clsEl) { clsEl.textContent = cls.label; clsEl.style.color = cls.color; }
            }
        }
        // ─────────────────────────────────────────────────────────────────────




        function updateChart(data) {
            if (!chart) return;
            const labelsRaw = data.labels || [];
            const avgRaw = data.chartData || [];
            const minRaw = data.minData || [];
            const maxRaw = data.maxData || [];
            const rangeNode = document.querySelector('input[name="range"]:checked');
            const range = rangeNode ? rangeNode.value : 'day';
            const isBar = (data.tipe_graf === 'bar');

            // ── Rebuild chart if type changed (ensures correct x-axis offset) ──
            const neededType = isBar ? 'bar' : 'line';
            if (currentChartType !== neededType) {
                buildChart(isBar);
            }

            if (!hasAnyDataPayload(data)) {
                chart.data.labels = [];
                chart.data.datasets[0].data = [];
                chart.data.datasets[1].data = [];
                chart.data.datasets[2].data = [];
                chart.update();
                updateRainfallCard(data);
                updatePhAirPanel(data);
                return;
            }

            let filteredLabels, filteredAvg, filteredMin, filteredMax;

            if (range === 'custom') {
                filteredLabels = labelsRaw;
                filteredAvg = avgRaw;
                filteredMin = minRaw;
                filteredMax = maxRaw;
            } else {
                const f = filterSeriesToNow(labelsRaw, range, avgRaw, minRaw, maxRaw);
                filteredLabels = f.labels;
                filteredAvg = f.series[0];
                filteredMin = f.series[1];
                filteredMax = f.series[2];
            }

            chart.data.labels = filteredLabels;

            if (isBar) {
                // Bar chart: strip null slots so bars align perfectly with labels
                const barLabels = [];
                const barValues = [];
                for (let i = 0; i < (filteredLabels || []).length; i++) {
                    const v = (filteredAvg || [])[i];
                    if (v !== null && v !== undefined) {
                        barLabels.push(filteredLabels[i]);
                        barValues.push(v);
                    }
                }
                const barColors = barValues.map(v => getRainfallColor(v));
                chart.data.labels                       = barLabels;
                chart.data.datasets[0].data             = barValues;
                chart.data.datasets[0].backgroundColor  = barColors;
                chart.data.datasets[0].borderColor      = barColors;
                chart.data.datasets[1].data             = [];
                chart.data.datasets[2].data             = [];
            } else {
                chart.data.labels              = filteredLabels;
                chart.data.datasets[0].data    = filteredAvg;
                chart.data.datasets[1].data    = filteredMin;
                chart.data.datasets[2].data    = filteredMax;
            }

            chart.update();
            updateRainfallCard(data);
            updatePhAirPanel(data);
        }

        function updateTable(data) {
            const tbody = document.getElementById('dataTableBody');
            const rbody = document.getElementById('rainfallTableBody');
            const mainWrap = document.getElementById('mainTableWrap');
            const rfWrap = document.getElementById('rainfallTableWrap');
            if (!tbody) return;

            const isBar = (data.tipe_graf === 'bar');
            const rangeNode = document.querySelector('input[name="range"]:checked');
            const range = rangeNode ? rangeNode.value : 'day';
            const rows = Array.isArray(data.tableData) ? data.tableData : [];
            const labelsRaw = Array.isArray(data.labels) ? data.labels : [];
            const unit = getSelectedUnit();
            const isAllEmpty = !hasAnyDataPayload(data);

            if (isBar) {
                // Show rainfall table, hide main table
                mainWrap?.classList.add('hidden');
                rfWrap?.classList.remove('hidden');

                if (isAllEmpty || !rbody) {
                    if (rbody) rbody.innerHTML =
                        '<tr><td colspan="2" class="text-center py-10 text-slate-400">Tidak ada data</td></tr>';
                    return;
                }

                // Filter to visible labels
                let visibleLabels;
                if (range === 'custom' || range === 'month') {
                    visibleLabels = null; // show all non-null
                } else {
                    const f = filterSeriesToNow(labelsRaw, range);
                    visibleLabels = new Set(f.labels || []);
                }

                const filtered = rows.filter(r => {
                    if (!r) return false;
                    if (r.rerata === null && r.minimum === null && r.maksimum === null) return false;
                    if (visibleLabels && !visibleLabels.has(String(r.waktu))) return false;
                    return true;
                });

                if (!filtered.length) {
                    rbody.innerHTML =
                        '<tr><td colspan="2" class="text-center py-10 text-slate-400">Tidak ada data</td></tr>';
                    return;
                }

                let html = '';
                for (const r of filtered) {
                    const val = r.rerata;
                    html += `<tr>
                        <td class="px-4 py-2">${r.waktu ?? '-'}</td>
                        <td class="px-4 py-2">${fmtWithUnit(val, unit)}</td>
                    </tr>`;
                }
                rbody.innerHTML = html;
                return;
            }

            // ── Line chart: use main table ──
            mainWrap?.classList.remove('hidden');
            rfWrap?.classList.add('hidden');

            if (isAllEmpty) {
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">Tidak ada data</td></t r>';
                return;
            }
            if (range === 'custom' || range === 'month') {
                const filtered = rows.filter(r => {
                    if (!r) return false;
                    const a = r.rerata,
                        b = r.minimum,
                        c = r.maksimum;
                    return !((a == null || a === '') && (b == null || b === '') && (c == null || c === ''));
                });
                if (!filtered.length) {
                    tbody.innerHTML =
                        '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">Tidak ada data</td></tr>';
                    return;
                }
                let html = '';
                for (const r of filtered) {
                    html += `<tr>
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
                const a = r.rerata,
                    b = r.minimum,
                    c = r.maksimum;
                return !((a == null || a === '') && (b == null || b === '') && (c == null || c === ''));
            });
            if (!filtered.length) {
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">Tidak ada data</td></tr>';
                return;
            }
            let html = '';
            for (const r of filtered) {
                html += `<tr>
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
            const query = new URLSearchParams({
                parameter: selectedParam,
                range: range,
                date: date
            });
            const url = `{{ route('analisa.export', ['id_logger' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', loggerId) +
                `?${query.toString()}`;
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
            if (event && typeof event.stopPropagation === 'function') event.stopPropagation();
            updateDocPhoto(docPhotoIndex - 1);
        }

        function nextDocPhoto(event) {
            if (event && typeof event.stopPropagation === 'function') event.stopPropagation();
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper: posisikan panel fixed di bawah trigger wrap
            function positionPanel(anchorWrap, panel) {
                const rect = anchorWrap.getBoundingClientRect();
                // Ukur dimensi panel: sementara buat invisible untuk bisa mengukur
                const wasHidden = panel.classList.contains('hidden');
                if (wasHidden) {
                    panel.style.visibility = 'hidden';
                    panel.classList.remove('hidden');
                }
                const panelWidth = panel.offsetWidth || 320;
                const panelH = panel.offsetHeight || 400;
                if (wasHidden) {
                    panel.classList.add('hidden');
                    panel.style.visibility = '';
                }
                const winW = window.innerWidth;
                const winH = window.innerHeight;
                let top = rect.bottom + 8;
                let left = rect.left;
                // Jangan sampai keluar batas kanan layar
                if (left + panelWidth > winW - 8) {
                    left = winW - panelWidth - 8;
                }
                if (left < 8) left = 8;
                // Jika tidak muat di bawah, tampilkan di atas
                if (top + panelH > winH - 8) {
                    top = rect.top - panelH - 8;
                    if (top < 8) top = 8;
                }
                panel.style.top = top + 'px';
                panel.style.left = left + 'px';
            }

            // =========================
            // 1) DATE PICKER (HARI) - KODE KAMU (dpWrap)
            // =========================
            const wrap = document.getElementById('dpWrap')
            const input = document.getElementById('dateInput')
            const btn = document.getElementById('dpBtn')
            const panel = document.getElementById('dpPanel')
            const grid = document.getElementById('dpGrid')

            const prevBtn = document.getElementById('dpPrev')
            const nextBtn = document.getElementById('dpNext')

            const monthBtn = document.getElementById('dpMonthBtn')
            const yearBtn = document.getElementById('dpYearBtn')
            const monthLabel = document.getElementById('dpMonthLabel')
            const yearLabel = document.getElementById('dpYearLabel')
            const monthMenu = document.getElementById('dpMonthMenu')
            const yearMenu = document.getElementById('dpYearMenu')
            const monthItems = document.getElementById('dpMonthItems')
            const yearItems = document.getElementById('dpYearItems')

            if (wrap && input && panel && grid) {
                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                    'September', 'Oktober', 'November', 'Desember'
                ]

                function pad(n) {
                    return String(n).padStart(2, '0')
                }

                function fmt(y, m, d) {
                    return `${y}-${pad(m+1)}-${pad(d)}`
                }

                function parseInputToDate(v) {
                    const s = String(v || '').trim()
                    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/)
                    if (!m) return null
                    const y = Number(m[1]),
                        mo = Number(m[2]) - 1,
                        d = Number(m[3])
                    const dt = new Date(y, mo, d)
                    if (dt.getFullYear() !== y || dt.getMonth() !== mo || dt.getDate() !== d) return null
                    return dt
                }

                function closeMenus() {
                    if (monthMenu) monthMenu.classList.add('hidden')
                    if (yearMenu) yearMenu.classList.add('hidden')
                }

                function openPanel() {
                    panel.classList.remove('hidden')
                    positionPanel(wrap, panel)
                    closeMenus()
                    render()
                }

                function closePanel() {
                    panel.classList.add('hidden')
                    closeMenus()
                }

                function togglePanel() {
                    panel.classList.toggle('hidden')
                    if (!panel.classList.contains('hidden')) {
                        positionPanel(wrap, panel)
                        closeMenus()
                        render()
                    }
                }

                let viewDate = parseInputToDate(input.value) || new Date()
                let selectedDate = parseInputToDate(input.value)

                function buildMonthItems() {
                    if (!monthItems) return
                    monthItems.innerHTML = ''
                    monthNames.forEach((name, idx) => {
                        const b = document.createElement('button')
                        b.type = 'button'
                        b.className =
                            'w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-700'
                        b.textContent = name
                        b.addEventListener('click', () => {
                            viewDate = new Date(viewDate.getFullYear(), idx, 1)
                            closeMenus()
                            render()
                        })
                        monthItems.appendChild(b)
                    })
                }

                function buildYearItems() {
                    if (!yearItems) return
                    const yNow = new Date().getFullYear()
                    const yStart = yNow - 10
                    const yEnd = yNow + 10
                    yearItems.innerHTML = ''
                    for (let y = yStart; y <= yEnd; y++) {
                        const b = document.createElement('button')
                        b.type = 'button'
                        b.className = 'w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-700'
                        b.textContent = String(y)
                        b.addEventListener('click', () => {
                            viewDate = new Date(y, viewDate.getMonth(), 1)
                            closeMenus()
                            render()
                        })
                        yearItems.appendChild(b)
                    }
                }

                function renderHeader() {
                    const y = viewDate.getFullYear()
                    const m = viewDate.getMonth()
                    if (monthLabel) monthLabel.textContent = monthNames[m]
                    if (yearLabel) yearLabel.textContent = String(y)
                }

                function renderGrid() {
                    const y = viewDate.getFullYear()
                    const m = viewDate.getMonth()
                    const first = new Date(y, m, 1)
                    const last = new Date(y, m + 1, 0)
                    const daysInMonth = last.getDate()
                    const dowMon0 = (first.getDay() + 6) % 7

                    grid.innerHTML = ''

                    for (let i = 0; i < dowMon0; i++) {
                        const empty = document.createElement('div')
                        empty.className = 'h-9'
                        grid.appendChild(empty)
                    }

                    const today = new Date()
                    const todayKey = fmt(today.getFullYear(), today.getMonth(), today.getDate())
                    const selectedKey = selectedDate ? fmt(selectedDate.getFullYear(), selectedDate.getMonth(),
                        selectedDate.getDate()) : null

                    for (let d = 1; d <= daysInMonth; d++) {
                        const key = fmt(y, m, d)
                        const isSelected = selectedKey === key
                        const isToday = todayKey === key

                        const b = document.createElement('button')
                        b.type = 'button'
                        b.textContent = String(d)

                        let cls = 'h-9 rounded-lg text-sm flex items-center justify-center hover:bg-slate-100'
                        if (isToday) cls += ' ring-1 ring-blue-400'
                        if (isSelected) cls += ' bg-[#303481] text-white hover:bg-[#10134B]'
                        else cls += ' text-slate-700'
                        b.className = cls

                        b.addEventListener('click', () => {
                            selectedDate = new Date(y, m, d)
                            input.value = key
                            input.dispatchEvent(new Event('change', {
                                bubbles: true
                            }))
                            closePanel()
                        })

                        grid.appendChild(b)
                    }
                }

                function render() {
                    renderHeader()
                    renderGrid()
                }

                buildMonthItems()
                buildYearItems()
                renderHeader()

                if (btn) btn.addEventListener('click', togglePanel)
                input.addEventListener('focus', openPanel)

                if (monthBtn) monthBtn.addEventListener('click', () => {
                    if (panel.classList.contains('hidden')) openPanel()
                    monthMenu.classList.toggle('hidden')
                    yearMenu.classList.add('hidden')
                })

                if (yearBtn) yearBtn.addEventListener('click', () => {
                    if (panel.classList.contains('hidden')) openPanel()
                    yearMenu.classList.toggle('hidden')
                    monthMenu.classList.add('hidden')
                })

                if (prevBtn) prevBtn.addEventListener('click', () => {
                    viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1)
                    render()
                })

                if (nextBtn) nextBtn.addEventListener('click', () => {
                    viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1)
                    render()
                })

                document.addEventListener('click', (e) => {
                    if (!wrap.contains(e.target)) closePanel()
                })

                input.addEventListener('change', () => {
                    const dt = parseInputToDate(input.value)
                    if (dt) {
                        selectedDate = dt
                        viewDate = new Date(dt.getFullYear(), dt.getMonth(), 1)
                        render()
                    }
                })

                if (monthMenu) monthMenu.addEventListener('click', (e) => e.stopPropagation())
                if (yearMenu) yearMenu.addEventListener('click', (e) => e.stopPropagation())
            }

            // =========================
            // 2) MONTH PICKER (BULAN) - mpWrap
            // =========================
            ;
            (function initMonthPicker() {
                const wrap = document.getElementById('mpWrap')
                const btn = document.getElementById('mpBtn')
                const panel = document.getElementById('mpPanel')
                const grid = document.getElementById('mpGrid')
                const yearBtn = document.getElementById('mpYearBtn')
                const yearLabel = document.getElementById('mpYearLabel')
                const yearMenu = document.getElementById('mpYearMenu')
                const yearItems = document.getElementById('mpYearItems')
                const inputHidden = document.getElementById('monthInput')
                const inputText = document.getElementById('monthInputText')

                if (!wrap || !btn || !panel || !grid || !yearBtn || !yearLabel || !yearMenu || !yearItems || !
                    inputHidden || !inputText) return

                const monthShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov',
                    'Des'
                ]
                const monthLong = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                    'September', 'Oktober', 'November', 'Desember'
                ]

                const now = new Date()
                const init = String(inputHidden.value || '').match(/^(\d{4})-(\d{2})$/)
                let viewYear = init ? Number(init[1]) : now.getFullYear()
                let selMonth = init ? Number(init[2]) - 1 : now.getMonth()

                function pad(n) {
                    return String(n).padStart(2, '0')
                }

                function setText() {
                    inputText.value = `${monthLong[selMonth]} ${viewYear}`
                }

                function buildYearMenu() {
                    const yNow = new Date().getFullYear()
                    const yStart = yNow - 10
                    const yEnd = yNow + 10
                    yearItems.innerHTML = ''
                    for (let y = yStart; y <= yEnd; y++) {
                        const b = document.createElement('button')
                        b.type = 'button'
                        b.className = 'w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-700'
                        b.textContent = String(y)
                        b.addEventListener('click', () => {
                            viewYear = y
                            yearLabel.textContent = String(viewYear)
                            yearMenu.classList.add('hidden')
                            renderGrid()
                        })
                        yearItems.appendChild(b)
                    }
                }

                function renderGrid() {
                    yearLabel.textContent = String(viewYear)
                    grid.innerHTML = ''
                    for (let m = 0; m < 12; m++) {
                        const b = document.createElement('button')
                        b.type = 'button'
                        b.textContent = monthShort[m]
                        b.className = [
                            'h-9 rounded-lg text-sm font-medium flex items-center justify-center',
                            m === selMonth ? 'bg-[#303481] text-white' : 'text-slate-700 hover:bg-slate-100'
                        ].join(' ')
                        b.addEventListener('click', () => {
                            selMonth = m
                            inputHidden.value = `${viewYear}-${pad(selMonth+1)}`
                            setText()
                            inputHidden.dispatchEvent(new Event('change', {
                                bubbles: true
                            }))
                            panel.classList.add('hidden')
                        })
                        grid.appendChild(b)
                    }
                }

                function openPanel() {
                    panel.classList.remove('hidden')
                    positionPanel(wrap, panel)
                    yearMenu.classList.add('hidden')
                    renderGrid()
                }

                btn.addEventListener('click', () => {
                    panel.classList.toggle('hidden')
                    if (!panel.classList.contains('hidden')) {
                        positionPanel(wrap, panel)
                        yearMenu.classList.add('hidden')
                        renderGrid()
                    }
                })

                inputText.addEventListener('focus', openPanel)

                yearBtn.addEventListener('click', (e) => {
                    e.stopPropagation()
                    yearMenu.classList.toggle('hidden')
                })

                document.addEventListener('click', (e) => {
                    if (!wrap.contains(e.target)) {
                        panel.classList.add('hidden')
                        yearMenu.classList.add('hidden')
                    }
                })

                yearMenu.addEventListener('click', (e) => e.stopPropagation())

                buildYearMenu()
                setText()
            })()

            // =========================
            // 3) YEAR PICKER (TAHUN) - ypWrap
            // =========================
            ;
            (function initYearPicker() {
                const wrap = document.getElementById('ypWrap')
                const btn = document.getElementById('ypBtn')
                const panel = document.getElementById('ypPanel')
                const grid = document.getElementById('ypGrid')
                const rangeLabel = document.getElementById('ypRangeLabel')
                const prev = document.getElementById('ypPrev')
                const next = document.getElementById('ypNext')
                const inputHidden = document.getElementById('yearInput')
                const inputText = document.getElementById('yearInputText')

                if (!wrap || !btn || !panel || !grid || !rangeLabel || !prev || !next || !inputHidden || !
                    inputText) return

                const now = new Date()
                let selectedYear = Number(inputHidden.value || now.getFullYear())
                inputHidden.value = String(selectedYear)
                inputText.value = String(selectedYear)

                let endYear = selectedYear
                let startYear = endYear - 11

                function render() {
                    rangeLabel.textContent = `${startYear} - ${endYear}`
                    grid.innerHTML = ''
                    for (let y = startYear; y <= endYear; y++) {
                        const b = document.createElement('button')
                        b.type = 'button'
                        b.textContent = String(y)
                        b.className = [
                            'h-9 rounded-lg text-sm font-medium flex items-center justify-center',
                            y === selectedYear ? 'bg-[#303481] text-white' :
                            'text-slate-700 hover:bg-slate-100'
                        ].join(' ')
                        b.addEventListener('click', () => {
                            selectedYear = y
                            inputHidden.value = String(selectedYear)
                            inputText.value = String(selectedYear)
                            inputHidden.dispatchEvent(new Event('change', {
                                bubbles: true
                            }))
                            panel.classList.add('hidden')
                            render()
                        })
                        grid.appendChild(b)
                    }
                }

                function openPanel() {
                    panel.classList.remove('hidden')
                    positionPanel(wrap, panel)
                    render()
                }

                btn.addEventListener('click', () => {
                    panel.classList.toggle('hidden')
                    if (!panel.classList.contains('hidden')) {
                        positionPanel(wrap, panel)
                        render()
                    }
                })

                inputText.addEventListener('focus', openPanel)

                prev.addEventListener('click', () => {
                    startYear -= 12
                    endYear -= 12
                    render()
                })

                next.addEventListener('click', () => {
                    startYear += 12
                    endYear += 12
                    render()
                })

                document.addEventListener('click', (e) => {
                    if (!wrap.contains(e.target)) panel.classList.add('hidden')
                })

                render()
            })();
            (function initRangePicker() {
                const wrap = document.getElementById('rpWrap')
                const btn = document.getElementById('rpBtn')
                const panel = document.getElementById('rpPanel')
                const rangeText = document.getElementById('rangeText')

                const startHidden = document.getElementById('startDateTime')
                const endHidden = document.getElementById('endDateTime')

                const startBox = document.getElementById('rpStartBox')
                const endBox = document.getElementById('rpEndBox')
                const daysEl = document.getElementById('rpDays')

                const prev = document.getElementById('rpPrev')
                const next = document.getElementById('rpNext')
                const cancel = document.getElementById('rpCancel')
                const apply = document.getElementById('rpApply')

                const gridL = document.getElementById('rpGridL')
                const gridR = document.getElementById('rpGridR')

                const monthLabelL = document.getElementById('rpMonthLabelL')
                const yearLabelL = document.getElementById('rpYearLabelL')
                const monthLabelR = document.getElementById('rpMonthLabelR')
                const yearLabelR = document.getElementById('rpYearLabelR')

                const monthBtnL = document.getElementById('rpMonthBtnL')
                const yearBtnL = document.getElementById('rpYearBtnL')
                const monthBtnR = document.getElementById('rpMonthBtnR')
                const yearBtnR = document.getElementById('rpYearBtnR')

                const monthMenuL = document.getElementById('rpMonthMenuL')
                const yearMenuL = document.getElementById('rpYearMenuL')
                const monthMenuR = document.getElementById('rpMonthMenuR')
                const yearMenuR = document.getElementById('rpYearMenuR')

                const monthItemsL = document.getElementById('rpMonthItemsL')
                const yearItemsL = document.getElementById('rpYearItemsL')
                const monthItemsR = document.getElementById('rpMonthItemsR')
                const yearItemsR = document.getElementById('rpYearItemsR')

                if (!wrap || !btn || !panel || !rangeText || !startHidden || !endHidden || !gridL || !gridR)
                    return

                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                    'September', 'Oktober', 'November', 'Desember'
                ]
                const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt',
                    'Nov', 'Des'
                ]

                function pad(n) {
                    return String(n).padStart(2, '0')
                }

                function key(d) {
                    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`
                }

                function fmtSlash(d) {
                    return `${d.getFullYear()}/${pad(d.getMonth()+1)}/${pad(d.getDate())}`
                }

                function parseDT(v) {
                    const s = String(v || '').trim()
                    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})T/)
                    if (!m) return null
                    const y = Number(m[1]),
                        mo = Number(m[2]) - 1,
                        da = Number(m[3])
                    const dt = new Date(y, mo, da)
                    if (dt.getFullYear() !== y || dt.getMonth() !== mo || dt.getDate() !== da) return null
                    return dt
                }

                function daysDiff(a, b) {
                    const ms = 24 * 60 * 60 * 1000
                    const aa = new Date(a.getFullYear(), a.getMonth(), a.getDate()).getTime()
                    const bb = new Date(b.getFullYear(), b.getMonth(), b.getDate()).getTime()
                    return Math.round((bb - aa) / ms) + 1
                }

                function closeMenus() {
                    monthMenuL.classList.add('hidden')
                    yearMenuL.classList.add('hidden')
                    monthMenuR.classList.add('hidden')
                    yearMenuR.classList.add('hidden')
                }

                let appliedStart = parseDT(startHidden.value) || new Date()
                let appliedEnd = parseDT(endHidden.value) || new Date()

                let tempStart = new Date(appliedStart.getFullYear(), appliedStart.getMonth(), appliedStart
                    .getDate())
                let tempEnd = new Date(appliedEnd.getFullYear(), appliedEnd.getMonth(), appliedEnd.getDate())

                let viewLeft = new Date(tempStart.getFullYear(), tempStart.getMonth(), 1)

                // picking=false: klik berikutnya menjadi START (termasuk klik ketiga/reset)
                // picking=true: klik berikutnya menjadi END
                let picking = false
                let hoverDate = null

                function syncRightFromLeft() {
                    return new Date(viewLeft.getFullYear(), viewLeft.getMonth() + 1, 1)
                }

                function setHeaderLabels() {
                    const lY = viewLeft.getFullYear()
                    const lM = viewLeft.getMonth()
                    const r = syncRightFromLeft()
                    const rY = r.getFullYear()
                    const rM = r.getMonth()
                    monthLabelL.textContent = monthNames[lM]
                    yearLabelL.textContent = String(lY)
                    monthLabelR.textContent = monthNames[rM]
                    yearLabelR.textContent = String(rY)
                }

                function updateTopInfo() {
                    const liveEnd = tempEnd
                    startBox.textContent =
                        `${tempStart.getDate()} ${monthNamesShort[tempStart.getMonth()]} ${tempStart.getFullYear()}`
                    endBox.textContent =
                        `${liveEnd.getDate()} ${monthNamesShort[liveEnd.getMonth()]} ${liveEnd.getFullYear()}`
                    const d = daysDiff(tempStart, liveEnd)
                    daysEl.textContent = `${d} hari`
                    rangeText.value = `${fmtSlash(tempStart)} - ${fmtSlash(liveEnd)}`
                }

                function isBetween(d, a, b) {
                    const t = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime()
                    const ta = new Date(a.getFullYear(), a.getMonth(), a.getDate()).getTime()
                    const tb = new Date(b.getFullYear(), b.getMonth(), b.getDate()).getTime()
                    return t >= ta && t <= tb
                }

                function renderMonthGrid(targetGrid, viewMonth, side) {
                    const y = viewMonth.getFullYear()
                    const m = viewMonth.getMonth()
                    const first = new Date(y, m, 1)
                    const daysInMonth = new Date(y, m + 1, 0).getDate()
                    const dowMon0 = (first.getDay() + 6) % 7

                    targetGrid.innerHTML = ''

                    for (let i = 0; i < dowMon0; i++) {
                        const e = document.createElement('div')
                        e.className = 'h-9'
                        targetGrid.appendChild(e)
                    }

                    for (let d = 1; d <= daysInMonth; d++) {
                        const cur = new Date(y, m, d)
                        const isS = key(cur) === key(tempStart)
                        const isE = key(cur) === key(tempEnd)
                        const isSE = isS && isE
                        const inRange = !isSE && isBetween(cur, tempStart, tempEnd)

                        // ── Outer wrapper: full-width grid cell ────────────────
                        const wrapper = document.createElement('div')
                        wrapper.className = 'relative h-9 flex items-center justify-center cursor-pointer'

                        // ── Strip background behind the circle ─────────────────
                        if (!isSE && (inRange || isS || isE)) {
                            const strip = document.createElement('div')
                            strip.className = 'absolute inset-y-0 bg-[#E9EAFB] pointer-events-none'
                            if (isS)      strip.style.cssText = 'left:50%;right:0'
                            else if (isE) strip.style.cssText = 'left:0;right:50%'
                            else          strip.style.cssText = 'left:0;right:0'
                            wrapper.appendChild(strip)
                        }

                        // ── Inner circle / day number ──────────────────────────
                        const circle = document.createElement('div')
                        circle.textContent = String(d)
                        if (isS || isE) {
                            circle.className = 'relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-sm bg-[#303481] text-white font-semibold'
                        } else if (inRange) {
                            circle.className = 'relative z-10 w-8 h-8 flex items-center justify-center text-sm text-slate-700'
                        } else {
                            circle.className = 'relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-sm text-slate-700 hover:bg-slate-100'
                        }
                        wrapper.appendChild(circle)

                        wrapper.addEventListener('click', (e) => {
                            e.stopPropagation()
                            const clicked = new Date(y, m, d)

                            if (!picking) {
                                // ── Klik Pertama (Start) ──
                                tempStart = clicked
                                tempEnd = clicked
                                picking = true
                            } else {
                                // ── Klik Kedua (End) ──
                                tempEnd = clicked
                                if (tempEnd.getTime() < tempStart.getTime()) {
                                    const t = tempStart
                                    tempStart = tempEnd
                                    tempEnd = t
                                }
                                picking = false
                            }

                            hoverDate = null
                            updateTopInfo()
                            render()
                        })

                        targetGrid.appendChild(wrapper)
                    }
                }

                function buildMonthMenu(itemsEl, onPick) {
                    itemsEl.innerHTML = ''
                    monthNames.forEach((nm, idx) => {
                        const b = document.createElement('button')
                        b.type = 'button'
                        b.className =
                            'w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-700'
                        b.textContent = nm
                        b.addEventListener('click', () => {
                            onPick(idx)
                            closeMenus()
                            render()
                        })
                        itemsEl.appendChild(b)
                    })
                }

                function buildYearMenu(itemsEl, onPick) {
                    const yNow = new Date().getFullYear()
                    const yStart = yNow - 10
                    const yEnd = yNow + 10
                    itemsEl.innerHTML = ''
                    for (let y = yStart; y <= yEnd; y++) {
                        const b = document.createElement('button')
                        b.type = 'button'
                        b.className = 'w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-700'
                        b.textContent = String(y)
                        b.addEventListener('click', () => {
                            onPick(y)
                            closeMenus()
                            render()
                        })
                        itemsEl.appendChild(b)
                    }
                }

                function render() {
                    setHeaderLabels()
                    updateTopInfo()
                    renderMonthGrid(gridL, viewLeft, 'L')
                    renderMonthGrid(gridR, syncRightFromLeft(), 'R')
                }

                function openPanel() {
                    panel.classList.remove('hidden')
                    positionPanel(wrap, panel)
                    closeMenus()
                    render()
                }

                function closePanel() {
                    panel.classList.add('hidden')
                    picking = false
                    hoverDate = null
                    closeMenus()
                }

                btn.addEventListener('click', () => {
                    if (panel.classList.contains('hidden')) {
                        openPanel()
                    } else {
                        closePanel()
                    }
                })

                rangeText.addEventListener('focus', openPanel)

                prev.addEventListener('click', () => {
                    viewLeft = new Date(viewLeft.getFullYear(), viewLeft.getMonth() - 1, 1)
                    render()
                })

                next.addEventListener('click', () => {
                    viewLeft = new Date(viewLeft.getFullYear(), viewLeft.getMonth() + 1, 1)
                    render()
                })

                cancel.addEventListener('click', () => {
                    tempStart = new Date(appliedStart.getFullYear(), appliedStart.getMonth(),
                        appliedStart.getDate())
                    tempEnd = new Date(appliedEnd.getFullYear(), appliedEnd.getMonth(), appliedEnd
                        .getDate())
                    picking = false
                    hoverDate = null
                    viewLeft = new Date(tempStart.getFullYear(), tempStart.getMonth(), 1)
                    closePanel()
                })

                apply.addEventListener('click', () => {
                    appliedStart = new Date(tempStart.getFullYear(), tempStart.getMonth(), tempStart
                        .getDate())
                    appliedEnd = new Date(tempEnd.getFullYear(), tempEnd.getMonth(), tempEnd.getDate())

                    startHidden.value = `${key(appliedStart)}T00:00`
                    endHidden.value = `${key(appliedEnd)}T23:59`

                    startHidden.dispatchEvent(new Event('change', {
                        bubbles: true
                    }))
                    endHidden.dispatchEvent(new Event('change', {
                        bubbles: true
                    }))

                    picking = false
                    hoverDate = null
                    closePanel()
                })

                monthBtnL.addEventListener('click', (e) => {
                    e.stopPropagation();
                    monthMenuL.classList.toggle('hidden');
                    yearMenuL.classList.add('hidden');
                    monthMenuR.classList.add('hidden');
                    yearMenuR.classList.add('hidden')
                })
                yearBtnL.addEventListener('click', (e) => {
                    e.stopPropagation();
                    yearMenuL.classList.toggle('hidden');
                    monthMenuL.classList.add('hidden');
                    monthMenuR.classList.add('hidden');
                    yearMenuR.classList.add('hidden')
                })
                monthBtnR.addEventListener('click', (e) => {
                    e.stopPropagation();
                    monthMenuR.classList.toggle('hidden');
                    yearMenuR.classList.add('hidden');
                    monthMenuL.classList.add('hidden');
                    yearMenuL.classList.add('hidden')
                })
                yearBtnR.addEventListener('click', (e) => {
                    e.stopPropagation();
                    yearMenuR.classList.toggle('hidden');
                    monthMenuR.classList.add('hidden');
                    monthMenuL.classList.add('hidden');
                    yearMenuL.classList.add('hidden')
                })

                buildMonthMenu(monthItemsL, (m) => {
                    viewLeft = new Date(viewLeft.getFullYear(), m, 1)
                })
                buildYearMenu(yearItemsL, (y) => {
                    viewLeft = new Date(y, viewLeft.getMonth(), 1)
                })

                buildMonthMenu(monthItemsR, (m) => {
                    const left = new Date(viewLeft.getFullYear(), viewLeft.getMonth(), 1)
                    const right = new Date(left.getFullYear(), left.getMonth() + 1, 1)
                    const newRight = new Date(right.getFullYear(), m, 1)
                    viewLeft = new Date(newRight.getFullYear(), newRight.getMonth() - 1, 1)
                })
                buildYearMenu(yearItemsR, (y) => {
                    const left = new Date(viewLeft.getFullYear(), viewLeft.getMonth(), 1)
                    const right = new Date(left.getFullYear(), left.getMonth() + 1, 1)
                    const newRight = new Date(y, right.getMonth(), 1)
                    viewLeft = new Date(newRight.getFullYear(), newRight.getMonth() - 1, 1)
                })

                document.addEventListener('click', (e) => {
                    const path = typeof e.composedPath === 'function' ? e.composedPath() : []
                    const isInside = path.length ?
                        (path.includes(wrap) || path.includes(panel)) :
                        (wrap.contains(e.target) || panel.contains(e.target))
                    if (!isInside) {
                        closePanel()
                    }
                })

                monthMenuL.addEventListener('click', (e) => e.stopPropagation())
                yearMenuL.addEventListener('click', (e) => e.stopPropagation())
                monthMenuR.addEventListener('click', (e) => e.stopPropagation())
                yearMenuR.addEventListener('click', (e) => e.stopPropagation())

                render()
                closePanel()
            })()
        })
    </script>
@endpush
