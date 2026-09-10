<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    private function document(): array
    {
        $path = resource_path('docs/mini-stesy-openapi.json');
        $this->assertFileExists($path);

        $document = json_decode(file_get_contents($path), true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error(), 'dokumen OpenAPI harus JSON valid');

        return $document;
    }

    public function test_openapi_document_is_well_formed(): void
    {
        $document = $this->document();

        $this->assertSame('3.0.3', $document['openapi']);
        $this->assertSame('http', $document['components']['securitySchemes']['BearerAuth']['type']);
        $this->assertSame('bearer', $document['components']['securitySchemes']['BearerAuth']['scheme']);
        $this->assertSame([['BearerAuth' => []]], $document['security'], 'default-nya semua endpoint butuh token');

        // Endpoint yang memang publik harus menimpa security default jadi kosong.
        foreach (['/api/datamasuk' => 'post', '/api/ping-awlr' => 'get', '/api/v1/mobile/auth/login' => 'post', '/api/v1/mobile/auth/config' => 'get'] as $path => $method) {
            $this->assertSame([], $document['paths'][$path][$method]['security'], "{$path} harus ditandai publik");
        }

        // Endpoint terproteksi tidak boleh ikut ditandai publik.
        $this->assertArrayNotHasKey('security', $document['paths']['/api/v1/mobile/data-perangkat']['get']);
    }

    public function test_every_documented_endpoint_is_a_real_route(): void
    {
        $document = $this->document();

        $registered = collect(Route::getRoutes())
            ->flatMap(fn ($route) => collect($route->methods())->map(fn ($m) => $m . ' ' . $route->uri()))
            ->unique()
            ->all();

        $verbs = ['get', 'post', 'put', 'patch', 'delete'];

        foreach ($document['paths'] as $path => $operations) {
            foreach (array_intersect_key($operations, array_flip($verbs)) as $method => $_) {
                $signature = strtoupper($method) . ' ' . ltrim($path, '/');
                $this->assertContains($signature, $registered, "didokumentasikan tapi tidak terdaftar: {$signature}");
            }
        }
    }

    public function test_mobile_api_routes_are_all_documented(): void
    {
        $document = $this->document();

        $documented = [];
        foreach ($document['paths'] as $path => $operations) {
            foreach ($operations as $method => $_) {
                $documented[] = strtoupper($method) . ' ' . ltrim($path, '/');
            }
        }

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/v1/mobile/')) {
                continue;
            }

            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'], true)) {
                    continue;
                }

                $signature = $method . ' ' . $route->uri();
                $this->assertContains($signature, $documented, "rute mobile belum didokumentasikan: {$signature}");
            }
        }
    }

    public function test_logger_access_rules_are_documented(): void
    {
        $description = $this->document()['info']['description'];

        // Aturan hak akses per user adalah inti dokumen ini; jangan sampai hilang saat diedit.
        foreach ([
            'Hak akses logger per user',
            'scopeForUser',
            'user_logger_access',
            'superadmin',
            'instansi_admin',
            'pegawai',
            'GET /api/v1/mobile/data-perangkat',
            '404',
        ] as $needle) {
            $this->assertStringContainsString($needle, $description, "keterangan hak akses kehilangan: {$needle}");
        }

        // Kredensial broker tidak boleh bocor ke dokumen.
        $raw = file_get_contents(resource_path('docs/mini-stesy-openapi.json'));
        $this->assertStringNotContainsString('b34c0n', $raw);
        $this->assertStringNotContainsString('userlog', $raw);
    }

    public function test_docs_page_serves_swagger_ui_and_needs_login(): void
    {
        $view = file_get_contents(resource_path('views/docs/api.blade.php'));
        $this->assertStringContainsString('swagger-ui-bundle.js', $view);
        $this->assertStringContainsString("route('docs.api.json')", $view);

        $this->get('/docs/api')->assertRedirect('/login');
        $this->get('/docs/api/openapi.json')->assertRedirect('/login');
    }
}
