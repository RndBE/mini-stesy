@extends('layouts.app')
@section('title', $title)
@push('head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Sometype+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }

        :root {
            --ink: #14163f;
            --ink-2: #23266b;
            --brand: #303481;
            --analysis-header: #ffffff;
            --analysis-header-text: #303481;
            --deck: #ffffff;
            --deck-2: #f8fafc;
            --deck-edge: #d9deee;
            --deck-field: #f8fafc;
            --deck-text: #111827;
            --deck-muted: #64748b;
            --accent: #4cc9f0;
            --hairline: #e3e5f2;
            --mono: 'Sometype Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        /* Select2 fields inside the control deck */
        .control-deck .select2-container--default .select2-selection--single {
            background: var(--deck-field);
            border: 1px solid var(--deck-edge);
            border-radius: 8px;
            height: 38px;
            padding: 0 8px;
            display: flex;
            align-items: center;
            transition: border-color .15s, box-shadow .15s;
        }

        .control-deck .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
            top: 0;
            right: 8px;
        }

        .control-deck .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--deck-muted) transparent transparent transparent;
        }

        .control-deck .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent var(--deck-muted) transparent;
        }

        .control-deck .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding: 0 4px;
            color: var(--deck-text);
            font-family: var(--mono);
            font-size: 0.8rem;
        }

        .control-deck .select2-container--default.select2-container--focus .select2-selection--single,
        .control-deck .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, .24);
            outline: none;
        }

        /* Dropdown is appended to <body>: light paper panel */
        .select2-dropdown {
            border: 1px solid #c9cce6;
            border-radius: 10px;
            box-shadow: 0 12px 32px -12px rgba(20, 22, 63, .35);
            font-size: 0.875rem;
            overflow: hidden;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #c9cce6;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 0.8rem;
            outline: none;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, .25);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #e0f2fe;
            color: #0f172a;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #eff6ff;
            color: var(--brand);
            font-weight: 600;
        }
    </style>
    <style>
        /* ============ Analisa — instrument console ============ */
        @keyframes rise {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: none; }
        }

        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes led-ping {
            0% { transform: scale(.5); opacity: .9; }
            80%, 100% { transform: scale(1.5); opacity: 0; }
        }

        .analysis-container {
            width: 100%;
        }

        /* — Station header — */
        .station-bar {
            animation: fade-in .45s ease-out both;
        }

        .station-back {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: var(--brand);
            transition: background .15s;
        }

        .station-back:hover {
            background: #eef0fb;
        }

        .led {
            width: 11px;
            height: 11px;
            border-radius: 9999px;
            position: relative;
        }

        .led-on {
            background: #10b981;
        }

        .led-on::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 9999px;
            border: 2px solid rgba(16, 185, 129, .45);
            animation: led-ping 1.8s ease-out infinite;
        }

        .led-off {
            background: #f43f5e;
            box-shadow: 0 0 0 4px rgba(244, 63, 94, .12);
        }

        .station-eyebrow {
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #7d81b8;
        }

        .station-name {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -.01em;
            line-height: 1.25;
            color: var(--ink);
        }

        .station-sub {
            font-size: .75rem;
            font-weight: 600;
        }

        /* — Console buttons (header actions) — */
        .station-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-shrink: 0;
        }

        .station-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 9px 13px;
            border-radius: 11px;
            border: 1px solid #d5daef;
            background: #ffffff;
            color: var(--brand);
            font-size: .82rem;
            font-weight: 700;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(20, 22, 63, .05);
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
            white-space: nowrap;
        }

        .station-action:hover {
            background: #f8fafc;
            border-color: #bfc6e8;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -16px rgba(20, 22, 63, .45);
        }

        .station-action svg,
        .station-action img {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
        }

        .station-action-primary {
            background: var(--ink);
            border-color: var(--ink);
            color: #ffffff;
            box-shadow: 0 10px 24px -18px rgba(20, 22, 63, .8);
        }

        .station-action-primary:hover {
            background: var(--ink);
            border-color: var(--ink);
            color: #ffffff;
        }

        .station-action-outline {
            background: #ffffff;
            color: var(--brand);
        }

        .station-action-warning {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #b45309;
        }

        .station-action-warning:hover {
            background: #ffedd5;
            border-color: #fdba74;
        }

        .station-action-active,
        .station-action-active:hover {
            background: var(--ink);
            border-color: var(--ink);
            color: #ffffff;
        }

        @media (min-width: 768px) {
            .analysis-filter-toggle {
                display: none !important;
            }
        }

        /* — Ruler strip — */
        .ruler-strip {
            height: 12px;
            background-image:
                repeating-linear-gradient(90deg, #b9bdde 0 1.5px, transparent 1.5px 10px),
                repeating-linear-gradient(90deg, #888cc0 0 1.5px, transparent 1.5px 50px);
            background-size: 100% 6px, 100% 12px;
            background-repeat: no-repeat;
            background-position: left bottom;
            opacity: .65;
        }

        /* — Control deck (filter sidebar) — */
        .control-deck {
            background: #fff;
            border: 1px solid var(--deck-edge);
            border-radius: 14px;
            box-shadow: 0 12px 28px -22px rgba(20, 22, 63, .35);
            animation: rise .45s .07s ease-out both;
        }

        .deck-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-bottom: 1px dashed #d9deee;
            font-family: var(--mono);
            font-size: 10.5px;
            letter-spacing: .22em;
            text-transform: uppercase;
            color: var(--deck-muted);
        }

        .deck-dot {
            width: 7px;
            height: 7px;
            border-radius: 2px;
            background: var(--accent);
            box-shadow: 0 0 8px rgba(76, 201, 240, .55);
        }

        .deck-body {
            padding: 16px;
        }

        .deck-label {
            display: block;
            font-family: var(--mono);
            font-size: 10.5px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--deck-muted);
            margin-bottom: 7px;
        }

        .deck-sep {
            border-top: 1px dashed #d9deee;
            margin: 18px 0 14px;
        }

        .range-input-group {
            margin-bottom: 0;
        }

        /* Control deck inputs override Tailwind utilities on the inputs */
        .calendar-input {
            width: 100%;
        }

        .control-deck .calendar-input {
            background: var(--deck-field) !important;
            border: 1px solid var(--deck-edge) !important;
            color: var(--deck-text) !important;
            border-radius: 8px;
            font-family: var(--mono);
            font-size: .8rem;
        }

        .control-deck .calendar-input::placeholder {
            color: #94a3b8;
        }

        .control-deck .calendar-input:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(76, 201, 240, .24) !important;
            outline: none;
        }

        .control-deck #dpBtn,
        .control-deck #mpBtn,
        .control-deck #ypBtn,
        .control-deck #rpBtn {
            color: var(--deck-muted);
        }

        /* — Segmented range control — */
        .seg-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
            margin-bottom: 18px;
        }

        .seg {
            position: relative;
            display: block;
            cursor: pointer;
        }

        .seg input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .seg span {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
            border: 1px solid var(--deck-edge);
            border-radius: 8px;
            font-family: var(--mono);
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--deck-muted);
            background: #fff;
            transition: all .15s;
        }

        .seg:hover span {
            background: var(--deck-field);
            border-color: #9bdff3;
            color: var(--deck-text);
        }

        .seg input:checked + span {
            background: #e0f7ff;
            border-color: var(--accent);
            color: #075985;
            box-shadow: 0 8px 18px -14px rgba(14, 116, 144, .55);
        }

        .seg input:focus-visible + span {
            box-shadow: 0 0 0 3px rgba(76, 201, 240, .25);
        }

        .analysis-mode-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 4px;
            border: 1px solid var(--deck-edge);
            border-radius: 10px;
            background: var(--deck-field);
        }

        .analysis-mode-btn {
            border: 0;
            border-radius: 7px;
            padding: 8px 6px;
            background: transparent;
            color: var(--deck-muted);
            font-family: var(--mono);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            transition: background .15s ease, color .15s ease, box-shadow .15s ease;
        }

        .analysis-mode-btn.is-active {
            background: #ffffff;
            color: var(--brand);
            box-shadow: 0 8px 18px -16px rgba(20, 22, 63, .5);
        }

        .multi-parameter-checklist {
            max-height: 16rem;
            overflow: auto;
            border: 1px solid var(--deck-edge);
            border-radius: 10px;
            background: #ffffff;
        }

        .multi-param-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-bottom: 1px solid #eef0f8;
            cursor: pointer;
            transition: background .15s;
        }

        .multi-param-option:last-child {
            border-bottom: 0;
        }

        .multi-param-option:hover {
            background: #f8fafc;
        }

        .multi-param-checkbox {
            width: 15px;
            height: 15px;
            border-radius: 4px;
            color: var(--brand);
        }

        .multi-param-text {
            min-width: 0;
            flex: 1;
            color: var(--deck-text);
            font-family: var(--mono);
            font-size: .78rem;
            line-height: 1.25;
        }

        .multi-param-unit {
            color: #94a3b8;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .multi-param-action {
            border: 0;
            background: transparent;
            color: var(--brand);
            font-size: .72rem;
            font-weight: 700;
            padding: 0;
        }

        .multi-chart-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-weight: 600;
            text-align: center;
            pointer-events: none;
        }

        @keyframes multichart-shimmer {
            0% { background-position: 120% 0; }
            100% { background-position: -120% 0; }
        }

        .multi-loading-overlay {
            position: absolute;
            inset: 0;
            display: none;
            align-items: stretch;
            justify-content: stretch;
            padding: 18px;
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(2px);
            z-index: 5;
            pointer-events: none;
        }

        .multi-loading-overlay.is-active {
            display: flex;
        }

        .multi-loading-card {
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(217, 222, 238, .9);
            background: rgba(248, 250, 252, .72);
            box-shadow: 0 18px 38px -28px rgba(20, 22, 63, .5);
            overflow: hidden;
        }

        .multi-loading-chart-grid {
            height: 100%;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 16px;
            padding: 22px;
        }

        .multi-loading-line {
            background: linear-gradient(90deg, #dbeafe 0%, #ffffff 38%, #38bdf8 52%, #ffffff 66%, #dbeafe 100%);
            background-size: 260% 100%;
            animation: multichart-shimmer .85s linear infinite;
            box-shadow: 0 10px 26px -18px rgba(15, 163, 209, .75);
        }

        .multi-loading-table {
            flex-direction: column;
            gap: 10px;
        }

        .multi-loading-table .multi-loading-card {
            padding: 16px;
        }

        .multi-loading-line {
            height: 14px;
            border-radius: 999px;
            margin-bottom: 12px;
        }

        .multi-loading-chart-lines .multi-loading-line {
            height: 12px;
            margin-bottom: 0;
            transform-origin: left center;
        }

        #analysisShell[data-analysis-mode="single"] [data-multichart-checklist],
        #analysisShell[data-analysis-mode="single"] [data-multichart-panel],
        #analysisShell[data-analysis-mode="multi"] #singleParameterField,
        #analysisShell[data-analysis-mode="multi"] #singleAnalysisActions,
        #analysisShell[data-analysis-mode="multi"] #singleAnalysisPanel {
            display: none !important;
        }

        #analysisShell[data-analysis-mode="multi"] [data-multichart-checklist],
        #analysisShell[data-analysis-mode="multi"] [data-multichart-panel] {
            display: block !important;
        }

        /* — Deck action buttons — */
        .btn-success {
            width: 100%;
            padding: 10px;
            background: #10b981;
            color: #04251a;
            border: none;
            border-radius: 9px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .15s;
        }

        .btn-success:hover {
            background: #34d399;
        }

        .btn-success-soft {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #bbf7d0;
            box-shadow: none;
        }

        .btn-success-soft:hover {
            background: #d1fae5;
            border-color: #86efac;
        }

        .btn-outline {
            width: 100%;
            padding: 10px;
            background: #fff;
            color: var(--brand);
            border: 1px solid var(--deck-edge);
            border-radius: 9px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .15s, border-color .15s;
        }

        .btn-outline:hover {
            background: #eff6ff;
            border-color: #9bdff3;
        }

        /* — Measurement panel (chart + table card) — */
        .panel-card {
            background: #fff;
            border: 1px solid var(--hairline);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(20, 22, 63, .04), 0 16px 40px -32px rgba(20, 22, 63, .25);
            animation: rise .45s .14s ease-out both;
        }

        .chart-section {
            margin-bottom: 0;
        }

        .panel-head {
            padding-bottom: 10px;
            border-bottom: 1px dashed #d9dcef;
        }

        .chart-panel-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .chart-panel-heading {
            min-width: 0;
        }

        .chart-export-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 36px;
            padding: 8px 13px;
            border-radius: 9px;
            border: 1px solid #d7dbef;
            background: #ffffff;
            color: #303481;
            font-weight: 700;
            font-size: 12px;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(20, 22, 63, .05);
            transition: background .18s ease, border-color .18s ease, transform .18s ease;
            white-space: nowrap;
        }

        .chart-export-btn:hover {
            background: #f8fafc;
            border-color: #bfc6e8;
            transform: translateY(-1px);
        }

        .chart-export-btn svg {
            width: 15px;
            height: 15px;
            flex: 0 0 auto;
        }

        .panel-eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: #9094c5;
            margin-bottom: 3px;
        }

        .panel-eyebrow::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 2px;
            background: var(--accent);
        }

        .chart-title {
            text-align: left;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
        }

        .table-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
        }

        .chart-wrapper {
            position: relative;
            background: #fff;
            padding: 0;
            border-radius: 8px;
        }

        #dataChart {
            display: block;
        }

        #rainfallCardTotal,
        #awqrParamValue {
            font-family: var(--mono);
            letter-spacing: -.02em;
        }

        /* — Data table — */
        .data-table-section {
            background: #fff;
        }

        .table-shell {
            width: 100%;
            overflow: hidden;
            border: 1px solid var(--hairline);
            border-radius: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            background: var(--analysis-header);
            color: var(--analysis-header-text);
            border-bottom: 1px solid var(--hairline);
            font-family: var(--mono);
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            text-align: left;
            padding: 11px 14px;
        }

        .data-table th:not(:first-child) {
            text-align: right;
        }

        .data-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #eef0f8;
            color: #41446b;
            font-family: var(--mono);
            font-size: .8rem;
            font-variant-numeric: tabular-nums;
        }

        .data-table td:not(:first-child) {
            text-align: right;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background: #f4f6ff;
        }

        /* — Info slide-over & modal reskins (ID-scoped to win the cascade) — */
        #infoPanel .info-panel-header {
            background: var(--analysis-header);
            color: var(--analysis-header-text);
            border-bottom: 1px solid var(--hairline);
        }

        #infoPanel .info-panel-close {
            color: var(--analysis-header-text);
        }

        #infoPanel .info-label {
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: .14em;
            color: #9094c5;
        }

        #infoPanel .info-value {
            font-family: var(--mono);
            font-size: .875rem;
            color: var(--ink);
        }

        .doc-modal .doc-modal-header {
            background: var(--analysis-header);
            color: var(--analysis-header-text);
            border-bottom: 1px solid var(--hairline);
        }

        .doc-modal .doc-modal-close {
            color: var(--analysis-header-text);
        }

        .doc-modal .doc-modal-content {
            border-radius: 14px;
        }

        .doc-modal .photo-nav {
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 2px 10px rgba(20, 22, 63, .25);
        }

        .pump-modal-shell[role="dialog"].fixed {
            z-index: 2100 !important;
        }
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
                                : asset('storage/' . ltrim($firstPhotoRaw, '/'));
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
                                            : asset('storage/' . ltrim($photoRaw, '/'));
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
    <div class="station-bar flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="window.location.href='{{ route('peta.lokasi') }}'"
                class="station-back flex-shrink-0" aria-label="Kembali">
                <svg width="8" height="20" viewBox="0 0 10 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.5 18.5L1 9.75L8.5 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            <span class="led {{ $status === 'online' ? 'led-on' : 'led-off' }} flex-shrink-0"></span>
            <div class="min-w-0">
                <div class="station-eyebrow">
                    {{ strtoupper($logger->kategori_logger ?? $logger->kategori?->nama_kategori ?? 'Logger') }} &middot; ID {{ $logger->id_logger }}
                </div>
                <div class="station-name truncate">{{ $logger->nama_pos }}</div>
                <div class="station-sub {{ $status === 'online' ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $status === 'online' ? 'Koneksi Terhubung' : 'Koneksi Terputus' }}
                </div>
            </div>
        </div>

        <div class="station-actions">
            <button @click="filterOpen = !filterOpen" :class="filterOpen ? 'station-action-active' : ''"
                class="station-action station-action-outline analysis-filter-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                <span>Filter</span>
            </button>
            <button class="station-action station-action-primary" onclick="openInfoPanel()">
                <svg class="sm:me-2" width="18" height="18" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 18.3334C14.6024 18.3334 18.3334 14.6025 18.3334 10.0001C18.3334 5.39771 14.6024 1.66675 10 1.66675C5.39765 1.66675 1.66669 5.39771 1.66669 10.0001C1.66669 14.6025 5.39765 18.3334 10 18.3334Z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10 13.3334V10.0001M10 6.66675H10.0083" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="hidden sm:inline">Informasi</span>
            </button>

            @if($logger->jiat?->has_pump && strtolower($logger->kategori->nama_kategori ?? '') === 'awlr')
