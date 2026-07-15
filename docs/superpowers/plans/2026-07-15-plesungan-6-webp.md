# Plesungan 6 WebP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Switch the Plesungan pipe scheme to version 6 artwork served as WebP files, keeping the image sharp and lighter than the source PNGs.

**Architecture:** The Laravel controller continues to provide static layer filenames for the `plesungan` scheme. New WebP assets are generated from the supplied PNG v6 files at original dimensions, then the existing Blade rendering path uses those filenames without view changes.

**Tech Stack:** Laravel 12, PHP 8.2, PHP GD `imagewebp`, PHPUnit 11.

## Global Constraints

- Do not create any git commits.
- Keep `public/Pipa_Plesungan_6.png` and `public/Detail_Pipa_Plesungan_6.png` unchanged.
- Create `public/Pipa_Plesungan_6.webp` and `public/Detail_Pipa_Plesungan_6.webp`.
- Preserve original v6 dimensions: `5867x4400`.
- Encode WebP with PHP GD quality `90`.
- Update only the `plesungan` layer references.

---

### Task 1: Switch Plesungan Layers To Version 6 WebP

**Files:**
- Modify: `tests/Feature/SkemaPipaDynamicPressureCalloutTest.php`
- Create: `public/Pipa_Plesungan_6.webp`
- Create: `public/Detail_Pipa_Plesungan_6.webp`
- Modify: `app/Http/Controllers/SkemaPipaController.php`

**Interfaces:**
- Consumes: route `route('skema-pipa', 'plesungan')`
- Produces: controller schema values `layers.base = Pipa_Plesungan_6.webp` and `layers.detail = Detail_Pipa_Plesungan_6.webp`

- [x] **Step 1: Write the failing feature test**

Add this method to `tests/Feature/SkemaPipaDynamicPressureCalloutTest.php`:

```php
public function test_plesungan_uses_v6_webp_layers(): void
{
    $user = t_User::create([
        'nama' => 'Super Admin',
        'username' => 'super-plesungan-v6',
        'password' => 'secret',
        'level_user' => 'superadmin',
    ]);

    $this->actingAs($user)
        ->get(route('skema-pipa', 'plesungan'))
        ->assertOk()
        ->assertSee('Pipa_Plesungan_6.webp', false)
        ->assertSee('Detail_Pipa_Plesungan_6.webp', false)
        ->assertDontSee('Pipa_Plesungan5.webp', false)
        ->assertDontSee('Detail_Pipa_Plesungan_5.webp', false);
}
```

- [x] **Step 2: Run test to verify it fails**

Run:

```powershell
php artisan test tests/Feature/SkemaPipaDynamicPressureCalloutTest.php --filter=test_plesungan_uses_v6_webp_layers
```

Expected: FAIL because the current controller still renders `Pipa_Plesungan5.webp` and `Detail_Pipa_Plesungan_5.webp`.

- [x] **Step 3: Generate WebP assets from v6 PNGs**

Run:

```powershell
php -r '$pairs = [["public/Pipa_Plesungan_6.png", "public/Pipa_Plesungan_6.webp"], ["public/Detail_Pipa_Plesungan_6.png", "public/Detail_Pipa_Plesungan_6.webp"]]; foreach ($pairs as [$source, $target]) { $image = imagecreatefrompng($source); if (!$image) { fwrite(STDERR, "Failed to decode $source\n"); exit(1); } imagepalettetotruecolor($image); imagealphablending($image, false); imagesavealpha($image, true); if (!imagewebp($image, $target, 90)) { fwrite(STDERR, "Failed to encode $target\n"); imagedestroy($image); exit(1); } imagedestroy($image); }'
```

Expected: both target WebP files exist.

- [x] **Step 4: Verify generated WebP dimensions and sizes**

Run:

```powershell
php -r '$files = ["public/Pipa_Plesungan_6.webp", "public/Detail_Pipa_Plesungan_6.webp"]; foreach ($files as $file) { $size = getimagesize($file); echo $file . " " . $size[0] . "x" . $size[1] . " " . filesize($file) . PHP_EOL; }'
```

Expected: both files report `5867x4400`, and each WebP file is smaller than its matching PNG source.

- [x] **Step 5: Update the Plesungan controller layer filenames**

In `app/Http/Controllers/SkemaPipaController.php`, change only the `plesungan` block from:

```php
'layers' => [
    'base' => 'Pipa_Plesungan5.webp',
    'detail' => 'Detail_Pipa_Plesungan_5.webp',
],
```

to:

```php
'layers' => [
    'base' => 'Pipa_Plesungan_6.webp',
    'detail' => 'Detail_Pipa_Plesungan_6.webp',
],
```

- [x] **Step 6: Run focused test to verify it passes**

Run:

```powershell
php artisan test tests/Feature/SkemaPipaDynamicPressureCalloutTest.php --filter=test_plesungan_uses_v6_webp_layers
```

Expected: PASS.

- [x] **Step 7: Run relevant feature test file**

Run:

```powershell
php artisan test tests/Feature/SkemaPipaDynamicPressureCalloutTest.php
```

Expected: PASS.

- [x] **Step 8: Inspect git status without committing**

Run:

```powershell
git status --short
```

Expected: modified controller/test, new WebP assets, existing untracked PNGs and docs, with no commit created.
