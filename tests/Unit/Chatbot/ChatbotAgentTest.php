<?php

namespace Tests\Unit\Chatbot;

use App\Models\t_User;
use App\Services\Chatbot\ChatbotAgent;
use App\Services\Chatbot\ContextEngine;
use App\Services\Chatbot\MonitoringData;
use App\Services\Chatbot\ProviderClient;
use App\Services\Chatbot\ToolRegistry;
use App\Services\Chatbot\Tools\ChatbotTool;
use App\Services\ChatbotPersona;
use Mockery;
use Tests\TestCase;

class ChatbotAgentTest extends TestCase
{
    public function test_ask_no_tool_returns_direct_reply(): void
    {
        $provider = Mockery::mock(ProviderClient::class);
        $provider->shouldReceive('configured')->andReturnTrue();
        $provider->shouldReceive('chat')->once()->andReturn(['content' => 'Selamat datang.']);

        $agent = $this->makeAgent($provider, new ToolRegistry());
        $out = $agent->ask(t_User::factory()->make(), 'halo');

        $this->assertSame('ai', $out['source']);
        $this->assertSame('Selamat datang.', $out['reply']);
    }

    public function test_ask_with_tool_call_executes_then_answers(): void
    {
        $registry = new ToolRegistry();
        $registry->register(new class implements ChatbotTool {
            public function name(): string { return 'list_loggers'; }
            public function schema(): array { return ['type' => 'function', 'function' => ['name' => 'list_loggers']]; }
            public function run(array $a, t_User $u): array { return ['text' => '{"offline_count":3}']; }
        });

        $provider = Mockery::mock(ProviderClient::class);
        $provider->shouldReceive('configured')->andReturnTrue();
        $provider->shouldReceive('chat')->twice()->andReturn(
            ['tool_calls' => [['id' => 'c1', 'type' => 'function', 'function' => ['name' => 'list_loggers', 'arguments' => '{}']]]],
            ['content' => 'Ada 3 logger offline.']
        );

        $agent = $this->makeAgent($provider, $registry);
        $out = $agent->ask(t_User::factory()->make(), 'berapa yang offline?');

        $this->assertStringContainsString('3 logger offline', $out['reply']);
    }

    private function makeAgent(ProviderClient $provider, ToolRegistry $registry): ChatbotAgent
    {
        $data = Mockery::mock(MonitoringData::class);
        $context = Mockery::mock(ContextEngine::class);
        $context->shouldReceive('lightContext')->andReturn(['user_name' => 'T']);
        $context->shouldReceive('history')->andReturn([]);
        return new ChatbotAgent($provider, $registry, $context, $data, app(ChatbotPersona::class));
    }
}