<div x-data="pumpControlApp()">
                <button @click="openPumpModal()"
                    class="station-action station-action-warning">
                    <img src="{{ asset('icons/pump.svg') }}"
                         class="h-5 w-5 sm:me-2 flex-shrink-0"
                         style="filter: invert(52%) sepia(60%) saturate(700%) hue-rotate(5deg) brightness(95%) contrast(90%);"
                         alt="Pump Icon" />
	                    <span class="hidden sm:inline font-semibold">Kontrol Pompa</span>
	                </button>
<template x-teleport="body">
<div x-show="showPumpModal" x-cloak class="pump-modal-shell fixed inset-0" role="dialog" aria-modal="true" style="display:none;">
<div x-show="showPumpModal"
	                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
	                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
	                        class="absolute inset-0 z-0 bg-slate-900/40 transition-opacity" @click="closePumpModal()"></div>

	                    <div class="absolute inset-0 z-10 flex items-center justify-center overflow-y-auto p-4" @click="closePumpModal()">
                        <div x-show="showPumpModal"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                            class="relative w-full max-w-2xl overflow-hidden rounded-2xl bg-white text-left shadow-xl"
                            @click.stop>
<div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-2 text-amber-700">
                                        <img src="{{ asset('icons/pump.svg') }}"
                                             class="h-6 w-6"
                                             style="filter: invert(52%) sepia(60%) saturate(700%) hue-rotate(5deg) brightness(95%) contrast(90%);"
                                             alt="Pump Icon" />
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900">Kontrol Pompa Air</h3>
                                    </div>
                                </div>
                                <button @click="closePumpModal()"
                                    :disabled="pumpRunning"
                                    class="transition-colors"
                                    :class="pumpRunning ? 'text-slate-300 cursor-not-allowed' : 'text-slate-500 hover:text-slate-700'">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
