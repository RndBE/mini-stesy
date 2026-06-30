<?php
namespace App\Services\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;

class LoggerHistoryTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}

    public function name(): string { return 'get_logger_history'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Agregat/historis satu pos pada rentang waktu (min/maks/rata-rata; hujan=akumulasi).',
            'parameters'=>['type'=>'object','properties'=>[
                'logger'=>['type'=>'string','description'=>'Nama/ID pos.'],
                'date_range'=>['type'=>'string','description'=>'Frasa periode, mis. "kemarin", "1-7 Juni", "bulan ini".'],
                'granularity'=>['type'=>'string','enum'=>['hourly','daily'],'description'=>'Granularitas opsional.'],
            ],'required'=>['logger','date_range']],
        ]];
    }

    public function run(array $args, t_User $user): array
    {
        $logger = $this->data->resolveLogger($user, (string)($args['logger'] ?? ''));
        if (! $logger) { return ['text' => 'Pos tidak ditemukan pada akses akun ini.']; }
        $range = $this->data->dateRange((string)($args['date_range'] ?? ''));
        if (! $range) { return ['text' => 'Rentang waktu tidak dapat dipahami. Sebutkan tanggal atau periode yang jelas.']; }
        $gran = isset($args['granularity']) ? (string)$args['granularity'] : null;
        return ['text' => $this->data->history($logger, $range, $gran)];
    }
}
