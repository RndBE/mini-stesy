<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MiniStesyService;
use Illuminate\Support\Facades\DB;

class SyncMiniStesy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:sync-mini-stesy';
    protected $signature = 'sync:ministesy';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';
    protected $description = 'Sync data dari server mini-stesy';

    /**
     * Execute the console command.
     */
    // public function handle()
    // {
    //     //
    // }
    public function handle(MiniStesyService $api)
    {
        $lokasi = $api->getLokasi();

        if (!$lokasi) {
            $this->error("Gagal ambil daftar logger");
            return;
        }

        foreach ($lokasi as $l) {
            $id = $l['id_logger'];

            $data = $api->getLoggerData($id);
            if (!$data || !isset($data['sensor'])) continue;

            $humidity = $battery = $temp = null;

            foreach ($data['sensor'] as $s) {
                if (str_contains($s['namaSensor'], 'Humidity')) $humidity = $s['value'];
                if (str_contains($s['namaSensor'], 'Battery')) $battery = $s['value'];
                if (str_contains($s['namaSensor'], 'Temperature')) $temp = $s['value'];
            }

            $status = now()->diffInMinutes($data['waktu']) < 120 ? 'online' : 'offline';

            DB::table('ministesy_loggers')->updateOrInsert(
                ['id_logger' => $id],
                [
                    'nama_lokasi' => $data['lokasi'],
                    'latitude'    => $data['lat'],
                    'longitude'   => $data['long'],
                    'waktu'       => $data['waktu'],
                    'humidity'    => $humidity,
                    'battery'     => $battery,
                    'temp'        => $temp,
                    'status'      => $status,
                    'updated_at'  => now()
                ]
            );

            $this->info("Synced $id");
        }

        $this->info("Selesai sync mini-stesy");
    }
}
