<?php

namespace App\Services;

use App\Models\ListParameter;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves the icon for a sensor parameter into a full, app-consumable URL.
 *
 * Priority:
 *   1. parameter_sensor.icon_app (per-device override)
 *   2. list_parameter.icon_app   (default for the parameter_utama)
 *
 * Returns null when no icon is configured, so the mobile app can fall back
 * to its own local icon map.
 */
class ParameterIconResolver
{
    /** @var array<string,string>|null Cached parameter_utama => icon_app map. */
    private static ?array $defaultsByUtama = null;

    public static function url(?string $iconApp, ?string $parameterUtama): ?string
    {
        $path = trim((string) $iconApp);

        if ($path === '') {
            $path = trim((string) self::defaultIconFor($parameterUtama));
        }

        if ($path === '') {
            return null;
        }

        return asset(ltrim($path, '/'));
    }

    private static function defaultIconFor(?string $parameterUtama): ?string
    {
        $key = strtolower(trim((string) $parameterUtama));
        if ($key === '') {
            return null;
        }

        return self::defaultsMap()[$key] ?? null;
    }

    /**
     * @return array<string,string>
     */
    private static function defaultsMap(): array
    {
        if (self::$defaultsByUtama !== null) {
            return self::$defaultsByUtama;
        }

        if (!Schema::hasTable('list_parameter')) {
            return self::$defaultsByUtama = [];
        }

        return self::$defaultsByUtama = ListParameter::query()
            ->whereNotNull('icon_app')
            ->where('icon_app', '!=', '')
            ->get(['parameter_utama', 'icon_app'])
            ->reduce(function (array $carry, ListParameter $row) {
                $utama = strtolower(trim((string) $row->parameter_utama));
                if ($utama !== '' && !isset($carry[$utama])) {
                    $carry[$utama] = (string) $row->icon_app;
                }
                return $carry;
            }, []);
    }
}
