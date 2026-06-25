<?php

namespace Tests\Feature;

use Tests\TestCase;

class BerandaJiatPumpPanelResponsiveTest extends TestCase
{
    public function test_jiat_electric_phase_cards_are_mobile_responsive(): void
    {
        $view = file_get_contents(resource_path('views/beranda/categories/partials/jiat_pump_panels.blade.php'));

        $this->assertStringContainsString('class="grid grid-cols-1 gap-3 sm:grid-cols-3"', $view);
        $this->assertStringNotContainsString('class="grid grid-cols-3 gap-3"', $view);
        $this->assertStringContainsString('class="grid grid-cols-2 divide-x divide-slate-100 sm:block sm:divide-x-0 sm:divide-y"', $view);
        $this->assertStringNotContainsString('class="divide-y divide-slate-100"', $view);
        $this->assertStringContainsString('class="flex flex-col items-start gap-1 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between"', $view);
        $this->assertStringContainsString('class="flex flex-col items-end gap-1 px-3 py-2.5 text-right sm:flex-row sm:items-center sm:justify-between sm:text-left"', $view);
        $this->assertStringContainsString('class="flex min-w-0 flex-wrap items-baseline gap-x-1"', $view);
    }
}
