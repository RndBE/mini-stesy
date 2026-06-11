<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaControlDeckLightPanelTest extends TestCase
{
    public function test_analisa_control_deck_uses_light_panel_styling(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('--deck: #ffffff;', $view);
        $this->assertStringContainsString('--deck-field: #f8fafc;', $view);
        $this->assertStringContainsString('--deck-text: #111827;', $view);
        $this->assertStringContainsString('.control-deck {', $view);
        $this->assertStringContainsString('background: #fff;', $view);
        $this->assertStringNotContainsString('background: linear-gradient(180deg, var(--deck-2), var(--deck) 70%);', $view);
        $this->assertStringContainsString('class="btn-success btn-success-soft"', $view);
        $this->assertStringNotContainsString('{{ $l->id_logger }} - {{ $l->nama_pos ?? \'Logger\' }}', $view);
        $this->assertStringContainsString('{{ $l->nama_pos ?? $l->nama_logger ?? \'Logger\' }}', $view);
    }
}
