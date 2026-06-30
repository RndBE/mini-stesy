<?php
namespace App\Services\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;

class LoggerChartTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}

    public function name(): string { return 'get_logger_chart'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Buat grafik deret waktu satu pos (untuk permintaan "grafik/visualisasi"). Mengembalikan data grafik untuk ditampilkan.',
            'parameters'=>['type'=>'object','properties'=>[
                'logger'=>['type'=>'string','description'=>'Nama/ID pos.'],
                'date_range'=>['type'=>'string','description'=>'Frasa periode opsional; default 7 hari.'],
                'granularity'=>['type'=>'string','enum'=>['hourly','daily'],'description'=>'Granularitas opsional.'],
            ],'required'=>['logger']],
        ]];
    }

    public function run(array $args, t_User $user): array
    {
        $logger = $this->data->resolveLogger($user, (string)($args['logger'] ?? ''));
        if (! $logger) { return ['text' => 'Pos untuk grafik tidak ditemukan pada akses akun ini.']; }
        $range = isset($args['date_range']) ? $this->data->dateRange((string)$args['date_range']) : null;
        $range = $range ?: $this->data->defaultWeekRange();
        $gran = isset($args['granularity']) ? (string)$args['granularity'] : null;

        $chart = $this->data->chart($logger, $range, (string)($args['logger'] ?? ''), $gran);
        if (! $chart) { return ['text' => 'Data grafik untuk pos itu tidak tersedia pada rentang tersebut.']; }

        return ['text' => $chart['explanation'], 'chart' => $chart['chart']];
    }
}
