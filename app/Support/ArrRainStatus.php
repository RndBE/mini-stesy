<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for ARR (rain logger) accumulation + BMKG-style
 * classification. Shared by the Beranda display and the ingestion notification
 * path so the value that is shown is always the value that is notified.
 *
 * Accumulation uses the running calendar hour/day (matching the Beranda
 * per-jam / per-hari status). Thresholds come from `klasifikasi_hujan`
 * (per logger, per `waktu` = perjam|perhari), the `debit_air` column holding
 * the rainfall accumulation in mm.
 */
class ArrRainStatus
{
    /** Intensities (and above) that warrant a notification. */
    private const ALERT_INTENSITIES = ['sedang', 'lebat', 'sangat lebat', 'sangat_lebat'];

    /**
     * SUM of the rain column over the running calendar hour. Null when the
     * table/column is unsafe to query.
     */
    public static function hourlyAccumulation(string $tableName, string $loggerId, string $rainColumn): ?float
    {
        return self::accumulate($tableName, $loggerId, $rainColumn, Carbon::now()->startOfHour(), Carbon::now()->endOfHour());
    }

    /** SUM of the rain column over the running calendar day. */
    public static function dailyAccumulation(string $tableName, string $loggerId, string $rainColumn): ?float
    {
        return self::accumulate($tableName, $loggerId, $rainColumn, Carbon::now()->startOfDay(), Carbon::now()->endOfDay());
    }

    private static function accumulate(string $tableName, string $loggerId, string $rainColumn, Carbon $start, Carbon $end): ?float
    {
        if (!self::canQueryRainTable($tableName, $rainColumn)) {
            return null;
        }

        $sum = DB::table($tableName)
            ->where('id_logger', $loggerId)
            ->whereBetween('waktu', [$start, $end])
            ->sum($rainColumn);

        return is_numeric($sum) ? (float) $sum : null;
    }

    /** Validate that a wide table exists and exposes the columns we sum over. */
    public static function canQueryRainTable(string $tableName, string $rainColumn): bool
    {
        if ($tableName === '' || !preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
            return false;
        }

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        return Schema::hasColumn($tableName, 'id_logger')
            && Schema::hasColumn($tableName, 'waktu')
            && Schema::hasColumn($tableName, $rainColumn);
    }

    /**
     * Classify an accumulation value into a klasifikasi_hujan intensity label
     * for the given period. Picks the highest threshold whose value <= $value;
     * falls back to the lowest threshold. Null when $value is null or there are
     * no thresholds.
     */
    public static function classify(string $loggerId, string $period, ?float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $thresholds = self::thresholds($loggerId, $period);

        if ($thresholds->isEmpty()) {
            return null;
        }

        $matched = null;
        foreach ($thresholds as $threshold) {
            if ($value >= (float) $threshold->debit_air) {
                $matched = $threshold;
            } else {
                break;
            }
        }

        return ($matched ?? $thresholds->first())->intensitas;
    }

    /**
     * True when this logger has at least one active (status=1) threshold row.
     * If the `status` column is not present yet (prod before the add-status
     * migration), treat ARR as enabled so ingestion never crashes and rain
     * notifications keep firing.
     */
    public static function notifEnabled(string $loggerId): bool
    {
        if (!Schema::hasColumn('klasifikasi_hujan', 'status')) {
            return true;
        }

        return DB::table('klasifikasi_hujan')
            ->where('logger_id', $loggerId)
            ->where('status', 1)
            ->exists();
    }

    /**
     * Per-category fallback (klasifikasi_threshold, range-based). Used when a
     * logger has no per-logger thresholds yet, so the Beranda status text stays
     * consistent with the icon (which already resolves from the category set).
     */
    public static function categoryStateLabel(?int $kategoriId, ?float $value): ?string
    {
        return self::categoryThreshold($kategoriId, $value)?->state_label;
    }

    public static function categoryStateKey(?int $kategoriId, ?float $value): ?string
    {
        return self::categoryThreshold($kategoriId, $value)?->state_key;
    }

    private static function categoryThreshold(?int $kategoriId, ?float $value)
    {
        if (!$kategoriId || $value === null) {
            return null;
        }

        $thresholds = DB::table('klasifikasi_threshold')
            ->where('id_kategori', $kategoriId)
            ->orderBy('sort_order')
            ->get(['state_key', 'state_label', 'min_value', 'max_value']);

        if ($thresholds->isEmpty()) {
            return null;
        }

        foreach ($thresholds as $threshold) {
            $min = is_numeric($threshold->min_value) ? (float) $threshold->min_value : null;
            $max = is_numeric($threshold->max_value) ? (float) $threshold->max_value : null;

            // Skip non-value states such as koneksi_terputus.
            if ($min === null && $max === null) {
                continue;
            }

            if (($min === null || $value >= $min) && ($max === null || $value < $max)) {
                return $threshold;
            }
        }

        return $thresholds->first(fn ($t) => $t->min_value !== null || $t->max_value !== null);
    }

    /** True for Hujan Sedang and heavier. */
    public static function isAlertIntensity(?string $intensitas): bool
    {
        if ($intensitas === null) {
            return false;
        }

        $s = strtolower(trim($intensitas));

        foreach (self::ALERT_INTENSITIES as $needle) {
            if (str_contains($s, $needle)) {
                return true;
            }
        }

        return false;
    }

    /** Numeric thresholds for a logger+period, ascending by value. */
    private static function thresholds(string $loggerId, string $period)
    {
        return DB::table('klasifikasi_hujan')
            ->where('logger_id', $loggerId)
            ->where('waktu', $period)
            ->get(['debit_air', 'intensitas'])
            ->filter(fn ($row) => is_numeric($row->debit_air))
            ->sortBy(fn ($row) => (float) $row->debit_air)
            ->values();
    }
}
