<?php

namespace Tests\Feature;

use Tests\TestCase;

class RiverChannelIllustrationTest extends TestCase
{
    public function test_river_illustration_follows_ilustrasi_awlr_svg(): void
    {
        $awlr  = file_get_contents(resource_path('views/beranda/categories/awlr.blade.php'));
        $river = file_get_contents(resource_path('views/beranda/categories/partials/river_channel.blade.php'));

        // AWLR sungai memakai partial + aset berlapis, bukan gambar prosedural lama.
        $this->assertStringContainsString("@include('beranda.categories.partials.river_channel')", $awlr);
        $this->assertStringNotContainsString('tiang-nonjiat', $awlr);
        $this->assertStringNotContainsString('riverGrad-', $awlr);

        $assets = ['sungai-belakang.svg', 'sungai-depan.svg'];
        foreach ($assets as $asset) {
            $this->assertFileExists(public_path('sungai/' . $asset));
            $this->assertStringContainsString("asset('sungai/{$asset}')", $river);
        }

        // fill="none" wajib: banyak path artwork hanya punya stroke, tanpa ini isinya jadi hitam.
        $this->assertStringContainsString('<svg viewBox="0 0 504 305" fill="none"', $river);

        // Air solid, tanpa transparansi: dinding tanggul yang terendam tidak boleh tembus.
        // Warnanya hasil komposit #8EDDFA 80% di atas putih pada artwork, jadi nada airnya sama.
        $this->assertStringContainsString("\$rvWaterFill = '#A5E4FB';", $river);
        $this->assertStringNotContainsString('fill-opacity', $river);
        // Badan air digambar sebelum bidang permukaan, mengikuti urutan artwork.
        $this->assertLessThan(
            strpos($river, '<path d="{{ $rvSurfPath }}" fill="{{ $rvWaterFill }}"'),
            strpos($river, '<path d="{{ $rvBodyPath }}" fill="{{ $rvWaterFill }}"'),
            'Badan air harus digambar sebelum bidang permukaan'
        );

        // Di artwork asli, di atas tepi belakang air tidak ada apa-apa (putih murni):
        // sisi jauh saluran memang tidak digambar. Mengisinya dengan dinding/dasar bikin
        // ilustrasi terlihat seperti sungai bertutup.
        $this->assertFileDoesNotExist(public_path('sungai/sungai-dasar.svg'));
        $this->assertStringNotContainsString('rvKering', $river);
        $this->assertStringNotContainsString('mask=', $river);
        $back = file_get_contents(public_path('sungai/sungai-belakang.svg'));
        $this->assertStringNotContainsString('#A08876', $back, 'Dinding jauh tambahan masih ada di aset');
        $this->assertSame(
            substr_count(file_get_contents(resource_path('views/beranda/categories/partials/river_channel.blade.php')), '<image '),
            2,
            'Ilustrasi sungai hanya boleh dua lapis aset'
        );

        // Nol skala di dasar saluran, elevasi_max di puncak tanggul: seluruh rentang
        // konfigurasi terpakai oleh gambar airnya.
        $this->assertStringContainsString('$rvScaleBotY = $rvBotY;', $river);
        $this->assertStringContainsString('$rvScaleTopY = $rvCrestY;', $river);
        $this->assertStringContainsString('$rvBotY      = 302.987;', $river);
        $this->assertStringContainsString('$rvCrestY    = 224.520;', $river);

        // Papan peil mengikuti geometri artwork: x 393..428,174, tinggi 135..303,056.
        $this->assertStringContainsString('M424.174 303.056H397', $river);
        $this->assertStringContainsString('$rvTickX     = 395.932;', $river);
        $this->assertStringContainsString('$rvMinorX2   = 407.656;', $river);
        $this->assertStringContainsString('$rvMajorX2   = 413.519;', $river);
        // Kerapatan tick dari jarak piksel, dan tick diteruskan di atas elevasi_max.
        $this->assertStringContainsString('$rvCandidate * $rvPpu >= 30.0', $river);
        $this->assertStringContainsString('if ($rvTickY < $rvBoardTopY)', $river);
        // Font label dihitung dari label yang benar-benar digambar, bukan cuma ujung skala.
        $this->assertStringContainsString("strlen(\$rvTick['label'])", $river);
        // Bacaan di luar rentang diberitahu, bukan dijepit diam-diam.
        $this->assertStringContainsString('$rvOutOfRange', $river);
        // SVG root punya overflow:hidden, jadi rounded-lg akan memotong artwork full-bleed.
        $this->assertStringNotContainsString('rounded-lg', $river);

        foreach ($assets as $asset) {
            $layer = file_get_contents(public_path('sungai/' . $asset));
            // Aset tidak boleh menggeser elemen dari posisi aslinya.
            $this->assertStringNotContainsString('translate', $layer, "Aset {$asset} menggeser elemen");
            // Papan peil dan air digambar dinamis, jadi tidak boleh ter-bake di aset.
            $this->assertStringNotContainsString('#FFD178', $layer, "Papan peil ter-bake di {$asset}");
            $this->assertStringNotContainsString('#8EDDFA', $layer, "Air ter-bake di {$asset}");
        }
    }
}
