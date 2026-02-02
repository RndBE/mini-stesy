<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class MiniStesyService
{
    protected $base = "https://mini-stesy.beacontelemetry.com/api";

    public function getLokasi()
    {
        return Http::withoutVerifying()->get("$this->base/lokasi_new", [
            'level' => 'user',
            'id_user' => 5
        ])->json();
    }

    public function getLoggerData($id)
    {
        return Http::withoutVerifying()->get("$this->base/api_logger/$id", [
            'username' => 'userbbws',
            'password' => 'user1'
        ])->json();
    }
}
