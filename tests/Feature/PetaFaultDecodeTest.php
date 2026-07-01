<?php

namespace Tests\Feature;

use App\Support\FaultStatus;
use Tests\TestCase;

class PetaFaultDecodeTest extends TestCase
{
    public function test_display_parameter_value_summarizes_fault_bitmask(): void
    {
        $controller = new \App\Http\Controllers\PetaController();
        $method = new \ReflectionMethod($controller, 'displayParameterValue');
        $method->setAccessible(true);

        $param = (object) [
            'nama_parameter'  => 'Fault',
            'parameter_utama' => null,
            'kolom_sensor'    => 'sensor3',
            'satuan'          => '',
        ];

        $this->assertSame('Normal', $method->invoke($controller, $param, 0));
        $this->assertSame('Fault (1)', $method->invoke($controller, $param, 1024));
        $this->assertSame('Fault (2)', $method->invoke($controller, $param, 1026));
    }

    public function test_peta_blade_renders_fault_detail_tooltip(): void
    {
        $view = file_get_contents(resource_path('views/peta/index.blade.php'));
        $this->assertStringContainsString('fault_detail', $view);
    }
}
