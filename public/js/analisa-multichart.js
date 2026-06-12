(function () {
    let analysisMode = 'single';
    let multiChart = null;
    let loadToken = 0;

    const palette = [
        '#303481',
        '#0fa3d1',
        '#16a34a',
        '#f59e0b',
        '#dc2626',
        '#7c3aed',
        '#0891b2',
        '#be123c'
    ];

    function qs(selector) {
        return document.querySelector(selector);
    }

    function qsa(selector) {
        return Array.from(document.querySelectorAll(selector));
    }

    function getConfig() {
        return window.AnalisaMultiChartConfig || {};
    }

    function formatLabel(value) {
        if (typeof window.formatChartLabel === 'function') {
            return window.formatChartLabel(value);
        }

        return String(value || '')
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    }

    function formatTableValue(value) {
        if (value === null || value === undefined || value === '') return '-';

        const numeric = Number(value);
        if (!Number.isFinite(numeric)) return '-';

        return Number.isInteger(numeric) ? String(numeric) : numeric.toFixed(2);
    }

    function getPostName() {
        if (typeof window.getChartPostName === 'function') {
            return window.getChartPostName();
        }

        return formatLabel(getConfig().postName || 'Logger');
    }

    function getDateMetadata() {
        if (typeof window.getChartDateMetadata === 'function') {
            return window.getChartDateMetadata();
        }

        return {
            datePrefix: 'Periode Data',
            dateLabel: '-'
        };
    }

    function getActiveRange() {
        return qs('input[name="range"]:checked')?.value || 'day';
    }

    function getActiveDateValue(range) {
        if (range === 'day') return qs('#dateInput')?.value || '';
        if (range === 'month') return qs('#monthInput')?.value || '';
        if (range === 'year') return qs('#yearInput')?.value || '';

        const start = qs('#startDateTime')?.value || '';
        const end = qs('#endDateTime')?.value || '';
        return `${start},${end}`;
    }

    function getSelectedParameters() {
        return qsa('[data-multi-parameter]:checked').map((input) => ({
            name: input.value,
            label: formatLabel(input.dataset.label || input.value),
            unit: String(input.dataset.unit || '').trim(),
            tipeGraf: input.dataset.tipeGraf || 'line'
        }));
    }

    function getDataUrl(parameter) {
        const config = getConfig();
        const loggerId = config.loggerId || '';
        const template = config.dataUrlTemplate || '';
        const range = getActiveRange();
        const date = getActiveDateValue(range);
        const query = new URLSearchParams({ parameter, range, date });

        return template.replace(':id', loggerId) + `?${query.toString()}`;
    }

    function toNumericValue(value) {
        if (value === null || value === undefined || value === '') return null;

        const numeric = Number(value);
        return Number.isFinite(numeric) ? numeric : null;
    }

    function mergeSeriesByLabels(series) {
        const labels = [];
        const seen = new Set();

        series.forEach((item) => {
            (item.labels || []).forEach((label) => {
                const key = String(label);
                if (seen.has(key)) return;
                seen.add(key);
                labels.push(label);
            });
        });

        const mergedSeries = series.map((item) => {
            const valueByLabel = new Map();

            (item.labels || []).forEach((label, index) => {
                valueByLabel.set(String(label), toNumericValue((item.values || [])[index]));
            });

            return {
                ...item,
                values: labels.map((label) => valueByLabel.get(String(label)) ?? null)
            };
        });

        return { labels, series: mergedSeries };
    }

    function getAxisAssignments(parameters) {
        const unitAxisMap = new Map();
        const axisUnits = [];

        parameters.forEach((parameter) => {
            const unit = parameter.unit || '';
            if (!unitAxisMap.has(unit)) {
                axisUnits.push(unit);
                unitAxisMap.set(unit, axisUnits.length === 2 ? 'yRight' : 'yLeft');
            }
        });

        return {
            unitAxisMap,
            leftUnit: axisUnits[0] || '',
            rightUnit: axisUnits[1] || ''
        };
    }

    function updateTitle() {
        const title = qs('#multiChartTitle');
        const dateMeta = getDateMetadata();
        const postName = getPostName();
        const titleText = [dateMeta.dateLabel, postName].filter(Boolean).join(' - ');

        if (title) {
            title.textContent = titleText;
        }

        const tableTitle = qs('#multiTableTitle');
        if (tableTitle) {
            tableTitle.textContent = `Tabel Multi Parameter ${dateMeta.dateLabel || ''}`.trim();
        }
    }

    function setEmptyState(message, show) {
        const empty = qs('#multiChartEmpty');
        if (!empty) return;

        empty.textContent = message;
        empty.style.display = show ? 'flex' : 'none';
    }

    function setMultiLoading(isLoading) {
        qsa('#multiChartLoading, #multiTableLoading').forEach((overlay) => {
            overlay.classList.toggle('is-active', Boolean(isLoading));
            overlay.setAttribute('aria-hidden', isLoading ? 'false' : 'true');
        });

        const panel = qs('[data-multichart-panel]');
        if (panel) {
            panel.setAttribute('aria-busy', isLoading ? 'true' : 'false');
        }
    }

    function destroyMultiChart() {
        if (!multiChart) return;

        multiChart.destroy();
        multiChart = null;
    }

    function resetMultiTable(message, columnCount = 1) {
        const head = qs('#multiDataTableHead');
        const body = qs('#multiDataTableBody');
        if (!head || !body) return;

        head.innerHTML = '<tr><th>Waktu</th></tr>';
        body.innerHTML = `
            <tr>
                <td colspan="${Math.max(1, columnCount)}" style="text-align: center; padding: 40px; color: #9ca3af;">
                    ${escapeHtml(message)}
                </td>
            </tr>
        `;
    }

    function renderMultiTable(labels, series) {
        const head = qs('#multiDataTableHead');
        const body = qs('#multiDataTableBody');
        if (!head || !body) return;

        if (!Array.isArray(labels) || labels.length === 0 || !Array.isArray(series) || series.length === 0) {
            resetMultiTable('Data tidak tersedia untuk parameter terpilih', (series?.length || 0) + 1);
            return;
        }

        const parameterHeaders = series.map((item) => {
            const unitSuffix = item.unit ? ` (${item.unit})` : '';
            return `<th>${escapeHtml(`${item.label}${unitSuffix}`)}</th>`;
        }).join('');

        head.innerHTML = `<tr><th>Waktu</th>${parameterHeaders}</tr>`;
        body.innerHTML = labels.map((label, rowIndex) => {
            const cells = series.map((item) => {
                return `<td>${escapeHtml(formatTableValue(item.values?.[rowIndex]))}</td>`;
            }).join('');

            return `<tr><td>${escapeHtml(label)}</td>${cells}</tr>`;
        }).join('');
    }

    function buildDatasets(mergedSeries, axisAssignments) {
        return mergedSeries.map((item, index) => {
            const color = palette[index % palette.length];
            const axisId = axisAssignments.unitAxisMap.get(item.unit || '') || 'yLeft';
            const unitSuffix = item.unit ? ` (${item.unit})` : '';

            return {
                label: `${item.label}${unitSuffix}`,
                data: item.values,
                yAxisID: axisId,
                borderColor: color,
                backgroundColor: color,
                borderWidth: 2.5,
                pointRadius: 2,
                pointHoverRadius: 4,
                tension: 0.35,
                cubicInterpolationMode: 'monotone',
                fill: false,
                spanGaps: false
            };
        });
    }

    function renderChart(labels, datasets, axisAssignments) {
        const canvas = qs('#multiDataChart');
        if (!canvas || typeof Chart === 'undefined') return;

        destroyMultiChart();

        multiChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 16,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function (ctx) {
                                const value = ctx.parsed.y;
                                if (value === null || value === undefined) return null;

                                const formatted = Number.isInteger(value) ? value : Number(value).toFixed(2);
                                return `${ctx.dataset.label}: ${formatted}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8', maxRotation: 0 },
                        border: { display: false }
                    },
                    yLeft: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grace: '15%',
                        title: {
                            display: Boolean(axisAssignments.leftUnit),
                            text: axisAssignments.leftUnit
                        },
                        grid: { color: 'rgba(148,163,184,0.15)', lineWidth: 1 },
                        ticks: { font: { size: 11 }, color: '#94a3b8' },
                        border: { display: false }
                    },
                    yRight: {
                        type: 'linear',
                        display: Boolean(axisAssignments.rightUnit),
                        position: 'right',
                        grace: '15%',
                        title: {
                            display: Boolean(axisAssignments.rightUnit),
                            text: axisAssignments.rightUnit
                        },
                        grid: { drawOnChartArea: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8' },
                        border: { display: false }
                    }
                }
            }
        });
    }

    function fetchParameterSeries(parameter) {
        return fetch(getDataUrl(parameter.name))
            .then((response) => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then((data) => ({
                ...parameter,
                labels: Array.isArray(data.labels) ? data.labels : [],
                values: Array.isArray(data.chartData) ? data.chartData : []
            }))
            .catch((error) => {
                console.warn(`Gagal memuat parameter ${parameter.name}`, error);
                return null;
            });
    }

    function loadMultiChart() {
        if (analysisMode !== 'multi') return;

        updateTitle();
        const currentToken = ++loadToken;

        const selectedParameters = getSelectedParameters();
        if (selectedParameters.length === 0) {
            setMultiLoading(false);
            destroyMultiChart();
            setEmptyState('Centang parameter untuk menampilkan grafik', true);
            resetMultiTable('Centang parameter untuk menampilkan data');
            return;
        }

        setEmptyState('', false);
        setMultiLoading(true);

        Promise.all(selectedParameters.map(fetchParameterSeries)).then((responses) => {
            if (currentToken !== loadToken) return;
            if (analysisMode !== 'multi') return;
            setMultiLoading(false);

            const availableSeries = responses.filter(Boolean);
            const merged = mergeSeriesByLabels(availableSeries);

            if (merged.labels.length === 0 || merged.series.length === 0) {
                destroyMultiChart();
                setEmptyState('Data tidak tersedia untuk parameter terpilih', true);
                resetMultiTable('Data tidak tersedia untuk parameter terpilih', selectedParameters.length + 1);
                return;
            }

            const axisAssignments = getAxisAssignments(merged.series);
            const datasets = buildDatasets(merged.series, axisAssignments);

            renderChart(merged.labels, datasets, axisAssignments);
            renderMultiTable(merged.labels, merged.series);
            setEmptyState('', false);
        });
    }

    function setMode(mode) {
        analysisMode = mode === 'multi' ? 'multi' : 'single';
        loadToken++;
        setMultiLoading(false);

        const shell = qs('#analysisShell');
        if (shell) {
            shell.dataset.analysisMode = analysisMode;
        }

        qsa('[data-analysis-mode-button]').forEach((button) => {
            const isActive = button.dataset.analysisModeButton === analysisMode;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        if (analysisMode === 'multi') {
            loadMultiChart();
        }
    }

    function selectAllParameters(checked) {
        qsa('[data-multi-parameter]').forEach((input) => {
            input.checked = checked;
        });

        loadMultiChart();
    }

    function getMultiExportMetadata() {
        const dateMeta = getDateMetadata();
        const postName = getPostName();
        const parameterLabel = getSelectedParameters().map((parameter) => parameter.label).join(', ') || 'Multi Parameter';
        const filename = [postName, parameterLabel, dateMeta.dateLabel]
            .map((value) => typeof window.formatChartFilename === 'function' ? window.formatChartFilename(value) : formatLabel(value))
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

    function drawFallbackHeader(ctx, metadata, scale, width, padding) {
        ctx.fillStyle = '#38bdf8';
        ctx.fillRect(padding, Math.round(35 * scale), Math.round(7 * scale), Math.round(7 * scale));
        ctx.fillStyle = '#9094c5';
        ctx.font = `${Math.round(10 * scale)}px "Courier New", monospace`;
        ctx.fillText('GRAFIK MULTI PARAMETER', padding + Math.round(15 * scale), Math.round(42 * scale));
        ctx.fillStyle = '#14163f';
        ctx.font = `700 ${Math.round(18 * scale)}px system-ui, sans-serif`;
        ctx.fillText(metadata.postName, padding, Math.round(74 * scale));
        ctx.fillStyle = '#303481';
        ctx.font = `600 ${Math.round(12 * scale)}px system-ui, sans-serif`;
        ctx.fillText(`Parameter: ${metadata.parameterLabel}`, padding, Math.round(108 * scale));
        ctx.fillText(`${metadata.datePrefix}: ${metadata.dateLabel}`, padding, Math.round(132 * scale));
        ctx.strokeStyle = '#d9dcef';
        ctx.beginPath();
        ctx.moveTo(padding, Math.round(154 * scale));
        ctx.lineTo(width - padding, Math.round(154 * scale));
        ctx.stroke();
    }

    function downloadMultiChart() {
        if (!multiChart) {
            alert('Chart multi parameter belum tersedia');
            return;
        }

        const sourceCanvas = multiChart.canvas || qs('#multiDataChart');
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
        const metadata = getMultiExportMetadata();
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, exportCanvas.width, exportCanvas.height);

        if (typeof window.drawChartExportHeader === 'function') {
            window.drawChartExportHeader(ctx, metadata, scale, exportCanvas.width, exportPadding);
        } else {
            drawFallbackHeader(ctx, metadata, scale, exportCanvas.width, exportPadding);
        }

        ctx.drawImage(sourceCanvas, exportPadding, headerHeight);

        const link = document.createElement('a');
        const filename = typeof window.formatChartFilename === 'function'
            ? window.formatChartFilename(metadata.filename)
            : formatLabel(metadata.filename);
        link.href = exportCanvas.toDataURL('image/png', 1);
        link.download = `${filename || 'Chart Multi Parameter'}.png`;
        document.body.appendChild(link);
        link.click();
        link.remove();
    }

    function bindEvents() {
        qsa('[data-analysis-mode-button]').forEach((button) => {
            button.addEventListener('click', () => setMode(button.dataset.analysisModeButton));
        });

        qsa('[data-multi-parameter]').forEach((input) => {
            input.addEventListener('change', loadMultiChart);
        });

        qs('[data-multichart-select-all]')?.addEventListener('click', () => selectAllParameters(true));
        qs('[data-multichart-clear]')?.addEventListener('click', () => selectAllParameters(false));

        qsa('input[name="range"], #dateInput, #monthInput, #yearInput, #startDateTime, #endDateTime').forEach((input) => {
            input.addEventListener('change', loadMultiChart);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindEvents();
        setMode('single');
    });

    window.downloadMultiChart = downloadMultiChart;
    window.AnalisaMultiChart = {
        isMultiMode: () => analysisMode === 'multi',
        refresh: loadMultiChart,
        setMode
    };
})();
