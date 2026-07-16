# APMS Dashboard Layout Design

## Objective

Polish the APMS logger card to follow the supplied monitoring-dashboard
reference while preserving the existing AWLR well illustration and APMS sensor
contract.

## Measurement contract

APMS uses these mappings:

| Measurement | Sensor | Placement |
| --- | --- | --- |
| Groundwater level | `sensor1` | Callout in the well illustration |
| Soil pH | `sensor2` | Measurement list |
| Electrical conductivity | `sensor3` | Measurement list |
| Soil moisture | `sensor4` | Measurement list |
| Soil temperature | `sensor5` | Measurement list |
| Rainfall | `sensor6` | Rainfall accumulation cards |
| Salinity | `sensor7` | Measurement list |
| Logger humidity | `sensor14` | Data Logger |
| Logger battery | `sensor15` | Data Logger |
| Logger temperature | `sensor16` | Data Logger |

Salinity uses the existing global `salinity` parameter, unit `PSU`, and
`icons/awgr/salinity.svg`.

## Layout

The content below the logger header is divided into two rows.

### Data Pengukuran

- A full-width section is titled `Data Pengukuran`.
- The left side reuses `partials.monitoring_well` from AWLR.
- APMS continues to pass `showWellHardware = false`, so the pump body, pump
  connector, and pump-elevation callout are absent.
- The groundwater level remains visible in the well callout and is not
  duplicated in the measurement list.
- The right side contains five single-row cards, in this order: soil pH,
  electrical conductivity, soil moisture, soil temperature, and salinity.
- Each card has a compact icon tile, an uppercase label, a dominant
  tabular-number value, and its unit.

### Data Logger and Curah Hujan

- The lower row is split evenly on desktop and stacked on small screens.
- `Data Logger` displays the existing health cards vertically for humidity,
  battery, and temperature.
- `Curah Hujan` displays hourly and daily accumulation cards side by side.
- Rain calculation, state text, weather artwork, and analysis link are reused
  from ARR through a shared partial rather than copied into APMS.

## Visual rules

- All measurement, logger, and rainfall cards use the same
  `border-slate-200`, white surface, `rounded-xl`, and default Tailwind shadow.
- A single neutral/slate treatment is used across the card borders; individual
  parameter border colors are removed.
- Parameter icons may retain their existing artwork colors.
- Headings use balanced wrapping; live numeric values use tabular numerals.
- Interactive cards use a subtle border and shadow response without entrance
  animation, gradients, glow, or new dependencies.
- The desktop layout follows the reference image but remains one column on
  small screens.

## Data and migration behavior

- The APMS category template adds salinity at order 6 on `sensor7`.
- Rainfall moves to order 7 but remains on `sensor6`.
- Logger health parameters move to orders 8–10 and remain on sensors 14–16.
- The existing logger `30081` receives or updates the salinity parameter on
  `sensor7` without deleting existing measurements or logger-health mappings.
- A forward-only corrective migration updates existing installations because
  the original APMS migration may already have run.

## Reuse boundaries

- `monitoring_well.blade.php` remains the single well implementation for AWLR
  JIAT and APMS.
- A new rainfall-card partial becomes the shared rendering implementation for
  ARR and APMS.
- The APMS Blade remains responsible only for APMS parameter lookup and layout.

## Verification

- Extend the focused APMS contract test for salinity `sensor7`, rainfall
  `sensor6`, shared rainfall partial usage, shared well usage, and absence of
  pump UI in the APMS Blade.
- Compile Blade templates.
- Run `git diff --check`.
- Do not run the full application test suite.
