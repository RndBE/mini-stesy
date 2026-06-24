<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for the "data masuk per hari" completeness metric.
 *
 * Canonical count = number of distinct minutes (00:00..23:59) that have at
 * least one reading row for a logger on a given day. This replaces the four
 * divergent counters that previously lived in RekapDataController,
 * DataMasukController and AnalisaController. SQL is kept portable
 * (SUBSTR-based bucketing) so it runs identically on MySQL and SQLite.
 */
class DataMasukStats
{
    /**
     * Per-day stats for a list of 'Y-m-d' dates.
     *
     * @return array<string, array{count:int, expected:int, percentage:float}>
     */
    public static function forLogger($logger, array $dates, ?Carbon $now = null): array
    {
        if (empty($dates)) {
            return [];
        }

        $now   = $now ?: Carbon::now(config('app.timezone'));
        $table = self::resolveTable($logger);

        $min = min($dates);
        $max = max($dates);

        $counts = DB::table($table)
            ->selectRaw('SUBSTR(waktu, 1, 10) as d, COUNT(DISTINCT SUBSTR(waktu, 1, 16)) as c')
            ->where('id_logger', $logger->id_logger)
            ->whereBetween('waktu', [$min . ' 00:00:00', $max . ' 23:59:59'])
            ->groupByRaw('SUBSTR(waktu, 1, 10)')
            ->pluck('c', 'd');

        $out = [];
        foreach ($dates as $date) {
            $count    = (int) ($counts[$date] ?? 0);
            $expected = self::expectedForDate($date, $now);
            $pct      = $expected > 0 ? min(100.0, round($count / $expected * 100, 2)) : 0.0;
            $out[$date] = ['count' => $count, 'expected' => $expected, 'percentage' => $pct];
        }

        return $out;
    }

    /** Stats for a single day (thin wrapper over forLogger). */
    public static function forDate($logger, string $date, ?Carbon $now = null): array
    {
        return self::forLogger($logger, [$date], $now)[$date];
    }

    /**
     * Which minutes of a day have no reading row, collapsed into ranges.
     *
     * The expected window mirrors expectedForDate(): a full 00:00..23:59 for a
     * past day, 00:00..now for today, empty for a future day. Present minutes
     * outside that window are ignored so "missing" stays consistent with the
     * count/expected shown on the rekap-data grid.
     *
     * @return array{expected:int, present:int, missing:int, ranges:array<int, array{start:string, end:string, count:int}>}
     */
    public static function missingMinutesForDate($logger, string $date, ?Carbon $now = null): array
    {
        $now      = $now ?: Carbon::now(config('app.timezone'));
        $expected = self::expectedForDate($date, $now);

        if ($expected <= 0) {
            return ['expected' => 0, 'present' => 0, 'missing' => 0, 'ranges' => []];
        }

        $table = self::resolveTable($logger);

        // Distinct present minutes of the day as 'HH:MM' (SUBSTR keeps it portable).
        $present = DB::table($table)
            ->where('id_logger', $logger->id_logger)
            ->whereBetween('waktu', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->selectRaw('DISTINCT SUBSTR(waktu, 12, 5) as hm')
            ->pluck('hm');

        $presentSet = [];
        foreach ($present as $hm) {
            $idx = ((int) substr($hm, 0, 2)) * 60 + (int) substr($hm, 3, 2);
            if ($idx >= 0 && $idx < $expected) {
                $presentSet[$idx] = true;
            }
        }

        $ranges   = [];
        $runStart = null;
        for ($i = 0; $i < $expected; $i++) {
            if (!isset($presentSet[$i])) {
                if ($runStart === null) {
                    $runStart = $i;
                }
            } elseif ($runStart !== null) {
                $ranges[] = self::minuteRange($runStart, $i - 1);
                $runStart = null;
            }
        }
        if ($runStart !== null) {
            $ranges[] = self::minuteRange($runStart, $expected - 1);
        }

        $presentCount = count($presentSet);

        return [
            'expected' => $expected,
            'present'  => $presentCount,
            'missing'  => $expected - $presentCount,
            'ranges'   => $ranges,
        ];
    }

    private static function minuteRange(int $start, int $end): array
    {
        return [
            'start' => self::minuteToHm($start),
            'end'   => self::minuteToHm($end),
            'count' => $end - $start + 1,
        ];
    }

    private static function minuteToHm(int $idx): string
    {
        return sprintf('%02d:%02d', intdiv($idx, 60), $idx % 60);
    }

    /** Expected record count for a date relative to $now. */
    public static function expectedForDate(string $date, Carbon $now): int
    {
        $tz         = config('app.timezone');
        $day        = Carbon::parse($date, $tz)->startOfDay();
        $todayStart = $now->copy()->setTimezone($tz)->startOfDay();

        if ($day->gt($todayStart)) {
            return 0; // future
        }
        if ($day->eq($todayStart)) {
            return max(1, (int) $todayStart->diffInMinutes($now)); // today
        }
        return 1440; // past
    }

    /** Resolve the table holding this logger's rows, once, with fallback. */
    public static function resolveTable($logger): string
    {
        $sensorCount = self::sensorCount($logger);
        $primary     = self::canonicalTable($logger->tabel_main ?? null, $sensorCount);

        if (DB::table($primary)->where('id_logger', $logger->id_logger)->exists()) {
            return $primary;
        }

        $fallback = self::fallbackTable($primary, $sensorCount);
        if ($fallback !== $primary
            && self::isSupportedTable($fallback)
            && DB::table($fallback)->where('id_logger', $logger->id_logger)->exists()) {
            return $fallback;
        }

        return $primary;
    }

    private static function sensorCount($logger): int
    {
        foreach (['jumlah_sensor', 'sensor_count'] as $attr) {
            if (isset($logger->$attr) && is_numeric($logger->$attr)) {
                return (int) $logger->$attr;
            }
        }
        return 16;
    }

    private static function canonicalTable(?string $tableMain, int $sensorCount): string
    {
        $tableMain = trim((string) $tableMain);
        if (self::isSupportedTable($tableMain)) {
            return $tableMain;
        }
        return SensorFamily::mainTablePrefix(SensorFamily::familyFor($sensorCount)) . '01';
    }

    private static function isSupportedTable(string $tableName): bool
    {
        return $tableName !== '' && SensorFamily::isFamilyTable($tableName) && Schema::hasTable($tableName);
    }

    private static function fallbackTable(string $tableMain, int $sensorCount): string
    {
        if (preg_match('/^t_s(16|19)_(\d{2,})$/', $tableMain, $m)) {
            $other = ((int) $m[1] === 19) ? 16 : 19;
            return 't_s' . $other . '_' . $m[2];
        }
        return SensorFamily::mainTablePrefix(SensorFamily::familyFor($sensorCount)) . '01';
    }
}
