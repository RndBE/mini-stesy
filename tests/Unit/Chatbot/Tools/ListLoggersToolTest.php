<?php

namespace Tests\Unit\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\Tools\ListLoggersTool;
use Mockery;
use Tests\TestCase;

class ListLoggersToolTest extends TestCase
{
    public function test_returns_text_with_online_and_offline_counts(): void
    {
        $ctx = ['some' => 'context'];
        $facts = [
            'logger_total_visible' => 5,
            'logger_online_count' => 3,
            'logger_offline_count' => 2,
            'online_loggers' => ['Logger A (A1)', 'Logger B (B1)', 'Logger C (C1)'],
            'offline_loggers' => ['Logger D (D1)', 'Logger E (E1)'],
            'loggers_truncated' => false,
        ];

        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('context')->once()->andReturn($ctx);
        $data->shouldReceive('groundedFacts')->with($ctx)->once()->andReturn($facts);

        $tool = new ListLoggersTool($data);
        $out = $tool->run([], t_User::factory()->make());

        $this->assertArrayHasKey('text', $out);
        $this->assertArrayHasKey('data', $out);
        $this->assertStringContainsString('3', $out['text']);
        $this->assertStringContainsString('2', $out['text']);
    }

    public function test_filters_offline_only_excludes_online_loggers(): void
    {
        $ctx = [];
        $facts = [
            'logger_total_visible' => 2,
            'logger_online_count' => 1,
            'logger_offline_count' => 1,
            'online_loggers' => ['Logger A (A1)'],
            'offline_loggers' => ['Logger B (B1)'],
            'loggers_truncated' => false,
        ];

        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('context')->once()->andReturn($ctx);
        $data->shouldReceive('groundedFacts')->with($ctx)->once()->andReturn($facts);

        $tool = new ListLoggersTool($data);
        $out = $tool->run(['status' => 'offline'], t_User::factory()->make());

        $this->assertArrayNotHasKey('online_loggers', $out['data']);
        $this->assertArrayHasKey('offline_loggers', $out['data']);
    }
}
