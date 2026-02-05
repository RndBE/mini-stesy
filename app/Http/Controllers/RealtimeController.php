<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RealtimeController extends Controller
{
    public function index()
    {
        // $devices = t_Logger::orderBy('id_logger')->get();
        // return view('realtime.index', [
        //     'title' => 'Realtime Monitoring',
        //     'devices' => $devices
        // ]);
        $devices = t_Logger::query()
            ->with(['lokasi', 'kategori', 'jiat', 'params', 'temp16', 'temp19'])
            ->orderBy('nama_logger')
            ->get()
            ->map(function ($lg) {

                $waktu16 = optional($lg->temp16)->waktu;
                $waktu19 = optional($lg->temp19)->waktu;

                $latestWaktu = collect([$waktu16, $waktu19])
                    ->filter()
                    ->sortDesc()
                    ->first();

                $diffMinutes = null;

                if ($latestWaktu) {
                    $diffMinutes = Carbon::parse($latestWaktu)->diffInMinutes(now());
                }

                $lg->status_logger = $diffMinutes < 120 ? 'online' : 'offline';
                $lg->latest_waktu  = $latestWaktu;
                $lg->selisih_menit = $diffMinutes;

                return $lg;
            });

        return view('realtime.index', [
            'title'   => 'Realtime Monitoring',
            'devices' => $devices
        ]);
    }

    public function getData($id)
    {
        // 1. Fetch Device/Logger using Query Builder (Raw SQL equivalent)
        $device = DB::table('t_logger')
            ->where('id_logger', $id)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        }

        // 2. Fetch Parameters using Query Builder
        $params = DB::table('parameter_sensor')
            ->where('logger_id', $id)
            ->get();

        // 3. Determine table based on sensor_count logic
        $tableName = ((int)$device->sensor_count === 19) ? 't_s19_01' : 't_s16_01';

        try {
            // 4. Fetch Sensor Data
            $data = DB::table($tableName)
                ->where('id_logger', $id)
                ->orderBy('waktu', 'desc')
                ->limit(60)
                ->get();

            return response()->json([
                'success' => true,
                'device'  => $device,
                'params'  => $params,
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data error: ' . $e->getMessage(),
                'device'  => $device,
                'params'  => $params,
                'data'    => []
            ]);
        }
    }
}
