<?php
namespace App\Services\Chatbot\Tools;

use App\Models\t_User;

interface ChatbotTool
{
    /** Nama unik tool, mis. "get_logger_history". */
    public function name(): string;

    /** Skema fungsi OpenAI: ['type'=>'function','function'=>[...]]. */
    public function schema(): array;

    /**
     * Eksekusi deterministik, ter-scope $user.
     * Return: ['text'=>string, 'data'?=>array, 'chart'?=>array].
     */
    public function run(array $args, t_User $user): array;
}
