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
            ->forUser(auth()->user())
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
            ->forUser(auth()->user())
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

        // 🔹 Ambil waktu terakhir dari relasi temp16 dan temp19
        $waktu16 = optional($logger->temp16)->waktu;
        $waktu19 = optional($logger->temp19)->waktu;

        $lastTime = collect([$waktu16, $waktu19])
            ->filter()
            ->sortDesc()
            ->first();

        $status = 'tidak_aktif';

        if ($lastTime) {
            $minutesDiff = Carbon::parse($lastTime)->diffInMinutes(now());
            $status = $minutesDiff < 120 ? 'online' : 'offline';
        }

        return view('peta.analisa', [
            'title' => 'Analisa Data',
            'logger' => $logger,
            'parameters' => $parameters,
            'photos' => $photos,
            'status' => $status,
            'lastTime' => $lastTime,
        ]);
    }

    /**
     * Get chart data for a specific logger and parameter
     */
    public function getChartData(Request $request, $id_logger)
    {
        $data = $this->processData($id_logger, $request);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], 404);
        }

        return response()->json($data);
    }

    /**
     * Export data to generic CSV/Excel
     */
    public function exportExcel(Request $request, $id_logger)
    {
        $logger = $this->findLoggerForUser($id_logger);
        if (!$logger) {
            return redirect()->back()->with('error', 'Logger not found');
        }
        $result = $this->processData($id_logger, $request);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        $timestamp = date('Ymd_His');
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"DATA_LOGGER_{$id_logger}_{$timestamp}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];


        $callback = function () use ($result, $request, $id_logger, $logger) {
            $file = fopen('php://output', 'w');

            // Metadata Headers
            $loggerName = $logger->nama_logger ?? $id_logger;
            $paramName = $request->input('parameter', 'Parameter');
            $date = $request->input('date', '');
            $range = $request->input('range', 'day');

            // Format Date for display
            $displayDate = $date;
            if ($range === 'day') $displayDate = date('d F Y', strtotime($date));
            elseif ($range === 'month') $displayDate = date('F Y', strtotime($date));
            elseif ($range === 'year') $displayDate = date('Y', strtotime($date));

            fputcsv($file, ['NAMA LOGGER', '', ': ', $loggerName]);
            fputcsv($file, ['PARAMETER', '', ': ', $paramName]);
            fputcsv($file, ['TANGGAL', '', ': ', $displayDate]);
            fputcsv($file, []); // Blank line

            // Table Header
            fputcsv($file, ['WAKTU', 'RERATA', 'MINIMUM', 'MAKSIMUM']);

            // Table Data
            foreach ($result['tableData'] as $row) {
                // Ensure row is array-accessible or object
                $waktu = is_array($row) ? $row['waktu'] : $row->waktu;
                $rerata = is_array($row) ? $row['rerata'] : $row->rerata;
                $min = is_array($row) ? $row['minimum'] : $row->minimum;
                $max = is_array($row) ? $row['maksimum'] : $row->maksimum;

                if ($rerata === null && $min === null) continue; // Skip empty rows

                fputcsv($file, [
                    $waktu,
                    $rerata,
                    $min,
                    $max
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Shared Logic for Data Processing
     */
    private function processData($id_logger, $request)
    {
        // 1. Fetch Logger (Query Builder)
        $logger = $this->findLoggerForUser($id_logger);

        if (!$logger) {
            return ['error' => 'Logger not found'];
        }

        $parameter = $request->input('parameter', 'muka_air_tanah');
        $date = $request->input('date', now()->format('Y-m-d'));
        $range = $request->input('range', 'day'); // day, month, year

        // 2. Fetch Parameter Config (Query Builder)
        $param = DB::table('parameter_sensor')
            ->where('logger_id', $id_logger)
            ->where('nama_parameter', $parameter)
            ->first();

        if (!$param || !$logger->tabel_main) {
            return [
                'labels' => [],
                'chartData' => [],
                'minData' => [],
                'maxData' => [],
                'tableData' => [],
                'rerata' => 0,
                'minimum' => 0,
                'maksimum' => 0,
            ];
        }

        $column = $param->kolom_sensor;

        // 3. Determine time column
        $pTempS19 = DB::table('parameter_sensor')
            ->where('logger_id', $id_logger)
            ->where('kolom_sensor', 'temp_s19')
            ->first();

        $pTempS16 = DB::table('parameter_sensor')
            ->where('logger_id', $id_logger)
            ->where('kolom_sensor', 'temp_s16')
            ->first();

        $timeColumn = $pTempS19 ? 'temp_s19' : ($pTempS16 ? 'temp_s16' : 'waktu');

        // 4. Build query based on range
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

        // Optimasi: Select hanya kolom yang dibutuhkan
        $data = $query->select($timeColumn, $column)
            ->orderBy($timeColumn, 'asc')
            ->get();

        $labels = [];
        $values = [];
        $minValues = [];
        $maxValues = [];
        $tableData = [];

        if ($range === 'day') {

            $grouped = $data->groupBy(function ($item) use ($timeColumn) {
                // Ensure timeColumn exists on item
                if (!isset($item->{$timeColumn})) return '00:00';
                return date('H:00', strtotime($item->{$timeColumn}));
            });

            for ($i = 0; $i < 24; $i++) {
                $hour = sprintf('%02d:00', $i);
                $labels[] = $hour;

                $hourData = $grouped->get($hour, collect());

                if ($hourData->isNotEmpty()) {
                    $avg = $hourData->avg($column);
                    $min = $hourData->min($column);
                    $max = $hourData->max($column);

                    $values[] = round($avg, 2);
                    $minValues[] = round($min, 2);
                    $maxValues[] = round($max, 2);

                    $tableData[] = [
                        'waktu'   => $hour,
                        'rerata'  => round($avg, 2),
                        'minimum' => round($min, 2),
                        'maksimum' => round($max, 2),
                    ];
                } else {
                    $values[] = null;
                    $minValues[] = null;
                    $maxValues[] = null;

                    $tableData[] = [
                        'waktu'   => $hour,
                        'rerata'  => null,
                        'minimum' => null,
                        'maksimum' => null,
                    ];
                }
            }
        } elseif ($range === 'month') {

            $daysInMonth = date('t', strtotime($date));

            $grouped = $data->groupBy(function ($item) use ($timeColumn) {
                return date('j', strtotime($item->{$timeColumn}));
            });

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = (string)$i;

                $dayData = $grouped->get((string)$i, collect());

                if ($dayData->isNotEmpty()) {
                    $avg = $dayData->avg($column);
                    $min = $dayData->min($column);
                    $max = $dayData->max($column);

                    $values[] = round($avg, 2);
                    $minValues[] = round($min, 2);
                    $maxValues[] = round($max, 2);

                    $tableData[] = [
                        'waktu'   => "Tanggal $i",
                        'rerata'  => round($avg, 2),
                        'minimum' => round($min, 2),
                        'maksimum' => round($max, 2),
                    ];
                } else {
                    $values[] = null;
                    $minValues[] = null;
                    $maxValues[] = null;

                    $tableData[] = [
                        'waktu'   => "Tanggal $i",
                        'rerata'  => null,
                        'minimum' => null,
                        'maksimum' => null,
                    ];
                }
            }
        } else { // YEAR

            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            $grouped = $data->groupBy(function ($item) use ($timeColumn) {
                return date('n', strtotime($item->{$timeColumn}));
            });

            foreach ($monthNames as $idx => $mName) {
                $monthNum = (string)($idx + 1);
                $labels[] = $mName;

                $monthData = $grouped->get($monthNum, collect());

                if ($monthData->isNotEmpty()) {
                    $avg = $monthData->avg($column);
                    $min = $monthData->min($column);
                    $max = $monthData->max($column);

                    $values[] = round($avg, 2);
                    $minValues[] = round($min, 2);
                    $maxValues[] = round($max, 2);

                    $tableData[] = [
                        'waktu'   => $mName,
                        'rerata'  => round($avg, 2),
                        'minimum' => round($min, 2),
                        'maksimum' => round($max, 2),
                    ];
                } else {
                    $values[] = null;
                    $minValues[] = null;
                    $maxValues[] = null;

                    $tableData[] = [
                        'waktu'   => $mName,
                        'rerata'  => null,
                        'minimum' => null,
                        'maksimum' => null,
                    ];
                }
            }
        }

        $numericValues = collect($values)->filter();

        return [
            'labels'     => $labels,
            'chartData'  => $values,
            'minData'    => $minValues,
            'maxData'    => $maxValues,
            'tableData'  => $tableData,
            'rerata'     => $numericValues->avg() ? round($numericValues->avg(), 2) : 0,
            'minimum'    => $numericValues->min() ? round($numericValues->min(), 2) : 0,
            'maksimum'   => $numericValues->max() ? round($numericValues->max(), 2) : 0,
        ];
    }

    public function dataMasuk($id_logger)
    {
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id_logger)
            ->firstOrFail();

        $today = now();
        $start = $today->copy()->subDays(29)->startOfDay();

        $labels = [];
        $counts = [];

        for ($i = 0; $i < 30; $i++) {
            $date = $start->copy()->addDays($i);

            $count16 = 0;
            $count19 = 0;

            $tableName = ((int)$logger->sensor_count === 19) ? 't_s19_01' : 't_s16_01';

            if ($logger->tabel_s16) {
                $count16 = DB::table($tableName)
                    ->whereDate('waktu', $date)
                    ->count();
            }

            if ($logger->tabel_s19) {
                $count19 = DB::table($tableName)
                    ->whereDate('waktu', $date)
                    ->count();
            }

            $labels[] = $date->format('d M');
            $counts[] = $count16 + $count19;
        }

        return response()->json([
            'labels' => $labels,
            'counts' => $counts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getDataMasuk($id)
    {
        // $logger = t_Logger::findOrFail($id);
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id)
            ->firstOrFail();

        // Determine which relation to use based on table name or sensor count
        $query = null;
        if ($logger->tabel_main && str_contains($logger->tabel_main, '19')) {
            $query = $logger->s19data();
        } elseif ($logger->tabel_main && str_contains($logger->tabel_main, '16')) {
            $query = $logger->s16data();
        } else {
            // Fallback: check sensor count or default to s16
            if ($logger->sensor_count == 19) {
                $query = $logger->s19data();
            } else {
                $query = $logger->s16data();
            }
        }

        // Calculate expected data count per day
        $intervalMinutes = 5; // Default
        if ($logger->jeda_notif) {
            // Handle "00:05:00" format or integer minutes
            if (str_contains($logger->jeda_notif, ':')) {
                $parts = explode(':', $logger->jeda_notif);
                $intervalMinutes = ($parts[0] * 60) + $parts[1] + ($parts[2] / 60);
            } else {
                $intervalMinutes = (int) $logger->jeda_notif;
            }
        }
        if ($intervalMinutes <= 0) $intervalMinutes = 5;

        $expectedPerDay = 1440 / $intervalMinutes;

        // Get data for last 30 days
        $startDate = Carbon::now()->subDays(29)->format('Y-m-d');

        $nowTs = Carbon::now();

        $data = $query->select(
            DB::raw('DATE(waktu) as date'),
            DB::raw("COUNT(DISTINCT DATE_FORMAT(waktu, '%Y-%m-%d %H:%i')) as count")
        )
            ->where('waktu', '>=', $startDate . ' 00:00:00')
            ->where('waktu', '<=', $nowTs)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Fill missing dates with 0
        $result = [];
        $current = Carbon::parse($startDate);
        $now = Carbon::now();

        $dbData = $data->pluck('count', 'date')->toArray();
        $todayStr = Carbon::now()->format('Y-m-d');

        while ($current <= $now) {
            $dateStr = $current->format('Y-m-d');
            $count = (int)($dbData[$dateStr] ?? 0);

            if ($dateStr === $todayStr) {
                $minutesSoFar = Carbon::now()->diffInMinutes(Carbon::today());
                if ($minutesSoFar < 1) $minutesSoFar = 1;

                $expectedSoFar = $minutesSoFar / $intervalMinutes;
                if ($expectedSoFar < 1) $expectedSoFar = 1;

                $percentage = round(($count / $expectedSoFar) * 100, 2);
            } else {
                $percentage = ($count > 0) ? round(($count / $expectedPerDay) * 100, 2) : 0;
            }

            if ($percentage > 100) $percentage = 100;

            $result[] = [
                'date' => $dateStr,
                'count' => $count,
                'percentage' => $percentage
            ];

            $current->addDay();
        }


        return response()->json($result);
    }

    private function findLoggerForUser($id_logger)
    {
        return t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id_logger)
            ->first();
    }
}
