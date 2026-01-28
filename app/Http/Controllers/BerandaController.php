<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;

class BerandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $loggers = t_Logger::query()
        //     ->with(['lokasi', 'kategori', 'jiat', 'params'])
        //     ->leftJoin('temp_s16_latest as t16', 't16.id_logger', '=', 't_logger.id_logger')
        //     ->leftJoin('temp_s19_latest as t19', 't19.id_logger', '=', 't_logger.id_logger')
        //     ->selectRaw("
        //     t_logger.*,
        //     COALESCE(t19.waktu, t16.waktu) as last_waktu,
        //     COALESCE(t19.sensor1,  t16.sensor1)  as sensor1,
        //     COALESCE(t19.sensor2,  t16.sensor2)  as sensor2,
        //     COALESCE(t19.sensor3,  t16.sensor3)  as sensor3,
        //     COALESCE(t19.sensor4,  t16.sensor4)  as sensor4,
        //     COALESCE(t19.sensor5,  t16.sensor5)  as sensor5,
        //     COALESCE(t19.sensor6,  t16.sensor6)  as sensor6,
        //     COALESCE(t19.sensor7,  t16.sensor7)  as sensor7,
        //     COALESCE(t19.sensor8,  t16.sensor8)  as sensor8,
        //     COALESCE(t19.sensor9,  t16.sensor9)  as sensor9,
        //     COALESCE(t19.sensor10, t16.sensor10) as sensor10,
        //     COALESCE(t19.sensor11, t16.sensor11) as sensor11,
        //     COALESCE(t19.sensor12, t16.sensor12) as sensor12,
        //     COALESCE(t19.sensor13, t16.sensor13) as sensor13,
        //     COALESCE(t19.sensor14, t16.sensor14) as sensor14,
        //     COALESCE(t19.sensor15, t16.sensor15) as sensor15,
        //     COALESCE(t19.sensor16, t16.sensor16) as sensor16,
        //     t19.sensor17 as sensor17,
        //     t19.sensor18 as sensor18,
        //     t19.sensor19 as sensor19")
        //     ->orderBy('t_logger.nama_logger')
        //     ->get();
        $loggers = t_Logger::query()
            ->with(['lokasi', 'kategori', 'jiat','params', 'temp16', 'temp19'])
            ->orderBy('nama_logger')
            ->get();
        return view('beranda.index', [
            'title' => 'Beranda',
            'loggers' => $loggers,
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
