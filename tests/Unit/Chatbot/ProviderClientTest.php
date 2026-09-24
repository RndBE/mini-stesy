<?php
namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\ProviderClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderClientTest extends TestCase
{
    public function test_chat_returns_message_on_success(): void
    {
        config(['services.ai_chatbot.endpoint'=>'https://api.test/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'gpt-5']);
        Http::fake(['*'=>Http::response(['choices'=>[['message'=>['content'=>'halo']]]], 200)]);

        $msg = app(ProviderClient::class)->chat([['role'=>'user','content'=>'hi']]);
        $this->assertSame('halo', $msg['content']);
    }

    public function test_chat_returns_null_on_error(): void
    {
        config(['services.ai_chatbot.endpoint'=>'https://api.test/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'gpt-5']);
        Http::fake(['*'=>Http::response('boom', 500)]);

        $this->assertNull(app(ProviderClient::class)->chat([['role'=>'user','content'=>'hi']]));
    }

    public function test_chat_via_router_sends_max_tokens_and_disables_stream(): void
    {
        config(['services.ai_chatbot.endpoint'=>'http://127.0.0.1:20128/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'Chatbot']);
        Http::fake(['*'=>Http::response(['choices'=>[['message'=>['content'=>'halo']]]], 200)]);

        app(ProviderClient::class)->chat([['role'=>'user','content'=>'hi']]);

        Http::assertSent(fn ($req) => $req['max_tokens'] === 2000
            && $req['stream'] === false
            && ! array_key_exists('max_completion_tokens', $req->data()));
    }

    public function test_chat_to_openai_keeps_max_completion_tokens(): void
    {
        config(['services.ai_chatbot.endpoint'=>'https://api.openai.com/v1/chat/completions',
                'services.ai_chatbot.key'=>'k','services.ai_chatbot.model'=>'gpt-5']);
        Http::fake(['*'=>Http::response(['choices'=>[['message'=>['content'=>'halo']]]], 200)]);

        app(ProviderClient::class)->chat([['role'=>'user','content'=>'hi']]);

        Http::assertSent(fn ($req) => $req['max_completion_tokens'] === 2000
            && ! array_key_exists('max_tokens', $req->data()));
    }
}
