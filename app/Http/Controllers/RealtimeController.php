<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Support\SensorFamily;
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
            ->with(['lokasi', 'kategori', 'jiat', 'params', 'temp16', 'temp19', 'temp50'])
            ->orderBy('nama_logger')
            ->get()
            ->map(function ($lg) {

                $waktu16 = optional($lg->temp16)->waktu;
                $waktu19 = optional($lg->temp19)->waktu;
                $waktu50 = optional($lg->temp50)->waktu;

                $latestWaktu = collect([$waktu16, $waktu19, $waktu50])
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

        $firstDevice = $devices->firstWhere('status_logger', 'online') ?: $devices->first();

        return view('realtime.index', [
            'title'   => 'Realtime Monitoring',
            'devices' => $devices,
            'firstDevice' => $firstDevice,
            'mqttConfig' => [
                'broker' => env('MQTT_WS_HOST', '72.60.78.159'),
                'port' => (int) env('MQTT_WS_PORT', 8383),
                'path' => env('MQTT_WS_PATH', '/mqtt'),
                'user' => env('MQTT_WS_USER', env('MQTT_USER', 'beacon')),
                'pass' => env('MQTT_WS_PASS', env('MQTT_PASS', 'be_jogja')),
                'useSSL' => filter_var(env('MQTT_WS_SSL', false), FILTER_VALIDATE_BOOLEAN),
            ],
        ]);
    }

    public function getData($id)
    {
        $user = auth()->user();
        $device = t_Logger::query()
            ->forUser($user)
            ->where('id_logger', $id)
            ->first();

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
                $sensorCount = $params->count();
            }
            $tableMain = SensorFamily::mainTablePrefix(SensorFamily::familyFor($sensorCount)) . '01';
        }

        $primaryTable = $tableMain;
        $fallbackTable = $this->buildFallbackTableName($primaryTable);

        // Query window for reload:
        // - history keeps recent data
        // - future grace allows logger timestamps slightly ahead of server clock
        $historyMinutes = max((int) env('REALTIME_HISTORY_MINUTES', 60), 1);
        $futureGraceMinutes = max((int) env('REALTIME_FUTURE_GRACE_MINUTES', 15), 0);
        $start = now()->subMinutes($historyMinutes);
        $end = now()->addMinutes($futureGraceMinutes);

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
        if (!SensorFamily::isFamilyTable($tableName)) {
            return false;
        }

        return Schema::hasTable($tableName);
    }

    private function buildFallbackTableName(string $tableMain): string
    {
        if (preg_match('/^t_s(16|19)_(\d{2,})$/', $tableMain, $m)) {
            $otherFamily = ((int) $m[1] === 19) ? 16 : 19;
            return 't_s' . $otherFamily . '_' . $m[2];
        }

        // No sibling family (e.g. t_s50_*): nothing better to try.
        return $tableMain;
    }
}
