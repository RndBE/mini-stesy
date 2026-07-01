<?php

namespace Tests\Feature;

use App\Support\FaultStatus;
use Tests\TestCase;

class AnalisaFaultChartTest extends TestCase
{
    public function test_analisa_view_injects_fault_bit_map_and_helpers(): void
    {
        // Proves the label is a real value of the map the blade serializes via @json,
        // rather than relying on a hand-written comment matching the raw source.
        $this->assertTrue(in_array('Reverse flow warning', FaultStatus::bits(), true));

        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('@json(\App\Support\FaultStatus::bits())', $view);
        $this->assertStringContainsString('window.FAULT_BITS', $view);
        $this->assertStringContainsString('function faultDecode', $view);
        $this->assertStringContainsString('function faultSummary', $view);
        $this->assertStringContainsString('currentIsFault', $view);
    }

    public function test_analisa_view_has_fault_legend_and_deduped_tooltip(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        // Bit legend beside Download Chart
        $this->assertStringContainsString('id="faultLegendWrap"', $view);
        $this->assertStringContainsString('function toggleFaultLegend', $view);
        $this->assertStringContainsString('function buildFaultLegend', $view);
        $this->assertStringContainsString('setFaultLegendVisible(currentIsFault)', $view);
        $this->assertStringContainsString('Legenda Bit', $view);

        // Warnings are labeled with their bit number so the count in the summary
        // ("Fault · N aktif") isn't mistaken for a bit number.
        $this->assertStringContainsString('function faultDecodeLabeled', $view);

        // Tooltip collapses the rerata/min/max index rows for fault so warnings
        // are not repeated once per dataset.
        $this->assertStringContainsString('return currentIsFault ? item.datasetIndex === 0 : true;', $view);
    }
}
