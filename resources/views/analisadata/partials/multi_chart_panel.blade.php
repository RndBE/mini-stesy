@php
    $multiChartSlot = $multiChartSlot ?? 'controls';
@endphp

@if ($multiChartSlot === 'controls')
    <div class="mt-4" data-multichart-controls>
        <label class="deck-label">Mode Analisa</label>
        <div class="analysis-mode-toggle" role="group" aria-label="Mode analisa">
            <button type="button" id="analysisModeSingle" class="analysis-mode-btn is-active"
                data-analysis-mode-button="single" aria-pressed="true">
                Single Parameter
            </button>
            <button type="button" id="analysisModeMulti" class="analysis-mode-btn"
                data-analysis-mode-button="multi" aria-pressed="false">
                Multi Parameter
            </button>
        </div>
    </div>

    <div class="mt-4" data-multichart-checklist>
        <div class="flex items-center justify-between gap-3">
            <label class="deck-label mb-0">Parameter Multi</label>
            <div class="flex items-center gap-3">
                <button type="button" class="multi-param-action" data-multichart-select-all>Pilih semua</button>
                <button type="button" class="multi-param-action" data-multichart-clear>Hapus semua</button>
            </div>
        </div>
        <div id="multiParameterChecklist" class="multi-parameter-checklist mt-2">
            @forelse ($parameters as $param)
                @php
                    $paramName = $param['nama_parameter'] ?? '';
                    $paramLabel = str_replace('_', ' ', $paramName);
                    $paramUnit = $param['satuan'] ?? '';
                @endphp
                <label class="multi-param-option">
                    <input type="checkbox" class="multi-param-checkbox" name="multi_parameters[]"
                        value="{{ $paramName }}" data-multi-parameter data-label="{{ $paramLabel }}"
                        data-unit="{{ $paramUnit }}" data-tipe-graf="{{ $param['tipe_graf'] ?? 'line' }}">
                    <span class="multi-param-text">{{ $paramLabel }}</span>
                    @if ($paramUnit !== '')
                        <span class="multi-param-unit">{{ $paramUnit }}</span>
                    @endif
                </label>
            @empty
                <div class="p-4 text-sm text-slate-400">Parameter tidak tersedia</div>
            @endforelse
        </div>
    </div>
@else
    <div class="panel-card" data-multichart-panel style="display: none;">
        <div class="chart-section px-4 pt-4 pb-4 mb-0">
            <div class="panel-head chart-panel-head">
                <div class="chart-panel-heading">
                    <div class="panel-eyebrow">Grafik Multi Parameter</div>
                    <div class="chart-title" id="multiChartTitle">
                        {{ date('F Y') }} - {{ $logger->nama_pos ?? $logger->nama_logger ?? 'Logger' }}
                    </div>
                </div>
                <button type="button" class="chart-export-btn" onclick="downloadMultiChart()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v12" />
                        <path d="m7 10 5 5 5-5" />
                        <path d="M5 21h14" />
                    </svg>
                    <span>Download Chart</span>
                </button>
            </div>
            <div class="chart-wrapper mb-0 mt-3" style="min-height: 400px;">
                <canvas id="multiDataChart" height="400"></canvas>
                <div id="multiChartEmpty" class="multi-chart-empty">
                    Centang parameter untuk menampilkan grafik
                </div>
                <div id="multiChartLoading" class="multi-loading-overlay" aria-hidden="true">
                    <div class="multi-loading-card">
                        <div class="multi-loading-chart-grid multi-loading-chart-lines">
                            <span class="multi-loading-line" style="width: 82%; transform: rotate(-1deg);"></span>
                            <span class="multi-loading-line" style="width: 68%; transform: rotate(1.4deg);"></span>
                            <span class="multi-loading-line" style="width: 88%; transform: rotate(-.8deg);"></span>
                            <span class="multi-loading-line" style="width: 74%; transform: rotate(1deg);"></span>
                            <span class="multi-loading-line" style="width: 58%; transform: rotate(-1.2deg);"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="data-table-section px-4 pb-4 pt-2">
            <div class="panel-head mb-3">
                <div class="panel-eyebrow">Tabel Data Multi</div>
                <div class="table-title" id="multiTableTitle">Multi Parameter</div>
            </div>
            <div id="multiTableWrap" class="table-shell mb-3 overflow-auto relative">
                <table class="data-table min-w-full">
                    <thead id="multiDataTableHead">
                        <tr>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="multiDataTableBody" class="text-sm">
                        <tr>
                            <td style="text-align: center; padding: 40px; color: #9ca3af;">
                                Centang parameter untuk menampilkan data
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div id="multiTableLoading" class="multi-loading-overlay multi-loading-table" aria-hidden="true">
                    <div class="multi-loading-card">
                        <div class="multi-loading-line" style="width: 92%;"></div>
                        <div class="multi-loading-line" style="width: 78%;"></div>
                        <div class="multi-loading-line" style="width: 86%;"></div>
                        <div class="multi-loading-line" style="width: 64%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
