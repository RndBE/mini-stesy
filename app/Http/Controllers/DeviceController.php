<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = t_Logger::query()
            ->with(['lokasi', 'params', 'jiat'])
            ->orderBy('id_logger')
            ->get();

        return view('device.index', [
            'title' => 'Pengaturan Device',
            'devices' => $devices,
        ]);
    }
}
