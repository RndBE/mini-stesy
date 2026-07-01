# Fault Status Decoder Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Decode the `Fault` uint16 bitmask parameter into human-readable warning labels across peta, beranda, and analisa displays.

**Architecture:** A single PHP helper `App\Support\FaultStatus` owns the bit→label mapping and all decode/aggregate logic. PHP display sites call it directly; the analisa Chart.js frontend receives the bit map via `@json(FaultStatus::bits())` so the mapping is never duplicated in JS.

**Tech Stack:** Laravel (PHP 8), PHPUnit 11, Blade, vanilla JS + Chart.js.

## Global Constraints

- Labels are **English**, verbatim from the datasheet (no localization).
- **One universal mapping** — bits 1..14 only; bits 15/16 are "Not used" and ignored.
- Bit *n* (1-based) has decimal value `1 << (n-1)`; a value is the sum of active bits.
- Aggregation of Fault over a time window = **bitwise-OR** (not average).
- Export CSV and the peta JSON API keep the **raw integer** (decode only in UI).
- Fault param identified by: normalized `nama_parameter` contains `fault` (lowercase, non-alphanumeric → `_`).

---

### Task 1: `App\Support\FaultStatus` helper + unit tests

**Files:**
- Create: `app/Support/FaultStatus.php`
- Test: `tests/Unit/FaultStatusTest.php`

**Interfaces:**
- Produces:
  - `FaultStatus::bits(): array` — `[1 => 'Insulation error', …, 14 => 'Reverse flow warning']`
  - `FaultStatus::decode(int $value): array` — active labels, ascending by bit
  - `FaultStatus::isFault(int $value): bool`
  - `FaultStatus::summary(int $value): string` — `'Normal'` | `'Fault (N)'`
  - `FaultStatus::combine(iterable $values): int` — bitwise-OR of all values (cast each to int)
  - `FaultStatus::isFaultParam(object $param): bool` — matches a `parameter_sensor` row

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/FaultStatusTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Support\FaultStatus;
use PHPUnit\Framework\TestCase;

class FaultStatusTest extends TestCase
{
    public function test_bits_map_has_14_labels_and_excludes_unused_bits(): void
    {
        $bits = FaultStatus::bits();
        $this->assertCount(14, $bits);
        $this->assertSame('Insulation error', $bits[1]);
        $this->assertSame('Reverse flow warning', $bits[14]);
        $this->assertArrayNotHasKey(15, $bits);
        $this->assertArrayNotHasKey(16, $bits);
    }

    public function test_decode_returns_empty_for_zero(): void
    {
        $this->assertSame([], FaultStatus::decode(0));
    }

    public function test_decode_single_bit(): void
    {
        $this->assertSame(['Empty pipe warning'], FaultStatus::decode(1024));
        $this->assertSame(['Reverse flow warning'], FaultStatus::decode(8192));
    }

    public function test_decode_multiple_bits_ascending_by_bit(): void
    {
        // 1026 = 2 (bit 2) + 1024 (bit 11)
        $this->assertSame(
            ['Coil current error', 'Empty pipe warning'],
            FaultStatus::decode(1026)
        );
    }

    public function test_decode_ignores_unused_high_bits(): void
    {
        // bit 15 (16384) + bit 16 (32768) only
        $this->assertSame([], FaultStatus::decode(16384 + 32768));
    }

    public function test_is_fault(): void
    {
        $this->assertFalse(FaultStatus::isFault(0));
        $this->assertTrue(FaultStatus::isFault(1));
        $this->assertFalse(FaultStatus::isFault(16384)); // only unused bit set
    }

    public function test_summary(): void
    {
        $this->assertSame('Normal', FaultStatus::summary(0));
        $this->assertSame('Fault (1)', FaultStatus::summary(1024));
        $this->assertSame('Fault (2)', FaultStatus::summary(1026));
    }

    public function test_combine_bitwise_ors_all_values(): void
    {
        $this->assertSame(8194, FaultStatus::combine([8192, 2]));
        $this->assertSame(0, FaultStatus::combine([]));
        $this->assertSame(6, FaultStatus::combine([2, 4, '2', null])); // casts, null->0
    }

