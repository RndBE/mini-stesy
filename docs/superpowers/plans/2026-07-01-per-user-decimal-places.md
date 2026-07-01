# Per-User Decimal Places Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Untuk user tertentu (mis. `spam_wosusokas`), tampilkan semua nilai pengukuran sensor dengan jumlah desimal tetap (1), tanpa mengubah tampilan user lain.

**Architecture:** Tambah kolom `decimal_places` (nullable) di `t_user`. Satu helper terpusat `App\Support\DisplayFormat` jadi satu-satunya sumber kebenaran format angka; `NULL` → pakai desimal default per-konteks yang ada sekarang, angka → override semua pengukuran user itu. Untuk chart (JS), nilai desimal user di-inject ke `window.__decimalUkur` + helper `window.fmtUkur`. Semua titik tampil pengukuran diarahkan ke helper ini.

**Tech Stack:** Laravel 11, Blade, MariaDB 11.4, Chart.js (inline), PHPUnit.

## Global Constraints

- **Prod DB write gated.** DB pada `.env` menunjuk prod (`72.60.78.159` / `db_ministesynew`). `ALTER TABLE` + `UPDATE` TIDAK dijalankan ke prod tanpa persetujuan eksplisit user. Migration & SQL disiapkan; eksekusi ke prod ditunda.
- **User lain 0% berubah.** Setiap perubahan format harus mempertahankan desimal lama sebagai `$default`, sehingga user tanpa setting (`decimal_places = NULL`) melihat output yang identik dengan sebelumnya.
- **Cakupan = nilai pengukuran sensor.** Termasuk: TMA/muka air, debit, curah hujan, pH, suhu, flow rate/velocity, pressure, totalizer, luas penampang, elevasi/jarak sensor (live), humidity/battery/temperature logger. TIDAK termasuk: label skala/tick pada gauge SVG, nilai konfigurasi statis (kedalaman sumur/sensor/pompa), persentase kelengkapan data, count, koordinat, ID.
- **Semantik "1 desimal":** `number_format(value, d)` — dibulatkan, pemisah ribuan tetap seperti perilaku `number_format` existing (`12` → `12.0`, `1234.56` → `1,234.6`).

---

### Task 1: Migration kolom `decimal_places` + model

**Files:**
- Create: `database/migrations/2026_07_01_090000_add_decimal_places_to_t_user_table.php`
- Modify: `app/Models/t_User.php` (fillable + casts)

**Interfaces:**
- Produces: kolom `t_user.decimal_places` (nullable int); properti `$user->decimal_places` (?int) pada model `t_User`.

- [ ] **Step 1: Buat migration**

Create `database/migrations/2026_07_01_090000_add_decimal_places_to_t_user_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $table->unsignedTinyInteger('decimal_places')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $table->dropColumn('decimal_places');
        });
    }
};
```

- [ ] **Step 2: Tambahkan ke `$fillable` + `$casts` di `t_User`**

Di `app/Models/t_User.php`, ubah blok `$fillable` (baris 24-32) menjadi menyertakan `'decimal_places'`:

```php
    protected $fillable = [
        'nama',
        'username',
        'password',
        'level_user',
        'instansi_id',
        'status',
        'suspend_reason',
        'decimal_places',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
    ];
```

(Tambahkan blok `$casts` tepat setelah `$fillable` bila belum ada; model ini belum punya `$casts`.)

- [ ] **Step 3: Jalankan migration di LOKAL saja**

Run: `php artisan migrate`
Expected: `... add_decimal_places_to_t_user_table ... DONE`

> Jangan jalankan `migrate` yang menyentuh prod. Konfirmasi koneksi lokal dulu (`.env` lokal / sqlite testing). SQL untuk prod dicatat di Task 7, dieksekusi hanya atas persetujuan user.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_07_01_090000_add_decimal_places_to_t_user_table.php app/Models/t_User.php
git commit -m "feat: add nullable decimal_places column to t_user"
```

---

### Task 2: Helper `App\Support\DisplayFormat` + unit test

**Files:**
- Create: `app/Support/DisplayFormat.php`
- Test: `tests/Unit/DisplayFormatTest.php`

**Interfaces:**
- Produces:
  - `DisplayFormat::decimalsForUser(): ?int` — desimal user login, atau `null`.
  - `DisplayFormat::format($value, ?int $decimals): string` — pure; `null`/non-numeric → nilai apa adanya sbg string, else `number_format`.
  - `DisplayFormat::ukur($value, ?int $default = null): string` — resolusi: `decimalsForUser() ?? $default`, lalu `format()`.
- Consumes: `Illuminate\Support\Facades\Auth`, properti `t_User::decimal_places` (Task 1).

- [ ] **Step 1: Tulis test yang gagal**

Create `tests/Unit/DisplayFormatTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Support\DisplayFormat;
use PHPUnit\Framework\TestCase;

