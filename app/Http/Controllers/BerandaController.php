<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use Carbon\Carbon;
use App\Services\MiniStesyApi;

class BerandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loggers = t_Logger::query()
            ->with(['lokasi', 'kategori', 'jiat', 'params', 'temp16', 'temp19'])
            ->orderBy('nama_logger')
            ->get()
            ->map(function ($lg) {

                // Ambil waktu terbaru dari temp16 & temp19
                $waktu16 = optional($lg->temp16)->waktu;
                $waktu19 = optional($lg->temp19)->waktu;

                $latestWaktu = collect([$waktu16, $waktu19])
                    ->filter()
                    ->sortDesc()
                    ->first();

                $isActive = false;

                if ($latestWaktu) {
                    // $isActive = Carbon::parse($latestWaktu)->isToday();
                    $isActive = Carbon::parse($latestWaktu)->diffInMinutes(now());
                }

                $lg->status_logger = $isActive < 120 ? 'online' : 'offline';
                $lg->latest_waktu  = $latestWaktu;

                return $lg;
            });

        return view('beranda.index', [
            'title' => 'Beranda',
            'loggers' => $loggers,
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
