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
        $this->assertSame('http', $document['components']['securitySchemes']['BasicAuth']['type']);
        $this->assertSame('basic', $document['components']['securitySchemes']['BasicAuth']['scheme']);
        $this->assertSame([['BasicAuth' => []]], $document['security']);
    }

    public function test_only_the_three_integration_endpoints_are_documented(): void
    {
        $document = $this->document();

        // API integrasi sengaja kecil. Endpoint mobile dan ingest alat tidak
        // ikut didokumentasikan di sini.
        $this->assertSame([
            '/api/integrasi',
            '/api/integrasi/all_logger',
            '/api/integrasi/range_tanggal',
        ], array_keys($document['paths']));

        foreach ($document['paths'] as $path => $operations) {
            $this->assertSame(['get'], array_keys($operations), "{$path} hanya boleh GET");
            $this->assertArrayNotHasKey(
                'security',
                $operations['get'],
                "{$path} tidak boleh menimpa security default jadi publik",
            );
        }
    }

    public function test_every_documented_endpoint_is_a_real_route(): void
    {
        $document = $this->document();

        $registered = collect(Route::getRoutes())
            ->flatMap(fn ($route) => collect($route->methods())->map(fn ($m) => $m . ' ' . $route->uri()))
            ->unique()
            ->all();

        foreach ($document['paths'] as $path => $operations) {
            foreach ($operations as $method => $_) {
                $signature = strtoupper($method) . ' ' . ltrim($path, '/');
                $this->assertContains($signature, $registered, "didokumentasikan tapi tidak terdaftar: {$signature}");
            }
        }
    }

    public function test_integration_routes_are_all_documented(): void
    {
        $documented = array_keys($this->document()['paths']);

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/integrasi')) {
                continue;
            }

            $this->assertContains(
                '/' . $route->uri(),
                $documented,
                "rute integrasi belum didokumentasikan: {$route->uri()}",
            );
        }
    }

    public function test_logger_access_rules_are_documented(): void
    {
        $description = $this->document()['info']['description'];

        // Aturan hak akses per user adalah inti dokumen ini; jangan sampai
        // hilang saat diedit.
        foreach ([
            'Hak akses logger per user',
            'scopeForUser',
            'user_logger_access',
            'superadmin',
            'instansi_admin',
            'pegawai',
            'all_logger',
            'Logger Tidak Terdaftar',
        ] as $needle) {
            $this->assertStringContainsString($needle, $description, "keterangan hak akses kehilangan: {$needle}");
        }
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
