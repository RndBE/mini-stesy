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
        $this->assertStringContainsString('class="flex flex-col items-center gap-1 px-3 py-2.5 text-center sm:flex-row sm:justify-between sm:text-left"', $view);
        $this->assertStringNotContainsString('items-start gap-1 px-3 py-2.5', $view);
        $this->assertStringNotContainsString('items-end gap-1 px-3 py-2.5 text-right', $view);
        $this->assertStringContainsString('class="flex min-w-0 flex-wrap items-baseline justify-center gap-x-1 sm:justify-start"', $view);
    }

    public function test_pump_and_quality_section_headers_are_text_only_but_quality_rows_keep_icons(): void
    {
        $view = file_get_contents(resource_path('views/beranda/categories/partials/jiat_pump_panels.blade.php'));

        $this->assertStringContainsString('<div class="mb-2 text-md font-semibold text-slate-700">', $view);
        $this->assertStringNotContainsString('rounded-md bg-amber-100', $view);
        $this->assertStringNotContainsString('rounded-md bg-emerald-100', $view);
        $this->assertStringContainsString('asset($q[\'icon\'])', $view);
        $this->assertStringContainsString('{{ $q[\'chip\'] }}', $view);
        $this->assertStringContainsString('text-[11px] font-black', $view);
    }
}
