<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use App\Support\SensorFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RealtimeApiController extends Controller
{
    /**
     * GET /api/v1/mobile/realtime/mqtt-config
     * Config MQTT broker untuk koneksi WebSocket dari mobile.
     */
    public function mqttConfigEndpoint()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->mqttConfig(),
        ]);
    }

    /**
     * GET /api/v1/mobile/realtime/devices
     * List semua device + status online/offline + mqtt config.
     */
    public function devices(Request $request)
    {
        $devices = t_Logger::query()
            ->forUser($request->user())
            ->with(['lokasi', 'kategori', 'jiat', 'params', 'temp16', 'temp19', 'temp50'])
            ->orderBy('nama_logger')
            ->get()
            ->map(function ($lg) {
                $waktu16 = optional($lg->temp16)->waktu;
                $waktu19 = optional($lg->temp19)->waktu;
                $waktu50 = optional($lg->temp50)->waktu;
                $lastTime = collect([$waktu16, $waktu19, $waktu50])->filter()->sortDesc()->first();
                $diffMin  = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
                $status   = ($diffMin !== null && $diffMin < 60) ? 'online' : 'offline';

                return [
                    'id_logger'     => $lg->id_logger,
                    'nama_logger'   => $lg->nama_logger,
                    'nama_lokasi'   => $lg->lokasi?->nama_lokasi,
                    'kategori'      => $lg->kategori?->nama_kategori,
                    'tabel_main'    => $lg->tabel_main,
                    'status'        => $status,
                    'last_time'     => $lastTime,
                    'selisih_menit' => $diffMin,
                    'params_count'  => $lg->params->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $devices,
            'mqtt'    => $this->mqttConfig(),
            'meta'    => ['total' => $devices->count()],
        ]);
    }

    /**
     * GET /api/v1/mobile/realtime/data/{id_logger}
     * Data sensor realtime untuk satu logger (1 jam terakhir).
     */
    public function data(Request $request, string $id)
    {
        $device = t_Logger::query()
            ->forUser($request->user())
            ->where('id_logger', $id)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan'], 404);
        }

        $params    = DB::table('parameter_sensor')->where('logger_id', $id)->get();
        $tableMain = trim((string) ($device->tabel_main ?? ''));

        if (!$this->isSupportedTable($tableMain)) {
            $sensorCount = (int) ($device->sensor_count ?? $device->jumlah_sensor ?? 0);
            if (!$sensorCount) $sensorCount = $params->count();
            $tableMain = SensorFamily::mainTablePrefix(SensorFamily::familyFor($sensorCount)) . '01';
        }

        $historyMin = max((int) env('REALTIME_HISTORY_MINUTES', 60), 1);
        $futureMin  = max((int) env('REALTIME_FUTURE_GRACE_MINUTES', 15), 0);
        $start = now()->subMinutes($historyMin);
        $end   = now()->addMinutes($futureMin);

        try {
            $data = $this->getRealtimeRows($tableMain, $id, $start, $end);

            if ($data->isEmpty()) {
                $fallback     = $this->buildFallbackTable($tableMain);
                $fallbackData = $this->getRealtimeRows($fallback, $id, $start, $end);
                if ($fallbackData->isNotEmpty()) {
                    $data = $fallbackData;
                    $tableMain = $fallback;
                }
            }

            return response()->json([
                'success'     => true,
                'device'      => [
                    'id_logger'   => $device->id_logger,
                    'nama_logger' => $device->nama_logger,
                    'tabel_main'  => $tableMain,
                ],
                'params'      => $params,
                'data'        => $data,
                'last_update' => $data->first()->waktu ?? null,
                'mqtt'        => $this->mqttConfig(),
                'meta'        => [
                    'from'    => $start->toIso8601String(),
                    'to'      => $end->toIso8601String(),
                    'count'   => $data->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error ambil data: ' . $e->getMessage(),
                'params'  => $params,
                'data'    => [],
            ], 500);
        }
    }

    /**
     * GET /api/v1/mobile/realtime/mqtt-config
     * Config broker untuk kontrol pompa dari mobile (via WebSocket).
     *
     * PENTING: mobile pakai MQTT HANYA untuk kontrol pompa, jadi harus mengarah
     * ke broker pompa (mqtt.beacontelemetry.com), sama seperti web pump control.
     * JANGAN pakai var MQTT_WS_* — itu milik broker realtime data (72.x) yang
     * sengaja dipisah agar tidak tercampur.
     */
    public function mqttConfig()
    {
        return [
            'broker'  => env('MQTT_HOST', 'mqtt.beacontelemetry.com'),
            'port'    => (int) env('MQTT_PUMP_WS_PORT', 8083),
            'path'    => env('MQTT_PUMP_WS_PATH', '/mqtt'),
            'user'    => env('MQTT_AUTH_USERNAME', env('MQTT_USER', 'userlog')),
            'pass'    => env('MQTT_AUTH_PASSWORD', env('MQTT_PASS', 'b34c0n')),
            'use_ssl' => filter_var(env('MQTT_PUMP_WS_SSL', true), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function getRealtimeRows(string $table, string $id, $start, $end)
    {
        return DB::table($table)
            ->where('id_logger', $id)
            ->whereBetween('waktu', [$start, $end])
            ->orderBy('waktu', 'desc')
            ->limit(2000)
            ->get();
    }

    private function isSupportedTable(string $table): bool
    {
        return SensorFamily::isFamilyTable($table) && Schema::hasTable($table);
    }

    private function buildFallbackTable(string $table): string
    {
        // Legacy 16↔19 pair: try the sibling family at the same shard suffix.
        if (preg_match('/^t_s(16|19)_(\d{2,})$/', $table, $m)) {
            $other = ((int) $m[1] === 19) ? 16 : 19;
            return "t_s{$other}_{$m[2]}";
        }
        // No sibling family (e.g. t_s50_*): nothing better to try.
        return $table;
    }
}
