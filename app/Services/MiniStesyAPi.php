<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class MiniStesyApi
{
    private $base = 'https://mini-stesy.beacontelemetry.com/api/';
    private $user = 'userbbws';
    private $pass = 'user1';

    private function request($url, $params = [])
    {
        return Http::withoutVerifying()->get($this->base.$url, array_merge([
            'username' => $this->user,
            'password' => $this->pass
        ], $params))->json();
    }

    public function logger($id)
    {
        return $this->request("api_logger/$id");
    }

    public function semuaLokasi()
    {
        return $this->request("lokasi_new", [
            'level' => 'user',
            'id_user' => 5
        ]);
    }

    public function liveChart($idLogger, $idSensor)
    {
        return $this->request("live_data2", [
            'id_logger' => $idLogger,
            'id_sensor' => $idSensor
        ]);
    }
}
