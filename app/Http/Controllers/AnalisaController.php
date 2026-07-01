<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Support\DataMasukStats;
use App\Support\SensorFamily;
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
            ->select('id_logger', 'nama_logger', 'idlokasi')
            ->with('lokasi')
            ->orderBy('nama_logger')
            ->get();

        // Get all parameters for this logger
        $parameters = $logger->params->sortBy(function ($param) {
            preg_match('/\d+/', $param->kolom_sensor, $matches);
            return $matches ? (int)$matches[0] : 999;
        })->values()->map(function ($param) {
            return [
                'nama_parameter' => $param->nama_parameter,
                'kolom_sensor' => $param->kolom_sensor,
                'satuan' => $param->satuan ?? '',
                'tipe_graf' => $this->chartTypeFor($param),
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
        $waktu50 = optional($logger->temp50)->waktu;

        $lastTime = collect([$waktu16, $waktu19, $waktu50])
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

        // 'day'/'custom' bars aggregate per hour -> hourly thresholds ('perjam').
        // 'month'/'year' bars aggregate per day or more -> daily thresholds ('perhari').
        $klasifikasiWaktu = in_array($range, ['day', 'custom'], true) ? 'perjam' : 'perhari';
        $klasifikasi = $this->rainfallClassificationsFor($logger, $klasifikasiWaktu);

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
                'tipe_graf'    => $this->chartTypeFor($param),
                'akumulasi'    => 0,
                'klasifikasi'  => $klasifikasi,
                'is_fault'     => false,
            ];
        }

        $tipeGraf = $this->chartTypeFor($param);
        $column = $param->kolom_sensor;
        $isFault = \App\Support\FaultStatus::isFaultParam($param);

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
                    $value = $this->aggregateValueFor($hourData, $column, $tipeGraf, $isFault);
                    $min = $hourData->min($column);
                    $max = $hourData->max($column);

                    $values[] = round($value, 3);
                    $minValues[] = round($min, 3);
                    $maxValues[] = round($max, 3);

                    $tableData[] = [
                        'waktu'   => $hour,
                        'rerata'  => round($value, 3),
                        'minimum' => round($min, 3),
                        'maksimum' => round($max, 3),
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
                    $value = $this->aggregateValueFor($dayData, $column, $tipeGraf, $isFault);
                    $min = $dayData->min($column);
                    $max = $dayData->max($column);

                    $values[] = round($value, 4);
                    $minValues[] = round($min, 4);
                    $maxValues[] = round($max, 4);

                    $tableData[] = [
                        'waktu'   => "Tanggal $i",
                        'rerata'  => round($value, 4),
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

            // For custom range, show data grouped by hour
            $grouped = $data->groupBy(function ($item) use ($timeColumn) {
                return date('Y-m-d H:00', strtotime($item->{$timeColumn}));
            });

            foreach ($grouped as $dateKey => $dateData) {
                $labels[] = date('d M Y, H:i', strtotime($dateKey));

                if ($dateData->isNotEmpty()) {
                    $value = $this->aggregateValueFor($dateData, $column, $tipeGraf, $isFault);
                    $min = $dateData->min($column);
                    $max = $dateData->max($column);

                    $values[] = round($value, 4);
                    $minValues[] = round($min, 4);
                    $maxValues[] = round($max, 4);

                    $tableData[] = [
                        'waktu'   => date('d M Y, H:i', strtotime($dateKey)),
                        'rerata'  => round($value, 4),
                        'minimum' => round($min, 4),
                        'maksimum' => round($max, 4),
                    ];
                } else {
                    $values[] = null;
                    $minValues[] = null;
                    $maxValues[] = null;

                    $tableData[] = [
                        'waktu'   => date('d M Y, H:i', strtotime($dateKey)),
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
                    $value = $this->aggregateValueFor($monthData, $column, $tipeGraf, $isFault);
                    $min = $monthData->min($column);
                    $max = $monthData->max($column);

                    $values[] = round($value, 4);
                    $minValues[] = round($min, 4);
                    $maxValues[] = round($max, 4);

                    $tableData[] = [
                        'waktu'   => $mName,
                        'rerata'  => round($value, 4),
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
        $akumulasi = $tipeGraf === 'bar'
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
            'tipe_graf'    => $tipeGraf,
            'akumulasi'    => $akumulasi,
            'klasifikasi'  => $klasifikasi,
            'is_fault'     => $isFault,
        ];
    }

    public function dataMasuk($id_logger)
    {
        $logger = $this->findAccessibleLogger($id_logger);
        if (!$logger) {
            abort(404);
        }

        $today = now(config('app.timezone'));
        $start = $today->copy()->subDays(29)->startOfDay();

        $dates  = [];
        $labels = [];
        for ($i = 0; $i < 30; $i++) {
            $date     = $start->copy()->addDays($i);
            $dates[]  = $date->toDateString();
            $labels[] = $date->format('d M');
        }

        $stats  = DataMasukStats::forLogger($logger, $dates);
        $counts = array_map(fn ($d) => $stats[$d]['count'], $dates);

        return response()->json([
            'labels' => $labels,
            'counts' => $counts,
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

        $start = Carbon::now(config('app.timezone'))->subDays(29)->startOfDay();

        $dates = [];
        for ($i = 0; $i < 30; $i++) {
            $dates[] = $start->copy()->addDays($i)->toDateString();
        }

        $stats  = DataMasukStats::forLogger($logger, $dates);
        $result = array_map(fn ($d) => [
            'date'       => $d,
            'count'      => $stats[$d]['count'],
            'percentage' => $stats[$d]['percentage'],
        ], $dates);

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

    private function rainfallClassificationsFor(t_Logger $logger, string $waktu = 'perjam'): array
    {
        $fromLogger = function ($query) use ($waktu) {
            return $query
                ->where('klasifikasi_hujan.waktu', $waktu)
                ->distinct()
                ->orderByRaw('CAST(klasifikasi_hujan.debit_air AS DECIMAL(10, 2))')
                ->get(['klasifikasi_hujan.debit_air', 'klasifikasi_hujan.intensitas'])
                ->map(fn($row) => [
                    'debit_air' => (float) $row->debit_air,
                    'intensitas' => $row->intensitas,
                ])
                ->values()
                ->toArray();
        };

        $classifications = $fromLogger(
            DB::table('klasifikasi_hujan')
                ->where('klasifikasi_hujan.logger_id', $logger->id_logger)
        );

        if ($classifications) {
            return $classifications;
        }

        if ($logger->id_katlogger) {
            $classifications = $fromLogger(
                DB::table('klasifikasi_hujan')
                    ->join('t_logger', 't_logger.id_logger', '=', 'klasifikasi_hujan.logger_id')
                    ->where('t_logger.id_katlogger', $logger->id_katlogger)
            );

            if ($classifications) {
                return $classifications;
            }

            return DB::table('klasifikasi_threshold')
                ->where('id_kategori', $logger->id_katlogger)
                ->where(function ($query) {
                    $query->whereNotNull('min_value')
                        ->orWhereNotNull('max_value');
                })
                ->orderBy('sort_order')
                ->get(['min_value', 'state_label'])
                ->map(fn($row) => [
                    'debit_air' => (float) ($row->min_value ?? 0),
                    'intensitas' => $row->state_label,
                ])
                ->values()
                ->toArray();
        }

        return [];
    }

    private function chartTypeFor($param): string
    {
        if ($this->isRainfallParameter($param)) {
            return 'bar';
        }

        return $param->tipe_graf ?? 'line';
    }

    private function isRainfallParameter($param): bool
    {
        $name = strtolower((string) ($param->nama_parameter ?? ''));
        $name = preg_replace('/\s+/', ' ', str_replace('_', ' ', trim($name)));

        return $name === 'curah hujan';
    }

    private function aggregateValueFor($rows, string $column, string $tipeGraf, bool $isFault = false): float
    {
        if ($isFault) {
            return (float) \App\Support\FaultStatus::combine($rows->pluck($column));
        }

        if ($tipeGraf === 'bar') {
            return (float) $rows->sum($column);
        }

        return (float) $rows->avg($column);
    }
}
