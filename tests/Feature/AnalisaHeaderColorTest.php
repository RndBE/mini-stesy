<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaHeaderColorTest extends TestCase
{
    public function test_analisa_header_surfaces_use_consistent_white_background(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('--analysis-header: #ffffff;', $view);
        $this->assertStringContainsString('--analysis-header-text: #303481;', $view);
        $this->assertStringContainsString('background: var(--analysis-header);', $this->cssBlock($view, '.data-table thead th'));
        $this->assertStringContainsString('color: var(--analysis-header-text);', $this->cssBlock($view, '.data-table thead th'));
        $this->assertStringContainsString('border-bottom: 1px solid var(--hairline);', $this->cssBlock($view, '.data-table thead th'));
        $this->assertStringContainsString('background: var(--analysis-header);', $this->cssBlock($view, '#infoPanel .info-panel-header'));
        $this->assertStringContainsString('border-bottom: 1px solid var(--hairline);', $this->cssBlock($view, '#infoPanel .info-panel-header'));
        $this->assertStringContainsString('background: var(--analysis-header);', $this->cssBlock($view, '.doc-modal .doc-modal-header'));
        $this->assertStringContainsString('border-bottom: 1px solid var(--hairline);', $this->cssBlock($view, '.doc-modal .doc-modal-header'));
    }

    private function cssBlock(string $view, string $selector): string
    {
        $start = strpos($view, $selector . ' {');
        $this->assertNotFalse($start, "Missing CSS selector [{$selector}]");

        $end = strpos($view, '}', $start);
        $this->assertNotFalse($end, "Missing CSS block end for [{$selector}]");

        return substr($view, $start, $end - $start);
    }
}
