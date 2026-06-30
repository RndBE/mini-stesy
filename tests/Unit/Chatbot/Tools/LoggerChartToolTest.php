<?php

namespace Tests\Unit\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\Tools\LoggerChartTool;
use Mockery;
use Tests\TestCase;

class LoggerChartToolTest extends TestCase
{
    public function test_returns_not_found_when_logger_is_null(): void
    {
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn(null);

        $tool = new LoggerChartTool($data);
        $out = $tool->run(['logger' => 'unknown'], t_User::factory()->make());

        $this->assertStringContainsString('tidak ditemukan', strtolower($out['text']));
    }

    public function test_returns_chart_key_when_chart_data_available(): void
    {
        $logger = ['id_logger' => 'X1', 'nama_logger' => 'Logger X'];
        $range = ['from' => now()->subDays(7), 'to' => now(), 'label' => '7 hari terakhir'];
        $chartData = [
            'explanation' => 'Grafik Logger X...',
            'chart' => ['type' => 'line', 'labels' => [], 'values' => []],
        ];

        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn($logger);
        $data->shouldReceive('defaultWeekRange')->andReturn($range);
        $data->shouldReceive('chart')->andReturn($chartData);

        $tool = new LoggerChartTool($data);
        $out = $tool->run(['logger' => 'Logger X'], t_User::factory()->make());

        $this->assertArrayHasKey('chart', $out);
        $this->assertStringContainsString('Grafik', $out['text']);
    }

    public function test_returns_no_data_message_when_chart_is_null(): void
    {
        $logger = ['id_logger' => 'X1', 'nama_logger' => 'Logger X'];
        $range = ['from' => now()->subDays(7), 'to' => now(), 'label' => '7 hari terakhir'];

        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn($logger);
        $data->shouldReceive('defaultWeekRange')->andReturn($range);
        $data->shouldReceive('chart')->andReturn(null);

        $tool = new LoggerChartTool($data);
        $out = $tool->run(['logger' => 'Logger X'], t_User::factory()->make());

        $this->assertArrayNotHasKey('chart', $out);
        $this->assertStringContainsString('tidak tersedia', strtolower($out['text']));
    }
}
