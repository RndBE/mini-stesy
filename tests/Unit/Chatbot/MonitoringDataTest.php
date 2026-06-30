<?php

namespace Tests\Unit\Chatbot;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_logger_returns_null_when_no_match(): void
    {
        $user = t_User::factory()->create();
        $data = app(MonitoringData::class);

        $this->assertNull($data->resolveLogger($user, 'pos yang tidak ada xyz123'));
    }

    public function test_date_range_parses_weekly_phrase(): void
    {
        $data = app(MonitoringData::class);
        $range = $data->dateRange('data seminggu terakhir');

        $this->assertIsArray($range);
        $this->assertArrayHasKey('from', $range);
        $this->assertArrayHasKey('to', $range);
    }

    public function test_date_range_returns_null_for_unrecognized_phrase(): void
    {
        $data = app(MonitoringData::class);
        $this->assertNull($data->dateRange('hello world'));
    }

    public function test_granularity_parses_hourly(): void
    {
        $data = app(MonitoringData::class);
        $this->assertSame('hourly', $data->granularity('data per jam'));
    }

    public function test_granularity_parses_daily(): void
    {
        $data = app(MonitoringData::class);
        $this->assertSame('daily', $data->granularity('data harian'));
    }

    public function test_granularity_returns_null_for_unrecognized(): void
    {
        $data = app(MonitoringData::class);
        $this->assertNull($data->granularity('tampilkan data'));
    }

    public function test_default_week_range_has_correct_keys(): void
    {
        $data = app(MonitoringData::class);
        $range = $data->defaultWeekRange();

        $this->assertIsArray($range);
        $this->assertArrayHasKey('from', $range);
        $this->assertArrayHasKey('to', $range);
        $this->assertArrayHasKey('label', $range);
    }

    public function test_is_rainfall_param_matches_hujan(): void
    {
        $data = app(MonitoringData::class);
        $this->assertTrue($data->isRainfallParam(['nama' => 'curah hujan', 'kolom' => 'sensor1']));
        $this->assertFalse($data->isRainfallParam(['nama' => 'suhu udara', 'kolom' => 'sensor2']));
    }

    public function test_category_definitions_returns_known_categories(): void
    {
        $data = app(MonitoringData::class);
        $defs = $data->categoryDefinitions();

        $this->assertArrayHasKey('AWLR', $defs);
        $this->assertArrayHasKey('ARR', $defs);
    }

    public function test_resolve_loggers_returns_empty_for_no_match(): void
    {
        $user = t_User::factory()->create();
        $data = app(MonitoringData::class);

        $result = $data->resolveLoggers($user, 'pos xyz999 dan pos abc888');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
