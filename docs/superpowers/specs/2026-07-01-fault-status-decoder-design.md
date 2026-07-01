# Fault Status Decoder — Design

**Date:** 2026-07-01
**Status:** Approved

## Problem

The `Fault` parameter (12 loggers, id 10368–10379, all `kolom_sensor = sensor3`) stores a **uint16 bitmask** where each bit represents a distinct flowmeter error/warning. The current code treats it as a plain number:

- **Peta popup** ([PetaController.php:298-299](../../../app/Http/Controllers/PetaController.php#L298-L299), [PetaApiController.php:133](../../../app/Http/Controllers/Api/PetaApiController.php#L133)): `0 → "Normal"`, anything else → `"Fault"`. Loses *which* fault.
- **Beranda AFMR card** ([afmr.blade.php:112-125](../../../resources/views/beranda/categories/afmr.blade.php#L112-L125)): same crude Normal/Fault.
- **Analisa** ([AnalisaController.php](../../../app/Http/Controllers/AnalisaController.php)): no fault handling. `aggregateValueFor` **averages** readings — meaningless for a bitmask (e.g. one `8192` averaged with many `0`s yields a fractional garbage value). Table/chart show the raw integer.
- **Export CSV**: raw integer (acceptable — CSV is raw data by convention).

## Bit Map (universal — 1 mapping for all 12 loggers)

| Bit | Decimal | Meaning |
|-----|---------|---------|
| 1 | 1 | Insulation error |
| 2 | 2 | Coil current error |
| 3 | 4 | Preamplifier overload |
| 4 | 8 | Database checksum error |
| 5 | 16 | Low power warning |
| 6 | 32 | Flow overload warning |
| 7 | 64 | Pulse A overload warning |
| 8 | 128 | Pulse B overload warning |
| 9 | 256 | Consumption interval warning |
| 10 | 512 | Leakage warning |
| 11 | 1024 | Empty pipe warning |
| 12 | 2048 | Low impedance warning |
| 13 | 4096 | Flow limit warning |
| 14 | 8192 | Reverse flow warning |
| 15–16 | — | Not used (ignored) |

Reading rule: bit *n* active when `value & (1 << (n-1)) != 0`. Value = sum of active bit decimals. Labels stay in **English**, verbatim from the datasheet.

## Decisions

- **1 universal mapping**, hardcoded (no per-logger config, no DB config table).
- **English labels** verbatim.
- Card / popup display: **compact summary + detail on hover/click** (not a stacked list inline).
- Analisa aggregation: **bitwise-OR** across the window ("which warnings were active during this period").
- Analisa chart: **kept** (not hidden). The **tooltip decodes** the bitmask into the warning list.
- Export CSV: **raw integer** (decode only in UI).

## Architecture

Single source of truth: a new helper class **`App\Support\FaultStatus`**. All display sites (peta, beranda, analisa) route through it. The JS side (Chart.js tooltip) receives the bit map from PHP via `@json(FaultStatus::bits())` so the mapping is never duplicated.

Rejected alternatives:
- Inline decode in each view/controller — duplication, drift.
- DB config table for bit→label — overkill; mapping is fixed and universal.

### `App\Support\FaultStatus`

```php
namespace App\Support;

class FaultStatus
{
    // bit number (1-based) => label. Bits 15/16 intentionally absent.
    private const BITS = [
        1 => 'Insulation error',
        2 => 'Coil current error',
        3 => 'Preamplifier overload',
        4 => 'Database checksum error',
        5 => 'Low power warning',
        6 => 'Flow overload warning',
        7 => 'Pulse A overload warning',
        8 => 'Pulse B overload warning',
        9 => 'Consumption interval warning',
        10 => 'Leakage warning',
        11 => 'Empty pipe warning',
        12 => 'Low impedance warning',
        13 => 'Flow limit warning',
        14 => 'Reverse flow warning',
    ];

    /** Bit map for the frontend (bit number => label). */
    public static function bits(): array;

    /** Active warning labels for a value. decode(0)=[], decode(1026)=['Coil current error','Empty pipe warning']. */
    public static function decode(int $value): array;

    /** True when any known bit (1..14) is set. */
    public static function isFault(int $value): bool;

    /** Compact card text: "Normal" | "Fault (N)" where N = count of active warnings. */
    public static function summary(int $value): string;

    /** Bitwise-OR of all values (analisa aggregation). combine([8192,2]) => 8194. */
    public static function combine(iterable $values): int;

    /** True when a parameter row is a Fault param (normalized nama_parameter contains 'fault'). */
    public static function isFaultParam($param): bool;
}
```

Notes:
- `decode`/`isFault`/`summary` mask off bits 15/16 (only bits 1..14 counted).
- Non-numeric / null input handled by callers (they already guard `-`).

## Integration Points

### 2a. Peta popup
[PetaController.php:298-299](../../../app/Http/Controllers/PetaController.php#L298-L299): replace the `0→Normal / else→Fault` branch with `FaultStatus::summary((int) $value)`. Add a decoded list (`FaultStatus::decode`) to the popup payload for hover/click detail. Mirror in [PetaApiController.php:133](../../../app/Http/Controllers/Api/PetaApiController.php#L133).

### 2b. Beranda AFMR card
[afmr.blade.php:112-125](../../../resources/views/beranda/categories/afmr.blade.php#L112-L125): `$faultLabel = FaultStatus::summary((int) $fault)`, `$faultOk = !FaultStatus::isFault((int) $fault)`. Wrap the badge with a tooltip (title attribute / popover) listing `FaultStatus::decode($fault)`. Colors unchanged: emerald = normal, rose = fault.

### 2c. Analisa
[AnalisaController.php](../../../app/Http/Controllers/AnalisaController.php):
- `aggregateValueFor($rows, $column, $tipeGraf)`: when the param is Fault, return `FaultStatus::combine($rows->pluck($column))` instead of `avg`. Read `sensor3` as **integer** — do not `round()` to float.
- Table data: the Fault value column renders `summary` + decoded list, not the raw integer.
- Chart: kept as a line. Inject `window.FAULT_BITS = @json(FaultStatus::bits())` and a JS helper `faultDecode(v)` / `faultSummary(v)` into the analisa view. In `buildChart` `tooltip.callbacks.label` ([analisa.blade.php:2554](../../../resources/views/analisadata/analisa.blade.php#L2554)): when the current param is Fault, return `faultDecode(y)` (array of strings → multi-line tooltip), or `"Normal"` when 0. A per-param `isFaultParam` flag flows from the controller/getChartData response to the frontend so the tooltip knows to decode.

### 2d. Export CSV
No change — Fault stays a raw integer in export.

## Testing

Unit test `App\Support\FaultStatus`:
- `decode(0) === []`
- `decode(1024) === ['Empty pipe warning']`
- `decode(1026) === ['Coil current error', 'Empty pipe warning']` (order by bit ascending)
- `decode(8192) === ['Reverse flow warning']`
- bits 15/16: `decode(0b1100000000000000)` ignores them → `[]`
- `isFault(0) === false`, `isFault(1) === true`
- `summary(0) === 'Normal'`, `summary(1026) === 'Fault (2)'`
- `combine([8192, 2]) === 8194`, `combine([]) === 0`
- `isFaultParam` matches nama_parameter "Fault"/"fault", rejects others

## Out of Scope

- Per-logger bit remapping.
- Localized (Indonesian) labels.
- Decoding in CSV export.
- Historical fault analytics / alerting.
