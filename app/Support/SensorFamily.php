<?php

namespace App\Support;

/**
 * Single source of truth for the supported sensor "families" (wide-table
 * variants). The system stores readings in wide tables t_s{family}_NN where
 * {family} is the column count: 16, 19, or 50.
 *
 * This centralises the family logic that used to be duplicated as
 * `str_contains($table,'19') ? 19 : 16` and `/^t_s(16|19)_\d{2,}$/` across the
 * ingestion controller, the device allocation logic and the read APIs.
 */
class SensorFamily
{
    /** Supported families, ascending. */
    public const FAMILIES = [16, 19, 50];

    /**
     * Map a logger's sensor_count to its storage family.
     *
     * Preserves the legacy threshold (>=19 ? 19 : 16) and appends the 50 tier
     * above 19, so existing 16/19 loggers route exactly as before:
     *   >=20 → 50 ; >=19 → 19 ; otherwise → 16
     */
    public static function familyFor(int $sensorCount): int
    {
        if ($sensorCount >= 20) {
            return 50;
        }
        if ($sensorCount >= 19) {
            return 19;
        }
        return 16;
    }

    /**
     * Number of sensor columns implied by a main/temp table name, read from the
     * family segment (e.g. t_s50_01 → 50). Falls back to 16 for anything that
     * does not look like a family table, matching the old default.
     */
    public static function maxSensorFor(string $tableName): int
    {
        if (preg_match('/^t_s(\d+)_\d{2,}$/', trim($tableName), $m)) {
            return (int) $m[1];
        }
        return 16;
    }

    /** Semantic alias of {@see maxSensorFor()} for table-name input. */
    public static function familyOf(string $tableName): int
    {
        return self::maxSensorFor($tableName);
    }

    /** Table-name prefix for a family, e.g. 16 → "t_s16_". */
    public static function mainTablePrefix(int $family): string
    {
        return 't_s' . $family . '_';
    }

    /** Latest-snapshot table for a given main table, e.g. t_s50_03 → temp_s50_latest. */
    public static function tempTableFor(string $mainTable): string
    {
        return 'temp_s' . self::maxSensorFor($mainTable) . '_latest';
    }

    /**
     * True if the name matches a supported family table pattern (regex only;
     * callers add Schema::hasTable() when existence matters).
     */
    public static function isFamilyTable(string $tableName): bool
    {
        $families = implode('|', self::FAMILIES);
        return (bool) preg_match('/^t_s(' . $families . ')_\d{2,}$/', trim($tableName));
    }
}
