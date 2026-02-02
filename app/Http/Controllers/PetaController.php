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

    /**
     * Display analysis page for a specific logger
     */
    public function analisa($id_logger)
    {
        $logger = t_Logger::query()
            ->with(['lokasi', 'params', 'jiat', 'informasi'])
            ->where('id_logger', $id_logger)
            ->firstOrFail();

        // Get all parameters for this logger
        $parameters = $logger->params->map(function ($param) {
            return [
                'nama_parameter' => $param->nama_parameter,
                'kolom_sensor' => $param->kolom_sensor,
                'satuan' => $param->satuan ?? '',
            ];
        });

        // Get photos from foto_pos table
        $photos = DB::table('foto_pos')
            ->where('id_logger', $id_logger)
            ->orderBy('foto_utama', 'desc')
            ->get();

        return view('peta.analisa', [
            'title' => 'Analisa Data',
            'logger' => $logger,
            'parameters' => $parameters,
            'photos' => $photos,
        ]);
    }

    /**
     * Get chart data for a specific logger and parameter
     */
    public function getChartData(Request $request, $id_logger)
    {
        $logger = t_Logger::where('id_logger', $id_logger)->firstOrFail();

        $parameter = $request->input('parameter', 'muka_air_tanah');
        $date = $request->input('date', now()->format('Y-m-d'));
        $range = $request->input('range', 'day'); // day, month, year

        // Find the parameter column
        $param = $logger->params()->where('nama_parameter', $parameter)->first();
        if (!$param || !$logger->tabel_main) {
            return response()->json([
                'labels' => [],
                'data' => [],
                'rerata' => 0,
                'minimum' => 0,
                'maksimum' => 0,
            ]);
        }

        $column = $param->kolom_sensor;

        // Determine time column
        $pTempS16 = $logger->params()->where('kolom_sensor', 'temp_s16')->first();
        $pTempS19 = $logger->params()->where('kolom_sensor', 'temp_s19')->first();
        $timeColumn = $pTempS19 ? 'temp_s19' : ($pTempS16 ? 'temp_s16' : 'created_at');

        // Build query based on range
        $query = DB::table($logger->tabel_main)
            ->where('id_logger', $id_logger)
            ->whereNotNull($column);

        if ($range === 'day') {
            $query->whereDate($timeColumn, $date);
        } elseif ($range === 'month') {
            $query->whereYear($timeColumn, date('Y', strtotime($date)))
                ->whereMonth($timeColumn, date('m', strtotime($date)));
        } elseif ($range === 'year') {
            $query->whereYear($timeColumn, date('Y', strtotime($date)));
        }

        $data = $query->orderBy($timeColumn)->get();

        // Group by hour for daily view
        $labels = [];
        $values = [];

        if ($range === 'day') {
            // Hourly data
            $grouped = $data->groupBy(function ($item) use ($timeColumn) {
                return date('H:00', strtotime($item->{$timeColumn}));
            });

            for ($i = 0; $i < 24; $i++) {
                $hour = sprintf('%02d:00', $i);
                $labels[] = $hour;
                $hourData = $grouped->get($hour, collect());
                $values[] = $hourData->isNotEmpty() ? round($hourData->avg($column), 2) : null;
            }
        }

        $numericValues = array_filter($values, fn($v) => $v !== null);

        return response()->json([
            'labels' => $labels,
            'data' => $values,
            'rerata' => count($numericValues) ? round(array_sum($numericValues) / count($numericValues), 2) : 0,
            'minimum' => count($numericValues) ? round(min($numericValues), 2) : 0,
            'maksimum' => count($numericValues) ? round(max($numericValues), 2) : 0,
            'tableData' => $data->map(function ($item) use ($timeColumn, $column) {
                return [
                    'waktu' => date('Y-m-d H:i', strtotime($item->{$timeColumn})),
                    'value' => round($item->{$column}, 2),
                ];
            }),
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
