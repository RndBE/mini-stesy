<?php

namespace Tests\Feature;

use App\Models\t_Logger;
use App\Support\DataMasukStats;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataMasukStatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time mid-day so "today" expected = 630 minutes, no boundary flakiness.
        Carbon::setTestNow(Carbon::parse('2026-06-23 10:30:00', config('app.timezone')));

        foreach (['t_s16_01', 't_s16_50', 't_s19_50'] as $t) {
            Schema::dropIfExists($t);
            Schema::create($t, function (Blueprint $table) {
                $table->increments('id');
                $table->string('id_logger', 15);
                $table->dateTime('waktu');
                $table->double('sensor1')->nullable();
            });
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function logger(string $id, ?string $tableMain, int $sensorCount): t_Logger
    {
        return new t_Logger([
            'id_logger'   => $id,
            'tabel_main'  => $tableMain,
            'sensor_count' => $sensorCount,
        ]);
    }

    public function test_count_is_unique_minutes_not_raw_rows(): void
    {
        // Two rows in the SAME minute count as 1; a third in another minute makes 2.
        DB::table('t_s16_50')->insert([
            ['id_logger' => 'L1', 'waktu' => '2026-06-20 08:00:10', 'sensor1' => 1],
            ['id_logger' => 'L1', 'waktu' => '2026-06-20 08:00:55', 'sensor1' => 2],
            ['id_logger' => 'L1', 'waktu' => '2026-06-20 08:01:00', 'sensor1' => 3],
        ]);

        $stats = DataMasukStats::forDate($this->logger('L1', 't_s16_50', 16), '2026-06-20');

        $this->assertSame(2, $stats['count']);
    }

    public function test_expected_is_1440_for_past_day(): void
    {
        $this->assertSame(1440, DataMasukStats::expectedForDate('2026-06-20', Carbon::now(config('app.timezone'))));
    }

    public function test_expected_is_minutes_so_far_for_today(): void
    {
        // Frozen at 10:30 → 630 minutes since midnight.
        $this->assertSame(630, DataMasukStats::expectedForDate('2026-06-23', Carbon::now(config('app.timezone'))));
    }

    public function test_expected_is_zero_for_future_day(): void
    {
        $this->assertSame(0, DataMasukStats::expectedForDate('2026-06-24', Carbon::now(config('app.timezone'))));
    }

    public function test_percentage_caps_at_100(): void
    {
        // Today (frozen 10:30 → expected=630). 700 distinct minutes → 111% → capped to 100.
        $rows = [];
        for ($m = 0; $m < 700; $m++) {
            $h = str_pad((string) intdiv($m, 60), 2, '0', STR_PAD_LEFT);
            $i = str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT);
            $rows[] = ['id_logger' => 'L1', 'waktu' => "2026-06-23 $h:$i:00", 'sensor1' => 1];
        }
        DB::table('t_s16_50')->insert($rows);

        $stats = DataMasukStats::forDate($this->logger('L1', 't_s16_50', 16), '2026-06-23');

        $this->assertSame(700, $stats['count']);
        $this->assertSame(100.0, $stats['percentage']);
    }

    public function test_percentage_is_zero_when_expected_zero(): void
    {
        $stats = DataMasukStats::forDate($this->logger('L1', 't_s16_50', 16), '2026-06-24'); // future
        $this->assertSame(0.0, $stats['percentage']);
        $this->assertSame(0, $stats['count']);
    }

    public function test_resolve_table_uses_valid_tabel_main(): void
    {
        DB::table('t_s16_50')->insert(['id_logger' => 'L1', 'waktu' => '2026-06-20 08:00:00', 'sensor1' => 1]);

        $this->assertSame('t_s16_50', DataMasukStats::resolveTable($this->logger('L1', 't_s16_50', 16)));
    }

    public function test_resolve_table_falls_back_to_canonical_when_tabel_main_blank(): void
    {
        // No tabel_main → canonical for sensor_count 16 is t_s16_01.
        $this->assertSame('t_s16_01', DataMasukStats::resolveTable($this->logger('L1', null, 16)));
    }

    public function test_resolve_table_falls_back_to_sibling_16_19(): void
    {
        // Primary t_s16_50 empty for L1, sibling t_s19_50 has rows → resolve to sibling.
        DB::table('t_s19_50')->insert(['id_logger' => 'L1', 'waktu' => '2026-06-20 08:00:00', 'sensor1' => 1]);

        $this->assertSame('t_s19_50', DataMasukStats::resolveTable($this->logger('L1', 't_s16_50', 16)));
    }

    public function test_for_logger_handles_multiple_dates_including_empty(): void
    {
        DB::table('t_s16_50')->insert([
            ['id_logger' => 'L1', 'waktu' => '2026-06-20 08:00:00', 'sensor1' => 1],
            ['id_logger' => 'L1', 'waktu' => '2026-06-20 08:05:00', 'sensor1' => 1],
            ['id_logger' => 'L1', 'waktu' => '2026-06-21 09:00:00', 'sensor1' => 1],
        ]);

        $out = DataMasukStats::forLogger(
            $this->logger('L1', 't_s16_50', 16),
            ['2026-06-19', '2026-06-20', '2026-06-21']
        );

        $this->assertSame(0, $out['2026-06-19']['count']); // no data
        $this->assertSame(2, $out['2026-06-20']['count']);
        $this->assertSame(1, $out['2026-06-21']['count']);
        $this->assertSame(1440, $out['2026-06-20']['expected']);
    }
}
