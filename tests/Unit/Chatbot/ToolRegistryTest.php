<?php

namespace Tests\Unit\Chatbot;

use App\Models\t_User;
use App\Services\Chatbot\ToolRegistry;
use App\Services\Chatbot\Tools\ChatbotTool;
use Tests\TestCase;

class ToolRegistryTest extends TestCase
{
    private function fakeTool(): ChatbotTool
    {
        return new class implements ChatbotTool {
            public function name(): string { return 'echo'; }
            public function schema(): array { return ['type' => 'function', 'function' => ['name' => 'echo']]; }
            public function run(array $args, t_User $user): array { return ['text' => 'ran:' . ($args['x'] ?? '')]; }
        };
    }

    public function test_schemas_lists_registered_tools(): void
    {
        $r = new ToolRegistry();
        $r->register($this->fakeTool());
        $this->assertCount(1, $r->schemas());
        $this->assertSame('echo', $r->schemas()[0]['function']['name']);
    }

    public function test_run_dispatches_by_name(): void
    {
        $r = new ToolRegistry();
        $r->register($this->fakeTool());
        $out = $r->run('echo', ['x' => 'hi'], t_User::factory()->make());
        $this->assertSame('ran:hi', $out['text']);
    }

    public function test_run_unknown_tool_returns_unavailable(): void
    {
        $r = new ToolRegistry();
        $out = $r->run('nope', [], t_User::factory()->make());
        $this->assertArrayHasKey('text', $out);
    }

    public function test_run_tool_exception_returns_friendly_text(): void
    {
        $throwingTool = new class implements ChatbotTool {
            public function name(): string { return 'boom'; }
            public function schema(): array { return ['type' => 'function', 'function' => ['name' => 'boom']]; }
            public function run(array $args, t_User $user): array { throw new \RuntimeException('kaboom'); }
        };

        $r = new ToolRegistry();
        $r->register($throwingTool);
        $out = $r->run('boom', [], t_User::factory()->make());
        $this->assertArrayHasKey('text', $out);
    }
}
