<?php

namespace Tests\Unit\Chatbot;

use App\Models\t_User;
use App\Services\Chatbot\ContextEngine;
use App\Services\Chatbot\MonitoringData;
use Mockery;
use Tests\TestCase;

class ContextEngineTest extends TestCase
{
    public function test_history_keeps_last_8_and_filters_empty(): void
    {
        $engine = new ContextEngine(Mockery::mock(MonitoringData::class));

        // 12 user turns
        $turns = [];
        for ($i = 0; $i < 12; $i++) {
            $turns[] = ['role' => 'user', 'text' => "m{$i}"];
        }
        // Add an empty-content assistant turn — must be discarded
        $turns[] = ['role' => 'assistant', 'text' => '   '];

        $out = $engine->history($turns);

        // Max 8 entries returned
        $this->assertCount(8, $out);

        // Each entry must have exactly role and content keys
        foreach ($out as $entry) {
            $this->assertArrayHasKey('role', $entry);
            $this->assertArrayHasKey('content', $entry);
        }

        // The empty-content assistant turn must be filtered out (all entries are user turns)
        foreach ($out as $entry) {
            $this->assertNotEmpty($entry['content']);
        }

        // Order is preserved: last (8th) entry content is m11 (the last non-empty user turn)
        $this->assertSame('m11', $out[7]['content']);

        // First entry in returned slice is m4 (12 user turns, keep last 8: m4..m11)
        $this->assertSame('m4', $out[0]['content']);
    }

    public function test_light_context_returns_expected_keys(): void
    {
        $mockData = Mockery::mock(MonitoringData::class);

        $ctx = [
            'user_name' => 'Tessa',
            'logger_total_visible' => 5,
            'logger_online_count' => 3,
            'logger_offline_count' => 2,
            'online_loggers' => ['Logger A (LA01)', 'Logger B (LB01)', 'Logger C (LC01)'],
            'offline_loggers' => ['Logger D (LD01)', 'Logger E (LE01)'],
            'all_loggers' => [
                ['id_logger' => 'LA01', 'nama' => 'Logger A', 'kategori' => 'AWLR', 'lokasi' => 'Loc A', 'status' => 'online', 'last_time' => null],
                ['id_logger' => 'LB01', 'nama' => 'Logger B', 'kategori' => 'ARR', 'lokasi' => 'Loc B', 'status' => 'online', 'last_time' => null],
            ],
            'category_definitions' => ['AWLR' => ['name' => 'Automatic Water Level Recorder']],
            'categories' => ['AWLR' => 1, 'ARR' => 1],
            'maintenance_loggers' => [],
            'sample_loggers' => [],
            'matched_logger' => null,
            'missing_logger_reference' => false,
        ];

        $facts = [
            'user_name' => 'Tessa',
            'server_time' => '2026-06-30 10:00:00',
            'logger_total_visible' => 5,
            'logger_online_count' => 3,
            'logger_offline_count' => 2,
            'online_loggers' => ['Logger A (LA01)', 'Logger B (LB01)', 'Logger C (LC01)'],
            'offline_loggers' => ['Logger D (LD01)', 'Logger E (LE01)'],
            'loggers_truncated' => false,
            'all_loggers' => [
                ['id_logger' => 'LA01', 'nama' => 'Logger A', 'kategori' => 'AWLR', 'lokasi' => 'Loc A', 'status' => 'online', 'last_time' => null],
                ['id_logger' => 'LB01', 'nama' => 'Logger B', 'kategori' => 'ARR', 'lokasi' => 'Loc B', 'status' => 'online', 'last_time' => null],
            ],
            'matched_logger' => null,
            'missing_logger_reference' => false,
            'categories' => ['AWLR' => 1, 'ARR' => 1],
            'category_definitions' => ['AWLR' => ['name' => 'Automatic Water Level Recorder']],
            'maintenance_loggers' => [],
        ];

        $user = Mockery::mock(t_User::class);

        $mockData->shouldReceive('context')
            ->once()
            ->with($user)
            ->andReturn($ctx);

        $mockData->shouldReceive('groundedFacts')
            ->once()
            ->with($ctx)
            ->andReturn($facts);

        $engine = new ContextEngine($mockData);
        $result = $engine->lightContext($user);

        $this->assertArrayHasKey('user_name', $result);
        $this->assertArrayHasKey('server_time', $result);
        $this->assertArrayHasKey('logger_total_visible', $result);
        $this->assertArrayHasKey('logger_online_count', $result);
        $this->assertArrayHasKey('logger_offline_count', $result);
        $this->assertArrayHasKey('logger_names', $result);
        $this->assertArrayHasKey('loggers_truncated', $result);
        $this->assertArrayHasKey('category_definitions', $result);

        $this->assertSame('Tessa', $result['user_name']);
        $this->assertSame(5, $result['logger_total_visible']);
        $this->assertIsArray($result['logger_names']);

        // logger_names should be "Nama (id_logger)" format
        $this->assertContains('Logger A (LA01)', $result['logger_names']);
        $this->assertContains('Logger B (LB01)', $result['logger_names']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
