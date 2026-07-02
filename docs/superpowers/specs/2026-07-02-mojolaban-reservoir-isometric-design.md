# Mojolaban Reservoir Isometric Page Design

## Summary

Build a new authenticated page for the Wosusokas SPAM regional network around Reservoir Mojolaban. The page should visually follow the supplied reference image: a rich isometric operational diagram with an orange trunk pipe, blue distribution pipes, DMA pins, metric callouts, neighborhood labels, small buildings, roads, and trees. The first version uses dummy data only.

## Goals

- Add a dedicated Reservoir Mojolaban page without changing the existing Leuwigoong irrigation scheme page.
- Make the page feel close to the reference image, not like a plain schematic.
- Keep the experience useful as an operations screen: pan/zoom, readable labels, active DMA points, metric panels, status legend, and clickable details.
- Keep dummy topology and metric data in a clear structure that can later be replaced by API data.
- Preserve the app layout conventions: authenticated route, topbar title, no footer for full-screen skema pages, and responsive behavior.

## Non-Goals

- No live SCADA, GIS, database, or telemetry integration in this version.
- No 3D engine dependency.
- No editing or control commands.
- No changes to existing `/skema-irigasi`, `/skema-irigasi/peta`, or AWGC control flows.

## User Flow

1. User opens `/skema-irigasi/mojolaban`.
2. The page shows a full-screen isometric canvas titled `Reservoir Mojolaban - SPAM Regional Wosusokas`.
3. User can pan and zoom across the network.
4. User can click a reservoir or DMA marker.
5. A dark detail panel opens with dummy metrics, status, and a short location summary.
6. User can reset the view, toggle labels, toggle flow animation, and read the legend.

## Route And Navigation

- Add `GET /skema-irigasi/mojolaban` inside the existing authenticated skema route group.
- Route name: `skema-irigasi.mojolaban`.
- Controller method: `SkemaIrigasiController::mojolaban()`.
- Page title: `Reservoir Mojolaban`.
- Sidebar link should be placed near existing skema/monitoring items if a suitable section exists. If the existing sidebar does not expose a skema link, keep the route accessible directly and avoid unrelated sidebar restructuring.

## Visual Design

The visual should use inline SVG inside a full-screen Blade view. SVG is the right format because the reference is a scalable network diagram and this codebase already renders operational skema pages with SVG and panzoom.

Canvas characteristics:

- White or very light gray background.
- Isometric coordinate feel using skewed groups and diagonal trunk geometry.
- Orange main pipe from Reservoir Mojolaban through DMA 1, a central junction, DMA 5, and DMA 6.
- Orange branch to DMA 3 / Joho.
- Blue distribution networks branching from each DMA into smaller neighborhood grids.
- Gray road lines under the network.
- Small house clusters, trees, service boxes, hydrants, and valve rings for detail.
- Red location pins with green centers for DMA points.
- Reservoir tank near the lower-right area with circular base, blue water body, and inlet/outlet pipe.
- Floating metric callouts modeled after the reference:
  - `Pressure`
  - `Flowrate`
  - `Totalizer`
  - two small action icons drawn as SVG buttons for visual fidelity only.

Named areas:

- `RESERVOIR MOJOLABAN`
- `DMA 1`
- `DMA 3`
- `DMA 5`
- `DMA 6`
- `PALUR`
- `TRIYAGAN`
- `JOHO`
- `DEMAKAN`
- `DUKUH`

## Interaction Design

- Use `panzoom@9.4.0`, matching the existing skema page dependency style.
- Initial viewport should frame the whole network on common desktop widths.
- `Reset View` returns the pan/zoom to the initial framing.
- `Label` toggle hides/shows area and DMA labels.
- `Flow` toggle pauses/resumes animated pipe highlights.
- Hovering a DMA/reservoir should subtly emphasize the marker.
- Clicking a DMA/reservoir opens the detail panel.
- Clicking a non-interactive decoration should not open the panel.

## Dummy Data Model

Dummy data can be defined as PHP arrays passed from the controller to the Blade view, then encoded into JavaScript using `@json`.

Top-level structure:

- `reservoir`: id, label, capacity, level, inflow, outflow, status, x, y.
- `dmas`: id, name, area, x, y, pressure, flowrate, totalizer, status, updated_at.
- `areas`: name, x, y.
- `pipeSegments`: id, kind (`trunk` or `distribution`), points, flow_status.
- `decorations`: generated in JavaScript from deterministic arrays for houses, trees, and roads.

Sample dummy statuses:

- Reservoir: `online`, 72% level, 148 L/s outflow.
- DMA 1: normal.
- DMA 3: normal.
- DMA 5: attention, slightly lower pressure.
- DMA 6: normal.

## Files

- Modify `app/Http/Controllers/SkemaIrigasiController.php`
  - Add `mojolaban()` method that returns the view with dummy topology.
- Modify `routes/web.php`
  - Add authenticated route under the skema-irigasi group.
- Create `resources/views/skema/mojolaban.blade.php`
  - Full-screen SVG canvas, floating controls, legend, detail panel, and page-local CSS/JS.
- Optionally modify `resources/views/partials/sidebar.blade.php`
  - Add a link only if it can be placed without reshaping the existing navigation.
- Add/modify feature tests under `tests/Feature/`
  - Verify the route requires auth.
  - Verify an authenticated user can render the page.
  - Verify expected title/labels are present.

## Accessibility And Responsiveness

- SVG markers should have accessible labels using `aria-label` on clickable wrappers where practical.
- Control buttons should have visible text and `type="button"`.
- The page should remain usable at mobile widths via pan/zoom and fixed overlays that do not exceed viewport width.
- Text labels should not rely on negative letter spacing or viewport-scaled font sizes.

## Performance

- Keep the page self-contained and avoid heavy external assets.
- Use SVG primitives for houses, trees, roads, callouts, and tank details.
- Avoid rendering thousands of DOM nodes. The first version should stay below roughly 500 SVG elements.
- Animations should be CSS-based and pausable through a class toggle.

## Verification

- Run the relevant Laravel feature test for the new route.
- Run the full project test command after the targeted route test.
- Open `/skema-irigasi/mojolaban` in the browser.
- Verify desktop rendering: trunk pipe, distribution pipe, reservoir, labels, pins, callouts, panel, controls.
- Verify mobile rendering: no body horizontal overflow; pan/zoom remains usable.
- Verify console has no JavaScript errors.
- Verify existing `/skema-irigasi` still renders.

## Risks

- Pixel-perfect isometric fidelity can become time-consuming. The implementation should prioritize close visual resemblance and operational readability over exact duplication.
- Full-page SVG with many decorative elements can become hard to maintain. Data arrays and rendering helper functions should keep repeated houses, trees, pipes, pins, and callouts consistent.
- The existing skema view is already large. The Mojolaban page should be a separate Blade file to avoid increasing complexity in the existing Leuwigoong page.
