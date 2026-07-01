<?php

namespace Tests\Feature;

use Tests\TestCase;

class BerandaAfmrFaultTest extends TestCase
{
    public function test_afmr_card_uses_faultstatus_helper(): void
    {
        $view = file_get_contents(resource_path('views/beranda/categories/afmr.blade.php'));

        $this->assertStringContainsString('FaultStatus::summary', $view);
        $this->assertStringContainsString('FaultStatus::isFault', $view);
        $this->assertStringContainsString('FaultStatus::decode', $view);
    }
}
