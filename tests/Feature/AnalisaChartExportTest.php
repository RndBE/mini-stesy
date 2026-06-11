<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaChartExportTest extends TestCase
{
    public function test_analisa_chart_can_be_downloaded_as_png(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('class="chart-export-btn"', $view);
        $this->assertStringContainsString('onclick="downloadChart()"', $view);
        $this->assertStringContainsString('Download Chart', $view);
        $this->assertStringContainsString('function downloadChart()', $view);
        $this->assertStringContainsString('id="chartPostName" class="hidden"', $view);
        $this->assertStringContainsString('{{ $logger->nama_pos ?? $logger->nama_logger ?? \'Logger\' }}', $view);
        $this->assertStringContainsString('function getChartPostName()', $view);
        $this->assertStringContainsString('const postName = getChartPostName();', $view);
        $this->assertStringContainsString('titleText += ` - ${postName}`;', $view);
        $this->assertStringContainsString('function getChartExportMetadata()', $view);
        $this->assertStringContainsString('function formatChartFilename(value)', $view);
        $this->assertStringContainsString('function drawChartExportHeader(ctx, metadata, scale, width, padding)', $view);
        $this->assertStringContainsString("ctx.fillText('GRAFIK PENGUKURAN', padding + Math.round(15 * scale), Math.round(42 * scale));", $view);
        $this->assertStringContainsString("ctx.fillText(metadata.postName, padding, Math.round(74 * scale));", $view);
        $this->assertStringContainsString('ctx.fillText(`Parameter: ${metadata.parameterLabel}`', $view);
        $this->assertStringContainsString('ctx.fillText(`${metadata.datePrefix}: ${metadata.dateLabel}`', $view);
        $this->assertStringContainsString('ctx.moveTo(padding, Math.round(154 * scale));', $view);
        $this->assertStringContainsString('const exportPadding = Math.round(40 * scale);', $view);
        $this->assertStringContainsString('const headerHeight = Math.round(174 * scale);', $view);
        $this->assertStringContainsString('exportCanvas.width = sourceCanvas.width + (exportPadding * 2);', $view);
        $this->assertStringContainsString('exportCanvas.height = sourceCanvas.height + headerHeight + exportPadding;', $view);
        $this->assertStringContainsString('drawChartExportHeader(ctx, metadata, scale, exportCanvas.width, exportPadding);', $view);
        $this->assertStringContainsString('ctx.drawImage(sourceCanvas, exportPadding, headerHeight);', $view);
        $this->assertStringContainsString('link.download = `${formatChartFilename(metadata.filename)}.png`;', $view);
        $this->assertStringContainsString("exportCanvas.toDataURL('image/png', 1)", $view);
        $this->assertStringNotContainsString('sanitizeChartFilename', $view);
        $this->assertStringNotContainsString('class="chart-post-name"', $view);
    }
}
