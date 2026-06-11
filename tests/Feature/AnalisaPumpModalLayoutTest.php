<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaPumpModalLayoutTest extends TestCase
{
    public function test_station_header_does_not_create_a_fixed_modal_containing_block(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $stationBarCssStart = strpos($view, '.station-bar {');
        $this->assertNotFalse($stationBarCssStart);

        $stationBarCssEnd = strpos($view, '}', $stationBarCssStart);
        $stationBarCss = substr($view, $stationBarCssStart, $stationBarCssEnd - $stationBarCssStart);

        $this->assertStringContainsString('@keyframes fade-in', $view);
        $this->assertStringContainsString('animation: fade-in .45s ease-out both;', $stationBarCss);
        $this->assertStringNotContainsString('animation: rise', $stationBarCss);

        $this->assertStringContainsString('.pump-modal-shell[role="dialog"].fixed {', $view);
        $this->assertStringContainsString('z-index: 2100 !important;', $view);
        $this->assertStringContainsString('<template x-teleport="body">', $view);
        $this->assertStringContainsString('class="pump-modal-shell fixed inset-0"', $view);
        $this->assertStringContainsString('class="absolute inset-0 z-0 bg-slate-900/40 transition-opacity"', $view);
        $this->assertStringContainsString('class="absolute inset-0 z-10 flex items-center justify-center overflow-y-auto p-4"', $view);
        $this->assertStringNotContainsString('class="fixed inset-0 z-[1100]" role="dialog"', $view);
    }
}
