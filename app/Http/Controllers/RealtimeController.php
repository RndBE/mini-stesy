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
            ->forUser(auth()->user())
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
            $status = 'tidak_aktif';

                if ($latestWaktu) {
                    $diffMinutes = Carbon::parse($latestWaktu)->diffInMinutes(now());
                    $status = $diffMinutes < 60 ? 'online' : 'offline';
                }

                // $lg->status_logger = $diffMinutes < 60 ? 'online' : 'offline';
                $lg->status_logger = $status;
                $lg->latest_waktu  = $latestWaktu;
                $lg->selisih_menit = $diffMinutes;

                return $lg;
            });

        return view('realtime.index', [
            'title'   => 'Realtime Monitoring',
            'devices' => $devices,
            'firstDevice' => $devices->first(),
        ]);
    }

    public function getData($id)
    {
        $user = auth()->user();
        $deviceQuery = DB::table('t_logger')->where('id_logger', $id);
        if ($user && $user->level_user !== 'superadmin') {
            $deviceQuery->where('instansi_id', $user->instansi_id);
        }
        $device = $deviceQuery->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        }

        $params = DB::table('parameter_sensor')->where('logger_id', $id)->get();

        $tableMain = isset($device->tabel_main) ? trim((string) $device->tabel_main) : '';
        if (!$this->isSupportedTable($tableMain)) {
            $sensorCount = null;
            if (isset($device->sensor_count) && is_numeric($device->sensor_count)) {
                $sensorCount = (int) $device->sensor_count;
            }
            if (!$sensorCount && isset($device->jumlah_sensor) && is_numeric($device->jumlah_sensor)) {
                $sensorCount = (int) $device->jumlah_sensor;
            }
            if (!$sensorCount) {
                $sensorCount = $params->count() >= 19 ? 19 : 16;
            }
            $tableMain = $sensorCount >= 19 ? 't_s19_01' : 't_s16_01';
        }

        $primaryTable = $tableMain;
        $fallbackTable = $primaryTable === 't_s19_01' ? 't_s16_01' : 't_s19_01';

        // Get data from last 60 minutes
        $start = now()->subMinutes(60);
        $end = now();

        try {
            $data = $this->getRealtimeRows($primaryTable, $id, $start, $end);
            $tableUsed = $primaryTable;

            // Fallback for inconsistent logger config vs stored table.
            if ($data->isEmpty()) {
                $fallbackData = $this->getRealtimeRows($fallbackTable, $id, $start, $end);
                if ($fallbackData->isNotEmpty()) {
                    $data = $fallbackData;
                    $tableUsed = $fallbackTable;
                }
            }

            $lastUpdate = $data->first()->waktu ?? null;

            return response()->json([
                'success' => true,
                'device' => $device,
                'params' => $params,
                'data' => $data,
                'table' => $tableUsed,
                'last_update' => $lastUpdate,
                'today' => $start->format('Y-m-d')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data error: ' . $e->getMessage(),
                'device' => $device,
                'params' => $params,
                'data' => [],
                'last_update' => null,
                'today' => $start->format('Y-m-d')
            ]);
        }
    }

    private function getRealtimeRows(string $tableName, string $id, $start, $end)
    {
        return DB::table($tableName)
            ->where('id_logger', $id)
            ->whereBetween('waktu', [$start, $end])
            ->orderBy('waktu', 'desc')
            ->limit(2000)
            ->get();
    }

    private function isSupportedTable(string $tableName): bool
    {
        if (!in_array($tableName, ['t_s16_01', 't_s19_01'], true)) {
            return false;
        }

        return Schema::hasTable($tableName);
    }
}
