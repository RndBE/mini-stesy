<?php

namespace Tests\Unit;

use App\Support\SensorFamily;
use PHPUnit\Framework\TestCase;

class SensorFamilyTest extends TestCase
{
    /** @dataProvider familyForCases */
    public function test_family_for_maps_sensor_count_to_family(int $count, int $expected): void
    {
        $this->assertSame($expected, SensorFamily::familyFor($count));
    }

    public static function familyForCases(): array
    {
        return [
            'min'              => [1, 16],
            'sixteen'          => [16, 16],
            'seventeen→16'     => [17, 16], // preserve legacy >=19?19:16 threshold
            'eighteen→16'      => [18, 16],
            'nineteen'         => [19, 19],
            'twenty→50'        => [20, 50],
            'fifty'            => [50, 50],
            'over→capped'      => [51, 50],
            'zero→16'          => [0, 16],
        ];
    }

    /** @dataProvider maxSensorCases */
    public function test_max_sensor_for_reads_family_from_table_name(string $table, int $expected): void
    {
        $this->assertSame($expected, SensorFamily::maxSensorFor($table));
    }

    public static function maxSensorCases(): array
    {
        return [
            't_s16_01' => ['t_s16_01', 16],
            't_s19_01' => ['t_s19_01', 19],
            't_s19_02' => ['t_s19_02', 19],
            't_s50_01' => ['t_s50_01', 50],
            't_s50_07' => ['t_s50_07', 50],
            'garbage→16 default' => ['not_a_table', 16],
            'empty→16 default'   => ['', 16],
        ];
    }

    public function test_main_table_prefix(): void
    {
        $this->assertSame('t_s16_', SensorFamily::mainTablePrefix(16));
        $this->assertSame('t_s19_', SensorFamily::mainTablePrefix(19));
        $this->assertSame('t_s50_', SensorFamily::mainTablePrefix(50));
    }

    /** @dataProvider tempTableCases */
    public function test_temp_table_for_main(string $main, string $expected): void
    {
        $this->assertSame($expected, SensorFamily::tempTableFor($main));
    }

    public static function tempTableCases(): array
    {
        return [
            ['t_s16_03', 'temp_s16_latest'],
            ['t_s19_01', 'temp_s19_latest'],
            ['t_s50_01', 'temp_s50_latest'],
        ];
    }

    /** @dataProvider familyTableCases */
    public function test_is_family_table(string $table, bool $expected): void
    {
        $this->assertSame($expected, SensorFamily::isFamilyTable($table));
    }

    public static function familyTableCases(): array
    {
        return [
            'valid 16'            => ['t_s16_01', true],
            'valid 19'            => ['t_s19_02', true],
            'valid 50'            => ['t_s50_01', true],
            'one-digit suffix'    => ['t_s50_1', false], // needs \d{2,}
            'unknown family 99'   => ['t_s99_01', false],
            'unknown family 32'   => ['t_s32_01', false],
            'not a table'         => ['temp_s16_latest', false],
            'junk'                => ['drop table', false],
        ];
    }
}
