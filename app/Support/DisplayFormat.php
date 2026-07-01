<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class DisplayFormat
{
    /** Desimal override untuk user yang sedang login, atau null bila tak ada. */
    public static function decimalsForUser(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }
        $d = $user->decimal_places ?? null;

        return $d === null ? null : (int) $d;
    }

    /**
     * Pure formatter.
     * - non-numeric  → dikembalikan apa adanya (mempertahankan guard '-').
     * - $decimals null → nilai apa adanya (perilaku "tanpa format").
     * - selain itu   → number_format dengan $decimals.
     */
    public static function format($value, ?int $decimals): string
    {
        if (! is_numeric($value)) {
            return (string) $value;
        }
        if ($decimals === null) {
            return (string) $value;
        }

        return number_format((float) $value, $decimals);
    }

    /** Format nilai pengukuran: override user menang atas $default per-konteks. */
    public static function ukur($value, ?int $default = null): string
    {
        return self::format($value, self::decimalsForUser() ?? $default);
    }
}
