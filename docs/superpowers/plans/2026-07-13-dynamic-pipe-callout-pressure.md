# Dynamic Pipe Callout Pressure Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let each pipe scheme callout choose whether to show Pressure 1, Pressure 2, both, or automatic pressure rows.

**Architecture:** Keep the backend responsible for exposing separate `pressure_1` and `pressure_2` values, with `pressure` retained as a Pressure 1 alias. Keep the frontend responsible for rendering the selected pressure rows from `pressure_display`.

**Tech Stack:** Laravel controllers, Eloquent models, Blade, vanilla JavaScript, PHPUnit feature tests.

## Global Constraints

- Do not commit changes.
- Do not add a database migration for this first implementation.
- Preserve Flowrate and Totalizer behavior.
- Treat `0` as a valid Pressure 2 value.
- Keep existing dirty/untracked user changes intact.

---

### Task 1: Add Failing Feature Test

**Files:**
- Create: `tests/Feature/SkemaPipaDynamicPressureCalloutTest.php`

**Interfaces:**
- Consumes: `GET /skema-pipa/{scheme?}` route and `SkemaPipaController::index()`.
- Produces: a regression test that expects the Blade payload to expose `pressure_1`, `pressure_2`, `pressure_display`, and `pc-pressure-rows`.

- [ ] **Step 1: Write the failing test**

Create a feature test that builds in-memory `t_user`, `t_logger`, `parameter_sensor`, `temp_s16_latest`, and `pipa_points` tables. Insert one point with `sensor6 = 1.11` and `sensor7 = 0`. Render `route('skema-pipa', 'plesungan')` as a user and assert the HTML contains `"pressure_1":1.11`, `"pressure_2":0`, `"pressure_display":"auto"`, and `id="pc-pressure-rows"`.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/SkemaPipaDynamicPressureCalloutTest.php`

Expected: FAIL because the current payload does not expose `pressure_1` and the view does not have `pc-pressure-rows`.

### Task 2: Expose Separate Pressure Values

**Files:**
- Modify: `app/Http/Controllers/SkemaPipaController.php`

**Interfaces:**
- Consumes: logger params from `parameter_sensor`.
- Produces: point keys `pressure_1`, `pressure_2`, `pressure_display`, and legacy `pressure`.

- [ ] **Step 1: Implement minimal backend enrichment**

Update `attachLiveData()` to resolve `pressure_1` and `pressure_2` separately using `parameter_utama` first, then normalized parameter names. Keep flowrate and totalizer lookup intact. Set `pressure_display` to existing point value or `auto`.

- [ ] **Step 2: Run the targeted test**

Run: `php artisan test tests/Feature/SkemaPipaDynamicPressureCalloutTest.php`

Expected: still FAIL until the Blade dynamic rows target exists.

### Task 3: Render Dynamic Pressure Rows

**Files:**
- Modify: `resources/views/skema/pipa.blade.php`

**Interfaces:**
- Consumes: point keys `pressure_display`, `pressure_1`, and `pressure_2`.
- Produces: dynamic pressure rows in `#pc-pressure-rows`.

- [ ] **Step 1: Replace static pressure row target**

Replace the fixed `pc-press` row with `<div id="pc-pressure-rows"></div>`.

- [ ] **Step 2: Implement dynamic row rendering**

Update `showCallout()` so it creates rows with DOM APIs based on `pressure_display`: `auto`, `pressure_1`, `pressure_2`, or `both`.

- [ ] **Step 3: Run the targeted test**

Run: `php artisan test tests/Feature/SkemaPipaDynamicPressureCalloutTest.php`

Expected: PASS.

### Task 4: Verify Nearby Behavior

**Files:**
- No new files.

**Interfaces:**
- Consumes: completed controller, Blade, and test changes.
- Produces: verification evidence.

- [ ] **Step 1: Run nearby tests**

Run: `php artisan test tests/Feature/SkemaPipaDynamicPressureCalloutTest.php tests/Feature/BerandaAfmrPressureCardTest.php`

Expected: PASS.

- [ ] **Step 2: Inspect git diff**

Run: `git diff -- app/Http/Controllers/SkemaPipaController.php resources/views/skema/pipa.blade.php tests/Feature/SkemaPipaDynamicPressureCalloutTest.php`

Expected: only scoped changes for dynamic pressure callout.
