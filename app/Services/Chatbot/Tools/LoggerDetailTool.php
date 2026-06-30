<?php
namespace App\Services\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;

class LoggerDetailTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}

    public function name(): string { return 'get_logger_detail'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Ambil kondisi/pembacaan sensor terbaru satu pos/logger berdasarkan nama atau ID.',
            'parameters'=>['type'=>'object','properties'=>[
                'logger'=>['type'=>'string','description'=>'Nama atau ID pos, mis. "AWLR Sinduadi" atau "X1".'],
            ],'required'=>['logger']],
        ]];
    }

    public function run(array $args, t_User $user): array
    {
        $logger = $this->data->resolveLogger($user, (string)($args['logger'] ?? ''));
        if (! $logger) {
            return ['text' => 'Pos yang dimaksud tidak ditemukan pada akses akun ini. Periksa ejaan atau minta akses ke admin.'];
        }
        return ['text' => $this->data->summary($logger), 'data' => $logger];
    }
}
