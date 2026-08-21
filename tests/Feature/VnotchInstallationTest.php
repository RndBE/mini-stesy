<?php

namespace Tests\Feature;

use Tests\TestCase;

class VnotchInstallationTest extends TestCase
{
    public function test_vnotch_is_an_awlr_non_jiat_installation_variant(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_08_19_000000_add_vnotch_installation_to_nonjiat_data_table.php'));
        $controller = file_get_contents(app_path('Http/Controllers/DeviceController.php'));
        $model = file_get_contents(app_path('Models/NonJiatData.php'));
        $awlr = file_get_contents(resource_path('views/beranda/categories/awlr.blade.php'));
        $partial = file_get_contents(resource_path('views/beranda/categories/partials/vnotch_weir.blade.php'));
        $deviceForm = file_get_contents(resource_path('views/device/index.blade.php'));
        $index = file_get_contents(resource_path('views/beranda/index.blade.php'));

        // Bukan kategori baru: tidak ada AVNR di dispatch maupun device setup.
        $this->assertStringNotContainsString('AVNR', $index);
        $this->assertStringNotContainsString('AVNR', $controller);
        $this->assertStringNotContainsString('beranda.categories.vnotch', $index);
        $this->assertFileDoesNotExist(resource_path('views/beranda/categories/vnotch.blade.php'));
        $this->assertFileDoesNotExist(public_path('kategori/avnr.svg'));

        // Sensor tetap ultrasonic/radar; pemasangan jadi field terpisah.
        $this->assertStringContainsString("'jenis_pemasangan'", $migration);
        $this->assertStringContainsString("->default('sungai')", $migration);
        $this->assertStringContainsString("'jenis_pemasangan',", $model);
        $this->assertStringContainsString("'jenis_sensor'      => 'nullable|in:ultrasonic,radar'", $controller);
        $this->assertStringContainsString("'jenis_pemasangan'  => 'nullable|in:sungai,v_notch'", $controller);

        // Kolom konfigurasi ambang tersimpan lewat jalur non-JIAT yang sudah ada.
        foreach (['elevasi_apex', 'kedalaman_notch'] as $column) {
            $this->assertStringContainsString("'{$column}'", $migration);
            $this->assertStringContainsString("'{$column}',", $model);
            $this->assertStringContainsString("name=\"{$column}\"", $deviceForm);
        }
        $this->assertStringContainsString("'jenis_pemasangan'    => \$validated['jenis_pemasangan'] ?? 'sungai'", $controller);
        $this->assertStringContainsString("'jenis_pemasangan'    => \$request->input('jenis_pemasangan', 'sungai')", $controller);

        // Satuan hanya label: TIDAK ada konversi nilai di mana pun.
        $this->assertStringNotContainsString('vnotchCmToM', $controller);
        $this->assertStringNotContainsString('applyPemasanganUnits', $deviceForm);
        $this->assertStringNotContainsString('onPemasanganChange', $deviceForm);
        $this->assertStringNotContainsString('$vnToCm', $partial);
        $this->assertStringNotContainsString('* 100.0', $partial);
        $this->assertStringNotContainsString('/ 100.0', $partial);
        // Label satuan ikut pemasangan, di form maupun kartu TMA.
        $this->assertSame(12, substr_count($deviceForm, "jenis_pemasangan === 'v_notch' ? 'cm' : 'm'"));
        $this->assertStringContainsString("\$vnUnit = 'cm';", $partial);

        // Aset harus setia ke ilustrasi_vnotch.svg: sensor/kabel tanpa pergeseran,
        // dan air hulu tetap transparan seperti fill-opacity aslinya.
        $this->assertStringContainsString('fill-opacity="0.5"', $partial);
        // SVG root punya overflow:hidden, jadi border-radius ikut meng-clip isi artwork.
        // Artwork v-notch full-bleed sampai pojok, jadi rounded-lg memotong keempat sudutnya.
        $this->assertStringNotContainsString('rounded-lg', $partial);
        // Papan peil mengikuti geometri papan di ilustrasi asli: x 111.5, lebar 26, radius 4.
        $this->assertStringContainsString('$vnBoardX   = 111.5;', $partial);
        $this->assertStringContainsString('$vnBoardW   = 26.0;', $partial);
        $this->assertStringContainsString('$vnBoardTop = 0.5;', $partial);
        $this->assertStringContainsString('$vnBoardBot = 128.5;', $partial);
        // Papan tetap -> rentang skala turunan dari papan, bukan sebaliknya.
        $this->assertStringContainsString('$vnScaleTopM = $vnFitTopM;', $partial);
        // Kerapatan tick berbasis jarak piksel supaya ikut tinggi papan.
        $this->assertStringContainsString('$vnMinMajorPx', $partial);
        $this->assertStringContainsString('$vnCandidate * $vnPpu >= $vnMinMajorPx', $partial);
        $this->assertStringContainsString('rx="4"', $partial);
        // Font label dihitung dari faktor lebar yang diukur, bukan ditebak.
        $this->assertStringContainsString('$vnLabelArea', $partial);
        $this->assertStringContainsString('0.62', $partial);
        foreach (['vnotch_belakang.svg', 'vnotch_depan.svg', 'vnotch_splash.svg'] as $asset) {
            $layer = file_get_contents(public_path('vnotch/' . $asset));
            $this->assertStringNotContainsString('translate', $layer, "Aset {$asset} menggeser elemen dari posisi aslinya");
        }
        $this->assertStringContainsString("\$tmaShowUnit = \$nonJiatMount === 'v_notch' ? 'cm'", $awlr);
        $this->assertStringContainsString('$tmaShow     = is_numeric($tma) ? (float) $tma : null;', $awlr);

        // Form: radio pemasangan untuk modal tambah dan edit.
        $this->assertSame(2, substr_count($deviceForm, 'name="jenis_pemasangan" value="v_notch"'));
        $this->assertStringContainsString("x-show=\"addData.jenis_pemasangan === 'v_notch'\"", $deviceForm);
        $this->assertStringContainsString("x-show=\"editData.jenis_pemasangan === 'v_notch'\"", $deviceForm);

        // AWLR memilih ilustrasi berdasarkan pemasangan, tiang sungai tetap default.
        $this->assertStringContainsString("\$lg->nonjiat?->jenis_pemasangan ?? 'sungai'", $awlr);
        $this->assertStringContainsString("@include('beranda.categories.partials.vnotch_weir')", $awlr);

        // Layout AWLR sungai TIDAK boleh berubah: Parameter Logger tetap penuh di bawah
        // ilustrasi (heading "Logger", 3 kartu sebaris), 9/3. Hanya v-notch yang 8/4
        // dengan Parameter Logger di kolom samping.
        $this->assertStringContainsString("\$nonJiatMount === 'v_notch' ? 'md:col-span-8' : 'md:col-span-9'", $awlr);
        $this->assertStringContainsString("\$nonJiatMount === 'v_notch' ? 'md:col-span-4' : 'md:col-span-3'", $awlr);
        $this->assertStringContainsString("@if (\$nonJiatMount === 'v_notch' && (\$pHumidity || \$pBattery || \$pTemp))", $awlr);
        $this->assertStringContainsString("@if (\$nonJiatMount !== 'v_notch' && (\$pHumidity || \$pBattery || \$pTemp))", $awlr);
        $this->assertStringContainsString("['gridClass' => 'grid grid-cols-3 gap-2']", $awlr);
        // Override grid partial harus punya default supaya apms/arr tak terpengaruh.
        $healthCards = file_get_contents(resource_path('views/beranda/categories/partials/logger_health_cards.blade.php'));
        $this->assertStringContainsString("\$gridClass ?? 'grid grid-cols-3 gap-2 md:grid-cols-1 md:gap-2'", $healthCards);
        // Ilustrasi sungai pindah ke partial sendiri (aset public/sungai/sungai-*.svg).
        $this->assertStringContainsString("@include('beranda.categories.partials.river_channel')", $awlr);

        // Aset ilustrasi terpisah jadi layer supaya air bisa disisipkan di tengah z-order.
        foreach (['vnotch_belakang.svg', 'vnotch_depan.svg', 'vnotch_splash.svg'] as $asset) {
            $this->assertFileExists(public_path('vnotch/' . $asset));
            $this->assertStringContainsString("asset('vnotch/{$asset}')", $partial);
        }

        // Air dan peil dinamis, nol peil terikat ke apex notch.
        foreach (['$vnHeadM', '$vnSurfY', '$vnPpu', '$vnTmaY'] as $symbol) {
            $this->assertStringContainsString($symbol, $partial);
        }
        $this->assertStringContainsString('$vnApexBack  = $vnApexY - $vnShift;', $partial);
        $this->assertStringContainsString('$vnApexBack - $vHead * $vnPpu', $partial);

        // Angka peil dibaca sebagai ketinggian air sekarang, bukan head.
        $this->assertStringContainsString('$vnLabelBase = $vnApexElev ?? 0.0;', $partial);
        $this->assertStringContainsString('$vnLabels[$i]', $partial);
        $this->assertStringContainsString('$vnNowText', $partial);

        // Badan air terpisah dari nappe: air tetap tampil walau tidak melimpah.
        $this->assertStringContainsString('$vnHasWater', $partial);
        $this->assertStringContainsString('$vnPoolBotM', $partial);
        $this->assertStringContainsString('$vnBelowApex', $partial);
        $this->assertStringContainsString('@if ($vnHasWater)', $partial);
        $this->assertStringContainsString('@if ($vnFlowing)', $partial);
        $this->assertStringContainsString('$vnFlowing   = $vnDrawM !== null && $vnDrawM > 0.0;', $partial);
        // Angka peil memakai level bertanda supaya level di bawah apex tetap benar.
        $this->assertStringContainsString('$vnNowValue = $vnLevelM !== null ? $vnLabelBase + $vnLevelM : null;', $partial);

        // Skala peil digerakkan elevasi_min/elevasi_max seperti AWLR sungai.
        $this->assertStringContainsString('$vnMinElev', $partial);
        $this->assertStringContainsString('$vnMaxElev', $partial);
        $this->assertStringContainsString('$vnDepthM = $vnMaxElev - $vnApexElev;', $partial);
        $this->assertStringContainsString('$vnScaleBotM = max($vnFitBotM, $vnWantBotM);', $partial);
        $this->assertStringContainsString('$vnScaleClipped', $partial);
        // px per meter tetap terikat geometri artwork, tidak boleh lepas dari notch.
        $this->assertStringContainsString('$vnPpu    = $vnDepthPx / $vnDepthM;', $partial);
        // Rentang yang tidak muat harus diberitahu, bukan dipotong diam-diam.
        $this->assertStringContainsString('@if ($vnScaleClipped)', $partial);

        // Ilustrasi dibatasi lebarnya supaya tidak memenuhi kolom.
        $this->assertStringContainsString('max-w-[480px]', $partial);

        // Aset tidak boleh lagi membawa papan peil statis.
        foreach (['vnotch_belakang.svg', 'vnotch_depan.svg'] as $asset) {
            $layer = file_get_contents(public_path('vnotch/' . $asset));
            $this->assertStringNotContainsString('#FFD178', $layer, "Papan peil masih ter-bake di {$asset}");
        }
    }
}
