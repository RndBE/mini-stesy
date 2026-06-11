<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalisaHeaderActionsTest extends TestCase
{
    public function test_header_actions_use_compact_redesigned_buttons(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('.station-actions {', $view);
        $this->assertStringContainsString('.station-action {', $view);
        $this->assertStringContainsString('.station-action-primary {', $view);
        $this->assertStringContainsString('.station-action-warning {', $view);
        $this->assertStringContainsString('class="station-actions"', $view);
        $this->assertStringContainsString('class="station-action station-action-primary"', $view);
        $this->assertStringContainsString('class="station-action station-action-warning"', $view);
        $this->assertStringContainsString('class="station-action station-action-outline"', $view);
    }

    public function test_filter_toggle_is_hidden_on_desktop(): void
    {
        $view = file_get_contents(resource_path('views/analisadata/analisa.blade.php'));

        $this->assertStringContainsString('analysis-filter-toggle', $view);
        $this->assertStringContainsString('@media (min-width: 768px) {', $view);
        $this->assertStringContainsString('.analysis-filter-toggle {', $view);
        $this->assertStringContainsString('display: none !important;', $view);
    }
}
