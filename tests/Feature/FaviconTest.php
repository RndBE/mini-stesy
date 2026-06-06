<?php

namespace Tests\Feature;

use Tests\TestCase;

class FaviconTest extends TestCase
{
    public function test_head_uses_local_root_favicon(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(asset('favicon.ico'), false);
        $response->assertDontSee('be-jogja.com/assets/dist/img/title.ico');
    }

    public function test_root_favicon_file_is_not_empty(): void
    {
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
    }
}
