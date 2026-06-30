<?php
namespace App\Services\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;

class ListLoggersTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}

    public function name(): string { return 'list_loggers'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Daftar & jumlah logger beserta status online/offline pada akses akun.',
            'parameters'=>['type'=>'object','properties'=>[
                'status'=>['type'=>'string','enum'=>['online','offline','all'],'description'=>'Filter status; default all.'],
            ]],
        ]];
    }

    public function run(array $args, t_User $user): array
    {
        $ctx = $this->data->context($user);
        $facts = $this->data->groundedFacts($ctx);
        $status = $args['status'] ?? 'all';

        $payload = [
            'total' => $facts['logger_total_visible'] ?? 0,
            'online_count' => $facts['logger_online_count'] ?? 0,
            'offline_count' => $facts['logger_offline_count'] ?? 0,
        ];
        if ($status !== 'offline') { $payload['online_loggers'] = $facts['online_loggers'] ?? []; }
        if ($status !== 'online') { $payload['offline_loggers'] = $facts['offline_loggers'] ?? []; }
        $payload['truncated'] = $facts['loggers_truncated'] ?? false;

        return ['text' => json_encode($payload, JSON_UNESCAPED_UNICODE), 'data' => $payload];
    }
}
