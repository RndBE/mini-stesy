<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\MiniStesyApi;

class PetaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $points = t_Logger::query()
            ->with(['lokasi', 'params', 'temp16', 'temp19', 'jiat'])
            ->whereNotNull('idlokasi')
            ->get()
            ->map(function ($l) {

                $lat = $l->lokasi?->latitude;
                $lng = $l->lokasi?->longitude;

                // 🔹 Ambil parameter suhu (sumber waktu)
                $pTempS16 = $l->params->firstWhere('kolom_sensor', 'temp_s16');
                $pTempS19 = $l->params->firstWhere('kolom_sensor', 'temp_s19');

                // 🔹 Tentukan kolom waktu yang dipakai
                $timeColumn = $pTempS19 ? 'temp_s19' : ($pTempS16 ? 'temp_s16' : null);

                // 🔹 Ambil data terakhir - coba beberapa kolom waktu
                $latest = null;
                if ($l->tabel_main) {
                    // Coba dengan temp_s19 atau temp_s16 jika ada
                    if ($timeColumn) {
                        $latest = DB::table($l->tabel_main)
                            ->where('id_logger', $l->id_logger)
                            ->whereNotNull($timeColumn)
                            ->orderByDesc($timeColumn)
                            ->first();
                    }

                    // Jika tidak ada, coba dengan id (record terakhir)
                    if (!$latest) {
                        $latest = DB::table($l->tabel_main)
                            ->where('id_logger', $l->id_logger)
                            ->orderByDesc('id')
                            ->first();
                    }
                }

                // 🔹 Mapping sensor lain
                $pHumidity = $l->params->firstWhere('nama_parameter', 'humidity_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'humidity');
                $pBattery  = $l->params->firstWhere('nama_parameter', 'battery_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'battery');
                $pTemp     = $l->params->firstWhere('nama_parameter', 'temperature_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'temperature');

                $humidity = ($latest && $pHumidity?->kolom_sensor)
                    ? $latest->{$pHumidity->kolom_sensor} ?? null
                    : null;

                $battery = ($latest && $pBattery?->kolom_sensor)
                    ? $latest->{$pBattery->kolom_sensor} ?? null
                    : null;

                $temp = ($latest && $pTemp?->kolom_sensor)
                    ? $latest->{$pTemp->kolom_sensor} ?? null
                    : null;

                // 🔹 WAKTU DIAMBIL DARI SENSOR SUHU

                $waktu16 = optional($l->temp16)->waktu;
                $waktu19 = optional($l->temp19)->waktu;
                // $lastTime = $latest?->waktu ?? null;
                $lastTime = collect([$waktu16, $waktu19])
                    ->filter()
                    ->sortDesc()
                    ->first();

                $status = 'tidak_aktif';

                if ($lastTime) {
                    $minutesDiff = Carbon::parse($lastTime)->diffInMinutes(now());
                    // $isActive = Carbon::parse($lastTime)->isToday();
                }

                $status = $minutesDiff < 120 ? 'online' : 'offline';

                // $status = ($lastTime && now()->diffInMinutes($lastTime) < 120)
                //     ? 'online'
                //     : 'offline';

                return [
                    'id_logger'   => $l->id_logger,
                    'nama_logger' => $l->nama_logger,
                    'nama_lokasi' => $l->lokasi?->nama_lokasi,
                    'lat'         => $lat !== null ? (float)$lat : null,
                    'lng'         => $lng !== null ? (float)$lng : null,

                    'humidity' => is_numeric($humidity) ? round($humidity, 1) : null,
                    'battery'  => is_numeric($battery) ? round($battery, 2) : null,
                    'temp'     => is_numeric($temp) ? round($temp, 1) : null,

                    'status'   => $status,
                    'last_time' => $lastTime,
                    'kedalaman_sumur' => $l->jiat?->kedalaman_sumur,
                ];
            })
            ->filter(fn($p) => $p['lat'] !== null && $p['lng'] !== null)
            ->values();

        return view('peta.index', [
            'title'  => 'Peta Lokasi',
            'points' => $points,
        ]);
    }
    // public function index(MiniStesyApi $api)
    // {
    //     $lokasi = $api->semuaLokasi();

    //     $points = collect($lokasi)->map(function ($l) {
    //         return [
    //             'id_logger' => $l['id_logger'],
    //             'nama'      => $l['lokasi'],
    //             'lat'       => (float) $l['latitude'],
    //             'lng'       => (float) $l['longitude'],
    //             'status'    => strtolower($l['koneksi_log']) === 'on' ? 'online' : 'offline',
    //             'waktu'     => $l['waktu'],
    //         ];
    //     })->values();

    //     return view('peta.index', compact('points'));
    // }
}
