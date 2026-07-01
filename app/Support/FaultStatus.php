<?php

namespace App\Support;

class FaultStatus
{
    /** bit number (1-based) => label. Bits 15/16 are "Not used" and intentionally absent. */
    private const BITS = [
        1  => 'Insulation error',
        2  => 'Coil current error',
        3  => 'Preamplifier overload',
        4  => 'Database checksum error',
        5  => 'Low power warning',
        6  => 'Flow overload warning',
        7  => 'Pulse A overload warning',
        8  => 'Pulse B overload warning',
        9  => 'Consumption interval warning',
        10 => 'Leakage warning',
        11 => 'Empty pipe warning',
        12 => 'Low impedance warning',
        13 => 'Flow limit warning',
        14 => 'Reverse flow warning',
    ];

    /** Bit map for the frontend (bit number => label). */
    public static function bits(): array
    {
        return self::BITS;
    }

    /** Active warning labels for a value, ascending by bit. */
    public static function decode(int $value): array
    {
        $labels = [];
        foreach (self::BITS as $bit => $label) {
            if (($value & (1 << ($bit - 1))) !== 0) {
                $labels[] = $label;
            }
        }
        return $labels;
    }

    /** True when any known bit (1..14) is set. */
    public static function isFault(int $value): bool
    {
        return self::decode($value) !== [];
    }

    /** Compact card text: "Normal" | "Fault (N)". */
    public static function summary(int $value): string
    {
        $count = count(self::decode($value));
        return $count === 0 ? 'Normal' : "Fault ($count)";
    }

    /** Bitwise-OR of all values (analisa aggregation). */
    public static function combine(iterable $values): int
    {
        $acc = 0;
        foreach ($values as $v) {
            $acc |= (int) $v;
        }
        return $acc;
    }

    /** True when a parameter_sensor row is a Fault param. */
    public static function isFaultParam(object $param): bool
    {
        $name = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', (string) ($param->nama_parameter ?? '')));
        return str_contains($name, 'fault');
    }
}
