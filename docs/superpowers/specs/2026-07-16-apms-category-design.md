# APMS Category Design

## Goal

Add `APMS` (`Automatic Peatland Monitoring System`) as a first-class logger category. Its dashboard card uses the existing monitoring-well visual language, measures peatland and soil conditions, and never displays pump artwork, pump controls, or pump-elevation information.

## Category and Parameter Template

Register APMS in existing databases through an idempotent migration and include it in the main seed data. Add an APMS category template with these sensor assignments:

| Sensor | Parameter | Key | Unit |
| --- | --- | --- | --- |
| `sensor1` | Tinggi Muka Air Tanah | `muka_air_tanah` | m |
| `sensor2` | pH Tanah | `ph_tanah` | - |
| `sensor3` | Electrical Conductivity | `electrical_conductivity` | uS/cm |
| `sensor4` | Kelembaban Tanah | `kelembaban_tanah` | % |
| `sensor5` | Temperature Tanah | `temperature_tanah` | C |
| `sensor6` | Curah Hujan | `hujan` | mm |
| `sensor14` | Humidity Logger | `humidity_logger` | % |
| `sensor15` | Battery Logger | `battery_logger` | Volt |
| `sensor16` | Temperature Logger | `temperature_logger` | C |

Reuse existing list parameters when the meaning matches, such as groundwater level, rainfall, and logger-health parameters. Add separate soil-specific list parameters so APMS labels and analysis links do not borrow water-quality or weather meanings.

## Device Configuration

Treat APMS as a monitoring-well category in device setup so it can store well depth and sensor depth in the existing well metadata. APMS does not expose the JIAT/non-JIAT choice, pump switch, or pump-depth input. When APMS well metadata is saved, pump depth is zero and `has_pump` is false.

Existing AWLR and AFMR setup behavior remains unchanged.

## Dashboard Card

Add a dedicated `beranda.categories.apms` view rather than adding APMS conditions throughout the already-large AWLR view.

The APMS card contains:

- The standard logger header and connection status.
- A monitoring-well illustration built from the existing `public/sumur` assets.
- Groundwater and sensor-depth callouts, but no pump image, pump line, pump cap, pump-elevation callout, flow card, or pump-control panel.
- Six measurement cards in the requested sensor order. Each card links to the matching analysis parameter.
- Three logger-health cards using the existing shared health-card partial.

Parameter presentation follows the nearest existing category patterns: groundwater from AWLR, pH and conductivity icon treatment from AWQR, rainfall from ARR/AWR, and logger health from shared dashboard cards. Soil pH, soil moisture, and soil temperature retain soil-specific labels even when an existing icon is reused.

## Data Flow

The category migration and seeders make APMS available in category management and provide its default parameters in device setup. Saving an APMS device creates its parameter rows from the template and stores well dimensions in the current well metadata table. `BerandaController` continues loading parameters and latest sensor data through existing logger relations; the APMS dashboard view resolves and displays the nine configured sensor values.

Missing sensor readings or well metadata display `-` and use the existing safe visual fallbacks. Offline rendering follows the same muted treatment as other categories.

## Verification

Keep verification lightweight:

1. Run PHP syntax checks on changed PHP files.
2. Compile Blade views with `php artisan view:cache`.
3. Run the existing frontend build only if frontend source changes require it.
4. Inspect the focused diff and confirm APMS contains no pump UI or pump behavior.

No broad or complex test suite is required for this change.

## Scope

This work adds the APMS category, its parameter defaults, device-setup behavior, and dashboard presentation. It does not create a sample APMS logger, change ingestion payloads, redesign analysis pages, add APMS map filters, or alter other category dashboards.
