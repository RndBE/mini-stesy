<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Models\KlasifikasiThreshold;
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
        // Fetch all thresholds grouped by kategori
        $thresholds = KlasifikasiThreshold::with('kategori')
            ->orderBy('id_kategori')->orderBy('sort_order')
            ->get()
            ->groupBy(function($item) {
                return $item->kategori?->nama_kategori ?? 'UNKNOWN';
            });

        // $points = t_Logger::query()
        //     ->forUser(auth()->user())
        //     ->with(['lokasi', 'params', 'temp16', 'temp19', 'jiat', 'klasifikasiHujan'])
        //     ->whereNotNull('idlokasi')
        //     ->get()
        //     ->map(function ($l) {

        //         $lat = $l->lokasi?->latitude;
        //         $lng = $l->lokasi?->longitude;

        //         // 🔹 Ambil parameter suhu (sumber waktu)
        //         $pTempS16 = $l->params->firstWhere('kolom_sensor', 'temp_s16');
        //         $pTempS19 = $l->params->firstWhere('kolom_sensor', 'temp_s19');

        //         // 🔹 Tentukan kolom waktu yang dipakai
        //         $timeColumn = $pTempS19 ? 'temp_s19' : ($pTempS16 ? 'temp_s16' : null);

        //         // 🔹 Ambil data terakhir - coba beberapa kolom waktu
        //         $latest = null;
        //         if ($l->tabel_main) {
        //             // Coba dengan temp_s19 atau temp_s16 jika ada
        //             if ($timeColumn) {
        //                 $latest = DB::table($l->tabel_main)
        //                     ->where('id_logger', $l->id_logger)
        //                     ->whereNotNull($timeColumn)
        //                     ->orderByDesc($timeColumn)
        //                     ->first();
        //             }

        //             // Jika tidak ada, coba dengan id (record terakhir)
        //             if (!$latest) {
        //                 $latest = DB::table($l->tabel_main)
        //                     ->where('id_logger', $l->id_logger)
        //                     ->orderByDesc('id')
        //                     ->first();
        //             }
        //         }

        //         // 🔹 Mapping sensor lain
        //         $pHumidity = $l->params->firstWhere('nama_parameter', 'humidity_logger')
        //             ?? $l->params->firstWhere('nama_parameter', 'humidity');
        //         $pBattery  = $l->params->firstWhere('nama_parameter', 'battery_logger')
        //             ?? $l->params->firstWhere('nama_parameter', 'battery');
        //         $pTemp     = $l->params->firstWhere('nama_parameter', 'temperature_logger')
        //             ?? $l->params->firstWhere('nama_parameter', 'temperature');

        //         $humidity = ($latest && $pHumidity?->kolom_sensor)
        //             ? $latest->{$pHumidity->kolom_sensor} ?? null
        //             : null;

        //         $battery = ($latest && $pBattery?->kolom_sensor)
        //             ? $latest->{$pBattery->kolom_sensor} ?? null
        //             : null;

        //         $temp = ($latest && $pTemp?->kolom_sensor)
        //             ? $latest->{$pTemp->kolom_sensor} ?? null
        //             : null;


        //         $waktu16 = optional($l->temp16)->waktu;
        //         $waktu19 = optional($l->temp19)->waktu;
        //         // $lastTime = $latest?->waktu ?? null;
        //         $lastTime = collect([$waktu16, $waktu19])
        //             ->filter()
        //             ->sortDesc()
        //             ->first();

        //         $status = 'tidak_aktif';

        //         if ($lastTime) {
        //             $minutesDiff = Carbon::parse($lastTime)->diffInMinutes(now());
        //             // $isActive = Carbon::parse($lastTime)->isToday();
        //         }

        //         $status = $minutesDiff < 120 ? 'online' : 'offline';

        //         $arrState = $this->getArrStateFromSensor8($l, $status);

        //         $kategori = $l->kategori?->kode ?? $l->kategori?->nama_kategori ?? $l->kategori?->nama ?? null;
        //         $kategori = $kategori ? strtoupper(trim($kategori)) : null;

        //         return [
        //             'id_logger'   => $l->id_logger,
        //             'nama_logger' => $l->nama_logger,
        //             'nama_lokasi' => $l->lokasi?->nama_lokasi,
        //             'lat'         => $lat !== null ? (float)$lat : null,
        //             'lng'         => $lng !== null ? (float)$lng : null,

        //             'kategori'  => $kategori,
        //             'arr_state' => $arrState,

        //             'humidity' => is_numeric($humidity) ? round($humidity, 1) : null,
        //             'battery'  => is_numeric($battery) ? round($battery, 2) : null,
        //             'temp'     => is_numeric($temp) ? round($temp, 1) : null,

        //             'status'   => $status,
        //             'last_time' => $lastTime,
        //             'kedalaman_sumur' => $l->jiat?->kedalaman_sumur,
        //         ];
        //     })
        //     ->filter(fn($p) => $p['lat'] !== null && $p['lng'] !== null)
        //     ->values();

        // return view('peta.index', [
        //     'title'  => 'Peta Lokasi',
        //     'points' => $points,
        // ]);
        $points = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'params', 'temp16', 'temp19', 'jiat', 'klasifikasiHujan', 'kategori'])
            ->whereNotNull('idlokasi')
            ->get()
            ->map(function ($l) use ($thresholds) {

                $lat = $l->lokasi?->latitude;
                $lng = $l->lokasi?->longitude;

                $pTempS16 = $l->params->firstWhere('kolom_sensor', 'temp_s16');
                $pTempS19 = $l->params->firstWhere('kolom_sensor', 'temp_s19');
                $timeColumn = $pTempS19 ? 'temp_s19' : ($pTempS16 ? 'temp_s16' : null);

                $latest = null;
                if ($l->tabel_main) {
                    // Try with time column first (temp_s19 or temp_s16)
                    if ($timeColumn) {
                        $latest = DB::table($l->tabel_main)
                            ->where('id_logger', $l->id_logger)
                            ->whereNotNull($timeColumn)
                            ->orderByDesc($timeColumn)
                            ->first();
                    }

                    // Fallback: try 'waktu' column
                    if (!$latest) {
                        $latest = DB::table($l->tabel_main)
                            ->where('id_logger', $l->id_logger)
                            ->whereNotNull('waktu')
                            ->orderByDesc('waktu')
                            ->first();
                    }

                    // Final fallback: by ID
                    if (!$latest) {
                        $latest = DB::table($l->tabel_main)
                            ->where('id_logger', $l->id_logger)
                            ->orderByDesc('id')
                            ->first();
                    }
                }

                $pHumidity = $l->params->firstWhere('nama_parameter', 'humidity_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'humidity');
                $pBattery  = $l->params->firstWhere('nama_parameter', 'battery_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'battery');
                $pTemp     = $l->params->firstWhere('nama_parameter', 'temperature_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'temperature');

                $humidity = ($latest && $pHumidity?->kolom_sensor) ? ($latest->{$pHumidity->kolom_sensor} ?? null) : null;
                $battery  = ($latest && $pBattery?->kolom_sensor) ? ($latest->{$pBattery->kolom_sensor} ?? null) : null;
                $temp     = ($latest && $pTemp?->kolom_sensor) ? ($latest->{$pTemp->kolom_sensor} ?? null) : null;

                $waktu16 = optional($l->temp16)->waktu;
                $waktu19 = optional($l->temp19)->waktu;

                $lastTime = collect([$waktu16, $waktu19])
                    ->filter()
                    ->sortDesc()
                    ->first();

                $minutesDiff = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
                $status = ($minutesDiff !== null && $minutesDiff < 60) ? 'online' : 'offline';

                $kategori = $l->kategori?->kode ?? $l->kategori?->nama_kategori ?? $l->kategori?->nama ?? null;
                $kategori = $kategori ? strtoupper(trim($kategori)) : null;

                $arrState = null;
                if ($kategori && isset($thresholds[$kategori])) {
                    $arrState = $this->getStateFromThreshold($l, $status, $latest, $thresholds[$kategori]);
                }

                return [
                    'id_logger'   => $l->id_logger,
                    'nama_logger' => $l->nama_logger,
                    'nama_lokasi' => $l->lokasi?->nama_lokasi,
                    'lat'         => $lat !== null ? (float) $lat : null,
                    'lng'         => $lng !== null ? (float) $lng : null,

                    'kategori'  => $kategori,
                    'arr_state' => $arrState,

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
            'thresholds' => $thresholds,
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

    /**
     * Get classification state from dynamic thresholds
     */
    private function getStateFromThreshold($logger, string $status, $latest, $thresholdCollection): string
    {
        // If offline, return koneksi_terputus state
        if ($status === 'offline') {
            return $thresholdCollection->firstWhere('state_key', 'koneksi_terputus')
                ?->state_key ?? 'koneksi_terputus';
        }

        // Find rain parameter dynamically
        $pRain = $logger->params->first(function ($p) {
            $n = strtolower(trim((string) $p->nama_parameter));
            $u = strtolower(trim((string) $p->parameter_utama));
            return $n === 'curah hujan' || $u === 'hujan';
        });

        $col = $pRain?->kolom_sensor;
        $value = null;

        if ($latest && $col && isset($latest->{$col})) {
            $value = is_numeric($latest->{$col}) ? (float) $latest->{$col} : null;
        }

        // If no value, default to first threshold (usually tidak_hujan)
        if ($value === null) {
            return $thresholdCollection->sortBy('sort_order')->first()?->state_key ?? 'tidak_hujan';
        }

        // Find matching threshold based on value
        foreach ($thresholdCollection->sortBy('sort_order') as $threshold) {
            // Skip special states like koneksi_terputus
            if ($threshold->min_value === null && $threshold->max_value === null) {
                continue;
            }

            $min = $threshold->min_value;
            $max = $threshold->max_value;

            $matchesMin = $min === null || $value >= $min;
            $matchesMax = $max === null || $value < $max;

            if ($matchesMin && $matchesMax) {
                return $threshold->state_key;
            }
        }

        // Fallback to first threshold
        return $thresholdCollection->sortBy('sort_order')->first()?->state_key ?? 'tidak_hujan';
    }
}
