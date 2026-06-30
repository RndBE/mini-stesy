<?php

namespace App\Services\Chatbot;

use App\Models\t_User;

/**
 * Provides a slim context for injection into the AI system prompt,
 * and normalises raw conversation turns into clean role/content pairs.
 */
class ContextEngine
{
    public function __construct(private MonitoringData $data) {}

    /**
     * Build a lightweight context array suitable for system-prompt injection.
     * Contains counts, name list, and category definitions — no heavy sensor data.
     *
     * @return array{
     *   user_name: string,
     *   server_time: string,
     *   logger_total_visible: int,
     *   logger_online_count: int,
     *   logger_offline_count: int,
     *   logger_names: list<string>,
     *   loggers_truncated: bool,
     *   category_definitions: array,
     * }
     */
    public function lightContext(t_User $user): array
    {
        $ctx   = $this->data->context($user);
        $facts = $this->data->groundedFacts($ctx);

        // Build "Nama (id_logger)" list from all_loggers (max 80 entries).
        // groundedFacts already slices all_loggers to 60; we accept whatever is there.
        $names = collect($facts['all_loggers'] ?? [])
            ->map(fn ($l) => ($l['nama'] ?? '-') . ' (' . ($l['id_logger'] ?? '-') . ')')
            ->take(80)
            ->values()
            ->all();

        $totalVisible = $facts['logger_total_visible'] ?? 0;

        return [
            'user_name'            => $facts['user_name'] ?? 'Pengguna',
            'server_time'          => $facts['server_time'] ?? now()->format('Y-m-d H:i:s'),
            'logger_total_visible' => $totalVisible,
            'logger_online_count'  => $facts['logger_online_count'] ?? 0,
            'logger_offline_count' => $facts['logger_offline_count'] ?? 0,
            'logger_names'         => $names,
            'loggers_truncated'    => count($names) < $totalVisible,
            'category_definitions' => $facts['category_definitions'] ?? $this->data->categoryDefinitions(),
        ];
    }

    /**
     * Normalise raw conversation turns into role/content pairs, keeping
     * the last 8 non-empty user/assistant turns.
     *
     * @param  array<int, array{role?: string, text?: string, content?: string}> $turns
     * @return list<array{role: string, content: string}>
     */
    public function history(array $turns): array
    {
        return collect($turns)
            ->filter(fn ($t) => in_array($t['role'] ?? '', ['user', 'assistant'], true))
            ->map(fn ($t) => [
                'role'    => $t['role'],
                'content' => trim((string) ($t['text'] ?? $t['content'] ?? '')),
            ])
            ->filter(fn ($t) => $t['content'] !== '')
            ->values()
            ->slice(-8)
            ->values()
            ->all();
    }
}
