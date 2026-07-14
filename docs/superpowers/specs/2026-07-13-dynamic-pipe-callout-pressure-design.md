# Dynamic Pipe Callout Pressure Design

## Summary

Make the pipe scheme callout able to choose which pressure values to show per point. The current callout always reads the first pressure parameter, so points with two pressure sensors cannot choose between Pressure 1 and Pressure 2.

## Goals

- Support per-point pressure display selection in the pipe scheme callout.
- Keep the default behavior safe for existing points.
- Preserve the current Flowrate and Totalizer display behavior.
- Avoid a database migration for the first implementation.
- Keep the change scoped to the pipe scheme controller, Blade view, and focused tests.

## Non-Goals

- No admin UI for editing the pressure display setting.
- No migration or schema change.
- No changes to unrelated skema pages.
- No changes to how sensor data is stored in `t_Loggers` or `parameter_sensor`.

## Pressure Display Contract

Each pipe point may expose a `pressure_display` value:

- `auto`: default behavior.
- `pressure_1`: show only Pressure 1.
- `pressure_2`: show only Pressure 2.
- `both`: show Pressure 1 and Pressure 2.

If a point does not define `pressure_display`, the backend treats it as `auto`.

The backend also sends separate pressure values:

- `pressure_1`: value from the Pressure 1 sensor, normally `sensor6`.
- `pressure_2`: value from the Pressure 2 sensor, normally `sensor7`.

For backward compatibility, the backend may keep `pressure` as an alias for `pressure_1`.

## Auto Behavior

For `auto`, the callout decides from the latest telemetry:

- If `pressure_2` is not `null`, show two rows: `Pressure 1` and `Pressure 2`.
- If `pressure_2` is `null`, show one row: `Pressure`.
- A value of `0` is valid data and must not be treated as missing.

This works with the current data shape where all logger configs may contain both pressure parameters, but only some latest rows have a real Pressure 2 value.

## Controller Design

`SkemaPipaController::attachLiveData()` will enrich every logger-backed point with:

- `pressure_1`
- `pressure_2`
- `pressure_display`
- existing `flowrate`
- existing `totalizer`

Pressure columns should be resolved by exact semantic names where possible:

- Prefer `parameter_utama` values such as `pressure_1` and `pressure_2`.
- Fall back to normalized `nama_parameter` values such as `pressure 1`, `pressure 2`, and `pressure`.

The broad first-match pressure lookup should no longer control the callout display, because it hides Pressure 2.

## View Design

The Blade callout will replace the single static Pressure row with a pressure rows container.

`showCallout(point, pinEl)` will render pressure rows dynamically:

- `pressure_1`: label `Pressure 1`, value from `point.pressure_1`.
- `pressure_2`: label `Pressure 2`, value from `point.pressure_2`.
- single-pressure display: label `Pressure`, value from the selected pressure.

The JavaScript should build rows using DOM APIs and `textContent` so point labels and values are not injected as HTML.

Flowrate and Totalizer rows stay as they are.

## Point Configuration

Initial configuration can live in the existing point arrays or model-backed point data that is already passed through `pointToArray()`.

Examples:

- A normal one-pressure point can omit `pressure_display` and use `auto`.
- A point that should show only Pressure 2 sets `pressure_display` to `pressure_2`.
- A point that should always show both sets `pressure_display` to `both`.

If the model does not yet have a column for `pressure_display`, hard-coded point definitions can use it now and database-backed points can default to `auto`.

## Testing

Add focused tests for the pipe scheme pressure enrichment and callout rendering contract:

- Pressure 1 and Pressure 2 are exposed separately from logger parameters.
- `0` in Pressure 2 is treated as present, not missing.
- Points without a display setting default to `auto`.
- The Blade view contains a dynamic pressure rows target instead of only one fixed Pressure value.

## Verification

- Run the new targeted test and confirm it fails before implementation.
- Implement the smallest controller and Blade changes required.
- Re-run the targeted test until it passes.
- Run the relevant existing feature tests for nearby skema/pressure behavior.
- Open the pipe scheme page and verify callout rows for a one-pressure point and a two-pressure point.

## Risks

- Some points are currently database-backed and may not carry custom display settings until a future migration. This version keeps them on `auto`.
- Existing logger parameter names must be handled carefully because `Pressure`, `Pressure 1`, and `Pressure 2` may all appear in historical data.
- The callout should not show Pressure 2 just because a parameter exists; it should show it in `auto` only when the latest value is not `null`.
