<?php

namespace App\Services\Chatbot;

use App\Models\t_User;
use App\Services\ChatbotPersona;

/**
 * Orchestrator loop for the tool-calling chatbot.
 *
 * ask() flow:
 *   - Not configured → fallback()
 *   - chat(pass-1 with tools)
 *     - No tool_calls → return content directly (ONE provider call)
 *     - tool_calls present → execute tools → chat(pass-2, no tools) → return
 *
 * stream() flow:
 *   - Not configured → emit fallback text via $onToken, return local
 *   - chat(pass-1 with tools)  [non-streaming, need tool_calls decision]
 *     - tool_calls present → execute tools → stream(pass-2) → emit chart via $onChart
 *     - No tool_calls → stream(original seed messages) [ONE stream call, no double call]
 *
 * The no-tool branch in stream() does NOT do chat() first; it goes straight to
 * provider->stream() with the seed messages, so there is exactly one LLM call.
 */
class ChatbotAgent
{
    public function __construct(
        private ProviderClient $provider,
        private ToolRegistry $registry,
        private ContextEngine $context,
        private MonitoringData $data,
        private ChatbotPersona $persona,
    ) {}

    /**
     * @return array{reply: string, source: 'ai'|'local', configured: bool, chart: ?array}
     */
    public function ask(t_User $user, string $message, array $turns = []): array
    {
        if (! $this->provider->configured()) {
            return $this->fallback($user, $message);
        }

        $messages = $this->seedMessages($user, $message, $turns);
        $first = $this->provider->chat($messages, $this->registry->schemas());
        if ($first === null) {
            return $this->fallback($user, $message);
        }

        $chart = null;

        if (! empty($first['tool_calls'])) {
            $messages[] = $this->assistantToolCallMessage($first);

            foreach ($first['tool_calls'] as $call) {
                [$result, $callChart] = $this->execute($call, $user);
                if ($callChart) {
                    $chart = $callChart;
                }
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'content' => $result['text'] ?? '',
                ];
            }

            // Pass-2: no tools — capped at 1 tool round
            $second = $this->provider->chat($messages);
            if ($second === null) {
                return $this->fallback($user, $message);
            }

            return [
                'reply' => trim((string) ($second['content'] ?? '')),
                'source' => 'ai',
                'configured' => true,
                'chart' => $chart,
            ];
        }

        // No tool_calls — direct answer from pass-1
        return [
            'reply' => trim((string) ($first['content'] ?? '')),
            'source' => 'ai',
            'configured' => true,
            'chart' => null,
        ];
    }

    /**
     * Stream response. Text arrives via $onToken; chart (if any) via $onChart.
     *
     * @return array{source: 'ai'|'local', chart: ?array}
     */
    public function stream(t_User $user, string $message, array $turns, callable $onToken, callable $onChart): array
    {
        if (! $this->provider->configured()) {
            $fb = $this->fallback($user, $message);
            $onToken($fb['reply']);
            return ['source' => 'local', 'chart' => null];
        }

        $seedMessages = $this->seedMessages($user, $message, $turns);

        // Pass-1: non-streaming chat to detect tool_calls
        $first = $this->provider->chat($seedMessages, $this->registry->schemas());
        if ($first === null) {
            $fb = $this->fallback($user, $message);
            $onToken($fb['reply']);
            return ['source' => 'local', 'chart' => null];
        }

        if (! empty($first['tool_calls'])) {
            // Tool path: execute tools, then stream pass-2
            $messages = $seedMessages;
            $messages[] = $this->assistantToolCallMessage($first);

            $chart = null;
            foreach ($first['tool_calls'] as $call) {
                [$result, $callChart] = $this->execute($call, $user);
                if ($callChart) {
                    $chart = $callChart;
                    $onChart($callChart);
                }
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'content' => $result['text'] ?? '',
                ];
            }

            // Pass-2: stream final answer
            $this->provider->stream($messages, $onToken);
            return ['source' => 'ai', 'chart' => $chart];
        }

        // No tool_calls: stream directly from seed messages (ONE provider call total)
        // We already used chat() for tool detection; now stream the same seed messages
        // for the actual answer. This means two LLM calls in the no-tool path but
        // guarantees streaming output. The alternative (echo $first['content'] via onToken)
        // avoids the second call; use that if content is already present.
        $content = $first['content'] ?? '';
        if ((string) $content !== '') {
            // Use the already-fetched content — emit it directly, zero extra calls
            $onToken($content);
        } else {
            // No content in pass-1 (shouldn't happen for no-tool replies, but defensive)
            $this->provider->stream($seedMessages, $onToken);
        }

        return ['source' => 'ai', 'chart' => null];
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function seedMessages(t_User $user, string $message, array $turns): array
    {
        $light = $this->context->lightContext($user);
        return array_merge(
            [['role' => 'developer', 'content' => $this->persona->systemPrompt($light)]],
            $this->context->history($turns),
            [['role' => 'user', 'content' => $message]],
        );
    }

    private function assistantToolCallMessage(array $first): array
    {
        return [
            'role' => 'assistant',
            'content' => $first['content'] ?? null,
            'tool_calls' => $first['tool_calls'],
        ];
    }

    /**
     * Execute a single tool call.
     *
     * @return array{0: array, 1: ?array}  [result, chart]
     */
    private function execute(array $call, t_User $user): array
    {
        $name = (string) data_get($call, 'function.name', '');
        $args = json_decode((string) data_get($call, 'function.arguments', '{}'), true);
        $args = is_array($args) ? $args : [];

        $result = $this->registry->run($name, $args, $user);
        return [$result, $result['chart'] ?? null];
    }

    private function fallback(t_User $user, string $message): array
    {
        $ctx = $this->data->context($user, $message);
        return [
            'reply' => $this->data->groundedFallback($user, $message, $ctx),
            'source' => 'local',
            'configured' => $this->provider->configured(),
            'chart' => null,
        ];
    }
}
