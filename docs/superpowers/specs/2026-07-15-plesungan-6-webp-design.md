# Plesungan Version 6 WebP Design

## Goal

Replace the active Plesungan version 5 artwork with the supplied version 6 artwork while keeping image loading reasonably light and preserving the clarity of thin pipe lines and transparent areas.

## Assets

The source assets are:

- `public/Pipa_Plesungan_6.png`
- `public/Detail_Pipa_Plesungan_6.png`

Both images are 5867 x 4400 pixels. The PNG files remain in the repository as source artwork.

The generated runtime assets will be:

- `public/Pipa_Plesungan_6.webp`
- `public/Detail_Pipa_Plesungan_6.webp`

## Conversion

Convert both PNG assets to WebP at their original 5867 x 4400 resolution with quality 90. Preserve transparency and do not resize or sharpen the artwork. This balances transfer size against the clarity of fine lines and avoids scaling-induced blur.

## Application Change

Update only the `plesungan` layer entries in `SkemaPipaController::schemes()` so that the base and detail layers reference the new version 6 WebP files. Keep the existing art canvas dimensions and marker coordinates unchanged because this change only replaces the artwork layers.

## Verification

Verify that:

1. Both WebP files can be decoded and retain the 5867 x 4400 dimensions.
2. Their combined file size is smaller than the source PNG files.
3. The Plesungan page references both new WebP filenames.
4. Existing Plesungan feature tests continue to pass.
5. The page renders both layers without missing-image errors and the artwork remains visually clear when zoomed.

## Scope

This change does not alter Plesungan data points, marker coordinates, zoom behavior, or Mojolaban assets.
