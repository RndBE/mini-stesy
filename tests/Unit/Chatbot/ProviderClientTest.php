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
}
