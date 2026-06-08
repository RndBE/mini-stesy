<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use Carbon\Carbon;
use App\Services\MiniStesyApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class BerandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = $this->currentUser();
        $loggers = t_Logger::query()
            ->forUser($user)
            ->whereNotNull('idlokasi')
            ->with(['lokasi', 'kategori', 'jiat', 'nonjiat', 'params', 'temp16', 'temp19', 'temp50'])
            ->orderBy('nama_logger')
            ->get()
            ->map(function ($lg) {

                // Ambil waktu terbaru dari temp16, temp19 & temp50
                $waktu16 = optional($lg->temp16)->waktu;
                $waktu19 = optional($lg->temp19)->waktu;
                $waktu50 = optional($lg->temp50)->waktu;

                $latestWaktu = collect([$waktu16, $waktu19, $waktu50])
                    ->filter()
                    ->sortDesc()
                    ->first();

                $isActive = false;

                if ($latestWaktu) {
                    // $isActive = Carbon::parse($latestWaktu)->isToday();
                    $isActive = Carbon::parse($latestWaktu)->diffInMinutes(now());
                }

                $status = $isActive < 60 ? 'online' : 'offline';
                if ($lg->status_perbaikan === 'perbaikan') {
                    $status = 'perbaikan';
                }

                $lg->status_logger = $status;
                $lg->latest_waktu  = $latestWaktu;
                $lg->arr_curah_hujan_perjam = null;
                $lg->arr_curah_hujan_harian = null;
                $lg->arr_status_perjam = null;
                $lg->arr_status_perhari = null;
                $lg->arr_state_perjam = null;
                $lg->arr_state_perhari = null;

                // Cari parameter hujan
                $pRain = $lg->params->first(function ($param) {
                    $name  = strtolower(trim((string) $param->nama_parameter));
                    $utama = strtolower(trim((string) $param->parameter_utama));
                    return str_contains($name, 'hujan')
                        || str_contains($name, 'rain')
                        || str_contains($utama, 'hujan')
                        || str_contains($utama, 'rain');
                });

                $tableName  = (string) ($lg->tabel_main ?? '');
                $rainColumn = $pRain?->kolom_sensor ? (string) $pRain->kolom_sensor : null;

                if ($rainColumn && $this->canQueryRainTable($tableName, $rainColumn)) {
                    $hourStart = now()->copy()->startOfHour();
                    $hourEnd   = now()->copy()->endOfHour();
                    $dayStart  = now()->copy()->startOfDay();
                    $dayEnd    = now()->copy()->endOfDay();

                    $hourlyRain = DB::table($tableName)
                        ->where('id_logger', $lg->id_logger)
                        ->whereBetween('waktu', [$hourStart, $hourEnd])
                        ->sum($rainColumn);

                    $dailyRain = DB::table($tableName)
                        ->where('id_logger', $lg->id_logger)
                        ->whereBetween('waktu', [$dayStart, $dayEnd])
                        ->sum($rainColumn);

                    $lg->arr_curah_hujan_perjam = is_numeric($hourlyRain) ? (float) $hourlyRain : null;
                    $lg->arr_curah_hujan_harian = is_numeric($dailyRain)  ? (float) $dailyRain  : null;
                    $lg->arr_status_perjam  = $this->resolveRainStatus($lg->id_logger, 'perjam',  $lg->arr_curah_hujan_perjam);
                    $lg->arr_status_perhari = $this->resolveRainStatus($lg->id_logger, 'perhari', $lg->arr_curah_hujan_harian);
                    $lg->arr_state_perjam   = $this->resolveRainStateKey($lg->id_katlogger, $lg->arr_curah_hujan_perjam);
                    $lg->arr_state_perhari  = $this->resolveRainStateKey($lg->id_katlogger, $lg->arr_curah_hujan_harian);
                }


                return $lg;
            });

        $groupedLoggers = $loggers
            ->groupBy(function ($lg) {
                $kategori = $lg->kategori?->nama_kategori ?? $lg->kategori?->kode ?? null;
                return $kategori ? strtoupper(trim($kategori)) : 'TANPA KATEGORI';
            })
            ->sortKeys()
            ->map(fn($categoryLoggers) => $categoryLoggers->sortBy('nama_logger')->values());

        return view('beranda.index', [
            'title' => 'Beranda',
            'loggers' => $loggers,
            'groupedLoggers' => $groupedLoggers,
        ]);
    }

    private function canQueryRainTable(string $tableName, string $rainColumn): bool
    {
        if ($tableName === '' || !preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
            return false;
        }

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        return Schema::hasColumn($tableName, 'id_logger')
            && Schema::hasColumn($tableName, 'waktu')
            && Schema::hasColumn($tableName, $rainColumn);
    }

    private function resolveRainStatus(string $loggerId, string $period, ?float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $thresholds = $this->rainThresholds($loggerId, $period);

        if ($thresholds->isEmpty()) {
            return null;
        }

        $matched = null;

        foreach ($thresholds as $threshold) {
            $min = is_numeric($threshold->debit_air) ? (float) $threshold->debit_air : null;

            if ($min === null) {
                continue;
            }

            if ($value >= $min) {
                $matched = $threshold;
            } else {
                break;
            }
        }

        if ($matched) {
            return $matched->intensitas;
        }

        return $thresholds->first()?->intensitas;
    }

    private function rainThresholds(string $loggerId, string $period): Collection
    {
        static $cache = [];
        $key = $loggerId . '|' . $period;

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = DB::table('klasifikasi_hujan')
                ->where('logger_id', $loggerId)
                ->where('waktu', $period)
                ->get(['debit_air', 'intensitas'])
                ->filter(fn($row) => is_numeric($row->debit_air))
                ->sortBy(fn($row) => (float) $row->debit_air)
                ->values();
        }

        return $cache[$key];
    }

    private function resolveRainStateKey(?int $kategoriId, ?float $value): ?string
    {
        if (!$kategoriId || $value === null) {
            return null;
        }

        $thresholds = $this->kategoriThresholds($kategoriId);

        if ($thresholds->isEmpty()) {
            return null;
        }

        foreach ($thresholds as $threshold) {
            $min = is_numeric($threshold->min_value) ? (float) $threshold->min_value : null;
            $max = is_numeric($threshold->max_value) ? (float) $threshold->max_value : null;

            // Skip non-value states such as koneksi_terputus.
            if ($min === null && $max === null) {
                continue;
            }

            $matchesMin = $min === null || $value >= $min;
            $matchesMax = $max === null || $value < $max;

            if ($matchesMin && $matchesMax) {
                return $threshold->state_key;
            }
        }

        return $thresholds
            ->first(function ($threshold) {
                return $threshold->min_value !== null || $threshold->max_value !== null;
            })
            ?->state_key;
    }

    private function kategoriThresholds(int $kategoriId): Collection
    {
        static $cache = [];

        if (!array_key_exists($kategoriId, $cache)) {
            $cache[$kategoriId] = DB::table('klasifikasi_threshold')
                ->where('id_kategori', $kategoriId)
                ->orderBy('sort_order')
                ->get(['state_key', 'min_value', 'max_value', 'sort_order']);
        }

        return $cache[$kategoriId];
    }
    // public function index(MiniStesyApi $api)
    // {
    //     // daftar logger statis dulu (nanti bisa dari API lokasi_new)
    //     $loggerIds = ['10360', '10361', '10362', '10363', '10364', '10365'];

    //     $loggers = collect($loggerIds)->map(function ($id) use ($api) {
    //         $data = $api->logger($id);

    //         if (!$data || !isset($data['sensor'])) return null;

    //         $humidity = $battery = $temp = $muka = $kedalaman = null;

    //         foreach ($data['sensor'] as $s) {
    //             if (str_contains($s['namaSensor'], 'Humidity')) $humidity = $s['value'];
    //             if (str_contains($s['namaSensor'], 'Battery')) $battery = $s['value'];
    //             if (str_contains($s['namaSensor'], 'Temperature')) $temp = $s['value'];
    //             if (str_contains($s['namaSensor'], 'Muka')) $muka = $s['value'];
    //             if (str_contains($s['namaSensor'], 'Kedalaman')) $kedalaman = $s['value'];
    //         }

    //         return [
    //             'id_logger' => $id,
    //             'nama_lokasi' => $data['lokasi'],
    //             'lat' => $data['lat'],
    //             'lng' => $data['long'],
    //             'waktu' => $data['waktu'],
    //             'humidity' => $humidity,
    //             'battery' => $battery,
    //             'temp' => $temp,
    //             'Muka_Air' => $muka,
    //             'Kedalaman_Air' => $kedalaman,
    //             // 'status' => now()->diffInMinutes($data['waktu']) < 120 ? 'online' : 'offline'
    //             'status' => now()->diffInMinutes($data['waktu']) < 120 ? 'offline' : 'online'
    //         ];
    //     })->filter();

    //     // dd($loggers);

    //     return view('beranda.index', [
    //         'title' => 'Beranda',
    //         'loggers' => $loggers,
    //     ]);
    // }

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
