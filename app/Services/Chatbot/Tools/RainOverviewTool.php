<?php
namespace App\Services\Chatbot\Tools;

use App\Models\t_User;
use App\Services\Chatbot\MonitoringData;

class RainOverviewTool implements ChatbotTool
{
    public function __construct(private MonitoringData $data) {}

    public function name(): string { return 'rain_overview'; }

    public function schema(): array
    {
        return ['type'=>'function','function'=>[
            'name'=>$this->name(),
            'description'=>'Ikhtisar curah hujan lintas pos: pos mana sedang hujan, akumulasi hari ini.',
            'parameters'=>['type'=>'object','properties'=>new \stdClass()],
        ]];
    }

    public function run(array $args, t_User $user): array
    {
        $overview = $this->data->rainOverview($user);
        return ['text' => json_encode($overview, JSON_UNESCAPED_UNICODE), 'data' => $overview];
    }
}