<div class="space-y-2 px-6 py-5">
<div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Nama Pos</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $logger->lokasi->nama_lokasi ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">ID Logger</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $logger->id_logger }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Nama Logger</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $logger->nama_logger }}</p>
                                    </div>
                                </div>
<div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">Status Pompa</p>
                                        </div>
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition-all duration-300"
                                            :class="pumpChecking || pumpRunning ? 'bg-amber-100 text-amber-700 animate-pulse' : pumpState === 'on' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'"
                                            x-text="pumpChecking ? '⟳ Mengecek status...' : pumpRunning ? (pumpTargetState === 'on' ? '⚙ Starting...' : '⚙ Stopping...') : pumpState === 'on' ? 'Pump ON' : 'Pump OFF'"></span>
                                    </div>
<div class="mt-6 flex flex-col items-center justify-between gap-6 sm:flex-row sm:items-stretch">
<div class="relative flex w-full flex-col items-center justify-center rounded-xl bg-gradient-to-b from-[#0b132b] to-[#0a2342] p-4 sm:w-1/3 shadow-[inset_0_4px_20px_rgba(0,0,0,0.8)] overflow-hidden">
<div class="absolute inset-x-0 bottom-0 transition-all duration-[2500ms] ease-in-out border-t-2"
                                                :class="pumpState === 'on' && !pumpRunning ? 'top-[20%] bg-[#0077b6]/30 border-[#00b4d8]/60 shadow-[0_-5px_20px_rgba(0,180,216,0.2)]' : pumpRunning ? 'top-[18%] bg-amber-900/20 border-amber-400/40 shadow-[0_-5px_20px_rgba(245,158,11,0.15)]' : 'top-[25%] bg-slate-800/60 border-slate-600/50 shadow-none'">
<div class="absolute inset-0 overflow-hidden transition-opacity duration-[1500ms] ease-in-out pointer-events-none"
                                                     :class="pumpState === 'on' && !pumpRunning ? 'opacity-100' : 'opacity-0'">
                                                    <div class="absolute bottom-4 left-[20%] w-1.5 h-1.5 rounded-full bg-cyan-200 animate-[ping_2s_linear_infinite]"></div>
                                                    <div class="absolute bottom-10 right-[30%] w-2 h-2 rounded-full bg-cyan-300 animate-[ping_2.5s_linear_infinite]" style="animation-delay:0.5s"></div>
                                                    <div class="absolute bottom-20 left-[40%] w-1 h-1 rounded-full bg-cyan-100 animate-[ping_1.5s_linear_infinite]" style="animation-delay:1.2s"></div>
                                                </div>
<div class="absolute inset-0 overflow-hidden transition-opacity duration-[800ms] ease-in-out pointer-events-none"
                                                     :class="pumpRunning ? 'opacity-100' : 'opacity-0'">
                                                    <div class="absolute bottom-3 left-[25%] w-1 h-1 rounded-full bg-amber-300 animate-[ping_3s_linear_infinite]"></div>
                                                    <div class="absolute bottom-8 right-[35%] w-1.5 h-1.5 rounded-full bg-amber-200 animate-[ping_4s_linear_infinite]" style="animation-delay:1s"></div>
                                                    <div class="absolute bottom-16 left-[45%] w-1 h-1 rounded-full bg-yellow-200 animate-[ping_3.5s_linear_infinite]" style="animation-delay:2s"></div>
                                                </div>
                                            </div>
                                            <div class="relative flex h-40 w-28 items-end justify-center z-10 mt-2">
                                                <div class="absolute left-2 top-0 bottom-[1.8rem] w-3 flex flex-col items-center z-0">
                                                    <div class="w-2.5 h-full bg-gradient-to-r from-slate-800 via-slate-600 to-slate-800 border-x border-slate-900 shadow-md"></div>
<div class="absolute bottom-0 w-1.5 transition-all duration-[1500ms] ease-in-out origin-bottom"
                                                         :class="pumpRunning ? 'h-[50%] opacity-80 bg-amber-400 shadow-[0_0_6px_#f59e0b]' : pumpState === 'on' ? 'h-full opacity-100 bg-cyan-300 shadow-[0_0_8px_#22d3ee]' : 'h-0 opacity-0 bg-cyan-300 shadow-none'"></div>
                                                </div>
                                                <div class="absolute left-1.5 bottom-[1.5rem] w-4 h-3 bg-gradient-to-r from-slate-700 to-slate-600 border border-slate-900 rounded-sm z-10 transition-all duration-[1000ms] ease-in-out"
                                                     :class="pumpRunning ? 'shadow-[0_0_8px_#f59e0b]' : pumpState === 'on' ? 'shadow-[0_0_8px_#00b4d8]' : 'shadow-[0_2px_5px_rgba(0,0,0,0.5)]'">
                                                    <div class="w-full h-0.5 mt-0.5 transition-colors duration-[1000ms] ease-in-out"
                                                         :class="pumpRunning ? 'bg-amber-400' : pumpState === 'on' ? 'bg-[#00f8ff]' : 'bg-slate-500'"></div>
                                                </div>
                                                <div class="absolute left-2.5 bottom-1 h-5 w-6 border-l-[10px] border-b-[10px] border-slate-700 border-opacity-90 rounded-bl-xl z-0 shadow-lg"></div>
                                                <div class="absolute left-[8px] bottom-[2px] h-6 w-7 border-l-2 border-b-2 border-slate-900 rounded-bl-xl z-20 pointer-events-none"></div>
                                                <div class="relative w-[3.5rem] h-[8.5rem] flex flex-col items-center bg-gradient-to-r from-[#2a3a4c] via-[#4a5f78] to-[#1e2a38] border-x-2 border-[#0d1622] rounded-sm shadow-2xl z-10">
                                                    <div class="w-[85%] h-6 border-b-2 border-[#0d1622] overflow-hidden relative">
<div class="absolute inset-0 bg-gradient-to-r from-[#1c2836] via-[#3a4f6b] to-[#1c2836]"></div>
<div class="absolute inset-0 opacity-60 mix-blend-multiply"
                                                             :class="pumpState === 'on' && !pumpRunning ? 'animate-[rotor-spin_0.2s_linear_infinite]' : ''"
                                                             style="background: repeating-linear-gradient(90deg, #000 0px, #000 6px, transparent 6px, transparent 15px); background-size: 15px 100%;">
                                                        </div>
<div class="absolute inset-0 bg-gradient-to-r from-[#000]/90 via-transparent to-[#000]/90 pointer-events-none"></div>
                                                    </div>
<div class="w-[105%] h-1.5 rounded-full transition-colors duration-[800ms] ease-in-out z-20 border"
                                                         :class="pumpRunning ? 'border-transparent shadow-[0_0_12px_#f59e0b] animate-[pump-flicker_0.7s_ease-in-out_infinite] bg-amber-400' : pumpState === 'on' ? 'bg-[#00f8ff] border-transparent shadow-[0_0_15px_#00f8ff]' : 'bg-slate-700 border-slate-800 shadow-none'"></div>
                                                    <div class="w-full flex-grow flex items-center justify-center relative shadow-[inset_4px_0_10px_rgba(255,255,255,0.1),inset_-6px_0_15px_rgba(0,0,0,0.6)]">
                                                        <div class="w-5 h-[4.5rem] bg-[#070b12] rounded-full border border-slate-800 shadow-[inset_0_4px_10px_rgba(0,0,0,0.8)] overflow-hidden flex flex-col justify-end p-[1.5px]">
<div class="relative w-full rounded-full transition-all duration-[1500ms] ease-in-out overflow-hidden flex flex-col justify-end"
                                                                 :class="pumpRunning ? 'h-[45%]' : pumpState === 'on' ? 'h-[85%] shadow-[0_0_10px_#00f8ff]' : 'h-[15%] shadow-none'">
<div class="absolute inset-0 w-full h-full bg-gradient-to-b from-[#00f8ff] to-[#0077b6] transition-opacity duration-[1000ms] ease-in-out"
                                                                     :class="pumpState === 'on' && !pumpRunning ? 'opacity-100 animate-[pulse_1.5s_infinite]' : 'opacity-0'"></div>
<div class="absolute inset-0 w-full h-full bg-gradient-to-b from-amber-300 to-amber-600 transition-opacity duration-[800ms] ease-in-out"
                                                                     :class="pumpRunning ? 'opacity-100 animate-[pulse_0.8s_infinite]' : 'opacity-0'"></div>
<div class="absolute inset-0 w-full h-full bg-slate-600 transition-opacity duration-[1000ms] ease-in-out"
                                                                     :class="!pumpRunning && pumpState !== 'on' ? 'opacity-100' : 'opacity-0'"></div>
                                                            </div>
                                                        </div>
                                                    </div>
