<?php

namespace Tests\Feature;

use App\Http\Controllers\AnalisaController;
use Tests\TestCase;

class AnalisaFaultAggregationTest extends TestCase
{
    public function test_aggregate_value_for_fault_uses_bitwise_or(): void
    {
        $controller = new AnalisaController();
        $method = new \ReflectionMethod($controller, 'aggregateValueFor');
        $method->setAccessible(true);

        $rows = collect([
            (object) ['sensor3' => 8192],
            (object) ['sensor3' => 2],
            (object) ['sensor3' => 0],
        ]);

        // isFault = true → OR = 8194, not average
        $this->assertSame(8194.0, $method->invoke($controller, $rows, 'sensor3', 'line', true));
        // isFault = false → average (unchanged behavior): (8192 + 2 + 0) / 3 = 2731.333...
        $this->assertEqualsWithDelta(2731.3333, $method->invoke($controller, $rows, 'sensor3', 'line', false), 0.001);
    }
}
