<?php

namespace Tests\Feature;

use Tests\TestCase;

class PetaMapSettingsPositionTest extends TestCase
{
    public function test_map_settings_button_is_anchored_to_the_desktop_left_edge(): void
    {
        $view = file_get_contents(resource_path('views/peta/index.blade.php'));

        $this->assertMatchesRegularExpression(
            '/\.map-settings-btn\s*\{[^}]*bottom:\s*12px;[^}]*left:\s*12px;[^}]*\}/s',
            $view
        );
    }

    public function test_map_settings_button_keeps_its_mobile_right_edge_position(): void
    {
        $view = file_get_contents(resource_path('views/peta/index.blade.php'));

        $this->assertMatchesRegularExpression(
            '/@media \(max-width:\s*1023px\)\s*\{.*?\.map-settings-btn\s*\{[^}]*left:\s*auto;[^}]*right:\s*12px;[^}]*\}/s',
            $view
        );
    }
}
