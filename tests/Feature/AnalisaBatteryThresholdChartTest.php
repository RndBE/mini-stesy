<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaBatteryThresholdChartTest extends TestCase
{
    public function test_single_parameter_chart_defines_battery_threshold_status_overlay(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('const BATTERY_THRESHOLDS', $view);
        $this->assertStringContainsString("value: 12.2", $view);
        $this->assertStringContainsString("value: 11.8", $view);
        $this->assertStringContainsString("label: 'Normal'", $view);
        $this->assertStringContainsString("label: 'Siaga'", $view);
        $this->assertStringContainsString("label: 'Kritis'", $view);
        $this->assertStringContainsString('Siaga 11.8-12.2 V', $view);
        $this->assertStringContainsString('function isBatteryParameterSelected()', $view);
        $this->assertStringContainsString('function getBatteryStatusForValue(value)', $view);
        $this->assertStringContainsString('const batteryThresholdPlugin', $view);
        $this->assertStringContainsString("id: 'batteryThresholds'", $view);
        $this->assertStringContainsString('getPixelForValue(threshold.value)', $view);
        $this->assertStringContainsString('plugins: [batteryThresholdPlugin]', $view);
        $this->assertStringContainsString('chart.options.plugins.batteryThresholds', $view);
        $this->assertStringContainsString('Baterai aman, sistem dapat beroperasi normal', $view);
        $this->assertStringContainsString('Baterai mulai menurun, perlu dipantau', $view);
        $this->assertStringContainsString('Baterai rendah, berisiko mengganggu operasional logger', $view);
    }
}
