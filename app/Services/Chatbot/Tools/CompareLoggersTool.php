<?php
namespace App\Services\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;

class CompareLoggersTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}

    public function name(): string { return 'compare_loggers'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Bandingkan dua atau lebih pos: pembacaan terakhir + agregat periode.',
            'parameters'=>['type'=>'object','properties'=>[
                'loggers'=>['type'=>'array','items'=>['type'=>'string'],'description'=>'Daftar nama/ID pos (minimal 2).'],
                'date_range'=>['type'=>'string','description'=>'Frasa periode opsional, mis. "minggu ini", "1-7 Juni".'],
            ],'required'=>['loggers']],
        ]];
    }

    public function run(array $args, t_User $user): array
    {
        $names = array_values(array_filter(array_map('strval', (array)($args['loggers'] ?? []))));
        if (count($names) < 2) {
            return ['text' => 'Sebutkan minimal dua pos untuk dibandingkan.'];
        }
        $loggers = $this->data->resolveLoggers($user, implode(' , ', $names), max(count($names), 3));
        if (count($loggers) < 2) {
            return ['text' => 'Pos yang dibandingkan tidak cukup ditemukan pada akses akun ini.'];
        }
        $range = isset($args['date_range']) ? $this->data->dateRange((string)$args['date_range']) : null;
        return ['text' => $this->data->comparison($loggers, $range)];
    }
}
