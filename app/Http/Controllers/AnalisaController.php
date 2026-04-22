<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalisaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id_logger)
    {
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'params', 'kategori', 'jiat', 'informasi'])
            ->where('id_logger', $id_logger)
            ->firstOrFail();

        $allLoggers = t_Logger::query()
            ->forUser(auth()->user())
            ->select('id_logger', 'nama_logger')
            ->orderBy('nama_logger')
            ->get();

        // Get all parameters for this logger
        $parameters = $logger->params->map(function ($param) {
            return [
                'nama_parameter' => $param->nama_parameter,
                'kolom_sensor' => $param->kolom_sensor,
                'satuan' => $param->satuan ?? '',
            ];
        });

        // Default parameter: ambil yang parameter_utama = 1, fallback ke yang pertama
        $defaultParameter = $logger->params
            ->first(fn($p) => !empty($p->parameter_utama))?->nama_parameter
            ?? $logger->params->first()?->nama_parameter
            ?? '';

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
            $status = $minutesDiff < 60 ? 'online' : 'offline';
        }

        return view('analisadata.analisa', [
            'title' => 'Analisa Data',
            'logger' => $logger,
            'allLoggers' => $allLoggers,
            'parameters' => $parameters,
            'defaultParameter' => $defaultParameter,
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


        $callback = function () use ($result, $request, $id_logger) {
            $file = fopen('php://output', 'w');

            // Metadata Headers
            $loggerName = $result['logger_name'] ?? $id_logger;
            $paramName = $request->input('parameter', 'Parameter');
            $date = $request->input('date', '');
            $range = $request->input('range', 'day');

            // Format Date for display
            $displayDate = $date;
            if ($range === 'day') $displayDate = date('d F Y', strtotime($date));
            elseif ($range === 'month') $displayDate = date('F Y', strtotime($date));
            elseif ($range === 'year') $displayDate = date('Y', strtotime($date));
            elseif ($range === 'custom') {
                $dateRange = explode(',', $date);
                if (count($dateRange) === 2) {
                    $start = date('d F Y H:i', strtotime(trim($dateRange[0])));
                    $end = date('d F Y H:i', strtotime(trim($dateRange[1])));
                    $displayDate = "$start - $end";
                }
            }

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
        // 1. Fetch Logger with access guard
        $logger = $this->findAccessibleLogger($id_logger);

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

        // Fetch klasifikasi hujan per jam for this logger
        $klasifikasiRaw = DB::table('klasifikasi_hujan')
            ->where('logger_id', $id_logger)
            ->where('waktu', 'perjam')
            ->orderBy('debit_air')
            ->get(['debit_air', 'intensitas']);
        $klasifikasi = $klasifikasiRaw->map(fn($r) => [
            'debit_air' => (float) $r->debit_air,
            'intensitas' => $r->intensitas,
        ])->values()->toArray();

        if (!$param || !$logger->tabel_main) {
            return [
                'labels'       => [],
                'chartData'    => [],
                'minData'      => [],
                'maxData'      => [],
                'tableData'    => [],
                'rerata'       => 0,
                'minimum'      => 0,
                'maksimum'     => 0,
                'tipe_graf'    => $param->tipe_graf ?? 'line',
                'akumulasi'    => 0,
                'klasifikasi'  => $klasifikasi,
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
        } elseif ($range === 'custom') {
            // Handle custom range: date comes as "startDateTime,endDateTime"
            $dateRange = explode(',', $date);
            if (count($dateRange) === 2) {
                $startDateTime = trim($dateRange[0]);
                $endDateTime = trim($dateRange[1]);
                $query->whereBetween($timeColumn, [$startDateTime, $endDateTime]);
            }
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

                    $values[] = round($avg, 4);
                    $minValues[] = round($min, 4);
                    $maxValues[] = round($max, 4);

                    $tableData[] = [
                        'waktu'   => $hour,
                        'rerata'  => round($avg, 4),
                        'minimum' => round($min, 4),
                        'maksimum' => round($max, 4),
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

                    $values[] = round($avg, 4);
                    $minValues[] = round($min, 4);
                    $maxValues[] = round($max, 4);

                    $tableData[] = [
                        'waktu'   => "Tanggal $i",
                        'rerata'  => round($avg, 4),
                        'minimum' => round($min, 4),
                        'maksimum' => round($max, 4),
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

        }
        else if ($range === 'custom') { // CUSTOM RANGE

            // For custom range, show data grouped by date (day)
            $grouped = $data->groupBy(function ($item) use ($timeColumn) {
                return date('Y-m-d', strtotime($item->{$timeColumn}));
            });

            foreach ($grouped as $dateKey => $dateData) {
                $labels[] = date('d M Y', strtotime($dateKey));

                if ($dateData->isNotEmpty()) {
                    $avg = $dateData->avg($column);
                    $min = $dateData->min($column);
                    $max = $dateData->max($column);

                    $values[] = round($avg, 4);
                    $minValues[] = round($min, 4);
                    $maxValues[] = round($max, 4);

                    $tableData[] = [
                        'waktu'   => date('d M Y', strtotime($dateKey)),
                        'rerata'  => round($avg, 4),
                        'minimum' => round($min, 4),
                        'maksimum' => round($max, 4),
                    ];
                } else {
                    $values[] = null;
                    $minValues[] = null;
                    $maxValues[] = null;

                    $tableData[] = [
                        'waktu'   => date('d M Y', strtotime($dateKey)),
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

                    $values[] = round($avg, 4);
                    $minValues[] = round($min, 4);
                    $maxValues[] = round($max, 4);

                    $tableData[] = [
                        'waktu'   => $mName,
                        'rerata'  => round($avg, 4),
                        'minimum' => round($min, 4),
                        'maksimum' => round($max, 4),
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
        $akumulasi = ($param->tipe_graf ?? 'line') === 'bar'
            ? round($numericValues->sum(), 2)
            : 0;

        return [
            'logger_name'  => $logger->nama_logger ?? $id_logger,
            'labels'       => $labels,
            'chartData'    => $values,
            'minData'      => $minValues,
            'maxData'      => $maxValues,
            'tableData'    => $tableData,
            'rerata'       => $numericValues->avg() ? round($numericValues->avg(), 4) : 0,
            'minimum'      => $numericValues->min() ? round($numericValues->min(), 4) : 0,
            'maksimum'     => $numericValues->max() ? round($numericValues->max(), 4) : 0,
            'tipe_graf'    => $param->tipe_graf ?? 'line',
            'akumulasi'    => $akumulasi,
            'klasifikasi'  => $klasifikasi,
        ];
    }

    public function dataMasuk($id_logger)
    {
        $logger = $this->findAccessibleLogger($id_logger);
        if (!$logger) {
            abort(404);
        }

        $today = now();
        $start = $today->copy()->subDays(29)->startOfDay();

        $labels = [];
        $counts = [];

        for ($i = 0; $i < 30; $i++) {
            $date = $start->copy()->addDays($i);
            $tableName = $logger->tabel_main ?: (((int) $logger->sensor_count === 19) ? 't_s19_01' : 't_s16_01');
            $count = DB::table($tableName)
                ->where('id_logger', $logger->id_logger)
                ->whereDate('waktu', $date)
                ->count();

            $labels[] = $date->format('d M');
            $counts[] = $count;
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
        $logger = $this->findAccessibleLogger($id);
        if (!$logger) {
            abort(404);
        }

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

        // Expected complete data per day is always 1440 (1 record per minute)
        $expectedPerDay = 1440;

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

                $percentage = round(($count / $minutesSoFar) * 100, 2);
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

    private function findAccessibleLogger(string $idLogger): ?t_Logger
    {
        return t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $idLogger)
            ->first();
    }
}