    public function test_is_fault_param_matches_nama_parameter(): void
    {
        $fault = (object) ['nama_parameter' => 'Fault', 'parameter_utama' => null];
        $lower = (object) ['nama_parameter' => 'fault', 'parameter_utama' => null];
        $other = (object) ['nama_parameter' => 'Debit', 'parameter_utama' => null];

        $this->assertTrue(FaultStatus::isFaultParam($fault));
        $this->assertTrue(FaultStatus::isFaultParam($lower));
        $this->assertFalse(FaultStatus::isFaultParam($other));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/FaultStatusTest.php`
Expected: FAIL — `Class "App\Support\FaultStatus" not found`.

- [ ] **Step 3: Write minimal implementation**

Create `app/Support/FaultStatus.php`:

```php
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/FaultStatusTest.php`
Expected: PASS (9 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Support/FaultStatus.php tests/Unit/FaultStatusTest.php
git commit -m "feat: add FaultStatus bitmask decoder helper"
```

---

### Task 2: Peta popup — decoded summary + hover detail

**Files:**
- Modify: `app/Http/Controllers/PetaController.php` (method `displayParameterValue` ~L286-299; param payload in `buildParameterGroups` ~L257-267)
- Modify: `resources/views/peta/index.blade.php` (~L668-699)
- Test: `tests/Feature/PetaFaultDecodeTest.php`

**Interfaces:**
- Consumes: `FaultStatus::summary`, `FaultStatus::decode`, `FaultStatus::isFaultParam` from Task 1.
- Produces: each measurement param array gains a `fault_detail` key — an array of warning strings when the param is Fault, otherwise `null`.

> Note: `PetaApiController::133` and `PetaController::110` emit a **raw** `fault` number into JSON/API payloads (mobile/markers). Per Global Constraints those stay raw — out of scope. Only the popup param list (`buildParameterGroups` → `display_value`) shows decoded text.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/PetaFaultDecodeTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Support\FaultStatus;
use Tests\TestCase;

class PetaFaultDecodeTest extends TestCase
{
    public function test_display_parameter_value_summarizes_fault_bitmask(): void
    {
        $controller = new \App\Http\Controllers\PetaController();
        $method = new \ReflectionMethod($controller, 'displayParameterValue');
        $method->setAccessible(true);

        $param = (object) [
            'nama_parameter'  => 'Fault',
            'parameter_utama' => null,
            'kolom_sensor'    => 'sensor3',
            'satuan'          => '',
        ];

        $this->assertSame('Normal', $method->invoke($controller, $param, 0));
        $this->assertSame('Fault (1)', $method->invoke($controller, $param, 1024));
        $this->assertSame('Fault (2)', $method->invoke($controller, $param, 1026));
    }

    public function test_peta_blade_renders_fault_detail_tooltip(): void
    {
        $view = file_get_contents(resource_path('views/peta/index.blade.php'));
        $this->assertStringContainsString('fault_detail', $view);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/PetaFaultDecodeTest.php`
Expected: FAIL — `displayParameterValue` returns `'Fault'` (not `'Fault (1)'`); blade lacks `fault_detail`.

- [ ] **Step 3: Update `displayParameterValue`**

In `app/Http/Controllers/PetaController.php`, replace the fault branch inside `displayParameterValue`:

```php
        if (str_contains($key, 'fault') && is_numeric($value)) {
            return ((int) $value === 0) ? 'Normal' : 'Fault';
        }
```

with:

```php
        if (\App\Support\FaultStatus::isFaultParam($param) && is_numeric($value)) {
            return \App\Support\FaultStatus::summary((int) $value);
        }
```

- [ ] **Step 4: Add `fault_detail` to the param payload**

In `buildParameterGroups`, inside the `->map(function ($p) …)` return array (right after the `'display_value' => …` line ~L265), add:

```php
                    'fault_detail' => (\App\Support\FaultStatus::isFaultParam($p) && is_numeric($value))
                        ? \App\Support\FaultStatus::decode((int) $value)
                        : null,
```

- [ ] **Step 5: Render the tooltip in the popup**

In `resources/views/peta/index.blade.php`, the three spots that print `{{ $param['display_value'] ?? '-' }}` (~L668, L676, L699) sit inside a loop over `$measurementParams`. Wrap each value node so a Fault param shows its warnings on hover. Change the L699 span from:

```blade
                                                    <span class="{{ $valueClass }} font-semibold">{{ $param['display_value'] ?? '-' }}</span>
```

to:

```blade
                                                    <span class="{{ $valueClass }} font-semibold"
                                                        @if(!empty($param['fault_detail'])) title="{{ implode(', ', $param['fault_detail']) }}" @endif>{{ $param['display_value'] ?? '-' }}</span>
```

Apply the same `@if(!empty($param['fault_detail'])) title="…" @endif` attribute to the two other `display_value` nodes at ~L668 and ~L676 (they are `<div>` elements — add the attribute inline the same way).

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test tests/Feature/PetaFaultDecodeTest.php`
Expected: PASS (2 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/PetaController.php resources/views/peta/index.blade.php tests/Feature/PetaFaultDecodeTest.php
git commit -m "feat: decode Fault bitmask in peta popup with hover detail"
```

---

### Task 3: Beranda AFMR card — summary + tooltip

**Files:**
- Modify: `resources/views/beranda/categories/afmr.blade.php` (~L112-125)
- Test: `tests/Feature/BerandaAfmrFaultTest.php`

**Interfaces:**
- Consumes: `FaultStatus::summary`, `FaultStatus::isFault`, `FaultStatus::decode` from Task 1. `$fault` (raw value) and `$pFault` (param row) already exist in scope (`resources/views/beranda/index.blade.php:299-306`).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/BerandaAfmrFaultTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class BerandaAfmrFaultTest extends TestCase
{
    public function test_afmr_card_uses_faultstatus_helper(): void
    {
        $view = file_get_contents(resource_path('views/beranda/categories/afmr.blade.php'));

        $this->assertStringContainsString('FaultStatus::summary', $view);
        $this->assertStringContainsString('FaultStatus::isFault', $view);
        $this->assertStringContainsString('FaultStatus::decode', $view);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/BerandaAfmrFaultTest.php`
Expected: FAIL — blade still uses the inline `(int) $fault === 0 ? 'Normal' : 'Fault'` logic.

- [ ] **Step 3: Replace the inline fault logic**

In `resources/views/beranda/categories/afmr.blade.php`, replace the `@php … @endphp` block (~L113-118):

```blade
                    @php
                        $faultLabel = is_numeric($fault)
                            ? ((int) $fault === 0 ? 'Normal' : 'Fault')
                            : ($fault ?? '-');
                        $faultOk = is_numeric($fault) && (int) $fault === 0;
                    @endphp
```

with:

```blade
                    @php
                        $faultLabel = is_numeric($fault)
                            ? \App\Support\FaultStatus::summary((int) $fault)
                            : ($fault ?? '-');
                        $faultOk = is_numeric($fault) && !\App\Support\FaultStatus::isFault((int) $fault);
                        $faultDetail = is_numeric($fault)
                            ? \App\Support\FaultStatus::decode((int) $fault)
                            : [];
                    @endphp
```

- [ ] **Step 4: Add the tooltip to the value node**

In the same file, change the value div (~L125):

```blade
                            <div class="text-base font-extrabold {{ $faultOk ? 'text-emerald-700' : 'text-rose-700' }}">{{ $faultLabel }}</div>
```

to:

```blade
                            <div class="text-base font-extrabold {{ $faultOk ? 'text-emerald-700' : 'text-rose-700' }}"
                                @if(!empty($faultDetail)) title="{{ implode(', ', $faultDetail) }}" @endif>{{ $faultLabel }}</div>
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/BerandaAfmrFaultTest.php`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/views/beranda/categories/afmr.blade.php tests/Feature/BerandaAfmrFaultTest.php
git commit -m "feat: decode Fault bitmask in beranda AFMR card"
```

---

### Task 4: Analisa backend — bitwise-OR aggregation + `is_fault` flag

**Files:**
- Modify: `app/Http/Controllers/AnalisaController.php` (`aggregateValueFor` ~L640; `processData` ~L215 for the `$isFault` flag; the four aggregation call sites; both return arrays ~L204 and ~L437)
- Test: `tests/Feature/AnalisaFaultAggregationTest.php`

**Interfaces:**
- Consumes: `FaultStatus::combine`, `FaultStatus::isFaultParam` from Task 1.
- Produces: `processData` response array gains `'is_fault' => bool`. `aggregateValueFor` gains a 4th param `bool $isFault = false`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/AnalisaFaultAggregationTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Http\Controllers\AnalisaController;
use Tests\TestCase;

class AnalisaFaultAggregationTest extends TestCase
{
    public function test_aggregate_value_for_fault_uses_bitwise_or(): void
    {
        $controller = new AnalisaController();
        $method = new \ReflectionMethod($controller, 'aggregateValueFor');
        $method->setAccessible(true);

        $rows = collect([
            (object) ['sensor3' => 8192],
            (object) ['sensor3' => 2],
            (object) ['sensor3' => 0],
        ]);

        // isFault = true → OR = 8194, not average
        $this->assertSame(8194.0, $method->invoke($controller, $rows, 'sensor3', 'line', true));
        // isFault = false → average (unchanged behavior)
        $this->assertEqualsWithDelta(3398.0, $method->invoke($controller, $rows, 'sensor3', 'line', false), 0.001);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AnalisaFaultAggregationTest.php`
Expected: FAIL — `aggregateValueFor` has only 3 params; the 4th argument is ignored and it averages both cases.

- [ ] **Step 3: Update `aggregateValueFor`**

In `app/Http/Controllers/AnalisaController.php`, replace:

```php
    private function aggregateValueFor($rows, string $column, string $tipeGraf): float
    {
        if ($tipeGraf === 'bar') {
            return (float) $rows->sum($column);
        }

        return (float) $rows->avg($column);
    }
```

with:

```php
    private function aggregateValueFor($rows, string $column, string $tipeGraf, bool $isFault = false): float
    {
        if ($isFault) {
            return (float) \App\Support\FaultStatus::combine($rows->pluck($column));
        }

        if ($tipeGraf === 'bar') {
            return (float) $rows->sum($column);
        }

        return (float) $rows->avg($column);
    }
```

- [ ] **Step 4: Compute `$isFault` and pass it at each call site**

In `processData`, right after `$column = $param->kolom_sensor;` (~L216) add:

```php
        $isFault = \App\Support\FaultStatus::isFaultParam($param);
```

Then update all four `aggregateValueFor(...)` calls (in the `day`, `month`, `custom`, and `year` branches) from:

```php
                    $value = $this->aggregateValueFor($hourData, $column, $tipeGraf);
```

to pass the flag (replace `$hourData` / `$dayData` / `$dateData` / `$monthData` as appropriate):

```php
                    $value = $this->aggregateValueFor($hourData, $column, $tipeGraf, $isFault);
```

- [ ] **Step 5: Add `is_fault` to both return arrays**

In the early guard return (`if (!$param || !$logger->tabel_main)` ~L200-213) add to the array:

```php
                'is_fault'     => false,
```

In the main return (~L437-451) add:

```php
            'is_fault'     => $isFault,
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test tests/Feature/AnalisaFaultAggregationTest.php`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/AnalisaController.php tests/Feature/AnalisaFaultAggregationTest.php
git commit -m "feat: bitwise-OR aggregation and is_fault flag for Fault param in analisa"
```

---

### Task 5: Analisa frontend — chart tooltip + table decode

**Files:**
- Modify: `resources/views/analisadata/analisa.blade.php` (inject bit map + helpers near the top of the main `<script>`; tooltip callback ~L2554; `updateChart` ~L3082; `updateTable` ~L3082-3271)
- Test: `tests/Feature/AnalisaFaultChartTest.php`

**Interfaces:**
- Consumes: `App\Support\FaultStatus::bits()` (via `@json`), and `data.is_fault` from Task 4's response.
- Produces: JS globals `window.FAULT_BITS`, `window.faultDecode(v)`, `window.faultSummary(v)`; module var `currentIsFault`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/AnalisaFaultChartTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaFaultChartTest extends TestCase
{
    public function test_analisa_view_injects_fault_bit_map_and_helpers(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('window.FAULT_BITS', $view);
        $this->assertStringContainsString('Reverse flow warning', $view); // proves @json rendered the map
        $this->assertStringContainsString('function faultDecode', $view);
        $this->assertStringContainsString('function faultSummary', $view);
        $this->assertStringContainsString('currentIsFault', $view);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AnalisaFaultChartTest.php`
Expected: FAIL — none of these symbols exist yet.

- [ ] **Step 3: Inject the bit map and helpers**

In `resources/views/analisadata/analisa.blade.php`, immediately after the main `<script>` opening tag that contains the chart code (just before `function buildChart` — search for the `let chart` / chart state declarations near the top of that script block), add:

```blade
        window.FAULT_BITS = @json(\App\Support\FaultStatus::bits());
        let currentIsFault = false;

        function faultDecode(value) {
            const v = Number(value) | 0;
            const labels = [];
            for (const [bit, label] of Object.entries(window.FAULT_BITS)) {
                if ((v & (1 << (Number(bit) - 1))) !== 0) labels.push(label);
            }
            return labels;
        }

        function faultSummary(value) {
            const n = faultDecode(value).length;
            return n === 0 ? 'Normal' : `Fault (${n})`;
        }
```

- [ ] **Step 4: Decode in the chart tooltip**

In `buildChart`, replace the `tooltip.callbacks.label` function (~L2554):

```javascript
                                label: function(ctx) {
                                    const v = ctx.parsed.y;
                                    if (v === null || v === undefined) return null;
                                    const formattedVal = Number.isInteger(v) ? v : window.fmtUkur(v, 2);
                                    return isBar ? `${ctx.dataset.label}: ${formattedVal} mm` : `${ctx.dataset.label}: ${formattedVal}`;
                                }
```

with:

```javascript
                                label: function(ctx) {
                                    const v = ctx.parsed.y;
                                    if (v === null || v === undefined) return null;
                                    if (currentIsFault) {
                                        const labels = faultDecode(v);
                                        return labels.length ? labels : 'Normal';
                                    }
                                    const formattedVal = Number.isInteger(v) ? v : window.fmtUkur(v, 2);
                                    return isBar ? `${ctx.dataset.label}: ${formattedVal} mm` : `${ctx.dataset.label}: ${formattedVal}`;
                                }
```

- [ ] **Step 5: Set `currentIsFault` when data loads**

In `updateChart(data)` (~L3083), right after `if (!chart) return;` add:

```javascript
            currentIsFault = !!data.is_fault;
```

- [ ] **Step 6: Render decoded values in the table**

In `updateTable(data)`, the day-range render loop (~L3262-3271) builds rows with `fmtWithUnit(r.rerata, …)`. Make the value cell fault-aware. Replace that final loop:

```javascript
            let html = '';
            for (const r of filtered) {
                html += `<tr>
            <td>${r.waktu ?? '-'}</td>
            <td>${fmtWithUnit(r.rerata, unit, tableDecimals)}</td>
            <td>${fmtWithUnit(r.minimum, unit, tableDecimals)}</td>
            <td>${fmtWithUnit(r.maksimum, unit, tableDecimals)}</td>
        </tr>`;
            }
            tbody.innerHTML = html;
```

with:

```javascript
            let html = '';
            for (const r of filtered) {
                if (data.is_fault) {
                    const labels = faultDecode(r.rerata);
                    const cell = labels.length
                        ? `<span title="${labels.join(', ')}">${faultSummary(r.rerata)}</span>`
                        : 'Normal';
                    html += `<tr>
            <td>${r.waktu ?? '-'}</td>
            <td colspan="3">${cell}</td>
        </tr>`;
                } else {
                    html += `<tr>
            <td>${r.waktu ?? '-'}</td>
            <td>${fmtWithUnit(r.rerata, unit, tableDecimals)}</td>
            <td>${fmtWithUnit(r.minimum, unit, tableDecimals)}</td>
            <td>${fmtWithUnit(r.maksimum, unit, tableDecimals)}</td>
        </tr>`;
                }
            }
            tbody.innerHTML = html;
```

Apply the same fault-aware branch to the `custom`/`month` render loop (~L3231-3240) which has the identical 4-cell structure.

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test tests/Feature/AnalisaFaultChartTest.php`
Expected: PASS.

- [ ] **Step 8: Manual smoke check**

Run the app, open Analisa for a Fault logger (e.g. 10368), select the `Fault` parameter. Confirm: chart tooltip lists warning names (or "Normal"), and the table value column shows `Fault (N)` with a hover title listing warnings.

- [ ] **Step 9: Commit**

```bash
git add resources/views/analisadata/analisa.blade.php tests/Feature/AnalisaFaultChartTest.php
git commit -m "feat: decode Fault bitmask in analisa chart tooltip and table"
```

---

## Self-Review Notes

- **Spec coverage:** FaultStatus helper (Task 1), peta popup 2a (Task 2), beranda 2b (Task 3), analisa aggregation + chart tooltip 2c (Tasks 4-5), CSV raw / API raw left unchanged per constraints (noted in Task 2). All spec sections covered.
- **Type consistency:** `aggregateValueFor(..., bool $isFault)`, `is_fault` response key, `currentIsFault` JS var, `faultDecode`/`faultSummary` used consistently across tasks.
- **Chart plotting note:** Fault stays a line of raw bitmask values (per approved decision "keep chart"); the tooltip carries the meaning. Y-axis shape is not meaningful by design — acceptable trade-off the user chose.