class DisplayFormatTest extends TestCase
{
    public function test_format_returns_raw_string_when_decimals_null(): void
    {
        $this->assertSame('12.34', DisplayFormat::format('12.34', null));
        $this->assertSame('62', DisplayFormat::format(62, null));
    }

    public function test_format_returns_dash_untouched_for_non_numeric(): void
    {
        $this->assertSame('-', DisplayFormat::format('-', 1));
        $this->assertSame('-', DisplayFormat::format('-', null));
    }

    public function test_format_applies_number_format_with_decimals(): void
    {
        $this->assertSame('12.0', DisplayFormat::format(12, 1));
        $this->assertSame('12.3', DisplayFormat::format(12.34, 1));
        $this->assertSame('1,234.6', DisplayFormat::format(1234.56, 1));
        $this->assertSame('12.34', DisplayFormat::format(12.34, 2));
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=DisplayFormatTest`
Expected: FAIL — `Class "App\Support\DisplayFormat" not found`.

- [ ] **Step 3: Implementasi helper**

Create `app/Support/DisplayFormat.php`:

```php
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
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=DisplayFormatTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Support/DisplayFormat.php tests/Unit/DisplayFormatTest.php
git commit -m "feat: add DisplayFormat helper for per-user decimal formatting"
```

---

### Task 3: Injeksi desimal user ke frontend (`window.__decimalUkur` + `fmtUkur`)

**Files:**
- Modify: `resources/views/layouts/app.blade.php:5` (setelah tag `<body ...>`)

**Interfaces:**
- Consumes: `DisplayFormat::decimalsForUser()` (Task 2).
- Produces (global JS): `window.__decimalUkur` (number|null); `window.fmtUkur(val, def)` → string. Dipakai Task 5/6 untuk mengganti `.toFixed(...)` pada nilai pengukuran.

- [ ] **Step 1: Tambahkan script global tepat setelah `<body>`**

Di `resources/views/layouts/app.blade.php`, tepat setelah baris 5 (`<body ... >`), sisipkan:

```blade
    <script>
        window.__decimalUkur = @json(\App\Support\DisplayFormat::decimalsForUser());
        window.fmtUkur = function (val, def) {
            var d = (window.__decimalUkur === null || window.__decimalUkur === undefined) ? def : window.__decimalUkur;
            if (d === null || d === undefined) return val;
            var n = Number(val);
            return isNaN(n) ? val : n.toFixed(d);
        };
    </script>
```

- [ ] **Step 2: Verifikasi manual render**

Run: `php artisan tinker --execute="echo view('layouts.app', ['slot' => ''])->render();"` bila layout butuh slot, ATAU cukup buka halaman mana pun saat menjalankan app dan cek di DevTools Console: `window.__decimalUkur` = `null` (user tanpa setting) dan `window.fmtUkur(12.34, 2)` = `"12.34"`.
Expected: `fmtUkur` terdefinisi; `__decimalUkur` = `null` untuk user default.

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "feat: expose per-user decimal setting to frontend JS"
```

---

### Transform Rules (dipakai Task 4–6)

Setiap titik tampil pengukuran diubah dengan salah satu rule berikut. **Selalu pertahankan guard `is_numeric(...) ? ... : '-'` yang sudah ada.**

- **Rule A — guarded `number_format` di Blade.**
  Ganti hanya bagian formatternya:
  `number_format((float) $X, N)` → `\App\Support\DisplayFormat::ukur($X, N)`
  Contoh: `{{ is_numeric($tma) ? number_format((float) $tma, 3) : '-' }}`
  → `{{ is_numeric($tma) ? \App\Support\DisplayFormat::ukur($tma, 3) : '-' }}`

- **Rule B — echo mentah nilai pengukuran (`{{ $X ?? '-' }}`).**
  Bungkus keseluruhan; default `null` → user biasa lihat nilai apa adanya, user override lihat N desimal:
  `{{ $X ?? '-' }}` → `{{ \App\Support\DisplayFormat::ukur($X ?? '-') }}`

- **Rule D — `.toFixed(N)` di JS pada nilai pengukuran.**
  `EXPR.toFixed(N)` → `window.fmtUkur(EXPR, N)`

> **Jangan** ubah: label tick/skala pada gauge SVG (mis. `{{ $v }}`, `{{ $afmrScaleMax }}`, `{{ $vRound }}`), nilai konfigurasi statis (`$lg->jiat->kedalaman_*`, `$depthVal`), persentase, count, koordinat.

---

### Task 4: Konversi Beranda (kategori + partials)

**Files:**
- Modify: `resources/views/beranda/categories/arr.blade.php:10,13`
- Modify: `resources/views/beranda/categories/awqr.blade.php:79,80,116,159,175,191`
- Modify: `resources/views/beranda/categories/afmr.blade.php:16,40,55,70,85,107,156,173,190,341,365,394,424,449,474,498,515,532`
- Modify: `resources/views/beranda/categories/awlr.blade.php:393,416,671,700,728,749,770`
- Modify: `resources/views/beranda/categories/default.blade.php:60`
- Modify: `resources/views/beranda/categories/partials/logger_health_cards.blade.php` (nilai humidity/battery/temp — via grep)
- Modify: `resources/views/beranda/categories/partials/jiat_flow_cards.blade.php`, `jiat_pump_panels.blade.php` (nilai pengukuran pompa/flow — via grep)

**Interfaces:**
- Consumes: `DisplayFormat::ukur()` (Task 2).

- [ ] **Step 1: arr.blade.php — Rule A pada 2 spot**

Baris 10: `? number_format((float) $curahHujanPerJam, 2)` → `? \App\Support\DisplayFormat::ukur($curahHujanPerJam, 2)`
Baris 13: `? number_format((float) $curahHujanHarian, 2)` → `? \App\Support\DisplayFormat::ukur($curahHujanHarian, 2)`

- [ ] **Step 2: awqr.blade.php**

- Baris 79 (Rule A): `number_format((float) $phVal, 2)` → `\App\Support\DisplayFormat::ukur($phVal, 2)`
- Baris 80 (Rule A): `number_format((float) $suhuVal, 1)` → `\App\Support\DisplayFormat::ukur($suhuVal, 1)`
- Baris 116 (grid pengukuran, saat ini tanpa format): ubah
  `$dispV = is_numeric($s['value']) ? $s['value'] : '-';`
  → `$dispV = is_numeric($s['value']) ? \App\Support\DisplayFormat::ukur($s['value']) : '-';`
- Baris 159/175/191 (humidity/battery/temp, Rule B): `{{ $humidity ?? '-' }}` → `{{ \App\Support\DisplayFormat::ukur($humidity ?? '-') }}`; idem `$battery`, `$temp`.

- [ ] **Step 3: afmr.blade.php — Rule A untuk number_format, Rule B untuk raw**

Rule A (ganti `number_format((float) $X, N)` → `\App\Support\DisplayFormat::ukur($X, N)`):
- 16 `$flowrate,2`; 40 `$totalizer1,2`; 55 `$totalizer2,2`; 70 `$pressure1,2`; 85 `$pressure2,2`; 341 `$luasPenampang,2`; 365 `$afmrDebit,2`; 394 `$flowVelocity,2`; 424 `$elevMukaAir,3`; 449 `$elevSensor,3`; 474 `$jarakSensor,2`.

Rule B (raw `{{ $X ?? '-' }}`):
- 107 `$fmBattery`; 156/173/190 `$humidity`/`$battery`/`$temp` (blok contact); 498/515/532 `$humidity`/`$battery`/`$temp` (blok non-contact).

> Jangan sentuh `{{ $v }}` (baris 292) & `{{ $afmrScaleMax }}` (baris 310) — itu label skala gauge.

- [ ] **Step 4: awlr.blade.php**

Rule B (raw): 393 `$dataAir`; 416 `$mukaAir`; 728/749/770 `$humidity`/`$battery`/`$temp`.
Rule A: 671 `number_format((float) $tma, 3)` → `\App\Support\DisplayFormat::ukur($tma, 3)`; 700 `number_format((float) $debit, 3)` → `\App\Support\DisplayFormat::ukur($debit, 3)`.

> Jangan sentuh 404/427 (`$lg?->jiat?->kedalaman_sensor`/`kedalaman_pompa`) & 265 (`$depthVal`) — konfigurasi statis, bukan pengukuran live. Jangan sentuh label skala (`{{ $vRound }}` baris 618, `{{ $scaleMax }}` 636).

- [ ] **Step 5: default.blade.php — Rule B**

Baris 60: `{{ $metric['value'] ?? '-' }}` → `{{ \App\Support\DisplayFormat::ukur($metric['value'] ?? '-') }}`

- [ ] **Step 6: Partials — konversi via grep**

Run: `grep -rn "number_format\|?? '-'\|?? \"-\"" resources/views/beranda/categories/partials/`
Untuk tiap kemunculan yang merupakan **nilai pengukuran sensor** (humidity, battery, temperature, debit pompa, flow, tekanan, dsb.), terapkan Rule A atau Rule B. Lewati nilai non-pengukuran (nama, status teks, tanggal).

- [ ] **Step 7: Cek sintaks Blade**

Run: `php artisan view:clear && php -l resources/views/beranda/categories/afmr.blade.php`
Expected: `No syntax errors detected` untuk tiap file yang diubah (ulangi per file, atau render halaman beranda saat app berjalan).

- [ ] **Step 8: Commit**

```bash
git add resources/views/beranda/categories/
git commit -m "feat: route beranda measurement displays through DisplayFormat"
```

---

### Task 5: Konversi Analisa (blade JS + tabel + CSV)

**Files:**
- Modify: `resources/views/analisadata/analisa.blade.php` (JS `.toFixed`, `number_format`, sel tabel — via grep)
- Modify: `app/Http/Controllers/AnalisaController.php` (hanya titik yang menyusun output CSV/tabel untuk ditampilkan — via grep)

**Interfaces:**
- Consumes: `window.fmtUkur` (Task 3), `DisplayFormat::ukur()` (Task 2).

**Catatan penting:** `round(...)` di `AnalisaController` menyiapkan data (dipakai chart & CSV). JANGAN ubah `round()` (menjaga presisi data). Override dilakukan di lapisan tampilan: JS chart via `fmtUkur`, sel tabel via `ukur`, dan saat menyusun string CSV via `ukur`.

- [ ] **Step 1: Petakan semua titik tampil di analisa.blade.php**

Run:
```bash
grep -n "toFixed\|number_format" resources/views/analisadata/analisa.blade.php
```
Titik yang diketahui dari eksplorasi (verifikasi baris terkini via grep di atas):
- `.toFixed(2)` tooltip nilai (≈2560), rainfall bar `.toFixed(2)+' mm'` (≈2561), y-axis ticks `.toFixed(2)` (≈2583), kartu total `.toFixed(3)` (≈2873), rata-rata `.toFixed(2)` (≈3069), tegangan baterai `.toFixed(2)+' V'` (≈2427).
- `number_format((float)$latestPhAwgr, 2)` (≈1986).

- [ ] **Step 2: Terapkan Rule D pada tiap `.toFixed(N)` yang memformat nilai pengukuran**

Untuk tiap kemunculan, `EXPR.toFixed(N)` → `window.fmtUkur(EXPR, N)`. Contoh:
- `value.toFixed(2)` → `window.fmtUkur(value, 2)`
- `total.toFixed(3)` → `window.fmtUkur(total, 3)`
- `avg.toFixed(2)` → `window.fmtUkur(avg, 2)`
- `(volt).toFixed(2) + ' V'` → `window.fmtUkur(volt, 2) + ' V'`

> Lewati `.toFixed(...)` yang bukan nilai pengukuran (mis. persentase progres, perhitungan posisi piksel/koordinat chart internal). Nilai axis tick pengukuran (mis. y-axis nilai) termasuk; axis waktu/kategori tidak.

- [ ] **Step 3: Terapkan Rule A pada `number_format` nilai pengukuran di blade**

`number_format((float) $latestPhAwgr, 2)` → `\App\Support\DisplayFormat::ukur($latestPhAwgr, 2)`. Terapkan pola sama untuk setiap `number_format` nilai pengukuran lain yang muncul di grep.

- [ ] **Step 4: Sel tabel & CSV**

Run: `grep -n "round(\|number_format\|fputcsv\|implode" app/Http/Controllers/AnalisaController.php`
- Untuk baris yang **menyusun nilai pengukuran untuk CSV/tabel yang ditampilkan** (mis. array baris sebelum `fputcsv`), format nilai lewat `\App\Support\DisplayFormat::ukur($nilai, N)` dengan `N` = desimal lama pada konteks itu.
- Untuk sel tabel di blade yang mengecho nilai (`{{ ... }}`), terapkan Rule A/B.
- JANGAN ubah baris `round(...)` yang hanya menyiapkan data JSON untuk chart.

- [ ] **Step 5: Cek sintaks**

Run: `php -l app/Http/Controllers/AnalisaController.php && php artisan view:clear`
Expected: `No syntax errors detected`.

- [ ] **Step 6: Commit**

```bash
git add resources/views/analisadata/analisa.blade.php app/Http/Controllers/AnalisaController.php
git commit -m "feat: route analisa measurement displays through DisplayFormat/fmtUkur"
```

---

### Task 6: Sweep sisa tampilan pengukuran (app-wide)

**Files:**
- Modify: view/controller lain yang menampilkan nilai pengukuran (mis. realtime monitoring, tabel/detail logger, partial peta/skema-irigasi, response API HTML) — ditemukan via grep.

**Interfaces:**
- Consumes: `DisplayFormat::ukur()` (Task 2), `window.fmtUkur` (Task 3).

- [ ] **Step 1: Inventaris kandidat**

Run:
```bash
grep -rn "toFixed\|number_format" resources/views app/Http/Controllers \
  | grep -v -E "beranda/categories|analisadata/analisa|DataMasuk|RekapData"
```

- [ ] **Step 2: Klasifikasi & konversi**

Untuk tiap baris hasil, tentukan apakah itu **nilai pengukuran sensor** (lihat definisi di Global Constraints). Jika ya, terapkan Rule A (Blade `number_format`), Rule B (echo mentah), atau Rule D (JS `.toFixed`). Jika bukan (persentase kelengkapan di `DataMasuk`/`RekapData`, koordinat, count, progress bar), lewati.

- [ ] **Step 3: Verifikasi tak ada pengukuran tersisa yang belum dialihkan**

Run: `grep -rn "number_format((float)" resources/views | grep -v DisplayFormat` dan tinjau sisa hasil; pastikan yang tersisa memang bukan pengukuran.

- [ ] **Step 4: Cek sintaks file yang diubah**

Run: `php artisan view:clear` lalu `php -l <tiap file php/controller yang diubah>`
Expected: `No syntax errors detected`.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: route remaining measurement displays through DisplayFormat"
```

---

### Task 7: Verifikasi & aktivasi untuk `spam_wosusokas` (prod gated)

**Files:** none (verifikasi + operasi data).

- [ ] **Step 1: Jalankan seluruh test**

Run: `php artisan test`
Expected: hijau (khususnya `DisplayFormatTest`).

- [ ] **Step 2: Verifikasi regressi user default (LOKAL)**

Dengan user tanpa `decimal_places` (NULL): buka Beranda & Analisa, bandingkan dengan sebelumnya — tampilan harus identik (desimal lama).

- [ ] **Step 3: Verifikasi perilaku override (LOKAL)**

Di DB lokal: `UPDATE t_user SET decimal_places = 1 WHERE username = 'spam_wosusokas';` (atau user uji lokal). Login sebagai user itu → semua pengukuran di Beranda & Analisa tampil 1 desimal; `window.__decimalUkur` = `1` di Console.

- [ ] **Step 4: Siapkan SQL prod (JANGAN eksekusi tanpa persetujuan user)**

SQL untuk prod (`db_ministesynew`):
```sql
ALTER TABLE t_user ADD COLUMN decimal_places TINYINT UNSIGNED NULL DEFAULT NULL AFTER status;
UPDATE t_user SET decimal_places = 1 WHERE username = 'spam_wosusokas';
```
Tunjukkan SQL ini ke user dan minta konfirmasi eksplisit sebelum dijalankan ke prod (mis. `php artisan migrate --database=prod` atau eksekusi SQL manual). Setelah dijalankan, verifikasi: `SELECT username, decimal_places FROM t_user WHERE username='spam_wosusokas';` → `1`.

- [ ] **Step 5: Commit dokumentasi bila perlu**

Tidak ada perubahan kode di task ini; commit hanya bila ada catatan/README diperbarui.

---

## Self-Review Notes

- **Spec coverage:** kolom DB (Task 1), helper terpusat (Task 2), injeksi JS (Task 3), konversi beranda/analisa/sisa (Task 4-6), verifikasi + prod gated (Task 7). ✔
- **Default preserved:** semua rule mempertahankan `$default` = desimal lama & guard `'-'`. ✔
- **Type consistency:** `decimalsForUser(): ?int`, `format($value, ?int): string`, `ukur($value, ?int $default = null): string`, `window.fmtUkur(val, def)` konsisten dipakai di Task 3-6. ✔
- **Exclusions eksplisit:** label skala SVG, konfigurasi statis, persentase/count/koordinat. ✔
