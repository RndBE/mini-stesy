<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeviceClientPaginationTest extends TestCase
{
    public function test_device_pages_expose_consistent_client_pagination_contract(): void
    {
        $views = [
            resource_path('views/device/index.blade.php'),
            resource_path('views/device/data_perangkat.blade.php'),
        ];

        foreach ($views as $view) {
            $html = file_get_contents($view);

            $this->assertStringContainsString('currentPage: 1', $html);
            $this->assertStringContainsString('perPage: 10', $html);
            $this->assertStringContainsString('perPageOptions: [10, 25, 50]', $html);
            $this->assertStringContainsString('paginatedDevices()', $html);
            $this->assertStringContainsString('paginationPages()', $html);
            $this->assertStringContainsString('goToPage(page)', $html);
            $this->assertStringContainsString('previousPage()', $html);
            $this->assertStringContainsString('nextPage()', $html);
            $this->assertStringContainsString('rowNumber(index)', $html);
            $this->assertStringContainsString('x-for="(device, index) in paginatedDevices()"', $html);
            $this->assertStringContainsString('perPage = option; currentPage = 1; perPageOpen = false', $html);
            $this->assertStringContainsString('Menampilkan', $html);
            $this->assertStringContainsString('Sebelumnya', $html);
            $this->assertStringContainsString('Selanjutnya', $html);
            $this->assertStringContainsString('seen.has(d.id_logger)', $html);
        }
    }

    public function test_pagination_controls_wrap_without_squeezing_the_per_page_label(): void
    {
        $views = [
            resource_path('views/device/index.blade.php'),
            resource_path('views/device/data_perangkat.blade.php'),
        ];

        foreach ($views as $view) {
            $html = file_get_contents($view);

            $this->assertStringContainsString('xl:flex-row', $html);
            $this->assertStringContainsString('sm:flex-wrap', $html);
            $this->assertStringContainsString('shrink-0 whitespace-nowrap', $html);
        }
    }

    public function test_per_page_control_uses_one_rotating_custom_chevron(): void
    {
        $views = [
            resource_path('views/device/index.blade.php'),
            resource_path('views/device/data_perangkat.blade.php'),
        ];

        foreach ($views as $view) {
            $html = file_get_contents($view);

            $this->assertStringContainsString('x-data="{ perPageOpen: false }"', $html);
            $this->assertStringContainsString('@click="perPageOpen = !perPageOpen"', $html);
            $this->assertStringContainsString(":class=\"perPageOpen ? 'rotate-180' : ''\"", $html);
            $this->assertStringContainsString('x-show="perPageOpen"', $html);
            $this->assertStringContainsString('@click.outside="perPageOpen = false"', $html);
            $this->assertStringContainsString('@keydown.escape.window="perPageOpen = false"', $html);
            $this->assertStringContainsString('bottom-full', $html);
            $this->assertStringContainsString('w-14 items-center gap-1.5', $html);
            $this->assertStringNotContainsString('w-16 items-center justify-between', $html);
            $this->assertStringNotContainsString('<select x-model.number="perPage"', $html);
        }
    }
}