<div class="w-[105%] h-1.5 rounded-full transition-colors duration-[800ms] ease-in-out z-20 border"
                                                         :class="pumpRunning ? 'border-transparent shadow-[0_0_12px_#f59e0b] animate-[pump-flicker_0.7s_ease-in-out_infinite_0.35s] bg-amber-400' : pumpState === 'on' ? 'bg-[#00f8ff] border-transparent shadow-[0_0_15px_#00f8ff]' : 'bg-slate-700 border-slate-800 shadow-none'"></div>
                                                    <div class="w-[90%] h-5 bg-gradient-to-r from-[#1c2836] to-[#2d3f54] border-x border-b border-[#0d1622] rounded-b-md flex flex-col justify-between items-center shadow-lg pb-1">
                                                        <div class="w-full h-1.5 border-b border-slate-800 opacity-50"></div>
                                                        <div class="flex w-[70%] justify-between">
                                                            <div class="w-1.5 h-2 bg-[#070b12] rounded-sm"></div>
                                                            <div class="w-1.5 h-2 bg-[#070b12] rounded-sm"></div>
                                                            <div class="w-1.5 h-2 bg-[#070b12] rounded-sm"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
<div class="absolute top-3 right-3 rounded bg-[#0b132b]/80 backdrop-blur-sm border px-2 py-1 flex items-center justify-center z-40 transition-all duration-500"
                                                 :class="pumpRunning ? 'border-amber-700/60' : 'border-cyan-900/50'">
                                                <div class="w-1.5 h-1.5 rounded-full mr-1.5 transition-all duration-500"
                                                     :class="pumpRunning ? 'bg-amber-400 shadow-[0_0_5px_#f59e0b] animate-[pump-flicker_0.7s_ease-in-out_infinite]' : pumpState === 'on' ? 'bg-[#00f8ff] shadow-[0_0_5px_#00f8ff] animate-pulse' : 'bg-slate-500'"></div>
                                                <span class="text-[9px] font-bold tracking-widest uppercase transition-colors duration-500 mt-0.5"
                                                      :class="pumpRunning ? 'text-amber-300' : pumpState === 'on' ? 'text-cyan-300' : 'text-slate-400'"
                                                      x-text="pumpRunning ? (pumpTargetState === 'on' ? 'Starting' : 'Stopping') : pumpState === 'on' ? 'Running' : 'Standby'"></span>
                                            </div>
<div class="absolute inset-0 rounded-xl pointer-events-none transition-opacity duration-500"
                                                 :class="pumpRunning ? 'animate-[pump-flash_1.8s_ease-in-out_infinite] bg-amber-400/5' : 'opacity-0'"></div>
                                        </div>
<div class="flex w-full flex-col justify-center space-y-4 sm:w-2/3">
                                            <button type="button" @click="runPumpAction('on')"
                                                class="group relative flex items-center justify-between rounded-xl bg-emerald-600 px-5 py-4 shadow-md transition-all hover:bg-emerald-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed"
                                                :disabled="!pumpStatusReady || pumpRunning || pumpState === 'on'">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-3 w-3 rounded-full bg-emerald-300"
                                                        :class="pumpRunning && pumpTargetState === 'on' ? 'animate-pulse' : 'group-hover:animate-pulse'"></span>
                                                    <div class="text-left">
                                                        <p class="text-sm font-bold text-white uppercase tracking-wider">Start Pump</p>
                                                        <p class="mt-0.5 text-xs text-emerald-100"
                                                            x-text="pumpRunning && pumpTargetState === 'on' ? 'Sending command...' : 'Kirim perintah untuk menyalakan'"></p>
                                                    </div>
                                                </div>
<svg x-show="!(pumpRunning && pumpTargetState === 'on')" class="h-6 w-6 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
<svg x-show="pumpRunning && pumpTargetState === 'on'" style="display: none;" class="h-6 w-6 animate-spin text-emerald-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </button>

                                            <button type="button" @click="runPumpAction('off')"
                                                class="group relative flex items-center justify-between rounded-xl bg-red-600 px-5 py-4 shadow-md transition-all hover:bg-red-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed"
                                                :disabled="!pumpStatusReady || pumpRunning || pumpState === 'off'">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-3 w-3 rounded-full bg-red-300"
                                                        :class="pumpRunning && pumpTargetState === 'off' ? 'animate-pulse' : ''"></span>
                                                    <div class="text-left">
                                                        <p class="text-sm font-bold text-white uppercase tracking-wider">Stop Pump</p>
                                                        <p class="mt-0.5 text-xs text-red-100"
                                                            x-text="pumpRunning && pumpTargetState === 'off' ? 'Sending command...' : 'Hentikan seketika mekanisme pompa'"></p>
                                                    </div>
                                                </div>
<svg x-show="!(pumpRunning && pumpTargetState === 'off')" class="h-6 w-6 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z" />
                                                </svg>
