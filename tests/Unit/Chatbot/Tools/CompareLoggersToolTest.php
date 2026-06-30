<?php

namespace Tests\Unit\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\Tools\CompareLoggersTool;
use Mockery;
use Tests\TestCase;

class CompareLoggersToolTest extends TestCase
{
    public function test_returns_message_when_fewer_than_two_loggers_given(): void
    {
        $data = Mockery::mock(MonitoringData::class);

        $tool = new CompareLoggersTool($data);
        $out = $tool->run(['loggers' => ['only one']], t_User::factory()->make());

        $this->assertStringContainsString('minimal dua', strtolower($out['text']));
    }

    public function test_returns_not_enough_found_when_resolve_returns_fewer_than_two(): void
    {
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLoggers')->andReturn([['id_logger' => 'A1', 'nama_logger' => 'Logger A']]);

        $tool = new CompareLoggersTool($data);
        $out = $tool->run(['loggers' => ['Logger A', 'Logger B']], t_User::factory()->make());

        $this->assertStringContainsString('tidak cukup', strtolower($out['text']));
    }

    public function test_calls_comparison_when_two_loggers_resolved(): void
    {
        $loggers = [
            ['id_logger' => 'A1', 'nama_logger' => 'Logger A'],
            ['id_logger' => 'B1', 'nama_logger' => 'Logger B'],
        ];

        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLoggers')->andReturn($loggers);
        $data->shouldReceive('comparison')->with($loggers, null)->andReturn('Perbandingan pos: ...');

        $tool = new CompareLoggersTool($data);
        $out = $tool->run(['loggers' => ['Logger A', 'Logger B']], t_User::factory()->make());

        $this->assertStringContainsString('Perbandingan', $out['text']);
    }
}
