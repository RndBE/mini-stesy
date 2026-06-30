<?php

namespace Tests\Unit\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\Tools\RainOverviewTool;
use Mockery;
use Tests\TestCase;

class RainOverviewToolTest extends TestCase
{
    public function test_returns_json_text_with_expected_keys(): void
    {
        $overview = [
            'generated_at' => '2026-06-30 10:00:00',
            'total_pos_hujan' => 3,
            'pos_sedang_hujan' => 1,
            'list' => [
                [
                    'nama' => 'ARR Sinduadi',
                    'id_logger' => 'R1',
                    'akumulasi_hari_ini' => 12.5,
                    'sedang_hujan' => true,
                ],
            ],
        ];

        $data = Mockery::mock(MonitoringData::class);
        $data->shouldReceive('rainOverview')->once()->andReturn($overview);

        $tool = new RainOverviewTool($data);
        $out = $tool->run([], t_User::factory()->make());

        $this->assertArrayHasKey('text', $out);
        $this->assertArrayHasKey('data', $out);

        $decoded = json_decode($out['text'], true);
        $this->assertArrayHasKey('total_pos_hujan', $decoded);
        $this->assertArrayHasKey('pos_sedang_hujan', $decoded);
        $this->assertSame(3, $decoded['total_pos_hujan']);
        $this->assertSame(1, $decoded['pos_sedang_hujan']);
    }
}
