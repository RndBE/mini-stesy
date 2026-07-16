# APMS Dashboard Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Match the APMS dashboard to the supplied reference, add salinity on sensor7, and reuse the existing rainfall presentation with consistent card borders.

**Architecture:** Keep the AWLR well in the existing shared monitoring-well partial. Extract ARR rainfall cards into a second shared partial consumed by ARR and APMS, while APMS owns only its soil-parameter lookup and two-row page layout. Add a corrective migration for installations where the first APMS migration already ran.

**Tech Stack:** Laravel 12, Blade, Tailwind CSS, PHPUnit.

## Global Constraints

- Reuse `beranda.categories.partials.monitoring_well` for the APMS well.
- APMS must not render pump hardware or pump elevation.
- Salinity uses `sensor7`, unit `PSU`, and `icons/awgr/salinity.svg`.
- Rainfall remains on `sensor6`; logger health remains on sensors 14–16.
- All APMS measurement, health, and rainfall card borders use `border-slate-200`.
- Reuse rainfall calculation, state labels, weather artwork, and analysis links from ARR.
- Run only the focused APMS test, Blade compilation, and diff checks.

---

### Task 1: Lock the revised APMS contract

**Files:**
- Modify: `tests/Feature/ApmsCategoryTest.php`

**Interfaces:**
- Consumes: APMS category template text, APMS Blade text, ARR Blade text.
- Produces: Contract assertions for salinity, rainfall reuse, shared well reuse, and layout headings.

- [ ] **Step 1: Add failing assertions**

Add mappings for:

```php
'salinity' => ['order' => 6, 'sensor' => 'sensor7'],
'hujan' => ['order' => 7, 'sensor' => 'sensor6'],
'humidity_logger' => ['order' => 8, 'sensor' => 'sensor14'],
'battery_logger' => ['order' => 9, 'sensor' => 'sensor15'],
'temperature_logger' => ['order' => 10, 'sensor' => 'sensor16'],
```

Also assert that ARR and APMS include
`beranda.categories.partials.rainfall_cards`, APMS includes `Data Pengukuran`,
`Data Logger`, and `Curah Hujan`, and the APMS source contains no custom
rainfall artwork duplication.

- [ ] **Step 2: Run the focused test and verify failure**

Run:

```bash
php artisan test tests/Feature/ApmsCategoryTest.php
```

Expected: FAIL because salinity and the shared rainfall partial are not yet
present.

### Task 2: Add salinity to the APMS data contract

**Files:**
- Create: `database/migrations/2026_07_16_010000_add_salinity_to_apms.php`
- Modify: `database/migrations/2026_07_16_000000_add_apms_category_and_parameters.php`
- Modify: `database/seeders/ListParameterSeeder.php`

**Interfaces:**
- Consumes: `list_parameter`, `template_kategori_parameter`,
  `parameter_sensor`, APMS category ID.
- Produces: APMS template order 1–10 and logger `30081` salinity mapping.

- [ ] **Step 1: Correct the original migration for clean installs**

Add salinity after soil temperature, map it to `sensor7`, move rainfall to
order 7 while keeping `sensor6`, and shift logger-health orders to 8–10.
Ensure parameter definition defaults also use the same sensor columns.

- [ ] **Step 2: Update the category seeder**

Apply the same APMS order and sensor mappings in
`ListParameterSeeder::$templates`.

- [ ] **Step 3: Add a corrective migration**

Create or locate the global `salinity` list parameter, update the APMS template,
and update-or-insert logger `30081`'s salinity row on `sensor7`. Update existing
APMS rainfall and health template orders without deleting data.

- [ ] **Step 4: Run the migration**

Run:

```bash
php artisan migrate --force
```

Expected: the corrective migration reports `DONE`.

### Task 3: Share rainfall cards between ARR and APMS

**Files:**
- Create: `resources/views/beranda/categories/partials/rainfall_cards.blade.php`
- Modify: `resources/views/beranda/categories/arr.blade.php`

**Interfaces:**
- Consumes: `$lg`, `$pRain`, `$curahHujanPerJam`, `$curahHujanHarian`,
  `$statusHujanPerJam`, `$statusHujanHarian`, `$stateHujanPerJam`,
  `$stateHujanHarian`, `$muted`.
- Produces: Two consistently bordered, linked rainfall cards.

- [ ] **Step 1: Extract the existing ARR rainfall card preparation and markup**

Move the rainfall display formatting, time-aware icon selection, analysis URL,
and both cards into `rainfall_cards.blade.php`.

- [ ] **Step 2: Normalize the surface**

Use `rounded-xl border border-slate-200 bg-white shadow-sm` on both cards.
Keep only `border-color`, `box-shadow`, and transform feedback under 200ms.
Use tabular numerals and balanced card headings.

- [ ] **Step 3: Replace ARR duplication**

Leave the ARR outer layout intact and include the new partial where the two
rainfall cards previously lived.

### Task 4: Implement the reference-driven APMS layout

**Files:**
- Modify: `resources/views/beranda/categories/apms.blade.php`

**Interfaces:**
- Consumes: shared monitoring well, shared rainfall cards, logger health cards.
- Produces: reference-aligned two-row APMS dashboard.

- [ ] **Step 1: Add salinity to APMS measurements**

Add:

```php
[
    'keys' => ['salinity'],
    'label' => 'Salinity',
    'unit' => 'PSU',
    'icon' => 'icons/awgr/salinity.svg',
],
```

Exclude groundwater level and rainfall from the right-hand soil list because
they have dedicated well and rainfall presentations.

- [ ] **Step 2: Build the top measurement section**

Render a `Data Pengukuran` heading, then an 8/4 desktop grid with the shared
well on the left and five full-width compact soil cards on the right.

- [ ] **Step 3: Build the lower section**

Render a two-column desktop grid. The left column contains `Data Logger` and
the existing health-card partial; the right contains `Curah Hujan` and the
shared rainfall partial.

- [ ] **Step 4: Polish cards**

Use one neutral border treatment, white surfaces, consistent icon tiles,
untruncated labels, tabular values, and responsive stacking. Do not add
animation, gradients, or dependencies.

### Task 5: Verify the focused change

**Files:**
- Test: `tests/Feature/ApmsCategoryTest.php`

**Interfaces:**
- Consumes: completed migration, seeders, partials, and APMS view.
- Produces: verified APMS contract and compilable Blade templates.

- [ ] **Step 1: Run the focused test**

```bash
php artisan test tests/Feature/ApmsCategoryTest.php
```

Expected: PASS.

- [ ] **Step 2: Compile Blade templates**

```bash
php artisan view:cache
```

Expected: `Blade templates cached successfully.`

- [ ] **Step 3: Check whitespace and generated CSS**

```bash
git diff --check
npm run build
```

Expected: no diff errors and Vite build succeeds.
