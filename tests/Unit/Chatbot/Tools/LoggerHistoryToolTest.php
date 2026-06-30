<?php

namespace Tests\Unit\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\Tools\LoggerHistoryTool;
use Mockery;
use Tests\TestCase;

class LoggerHistoryToolTest extends TestCase
{
    public function test_returns_not_found_when_logger_is_null(): void
    {
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn(null);

        $tool = new LoggerHistoryTool($data);
        $out = $tool->run(['logger' => 'unknown', 'date_range' => 'kemarin'], t_User::factory()->make());

        $this->assertStringContainsString('tidak ditemukan', strtolower($out['text']));
    }

    public function test_returns_range_error_when_date_range_unparseable(): void
    {
        $logger = ['id_logger' => 'X1', 'nama_logger' => 'Logger X'];
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn($logger);
        $data->shouldReceive('dateRange')->andReturn(null);

        $tool = new LoggerHistoryTool($data);
        $out = $tool->run(['logger' => 'Logger X', 'date_range' => 'gibberish xyz'], t_User::factory()->make());

        $this->assertStringContainsString('rentang waktu', strtolower($out['text']));
    }

    public function test_calls_history_on_happy_path(): void
    {
        $logger = ['id_logger' => 'X1', 'nama_logger' => 'Logger X'];
        $range = ['from' => now()->subDay(), 'to' => now(), 'label' => 'kemarin'];

        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn($logger);
        $data->shouldReceive('dateRange')->andReturn($range);
        $data->shouldReceive('history')->with($logger, $range, null)->andReturn('Ringkasan data Logger X...');

        $tool = new LoggerHistoryTool($data);
        $out = $tool->run(['logger' => 'Logger X', 'date_range' => 'kemarin'], t_User::factory()->make());

        $this->assertStringContainsString('Logger X', $out['text']);
    }
}
