<?php

namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\ProviderClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderClientStreamTest extends TestCase
{
    private function configureService(): void
    {
        config([
            'services.ai_chatbot.endpoint' => 'https://api.test/v1/chat/completions',
            'services.ai_chatbot.key'      => 'k',
            'services.ai_chatbot.model'    => 'gpt-5',
        ]);
    }

    public function test_stream_parses_sse_deltas_and_returns_full_string(): void
    {
        $this->configureService();

        $body = "data: {\"choices\":[{\"delta\":{\"content\":\"Ha\"}}]}\n\n"
              . "data: {\"choices\":[{\"delta\":{\"content\":\"lo\"}}]}\n\n"
              . "data: [DONE]\n\n";

        Http::fake(['*' => Http::response($body, 200)]);

        $collected = [];
        $result = app(ProviderClient::class)->stream(
            [['role' => 'user', 'content' => 'hi']],
            function (string $token) use (&$collected) {
                $collected[] = $token;
            }
        );

        $this->assertSame(['Ha', 'lo'], $collected, 'onToken should be called once per delta, in order');
        $this->assertSame('Halo', $result, 'stream() should return concatenated full string');
    }

    public function test_stream_skips_done_and_blank_lines(): void
    {
        $this->configureService();

        // Blank lines and [DONE] must not be passed to onToken
        $body = "\n"
              . "data: {\"choices\":[{\"delta\":{\"content\":\"X\"}}]}\n\n"
              . "data: \n\n"
              . "data: [DONE]\n\n";

        Http::fake(['*' => Http::response($body, 200)]);

        $collected = [];
        $result = app(ProviderClient::class)->stream(
            [['role' => 'user', 'content' => 'hi']],
            function (string $token) use (&$collected) {
                $collected[] = $token;
            }
        );

        $this->assertSame(['X'], $collected, 'Only real delta tokens should reach onToken');
        $this->assertSame('X', $result);
    }

    public function test_stream_returns_null_and_never_calls_ontokens_on_non_2xx(): void
    {
        $this->configureService();

        Http::fake(['*' => Http::response('err', 500)]);

        $called = false;
        $result = app(ProviderClient::class)->stream(
            [['role' => 'user', 'content' => 'hi']],
            function () use (&$called) {
                $called = true;
            }
        );

        $this->assertNull($result, 'stream() must return null for non-2xx responses');
        $this->assertFalse($called, 'onToken must never be called on error response');
    }
}