<svg x-show="pumpRunning && pumpTargetState === 'off'" style="display: none;" class="h-6 w-6 animate-spin text-red-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
<div x-show="pumpWorkflowVisible" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="mb-3 flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">Progress</p>
                                                <p class="mt-1 text-xs text-slate-500"
                                                    x-text="pumpRunning ? 'Menjalankan urutan simulasi koneksi...' : 'Seluruh tahap operasional pompa telah selesai.'"></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Overall Progress</p>
                                                <p class="mt-1 text-sm font-semibold text-slate-700" x-text="`${pumpPercent()}%`"></p>
                                            </div>
                                        </div>
                                        <div class="mb-4 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" :style="`width: ${pumpPercent()}%`"></div>
                                        </div>
                                        <div class="space-y-2">
                                            <template x-for="(step, index) in pumpSteps" :key="step.key">
                                                <div class="rounded-xl border p-3 transition"
                                                    :class="step.status === 'done' ? 'border-emerald-200 bg-emerald-50/70' : step.status === 'active' ? 'border-emerald-200 bg-emerald-50/40' : step.status === 'error' ? 'border-red-200 bg-red-50/70' : 'border-slate-200 bg-white/70 opacity-80'">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl"
                                                            :class="step.status === 'done' ? 'bg-emerald-100' : step.status === 'active' ? 'bg-emerald-100/80' : step.status === 'error' ? 'bg-red-100' : 'bg-slate-100'">
                                                            <template x-if="step.status === 'done'">
                                                                <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.42l2.293 2.295 6.493-6.495a1 1 0 011.415 0z" clip-rule="evenodd" />
                                                                </svg>
                                                            </template>
                                                            <template x-if="step.status === 'active'">
                                                                <span class="inline-block h-4 w-4 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin"></span>
                                                            </template>
                                                            <template x-if="step.status === 'error'">
                                                                <svg class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                </svg>
                                                            </template>
                                                            <template x-if="step.status === 'pending'">
                                                                <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                                            </template>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm font-semibold" :class="step.status === 'pending' ? 'text-slate-500' : step.status === 'error' ? 'text-red-700' : 'text-slate-900'" x-text="step.title"></p>
                                                            <p class="mt-0.5 text-xs" :class="step.status === 'pending' ? 'text-slate-400' : step.status === 'error' ? 'text-red-500' : 'text-slate-500'" x-text="step.subtitle"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
	                    </div>
	                </div>
	                </template>
	            </div>
	            @endif
            <button class="station-action station-action-outline" onclick="openDocModal()">
                <svg width="18" height="16" viewBox="0 0 20 18" class="sm:me-2" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.91667 13.5833C11.9417 13.5833 13.5833 11.9417 13.5833 9.91667C13.5833 7.89162 11.9417 6.25 9.91667 6.25C7.89162 6.25 6.25 7.89162 6.25 9.91667C6.25 11.9417 7.89162 13.5833 9.91667 13.5833Z"
                        stroke="currentColor" stroke-width="1.5" />
                    <path
                        d="M7.87983 17.25H11.9535C14.8144 17.25 16.2453 17.25 17.2729 16.5763C17.7164 16.2858 18.0983 15.9108 18.3967 15.4726C19.0833 14.4643 19.0833 13.059 19.0833 10.2504C19.0833 7.44171 19.0833 6.03646 18.3967 5.02813C18.0983 4.58995 17.7164 4.21491 17.2729 3.92446C16.6129 3.49088 15.7861 3.33596 14.5202 3.28096C13.9161 3.28096 13.3963 2.83179 13.2781 2.24971C13.1877 1.82334 12.9529 1.44124 12.6134 1.168C12.2738 0.894753 11.8503 0.747117 11.4145 0.750043H8.41883C7.51317 0.750043 6.73308 1.37796 6.55525 2.24971C6.437 2.83179 5.91725 3.28096 5.31317 3.28096C4.04817 3.33596 3.22133 3.49179 2.56042 3.92446C2.11724 4.21501 1.73567 4.59004 1.4375 5.02813C0.75 6.03646 0.75 7.44079 0.75 10.2504C0.75 13.06 0.75 14.4634 1.43658 15.4726C1.73358 15.909 2.11492 16.2839 2.56042 16.5763C3.588 17.25 5.01892 17.25 7.87983 17.25Z"
                        stroke="currentColor" stroke-width="1.5" />
                    <path d="M16.3333 7.16675H15.4166" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="hidden sm:inline">Dokumentasi</span>
            </button>
        </div>
    </div>
    <div class="ruler-strip mt-3 mb-4"></div>
    <div id="analysisShell" class="analysis-container grid grid-cols-1 md:grid-cols-12 gap-4"
        data-analysis-mode="single">
        <div :class="filterOpen ? 'block' : 'hidden md:block'"
            class="col-span-1 md:col-span-5 xl:col-span-3 2xl:col-span-2">
            <div class="control-deck">
                <div class="deck-head"><span class="deck-dot"></span>Konsol Analisa</div>
                <div class="deck-body">
                    <div>
                        <label class="deck-label">Pilih Logger</label>
                        <select id="loggerSelect" class="calendar-input text-sm py-2">
                            @foreach ($allLoggers as $l)
                                <option value="{{ $l->id_logger }}"
                                    {{ $logger->id_logger == $l->id_logger ? 'selected' : '' }}>
                                    {{ $l->nama_pos ?? $l->nama_logger ?? 'Logger' }}
                                </option>
                            @endforeach
                        </select>
                        @include('analisadata.partials.multi_chart_panel', ['multiChartSlot' => 'controls'])
                        <div id="singleParameterField" class="mt-4">
                            <label class="deck-label">Parameter</label>
                            <select id="parameterSelect" class="calendar-input text-sm py-2">
                                <option value="">Pilih Parameter</option>
                                @foreach ($parameters as $param)
                                    <option value="{{ $param['nama_parameter'] }}" data-unit="{{ $param['satuan'] ?? '' }}"
                                        data-tipe-graf="{{ $param['tipe_graf'] ?? 'line' }}">
                                        {{ str_replace('_', ' ', $param['nama_parameter']) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="deck-label mt-4">Analisa Dalam</label>
                        <div class="seg-grid">
                            <label class="seg"><input type="radio" name="range" value="day" checked><span>Hari</span></label>
                            <label class="seg"><input type="radio" name="range" value="month"><span>Bulan</span></label>
                            <label class="seg"><input type="radio" name="range" value="year"><span>Tahun</span></label>
                            <label class="seg"><input type="radio" name="range" value="custom"><span>Rentang</span></label>
                        </div>
                        <div id="rangeDay" class="range-input-group rounded-lg" style="display:block;">
                            <div class="deck-label">Tanggal</div>

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
                            <div class="deck-label">Bulan</div>

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
                            <div class="deck-label">Tahun</div>

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
                            <div class="deck-label">Rentang</div>

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
                    <div class="deck-sep"></div>
                    <div id="singleAnalysisActions">
                        <button type="button" class="btn-success btn-success-soft" onclick="downloadExcel()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3v12" />
                                <path d="m7 10 5 5 5-5" />
                                <path d="M5 21h14" />
                            </svg>
                            Download Excel
                        </button>
                        <button type="button" class="btn-outline" onclick="openDataMasukModal()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round">
                                <path d="M4 20v-6" />
                                <path d="M10 20V8" />
                                <path d="M16 20v-9" />
                                <path d="M2 20h20" />
                            </svg>
                            Data Masuk
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-1 md:col-span-7 xl:col-span-9 2xl:col-span-10">
            <div id="singleAnalysisPanel" class="panel-card">
                <div class="chart-section px-4 pt-4 pb-0 mb-0">
<div id="rainfallHeader" class="hidden mb-3">
                        <div class="flex flex-col md:flex-row gap-3">
<div
                                class="relative overflow-hidden flex items-center gap-4 bg-white border border-slate-200 rounded-xl px-5 py-4 shadow-sm min-w-[240px]">
                                <div class="z-10">
                                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1"
                                        id="rainfallCardLabel">AKUMULASI CURAH HUJAN</div>
                                    <div class="text-xs text-slate-400 mb-1" id="rainfallCardDate">–</div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-3xl font-bold text-slate-800"
                                            id="rainfallCardTotal">0.000</span>
                                        <span class="text-sm font-semibold text-slate-500">mm</span>
                                    </div>
                                    <div class="mt-1 text-xs font-medium" id="rainfallCardCategory">–</div>
                                </div>
                                <img id="rainfallCardIcon" src="{{ asset('klasifikasi_hujan/tidak_hujan.png') }}"
                                    onerror="this.onerror=null;this.src='{{ asset('klasifikasi_hujan/tidak_hujan.png') }}';"
                                    alt="Status Hujan"
                                    class="pointer-events-none absolute right-[-0.5rem] top-1/2 -translate-y-1/2 h-24 w-24 object-contain opacity-90">
                            </div>
<div class="flex-1 bg-white border border-slate-200 rounded-xl px-5 py-4 shadow-sm">
                                <div class="text-base font-bold text-slate-700 mb-3">Keterangan Intensitas Hujan Per Jam:
                                </div>
                                <div id="rainfallLegendItems" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-4 gap-y-3">
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
<div id="awqrParamHeader" class="hidden mb-3">
                        <div class="flex flex-col md:flex-row gap-3">
<div class="relative overflow-hidden flex-shrink-0 bg-white border border-slate-200 rounded-xl px-5 py-2 shadow-sm" style="min-width:290px">
                                <div class="z-10 relative">
                                    <div class="flex items-start justify-between gap-4 mt-2">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-700 uppercase tracking-wide" id="awqrParamLabel">NILAI PARAMETER</div>
                                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 16 16" stroke="currentColor"><rect x="1" y="2" width="14" height="13" rx="2" stroke-width="1.5"/><path d="M1 6h14" stroke-width="1.5"/><path d="M5 1v3M11 1v3" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                <span id="awqrParamTimeSpan">–</span>
                                            </div>
                                        </div>
                                        <div class="text-right leading-none">
                                            <span class="text-3xl font-bold text-slate-800" id="awqrParamValue">–</span>
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
<div class="flex-1 bg-white border border-slate-200 rounded-xl px-5 py-2 shadow-sm">
                                <div class="text-base font-bold text-slate-700 mb-2">Keterangan:</div>
                                <div class="flex flex-wrap gap-x-8 gap-y-3" id="awqrKeteranganItems">
</div>
                            </div>
                        </div>
                    </div>

                    @endif

                    <div class="panel-head chart-panel-head">
                        <div class="chart-panel-heading">
                            <div class="panel-eyebrow">Grafik Pengukuran</div>
                            <span id="chartPostName" class="hidden">{{ $logger->nama_pos ?? $logger->nama_logger ?? 'Logger' }}</span>
                            <div class="chart-title" id="chartTitle">{{ date('F Y') }} - {{ $logger->nama_pos ?? $logger->nama_logger ?? 'Logger' }}</div>
                        </div>
                        <button type="button" class="chart-export-btn" onclick="downloadChart()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3v12" />
                                <path d="m7 10 5 5 5-5" />
                                <path d="M5 21h14" />
                            </svg>
                            <span>Download Chart</span>
                        </button>
                    </div>
                    <div class="chart-wrapper mb-3 mt-3">
                        <canvas id="dataChart" height="400"></canvas>
                    </div>
                </div>
                <div class="data-table-section px-4 pb-4 pt-2">
                    <div class="panel-head mb-3">
                        <div class="panel-eyebrow">Tabel Data</div>
                        <div class="table-title" id="tableTitle">{{ date('F Y') }}</div>
                    </div>
                    <div id="mainTableWrap" class="table-shell mb-3">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Rerata</th>
                                    <th>Minimum</th>
                                    <th>Maksimum</th>
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
                    <div id="rainfallTableWrap" class="hidden table-shell mb-3">
                        <table class="data-table w-full">
                            <thead>
                                <tr>
                                    <th class="w-1/2">Waktu</th>
                                    <th class="w-1/2">Curah Hujan</th>
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
            @include('analisadata.partials.multi_chart_panel', ['multiChartSlot' => 'panel'])
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.AnalisaMultiChartConfig = {
            loggerId: @json((string) $logger->id_logger),
            dataUrlTemplate: @json(route('analisa.data', ':id')),
            postName: @json($logger->nama_pos ?? $logger->nama_logger ?? 'Logger')
        };
    </script>
    <script src="{{ asset('js/analisa-multichart.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        let chart = null;
        const loggerId = '{{ $logger->id_logger }}';
        document.addEventListener('DOMContentLoaded', function() {
            const paramSelectEl = document.getElementById('parameterSelect');
            const isMultiChartModeActive = () => window.AnalisaMultiChart?.isMultiMode?.() === true;
            const refreshCurrentParamData = () => {
                const param = paramSelectEl ? String(paramSelectEl.value || '').trim() : '';
                if (isMultiChartModeActive()) return;
                if (!param) return;
                updateChartTitle();
                loadData();
            };
            let parameterUsesSelect2 = false;
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
                if (param && !isMultiChartModeActive()) loadData();
            });
            document.getElementById('monthInput').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param && !isMultiChartModeActive()) loadData();
            });
            document.getElementById('yearInput').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param && !isMultiChartModeActive()) loadData();
            });
            document.getElementById('startDateTime').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param && !isMultiChartModeActive()) loadData();
            });
            document.getElementById('endDateTime').addEventListener('change', () => {
                updateChartTitle();
                const param = document.getElementById('parameterSelect').value;
                if (param && !isMultiChartModeActive()) loadData();
            });
            document.querySelectorAll('input[name="range"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    toggleRangeInputs(radio.value);
                    updateChartTitle();
                    const param = document.getElementById('parameterSelect').value;
                    if (param && !isMultiChartModeActive()) loadData();
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
            const defaultParam = '{{ $defaultParameter ?? '' }}';

            let initialParam = '';
            if (paramFromUrl && hasParamOption(paramFromUrl)) {
                initialParam = paramFromUrl;
            } else if (defaultParam && hasParamOption(defaultParam)) {
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

        function getChartPostName() {
            return formatChartLabel(document.getElementById('chartPostName')?.textContent || 'Logger');
        }

        function updateChartTitle() {
            const range = document.querySelector('input[name="range"]:checked').value;
            const parameterSelect = document.getElementById('parameterSelect');
            const param = parameterSelect.value;
            const paramLabel = param.replace(/_/g, ' ');
            const selectedOption = parameterSelect.options[parameterSelect.selectedIndex];
            const normalizedParam = paramLabel.toLowerCase().replace(/\s+/g, ' ').trim();
            const isAccumulation = selectedOption?.dataset?.tipeGraf === 'bar' || normalizedParam === 'curah hujan';
            const summaryLabel = isAccumulation ? 'Akumulasi' : 'Rerata';
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            let titleText = `${summaryLabel} ${paramLabel} `;
            let tableTitleText = `Tabel ${summaryLabel} ${paramLabel} `;
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
                const startDateTime = formatChartDate(document.getElementById('startDateTime').value, true);
                const endDateTime = formatChartDate(document.getElementById('endDateTime').value, true);
                titleText += `Dari ${startDateTime} hingga ${endDateTime}`;
                tableTitleText += `Dari ${startDateTime} hingga ${endDateTime}`;
            }
            const postName = getChartPostName();
            if (postName) {
                titleText += ` - ${postName}`;
            }
            document.getElementById('chartTitle').textContent = titleText;
            document.getElementById('tableTitle').textContent = tableTitleText;
        }

        let currentChartType = 'line';

        const BATTERY_THRESHOLDS = [
            {
                value: 12.2,
                color: '#16a34a',
                label: 'Normal',
                caption: '> 12.2 V',
                description: 'Baterai aman, sistem dapat beroperasi normal'
            },
            {
                value: 11.8,
                color: '#dc2626',
                label: 'Kritis',
                caption: '< 11.8 V',
                description: 'Baterai rendah, berisiko mengganggu operasional logger'
            }
        ];

        const BATTERY_STATUS_DEFINITIONS = [
            {
                label: 'Normal',
                color: '#16a34a',
                description: 'Baterai aman, sistem dapat beroperasi normal',
                test: (value) => value > 12.2
            },
            {
                label: 'Siaga',
                color: '#f59e0b',
                description: 'Baterai mulai menurun, perlu dipantau',
                test: (value) => value >= 11.8 && value <= 12.2
            },
            {
                label: 'Kritis',
                color: '#dc2626',
                description: 'Baterai rendah, berisiko mengganggu operasional logger',
                test: (value) => value < 11.8
            }
        ];

        function drawBatteryThresholdRoundedRect(ctx, x, y, width, height, radius) {
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(x, y, width, height, radius);
                return;
            }

            const r = Math.min(radius, width / 2, height / 2);
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + width - r, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + r);
            ctx.lineTo(x + width, y + height - r);
            ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
            ctx.lineTo(x + r, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - r);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
        }

        const batteryThresholdPlugin = {
            id: 'batteryThresholds',
            afterDraw(chart, args, options) {
                if (!options || !options.enabled || chart.config.type !== 'line') return;

                const yScale = chart.scales && chart.scales.y;
                const area = chart.chartArea;
                if (!yScale || !area) return;

                const ctx = chart.ctx;
                ctx.save();

                const normalY = yScale.getPixelForValue(12.2);
                const criticalY = yScale.getPixelForValue(11.8);
                if (normalY >= area.top && normalY <= area.bottom && criticalY >= area.top && criticalY <= area.bottom) {
                    const bandTop = Math.min(normalY, criticalY);
                    const bandHeight = Math.abs(criticalY - normalY);
                    ctx.fillStyle = 'rgba(245, 158, 11, .08)';
                    ctx.fillRect(area.left, bandTop, area.right - area.left, bandHeight);

                    ctx.fillStyle = '#b45309';
                    ctx.font = '700 11px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                    ctx.fillText('Siaga 11.8-12.2 V', area.left + 10, bandTop + Math.max(16, bandHeight / 2));
                }

                (options.thresholds || []).forEach((threshold) => {
                    const y = yScale.getPixelForValue(threshold.value);
                    if (y < area.top || y > area.bottom) return;

                    ctx.beginPath();
                    ctx.setLineDash([6, 5]);
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = threshold.color;
                    ctx.moveTo(area.left, y);
                    ctx.lineTo(area.right, y);
                    ctx.stroke();

                    const text = `${threshold.label} ${threshold.caption}`;
                    ctx.setLineDash([]);
                    ctx.font = '600 11px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                    const textWidth = ctx.measureText(text).width;
                    const pillWidth = textWidth + 16;
                    const pillHeight = 22;
                    const pillX = Math.max(area.left + 8, area.right - pillWidth - 8);
                    const pillY = Math.max(area.top + 4, Math.min(y - 28, area.bottom - pillHeight - 4));

                    ctx.beginPath();
                    drawBatteryThresholdRoundedRect(ctx, pillX, pillY, pillWidth, pillHeight, 7);
                    ctx.fillStyle = 'rgba(255,255,255,.92)';
                    ctx.fill();
                    ctx.strokeStyle = threshold.color;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                    ctx.fillStyle = threshold.color;
                    ctx.fillText(text, pillX + 8, pillY + 15);
                });

                if (options.status) {
                    const statusText = `Status: ${options.status.label}`;
                    const description = options.status.description;
                    const latestText = Number.isFinite(options.latestValue)
                        ? `${Number(options.latestValue).toFixed(2)} V`
                        : '';
                    const maxPillWidth = Math.max(220, area.right - area.left - 20);
                    const pillWidth = Math.min(maxPillWidth, 320);
                    const pillHeight = 48;
                    const pillX = area.left + 10;
                    const pillY = area.top + 10;

                    ctx.beginPath();
                    drawBatteryThresholdRoundedRect(ctx, pillX, pillY, pillWidth, pillHeight, 10);
                    ctx.fillStyle = 'rgba(255,255,255,.95)';
                    ctx.fill();
                    ctx.strokeStyle = options.status.color;
                    ctx.lineWidth = 1;
                    ctx.stroke();

                    ctx.fillStyle = options.status.color;
                    ctx.font = '700 12px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                    ctx.fillText(latestText ? `${statusText} (${latestText})` : statusText, pillX + 10, pillY + 18);
                    ctx.fillStyle = '#475569';
                    ctx.font = '500 11px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                    let descriptionText = description;
                    while (ctx.measureText(descriptionText).width > pillWidth - 20 && descriptionText.length > 4) {
                        descriptionText = `${descriptionText.slice(0, -4).trim()}...`;
                    }
                    ctx.fillText(descriptionText, pillX + 10, pillY + 36);
                }

                ctx.restore();
            }
        };

        function isBatteryParameterSelected() {
            const select = document.getElementById('parameterSelect');
            if (!select) return false;

            const selectedOption = select.options[select.selectedIndex];
            const label = [
                select.value,
                selectedOption?.textContent,
                selectedOption?.dataset?.unit
            ].join(' ').toLowerCase().replace(/[_-]+/g, ' ');

            return /\b(baterai|battery|vbat|aki)\b/.test(label);
        }

        function getBatteryStatusForValue(value) {
            const numeric = Number(value);
            if (!Number.isFinite(numeric)) return null;

            return BATTERY_STATUS_DEFINITIONS.find((status) => status.test(numeric)) || null;
        }

        function getLatestNumericValue(values) {
            if (!Array.isArray(values)) return null;

            for (let i = values.length - 1; i >= 0; i--) {
                const numeric = Number(values[i]);
                if (Number.isFinite(numeric)) return numeric;
            }

            return null;
        }

        function applyBatteryThresholdOptions(isBar, values) {
            if (!chart || !chart.options || !chart.options.plugins || !chart.options.scales?.y) return;

            const active = !isBar && isBatteryParameterSelected();
            const latestValue = getLatestNumericValue(values);
            chart.options.plugins.batteryThresholds = {
                enabled: active,
                thresholds: BATTERY_THRESHOLDS,
                status: active ? getBatteryStatusForValue(latestValue) : null,
                latestValue
            };

            if (active) {
                const numericValues = (values || []).map(Number).filter(Number.isFinite);
                const minValue = Math.min(11.8, ...numericValues);
                const maxValue = Math.max(12.2, ...numericValues);
                chart.options.scales.y.suggestedMin = Math.min(11.6, minValue - 0.2);
                chart.options.scales.y.suggestedMax = Math.max(12.6, maxValue + 0.2);
            } else {
                chart.options.scales.y.suggestedMin = undefined;
                chart.options.scales.y.suggestedMax = undefined;
            }
        }

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
                { label: 'Rerata',   data: [], borderColor: '#303481', backgroundColor: 'rgba(48,52,129,0.08)', tension: 0.4, cubicInterpolationMode: 'monotone', fill: false, borderWidth: 3, pointRadius: 2 },
                { label: 'Minimum',  data: [], borderColor: '#0fa3d1', backgroundColor: 'rgba(15,163,209,0.08)', tension: 0.4, cubicInterpolationMode: 'monotone', fill: 0,     borderWidth: 2, borderDash: [5,5], pointRadius: 0 },
                { label: 'Maksimum', data: [], borderColor: '#161a52', backgroundColor: 'rgba(22,26,82,0.08)', tension: 0.4, cubicInterpolationMode: 'monotone', fill: 0,     borderWidth: 2, borderDash: [5,5], pointRadius: 0 }
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
                        batteryThresholds: {
                            enabled: false,
                            thresholds: BATTERY_THRESHOLDS,
                            status: null,
                            latestValue: null
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(ctx) {
                                    const v = ctx.parsed.y;
                                    if (v === null || v === undefined) return null;
                                    const formattedVal = Number.isInteger(v) ? v : Number(v).toFixed(2);
                                    return isBar ? `${ctx.dataset.label}: ${formattedVal} mm` : `${ctx.dataset.label}: ${formattedVal}`;
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
                            beginAtZero: isBar,
                            grace: isBar ? '0%' : '15%',
                            min: isBar ? 0 : undefined,
                            grid: { color: 'rgba(148,163,184,0.15)', lineWidth: 1 },
                            ticks: {
                                font: { size: 11 },
                                color: '#94a3b8',
                                callback: function(value) { 
                                    const formatted = Number.isInteger(value) ? value : Number(value).toFixed(2);
                                    return isBar ? formatted + ' mm' : formatted; 
                                }
                            },
                            border: { display: false }
                        }
                    }
                },
                plugins: [batteryThresholdPlugin]
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
            if (Array.isArray(klasifikasi) && klasifikasi.length) {
                const sorted = [...klasifikasi].sort((a, b) => a.debit_air - b.debit_air);
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
                    <span class="inline-block w-7 h-7 rounded-md flex-shrink-0" style="background:${color}"></span>
                    <div class="leading-tight">
                        <div class="text-sm font-semibold text-slate-700">${row.intensitas}</div>
                        <div class="text-xs text-slate-400">${rangeLabel}</div>
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
            renderRainfallLegend(data.klasifikasi ?? []);

            const total = data.akumulasi ?? 0;
            document.getElementById('rainfallCardTotal').textContent = total.toFixed(3);
            const iconEl = document.getElementById('rainfallCardIcon');
            const iconState = getRainfallIconState(total, data.klasifikasi ?? []);
            if (iconEl) {
                iconEl.src = `{{ asset('klasifikasi_hujan') }}/${iconState}.png`;
                iconEl.alt = iconState.replace(/_/g, ' ');
            }
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
        const _isAwgr = @php
            echo (strtoupper($logger->kategori_logger ?? $logger->kategori?->nama_kategori ?? '') === 'AWQR') ? 'true' : 'false';
        @endphp;
        const _awqrParamDefs = {
            tinggi_muka_air: {
                aliases: ['tinggi_muka_air','tinggi muka air','water level','tma'],
                label: 'TINGGI MUKA AIR', unit: 'm',
                classify: () => null,
                keterangan: [] // tidak ada klasifikasi baku mutu
            },
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
            salinity: {
                aliases: ['salinity','salinitas'],
                label: 'NILAI SALINITY', unit: 'PSU',
                classify: () => ({ label: 'Kelas I \u2013 III', color: '#009CD9' }),
                keterangan: [
                    { color: '#009CD9', label: 'Kelas I \u2013 III', range: 'Mendekati Nol' },
                ]
            },
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
            updateAwqrParamPanel(data);
        }

        function updateAwqrParamPanel(data) {
            const panel = document.getElementById('awqrParamHeader');
            if (!panel) return;

            if (!_isAwgr) { panel.classList.add('hidden'); return; }

            const sel      = document.getElementById('parameterSelect');
            const paramVal = String(sel ? sel.value : '').toLowerCase().trim();
            if (!paramVal) { panel.classList.add('hidden'); return; }
            let def = null;
            for (const key of Object.keys(_awqrParamDefs)) {
                const d = _awqrParamDefs[key];
                if (d.aliases.some(a => paramVal === a || paramVal.includes(a) || a.includes(paramVal))) {
                    def = d; break;
                }
            }
            if (!def) { panel.classList.add('hidden'); return; }

            panel.classList.remove('hidden');
            const chartArr = Array.isArray(data && data.chartData) ? data.chartData : [];
            const valid    = chartArr.filter(v => v !== null && v !== undefined && !isNaN(Number(v))).map(Number);
            const avg      = valid.length > 0 ? valid.reduce((a, b) => a + b, 0) / valid.length : null;
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
            const cls = avg !== null ? def.classify(avg) : null;
            if (badgeEl) badgeEl.style.display = cls ? '' : 'none';
            if (cls) {
                if (dotEl) dotEl.style.background = cls.color;
                if (clsEl) { clsEl.textContent = cls.label; clsEl.style.color = cls.color; }
            }
        }




        function updateChart(data) {
            if (!chart) return;
            const labelsRaw = data.labels || [];
            const avgRaw = data.chartData || [];
            const minRaw = data.minData || [];
            const maxRaw = data.maxData || [];
            const rangeNode = document.querySelector('input[name="range"]:checked');
            const range = rangeNode ? rangeNode.value : 'day';
            const isBar = (data.tipe_graf === 'bar');
            const neededType = isBar ? 'bar' : 'line';
            if (currentChartType !== neededType) {
                buildChart(isBar);
            }

            if (!hasAnyDataPayload(data)) {
                chart.data.labels = [];
                chart.data.datasets[0].data = [];
                chart.data.datasets[1].data = [];
                chart.data.datasets[2].data = [];
                applyBatteryThresholdOptions(isBar, []);
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

            applyBatteryThresholdOptions(isBar, isBar ? [] : filteredAvg);
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
                mainWrap?.classList.add('hidden');
                rfWrap?.classList.remove('hidden');

                if (isAllEmpty || !rbody) {
                    if (rbody) rbody.innerHTML =
                        '<tr><td colspan="2" class="text-center py-10 text-slate-400">Tidak ada data</td></tr>';
                    return;
                }
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

        function normalizeChartText(value) {
            return String(value || '')
                .replace(/[_-]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function formatChartWord(word) {
            const lower = word.toLowerCase();
            if (lower === 'ph') return 'pH';
            if (/^[A-Z0-9]{2,5}$/.test(word)) return word;
            if (/[A-Z]/.test(word) && /[a-z]/.test(word)) return word;
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        }

        function formatChartLabel(value) {
            const text = normalizeChartText(value);
            if (!text) return '';
            return text.split(' ').map(formatChartWord).join(' ');
        }

        function formatChartFilename(value) {
            const filename = formatChartLabel(value)
                .replace(/[\\/:*?"<>|]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            return filename || 'Chart Analisa';
        }

        function getSelectedParameterLabel() {
            const select = document.getElementById('parameterSelect');
            if (!select) return 'Parameter';

            const selectedOption = select.options[select.selectedIndex];
            return formatChartLabel(selectedOption?.textContent || select.value || 'Parameter');
        }

        function formatChartDate(value, includeTime = false) {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const text = String(value || '').trim();
            const match = text.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2}))?/);
            if (!match) return normalizeChartText(text) || '-';

            const dateText = `${Number(match[3])} ${monthNames[Number(match[2]) - 1] || match[2]} ${match[1]}`;
            if (!includeTime || !match[4] || !match[5]) return dateText;

            return `${dateText} ${match[4]}:${match[5]}`;
        }

        function getChartDateMetadata() {
            const range = document.querySelector('input[name="range"]:checked')?.value || 'day';
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            if (range === 'day') {
                return {
                    datePrefix: 'Tanggal Data',
                    dateLabel: formatChartDate(document.getElementById('dateInput')?.value)
                };
            }

            if (range === 'month') {
                const [year, month] = String(document.getElementById('monthInput')?.value || '').split('-');
                return {
                    datePrefix: 'Periode Data',
                    dateLabel: year && month ? `${monthNames[Number(month) - 1] || month} ${year}` : '-'
                };
            }

            if (range === 'year') {
                return {
                    datePrefix: 'Periode Data',
                    dateLabel: document.getElementById('yearInput')?.value || '-'
                };
            }

            const start = formatChartDate(document.getElementById('startDateTime')?.value, true);
            const end = formatChartDate(document.getElementById('endDateTime')?.value, true);
            return {
                datePrefix: 'Periode Data',
                dateLabel: `${start} sampai ${end}`
            };
        }

        function getChartExportMetadata() {
            const dateMeta = getChartDateMetadata();
            const postName = getChartPostName();
            const parameterLabel = getSelectedParameterLabel();
            const filename = [postName, parameterLabel, dateMeta.dateLabel]
                .map(formatChartFilename)
                .filter(Boolean)
                .join(' ');

            return {
                postName,
                parameterLabel,
                datePrefix: dateMeta.datePrefix,
                dateLabel: dateMeta.dateLabel,
                filename
            };
        }

        function drawChartExportHeader(ctx, metadata, scale, width, padding) {
            ctx.fillStyle = '#38bdf8';
            ctx.fillRect(padding, Math.round(35 * scale), Math.round(7 * scale), Math.round(7 * scale));
            ctx.fillStyle = '#9094c5';
            ctx.font = `${Math.round(10 * scale)}px "Courier New", monospace`;
            ctx.letterSpacing = `${Math.round(2 * scale)}px`;
            ctx.fillText('GRAFIK PENGUKURAN', padding + Math.round(15 * scale), Math.round(42 * scale));

            ctx.letterSpacing = '0px';
            ctx.fillStyle = '#14163f';
            ctx.font = `700 ${Math.round(18 * scale)}px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`;
            ctx.fillText(metadata.postName, padding, Math.round(74 * scale));

            ctx.fillStyle = '#303481';
            ctx.font = `600 ${Math.round(12 * scale)}px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`;
            ctx.fillText(`Parameter: ${metadata.parameterLabel}`, padding, Math.round(108 * scale));
            ctx.fillText(`${metadata.datePrefix}: ${metadata.dateLabel}`, padding, Math.round(132 * scale));

            ctx.strokeStyle = '#d9dcef';
            ctx.lineWidth = Math.max(1, scale);
            ctx.beginPath();
            ctx.moveTo(padding, Math.round(154 * scale));
            ctx.lineTo(width - padding, Math.round(154 * scale));
            ctx.stroke();
        }

        function downloadChart() {
            if (!chart) {
                alert('Chart belum tersedia');
                return;
            }

            const sourceCanvas = chart.canvas || document.getElementById('dataChart');
            if (!sourceCanvas) {
                alert('Canvas chart belum tersedia');
                return;
            }

            const scale = sourceCanvas.width / (sourceCanvas.clientWidth || sourceCanvas.width || 1);
            const exportPadding = Math.round(40 * scale);
            const headerHeight = Math.round(174 * scale);
            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = sourceCanvas.width + (exportPadding * 2);
            exportCanvas.height = sourceCanvas.height + headerHeight + exportPadding;

            const ctx = exportCanvas.getContext('2d');
            const metadata = getChartExportMetadata();
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
            drawChartExportHeader(ctx, metadata, scale, exportCanvas.width, exportPadding);
            ctx.drawImage(sourceCanvas, exportPadding, headerHeight);

            const link = document.createElement('a');
            link.href = exportCanvas.toDataURL('image/png', 1);
            link.download = `${formatChartFilename(metadata.filename)}.png`;
            document.body.appendChild(link);
            link.click();
            link.remove();
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
                                    const formattedPct = Number.isInteger(pct) ? pct : Number(pct).toFixed(2);
                                    return `Data: ${count} (${formattedPct}%)`;
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
            function positionPanel(anchorWrap, panel) {
                // Portal the panel to <body> so position:fixed resolves against the
                // viewport (not a transformed ancestor like .control-deck) and it sits
                // at the root stacking context instead of being trapped behind the chart card.
                if (panel.parentElement !== document.body) document.body.appendChild(panel);
                const rect = anchorWrap.getBoundingClientRect();
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
                if (left + panelWidth > winW - 8) {
                    left = winW - panelWidth - 8;
                }
                if (left < 8) left = 8;
                if (top + panelH > winH - 8) {
                    top = rect.top - panelH - 8;
                    if (top < 8) top = 8;
                }
                panel.style.top = top + 'px';
                panel.style.left = left + 'px';
            }
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
                    if (!wrap.contains(e.target) && !panel.contains(e.target)) closePanel()
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
                    if (!wrap.contains(e.target) && !panel.contains(e.target)) {
                        panel.classList.add('hidden')
                        yearMenu.classList.add('hidden')
                    }
                })

                yearMenu.addEventListener('click', (e) => e.stopPropagation())

                buildYearMenu()
                setText()
            })()
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
                    if (!wrap.contains(e.target) && !panel.contains(e.target)) panel.classList.add('hidden')
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
                        const wrapper = document.createElement('div')
                        wrapper.className = 'relative h-9 flex items-center justify-center cursor-pointer'
                        if (!isSE && (inRange || isS || isE)) {
                            const strip = document.createElement('div')
                            strip.className = 'absolute inset-y-0 bg-[#E9EAFB] pointer-events-none'
                            if (isS)      strip.style.cssText = 'left:50%;right:0'
                            else if (isE) strip.style.cssText = 'left:0;right:50%'
                            else          strip.style.cssText = 'left:0;right:0'
                            wrapper.appendChild(strip)
                        }
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
                                tempStart = clicked
                                tempEnd = clicked
                                picking = true
                            } else {
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
@if($logger->jiat?->has_pump)
    <script>
        function pumpControlApp() {
            return {
                showPumpModal: false,
                pumpState: 'off',
                pumpRunning: false,
                pumpTargetState: 'off',
                pumpWorkflowVisible: false,
                pumpSteps: [],
                pumpTimers: [],
                pumpError: null,
                pumpChecking: false,
                pumpStatusReady: false,

                openPumpModal() {
                    this.showPumpModal = true
                    this.checkPumpStatus()
                },

                closePumpModal() {
                    if (this.pumpRunning || this.pumpChecking) return
                    this.pumpTimers.forEach(t => clearTimeout(t))
                    this.pumpTimers = []
                    this.pumpWorkflowVisible = false
                    this.pumpSteps = []
                    this.pumpError = null
                    this.pumpStatusReady = false
                    this.showPumpModal = false
                },

                // Cek status pompa via command GET sebelum kontrol diizinkan.
                async checkPumpStatus() {
                    this.resetPump()
                    this.pumpStatusReady = false
                    this.pumpChecking = true
                    try {
                        const res = await fetch('{{ route("pump.command") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                id_logger: '{{ $logger->id_logger }}',
                                action: 'get',
                            }),
                        })
                        const data = await res.json()
                        if (!res.ok) {
                            // Gagal cek: anggap pompa OFF, tanpa pesan.
                            this.pumpState = 'off'
                            return
                        }
                        this.pumpState = Number(data.pump?.state) === 1 ? 'on' : 'off'
                    } catch (e) {
                        console.error('Pump status check error:', e)
                        this.pumpState = 'off'
                    } finally {
                        this.pumpChecking = false
                        this.pumpStatusReady = true
                    }
                },

                pumpPercent() {
                    if (!this.pumpSteps.length) return 0
                    const done = this.pumpSteps.filter(s => s.status === 'done').length
                    const active = this.pumpSteps.some(s => s.status === 'active') ? 0.65 : 0
                    return Math.round(((done + active) / this.pumpSteps.length) * 100)
                },

                resetPump() {
                    this.pumpTimers.forEach(t => clearTimeout(t))
                    this.pumpTimers = []
                    this.pumpRunning = false
                    this.pumpWorkflowVisible = false
                    this.pumpSteps = []
                    this.pumpError = null
                },

                mark(key, status, subtitle) {
                    this.pumpSteps = this.pumpSteps.map(s => s.key === key ? {...s, status, subtitle: subtitle ?? s.subtitle} : s)
                },

                async runPumpAction(target) {
                    this.resetPump()
                    this.pumpTargetState = target
                    this.pumpRunning = true
                    this.pumpWorkflowVisible = true

                    const cmd = target === 'on' ? 'turn_on_pump' : 'turn_off_pump'
                    this.pumpSteps = [
                        { key: 'confirm', title: 'Send command', subtitle: `Sent command: ${cmd}`, status: 'done' },
                        { key: 'mqtt',   title: 'Connecting to MQTT broker', subtitle: 'Menghubungkan ke broker...', status: 'active' },
                        { key: 'logger', title: 'Mengirim perintah ke logger', subtitle: 'Mengirim perintah...', status: 'pending' },
                    ]

                    try {
                        this.mark('mqtt', 'active', 'Menghubungkan ke MQTT broker...')

                        // Langsung set logger ke active karena API akan konek + publish + tunggu respon sekaligus
                        const fetchPromise = fetch('{{ route("pump.command") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                id_logger: '{{ $logger->id_logger }}',
                                action: target === 'on' ? 'on' : 'off',
                            }),
                        })

                        // Setelah ~1 detik, anggap MQTT sudah konek, pindah ke step logger
                        await new Promise(r => setTimeout(r, 1000))
                        this.mark('mqtt', 'done', 'MQTT connected')
                        this.mark('logger', 'active', 'Menunggu respon dari logger...')

                        const res = await fetchPromise
                        const data = await res.json()

                        if (!res.ok) {
                            // Error: anggap pompa OFF, jangan tampilkan pesan.
                            this.resetPump()
                            this.pumpState = 'off'
                            return
                        }

                        // Sukses — logger merespons
                        this.mark('logger', 'done', data.pump?.msg || 'Respon diterima dari logger')

                        this.pumpRunning = false
                        this.pumpState = target

                        this.pumpTimers.push(setTimeout(() => { this.pumpWorkflowVisible = false }, 3000))

                    } catch (e) {
                        // Error: anggap pompa OFF, jangan tampilkan pesan.
                        console.error('Pump command error:', e)
                        this.resetPump()
                        this.pumpState = 'off'
                    }
                }
            }
        }
        ;(function() {
            const style = document.createElement('style')
            style.textContent = `
                @keyframes pump-flicker {
                    0%, 100% { opacity: 1; }
                    30% { opacity: 0.3; }
                    60% { opacity: 0.85; }
                    80% { opacity: 0.2; }
                }
                @keyframes pump-shake {
                    0%, 100% { transform: translateX(0) rotate(0deg); }
                    20% { transform: translateX(-1.5px) rotate(-0.4deg); }
                    40% { transform: translateX(1.5px) rotate(0.4deg); }
                    60% { transform: translateX(-1px) rotate(-0.2deg); }
                    80% { transform: translateX(1px) rotate(0.2deg); }
                }
                @keyframes pump-flash {
                    0%, 100% { opacity: 0; }
                    50% { opacity: 1; }
                }
                @keyframes rotor-spin {
                    from { background-position: 0 0; }
                    to { background-position: -15px 0; }
                }
            `
            document.head.appendChild(style)
        })()
    </script>
    @endif
@endpush
