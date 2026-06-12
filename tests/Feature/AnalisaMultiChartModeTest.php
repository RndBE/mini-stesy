<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaMultiChartModeTest extends TestCase
{
    public function test_analisa_view_registers_multichart_mode_assets_and_mount_points(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('id="analysisShell"', $view);
        $this->assertStringContainsString('data-analysis-mode="single"', $view);
        $this->assertStringContainsString('@keyframes multichart-shimmer', $view);
        $this->assertStringContainsString('.multi-loading-overlay', $view);
        $this->assertStringContainsString('animation: multichart-shimmer .85s linear infinite', $view);
        $this->assertStringContainsString('id="singleParameterField"', $view);
        $this->assertStringContainsString('id="singleAnalysisActions"', $view);
        $this->assertStringContainsString('id="singleAnalysisPanel"', $view);
        $this->assertStringContainsString("@include('analisadata.partials.multi_chart_panel'", $view);
        $this->assertStringContainsString('window.AnalisaMultiChartConfig', $view);
        $this->assertStringContainsString("asset('js/analisa-multichart.js')", $view);
    }

    public function test_multi_chart_partial_defines_toggle_checklist_and_chart_panel(): void
    {
        $partialPath = resource_path('views/analisadata/partials/multi_chart_panel.blade.php');

        $this->assertFileExists($partialPath);

        $partial = file_get_contents($partialPath);

        $this->assertStringContainsString('data-multichart-controls', $partial);
        $this->assertStringContainsString('id="analysisModeSingle"', $partial);
        $this->assertStringContainsString('id="analysisModeMulti"', $partial);
        $this->assertStringContainsString('id="multiParameterChecklist"', $partial);
        $this->assertStringContainsString('Pilih semua', $partial);
        $this->assertStringContainsString('Hapus semua', $partial);
        $this->assertStringContainsString('data-multichart-panel', $partial);
        $this->assertStringContainsString('id="multiDataChart"', $partial);
        $this->assertStringContainsString('id="multiChartEmpty"', $partial);
        $this->assertStringContainsString('id="multiChartLoading"', $partial);
        $this->assertStringContainsString('multi-loading-chart-lines', $partial);
        $this->assertStringNotContainsString('multi-loading-bar', $partial);
        $this->assertStringContainsString('id="multiDataTableHead"', $partial);
        $this->assertStringContainsString('id="multiDataTableBody"', $partial);
        $this->assertStringContainsString('id="multiTableLoading"', $partial);
        $this->assertStringContainsString('downloadMultiChart()', $partial);
    }

    public function test_multichart_js_contains_data_merge_axis_fetch_and_download_contract(): void
    {
        $scriptPath = public_path('js/analisa-multichart.js');

        $this->assertFileExists($scriptPath);

        $script = file_get_contents($scriptPath);

        $this->assertStringContainsString('let analysisMode = \'single\'', $script);
        $this->assertStringContainsString('function mergeSeriesByLabels', $script);
        $this->assertStringContainsString('Promise.all', $script);
        $this->assertStringContainsString('yLeft', $script);
        $this->assertStringContainsString('yRight', $script);
        $this->assertStringContainsString('drawOnChartArea: false', $script);
        $this->assertStringContainsString('usePointStyle: true', $script);
        $this->assertStringContainsString('function renderMultiTable', $script);
        $this->assertStringContainsString('function resetMultiTable', $script);
        $this->assertStringContainsString('function setMultiLoading', $script);
        $this->assertStringContainsString('window.downloadMultiChart', $script);
        $this->assertStringContainsString('new Chart', $script);
    }
}
