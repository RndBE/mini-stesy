<?php

namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\MonitoringData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringDataFormatTest extends TestCase
{
    use RefreshDatabase;

    private function sampleLogger(): array
    {
        return [
            'id_logger'        => 'X1',
            'nama_logger'      => 'AWLR Sinduadi',
            'kategori'         => 'AWLR',
            'lokasi'           => 'Sinduadi',
            'alamat'           => null,
            'status'           => 'online',
            'last_time'        => '2026-06-30 10:00:00',
            'selisih_menit'    => 5,
            'status_perbaikan' => 'normal',
            'sensor_values'    => [],
            'tabel_main'       => null,
            'sensor_count'     => 16,
            'params'           => [],
        ];
    }

    public function test_summary_renders_logger_name(): void
    {
        $data = app(MonitoringData::class);
        $text = $data->summary($this->sampleLogger());

        $this->assertStringContainsString('AWLR Sinduadi', $text);
    }

    public function test_summary_renders_status(): void
    {
        $data = app(MonitoringData::class);
        $text = $data->summary($this->sampleLogger());

        $this->assertStringContainsString('online', $text);
    }

    public function test_summary_renders_kategori(): void
    {
        $data = app(MonitoringData::class);
        $text = $data->summary($this->sampleLogger());

        $this->assertStringContainsString('AWLR', $text);
    }

    public function test_summary_renders_no_sensor_fallback(): void
    {
        $data = app(MonitoringData::class);
        $text = $data->summary($this->sampleLogger());

        $this->assertStringContainsString('belum ada pembacaan', $text);
    }

    public function test_comparison_renders_logger_names(): void
    {
        $data = app(MonitoringData::class);
        $loggers = [
            $this->sampleLogger(),
            array_merge($this->sampleLogger(), [
                'id_logger'   => 'X2',
                'nama_logger' => 'AWLR Demangan',
            ]),
        ];

        $text = $data->comparison($loggers, null);

        $this->assertStringContainsString('AWLR Sinduadi', $text);
        $this->assertStringContainsString('AWLR Demangan', $text);
    }

    public function test_comparison_without_date_range_renders_sensor_section(): void
    {
        $data = app(MonitoringData::class);
        $loggers = [
            $this->sampleLogger(),
            array_merge($this->sampleLogger(), ['id_logger' => 'X2', 'nama_logger' => 'AWLR B']),
        ];

        $text = $data->comparison($loggers, null);

        $this->assertStringContainsString('Perbandingan', $text);
    }

    public function test_history_returns_no_data_message_when_table_empty(): void
    {
        $data   = app(MonitoringData::class);
        $logger = $this->sampleLogger();
        $range  = $data->defaultWeekRange();

        $text = $data->history($logger, $range, null);

        // No table exists → should return a "belum ada data" / descriptive message
        $this->assertIsString($text);
        $this->assertNotEmpty($text);
        $this->assertStringContainsString('Belum ada data', $text);
    }

    public function test_chart_returns_null_when_no_table(): void
    {
        $data   = app(MonitoringData::class);
        $logger = $this->sampleLogger();
        $range  = $data->defaultWeekRange();

        // No params → should return null
        $result = $data->chart($logger, $range, 'grafik tinggi muka air', null);

        $this->assertNull($result);
    }
}
