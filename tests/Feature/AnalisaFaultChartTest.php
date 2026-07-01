<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaFaultChartTest extends TestCase
{
    public function test_analisa_view_injects_fault_bit_map_and_helpers(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('window.FAULT_BITS', $view);
        $this->assertStringContainsString('Reverse flow warning', $view); // proves @json rendered the map
        $this->assertStringContainsString('function faultDecode', $view);
        $this->assertStringContainsString('function faultSummary', $view);
        $this->assertStringContainsString('currentIsFault', $view);
    }
}
