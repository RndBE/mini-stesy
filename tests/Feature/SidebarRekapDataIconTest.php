<?php

namespace Tests\Feature;

use App\Models\t_User;
use Tests\TestCase;

class SidebarRekapDataIconTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->app['router']
            ->get('/__test/sidebar/rekap-data-active', fn () => view('partials.sidebar'))
            ->middleware('web')
            ->name('rekap-data.sidebar-icon-test');

        $this->app['router']
            ->get('/__test/sidebar/rekap-data-inactive', fn () => view('partials.sidebar'))
            ->middleware('web')
            ->name('sidebar-icon-test.inactive');
    }

    private function superadmin(): t_User
    {
        return new t_User(['level_user' => 'superadmin']);
    }

    public function test_rekap_data_uses_fill_icon_when_active(): void
    {
        $html = $this->actingAs($this->superadmin())
            ->get('/__test/sidebar/rekap-data-active')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<img[^>]+src="[^"]*icons\/rekap_data_fill\.svg"[^>]+alt="Rekap Data">/',
            $html
        );
    }

    public function test_rekap_data_uses_line_icon_when_inactive(): void
    {
        $html = $this->actingAs($this->superadmin())
            ->get('/__test/sidebar/rekap-data-inactive')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<img[^>]+src="[^"]*icons\/rekap_data\.svg"[^>]+alt="Rekap Data">/',
            $html
        );
    }

    public function test_rekap_data_line_icon_uses_consistent_sidebar_weight(): void
    {
        $svg = simplexml_load_file(public_path('icons/rekap_data.svg'));

        $this->assertNotFalse($svg);
        $svg->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');
        $paths = $svg->xpath('//svg:path');

        $this->assertCount(4, $paths);
        $this->assertSame('0.4', (string) $paths[0]['stroke-width']);

        foreach (array_slice($paths, 1) as $path) {
            $this->assertSame('1.6', (string) $path['stroke-width']);
        }
    }
}
