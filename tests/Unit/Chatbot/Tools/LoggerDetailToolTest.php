<?php

namespace Tests\Unit\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\Tools\LoggerDetailTool;
use Mockery;
use Tests\TestCase;

class LoggerDetailToolTest extends TestCase
{
    public function test_returns_summary_text_when_logger_found(): void
    {
        $logger = ['nama' => 'AWLR Sinduadi', 'id' => 'X1'];
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn($logger);
        $data->shouldReceive('summary')->with($logger)->andReturn('Pos AWLR Sinduadi: ...');

        $tool = new LoggerDetailTool($data);
        $out = $tool->run(['logger' => 'Sinduadi'], t_User::factory()->make());

        $this->assertStringContainsString('AWLR Sinduadi', $out['text']);
    }

    public function test_returns_not_found_text_when_missing(): void
    {
        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('resolveLogger')->andReturn(null);

        $tool = new LoggerDetailTool($data);
        $out = $tool->run(['logger' => 'zzz'], t_User::factory()->make());

        $this->assertStringContainsString('tidak ditemukan', strtolower($out['text']));
    }
}
