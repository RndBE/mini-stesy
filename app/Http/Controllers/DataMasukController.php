<?php

namespace App\Http\Controllers;

use App\Models\t_Logger;
use App\Models\Parameter_sensor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Bluerhinos\phpMQTT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataMasukController extends Controller
{
    public function index()
    {
        $loggers = t_Logger::query()
            ->forUser(auth()->user())
            ->orderBy('nama_logger')
            ->get();

        return view('data-masuk.index', ['title' => 'Data Masuk'], compact('loggers'));
    }

    public function getData(Request $request)
    {
        try {
            $logger_id = $request->query('logger_id');
            $tanggal = $request->query('tanggal');

            if (!$logger_id || !$tanggal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logger ID dan tanggal harus diisi.'
                ], 400);
            }

            try {
                $tanggalParsed = Carbon::createFromFormat('Y-m-d', $tanggal, config('app.timezone'))->startOfDay();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tanggal tidak valid.'
                ], 400);
            }

            $now = Carbon::now(config('app.timezone'));
            $tanggalAwal = $tanggalParsed->copy()->startOfDay();

            if ($tanggalParsed->isSameDay($now)) {
                $tanggalAkhir = $now;
            } else {
                $tanggalAkhir = $tanggalParsed->copy()->endOfDay();
            }

            $sensorCount = null;
            $loggerRow = t_Logger::query()
                ->forUser(auth()->user())
                ->where('id_logger', $logger_id)
                ->first();

            if (!$loggerRow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logger tidak ditemukan atau tidak memiliki akses.'
                ], 404);
            }

            $parameters = Parameter_sensor::where('logger_id', $logger_id)->get();
            if ($loggerRow && isset($loggerRow->jumlah_sensor) && is_numeric($loggerRow->jumlah_sensor)) {
                $sensorCount = (int) $loggerRow->jumlah_sensor;
            }
            if (!$sensorCount && $loggerRow && isset($loggerRow->sensor_count) && is_numeric($loggerRow->sensor_count)) {
                $sensorCount = (int) $loggerRow->sensor_count;
            }

            if (!$sensorCount) {
                $paramCount = $parameters->count();
                if ($paramCount >= 19) $sensorCount = 19;
                elseif ($paramCount >= 16) $sensorCount = 16;
                else $sensorCount = 16;
            }

            $tableMain = $this->resolveMainTable($loggerRow?->tabel_main, $sensorCount);
            $useS19 = str_contains($tableMain, '19');
            $query = DB::table($tableMain)
                ->where('id_logger', $logger_id)
                ->whereBetween('waktu', [$tanggalAwal, $tanggalAkhir])
                ->orderBy('waktu', 'desc');
            $sensorCount = $useS19 ? 19 : 16;

            if ($query->count() === 0) {
                $fallbackTable = $tableMain === 't_s19_01' ? 't_s16_01' : 't_s19_01';
                if ($this->isSupportedTable($fallbackTable)) {
                    $fallbackQuery = DB::table($fallbackTable)
                        ->where('id_logger', $logger_id)
                        ->whereBetween('waktu', [$tanggalAwal, $tanggalAkhir])
                        ->orderBy('waktu', 'desc');

                    if ($fallbackQuery->count() > 0) {
                        $query = $fallbackQuery;
                        $tableMain = $fallbackTable;
                        $useS19 = str_contains($tableMain, '19');
                        $sensorCount = $useS19 ? 19 : 16;
                    }
                }
            }

            $columns = [];
            $parameterMap = [];

            for ($i = 1; $i <= $sensorCount; $i++) {
                $sensorKey = 'sensor' . $i;
                $param = $parameters->firstWhere('kolom_sensor', $sensorKey);

                $columnHeader = $param && $param->nama_parameter
                    ? $param->nama_parameter
                    : 'Sensor ' . $i;

                $columns[] = $columnHeader;
                $parameterMap[$columnHeader] = $sensorKey;
            }

            $rawData = $query->get();

            $data = $rawData->map(function ($row) use ($columns, $parameterMap) {
                $transformed = [
                    'waktu' => $row->waktu
                ];
                foreach ($columns as $col) {
                    $key = $parameterMap[$col] ?? null;
                    $transformed[strtolower($col)] = $key ? ($row->{$key} ?? null) : null;
                }
                return $transformed;
            })->toArray();

            // Calculate completeness: actual records / expected records per day (1440 minutes)
            $expectedRecords = 1440; // 24 hours * 60 minutes per day
            $actualRecords = count($data);
            $completeness = round(($actualRecords / $expectedRecords) * 100, 2);

            return response()->json([
                'success' => true,
                'data' => $data,
                'columns' => $columns,
                'completeness' => $completeness,
                'total' => count($data),
                'range' => [
                    'start' => $tanggalAwal->toDateTimeString(),
                    'end' => $tanggalAkhir->toDateTimeString(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('DataMasukController@getData Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function add_awlr2(Request $request)
    {
        $id = $request->input('id_alat')
            ?? $request->input('id_logger')
            ?? $request->input('code_logger');
        $waktu = $request->input('waktu');

        if (!$id || !$waktu) {
            return response()->json(['success' => false, 'message' => 'id_alat dan waktu wajib'], 400);
        }

        $logger = \Illuminate\Support\Facades\DB::table('t_logger')->where('id_logger', $id)->first();
        if (!$logger) {
            return response()->json(['success' => false, 'message' => 'Logger tidak ditemukan'], 404);
        }

        try {
            $waktuParsed = \Carbon\Carbon::parse($waktu, config('app.timezone'))->toDateTimeString();
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Format waktu tidak valid'], 400);
        }

        $sensorCount = null;
        if (isset($logger->sensor_count) && is_numeric($logger->sensor_count)) $sensorCount = (int) $logger->sensor_count;
        if (!$sensorCount && isset($logger->jumlah_sensor) && is_numeric($logger->jumlah_sensor)) $sensorCount = (int) $logger->jumlah_sensor;
        if (!$sensorCount) $sensorCount = 16;

        $tableMain = $this->resolveMainTable($logger->tabel_main ?? null, $sensorCount);
        $tableTemp = str_contains($tableMain, '19') ? 'temp_s19_latest' : 'temp_s16_latest';

        $row = [
            'id_logger' => $id,
            'waktu' => $waktuParsed,
        ];

        $maxSensor = str_contains($tableMain, '19') ? 19 : 16;
        for ($i = 1; $i <= $maxSensor; $i++) {
            $k = 'sensor' . $i;
            $row[$k] = $request->input($k);
        }

        \Illuminate\Support\Facades\DB::table($tableMain)->insert($row);

        \Illuminate\Support\Facades\DB::table($tableTemp)->updateOrInsert(
            ['id_logger' => $id],
            $row
        );

        $payload = $row;
        $payload['code_logger'] = $id;

        $forwardUrl = config('services.awlr_forward.url', env('AWLR_FORWARD_URL'));
        $forwardOk = null;
        $forwardErr = null;

        if ($forwardUrl) {
            try {
                $resp = \Illuminate\Support\Facades\Http::asForm()->timeout(5)->post($forwardUrl, $payload);
                $forwardOk = $resp->successful();
                if (!$forwardOk) $forwardErr = 'HTTP ' . $resp->status();
            } catch (\Throwable $e) {
                $forwardOk = false;
                $forwardErr = $e->getMessage();
            }
        }

        $mqttHost = env('MQTT_HOST', 'mqtt.beacontelemetry.com');
        $mqttPort = (int) env('MQTT_PORT', 8883);
        $mqttUser = env('MQTT_USER', 'userlog');
        $mqttPass = env('MQTT_PASS', 'b34c0n');
        // $mqttCa = env('MQTT_CA', '/etc/ssl/certs/ca-bundle.crt');

        $mqttOk = null;
        $mqttErr = null;

        try {
            $clientId = 'bemqtt-' . $id . '-' . \Illuminate\Support\Str::random(6);
            $mqtt = new \Bluerhinos\phpMQTT($mqttHost, $mqttPort, $clientId);
            // $mqtt = new phpMQTT($mqttHost, $mqttPort, $clientId, $mqttCa);


            if ($mqtt->connect(true, null, $mqttUser, $mqttPass)) {
                $mqtt->publish((string) $id, json_encode($payload), 0, false);
                $mqtt->close();
                $mqttOk = true;
            } else {
                $mqttOk = false;
                $mqttErr = 'connect timeout';
            }
        } catch (\Throwable $e) {
            $mqttOk = false;
            $mqttErr = $e->getMessage();
        }

        return response()->json([
            'success' => true,
            'table_main' => $tableMain,
            'table_temp' => $tableTemp,
            'forward' => [
                'enabled' => (bool) $forwardUrl,
                'ok' => $forwardOk,
                'error' => $forwardErr,
            ],  
            'mqtt' => [
                'ok' => $mqttOk,
                'error' => $mqttErr,
                'topic' => (string) $id,
            ],
        ]);
    }

    private function resolveMainTable(?string $tableMain, int $sensorCount): string
    {
        $tableMain = trim((string) $tableMain);
        if ($this->isSupportedTable($tableMain)) {
            return $tableMain;
        }

        return $sensorCount >= 19 ? 't_s19_01' : 't_s16_01';
    }

    private function isSupportedTable(string $tableName): bool
    {
        if (!in_array($tableName, ['t_s16_01', 't_s19_01'], true)) {
            return false;
        }

        return Schema::hasTable($tableName);
    }
}
