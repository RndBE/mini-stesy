<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Support\ArrRainStatus;
use Carbon\Carbon;
use App\Services\MiniStesyApi;
use Illuminate\Support\Facades\DB;

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

                if ($rainColumn && ArrRainStatus::canQueryRainTable($tableName, $rainColumn)) {
                    $lg->arr_curah_hujan_perjam = ArrRainStatus::hourlyAccumulation($tableName, $lg->id_logger, $rainColumn);
                    $lg->arr_curah_hujan_harian = ArrRainStatus::dailyAccumulation($tableName, $lg->id_logger, $rainColumn);
                    // Per-logger thresholds win; fall back to the per-category
                    // label so the status text stays in sync with the icon even
                    // before a logger's klasifikasi_hujan has been configured.
                    $lg->arr_status_perjam  = ArrRainStatus::classify($lg->id_logger, 'perjam',  $lg->arr_curah_hujan_perjam)
                        ?? ArrRainStatus::categoryStateLabel($lg->id_katlogger, $lg->arr_curah_hujan_perjam);
                    $lg->arr_status_perhari = ArrRainStatus::classify($lg->id_logger, 'perhari', $lg->arr_curah_hujan_harian)
                        ?? ArrRainStatus::categoryStateLabel($lg->id_katlogger, $lg->arr_curah_hujan_harian);
                    $lg->arr_state_perjam   = ArrRainStatus::categoryStateKey($lg->id_katlogger, $lg->arr_curah_hujan_perjam);
                    $lg->arr_state_perhari  = ArrRainStatus::categoryStateKey($lg->id_katlogger, $lg->arr_curah_hujan_harian);
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
