<?php

namespace App\Services\Chatbot;

use App\Models\t_User;
use App\Services\Chatbot\Tools\ChatbotTool;

class ToolRegistry
{
    /** @var array<string,ChatbotTool> */
    private array $tools = [];

    public function register(ChatbotTool $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    /** @return array<int,array> */
    public function schemas(): array
    {
        return array_values(array_map(fn (ChatbotTool $t) => $t->schema(), $this->tools));
    }

    public function run(string $name, array $args, t_User $user): array
    {
        $tool = $this->tools[$name] ?? null;
        if (! $tool) {
            return ['text' => "Tool \"{$name}\" tidak tersedia."];
        }

        try {
            return $tool->run($args, $user);
        } catch (\Throwable $e) {
            report($e);
            return ['text' => 'Maaf, data untuk permintaan itu tidak dapat diambil saat ini.'];
        }
    }
}
