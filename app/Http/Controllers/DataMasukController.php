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
                $fallbackTable = $this->buildFallbackTableName($tableMain, $sensorCount);
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

    public function datamasuk(Request $request)
    {
        $id = $request->input('id_alat')
            ?? $request->input('id_logger')
            ?? $request->input('code_logger');

        // ── Bangun waktu: bisa dari 'waktu' (format lama) atau 'hari'+'jam' (format baru) ──
        $waktu = $request->input('waktu');
        if (!$waktu && $request->has('hari') && $request->has('jam')) {
            $waktu = $request->input('hari') . ' ' . $request->input('jam');
        }

        if (!$id || !$waktu) {
            return response()->json(['success' => false, 'message' => 'id_alat dan waktu wajib'], 400);
        }

        $logger = \Illuminate\Support\Facades\DB::table('t_logger')->where('id_logger', $id)->first();
        if (!$logger) {
            return response()->json(['success' => false, 'message' => 'Logger tidak ditemukan'], 404);
        }

        try {
            $appTimezone = config('app.timezone', 'Asia/Jakarta');
            $waktuParsed = \Carbon\Carbon::parse($waktu, $appTimezone)
                ->setTimezone($appTimezone)
                ->toDateTimeString();
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

        // ── Parsing sensor: dukung format nested {"nama":...,"nilai":...,"satuan":...}
        //    maupun format flat (nilai langsung)
        for ($i = 1; $i <= $maxSensor; $i++) {
            $k = 'sensor' . $i;
            $raw = $request->input($k);

            if (is_array($raw)) {
                // Format baru: ambil 'nilai', jika array kosong atau tidak ada 'nilai' → null
                $row[$k] = array_key_exists('nilai', $raw) ? $raw['nilai'] : null;
            } else {
                // Format lama: nilai langsung (string/number/null)
                $row[$k] = $raw;
            }
        }

        // Merge dengan snapshot terkini agar kolom NOT NULL tidak kosong
        // Sensor yang tidak dikirim alat ini diisi dari nilai sebelumnya (bukan null/0)
        $existing = \Illuminate\Support\Facades\DB::table($tableTemp)
            ->where('id_logger', $id)
            ->first();

        $merged = $existing ? (array) $existing : [];

        foreach ($row as $col => $val) {
            // Hanya update kolom yang ada nilainya (tidak null) dari request
            if ($val !== null) {
                $merged[$col] = $val;
            }
        }

        // Pastikan id_logger dan waktu selalu terupdate
        $merged['id_logger'] = $id;
        $merged['waktu']     = $row['waktu'];

        // Hapus kolom yang tidak ada di tabel (misal kolom tambahan dari cast array)
        // Ini memastikan insert tidak error karena kolom asing
        $allowedCols = array_merge(['id_logger', 'waktu'], array_map(fn($i) => 'sensor'.$i, range(1, $maxSensor)));
        $merged = array_intersect_key($merged, array_flip($allowedCols));

        // Insert ke tabel besar (histori permanen) — pakai data merged agar tidak ada null
        \Illuminate\Support\Facades\DB::table($tableMain)->insert($merged);

        // Update tabel latest (snapshot dashboard) — pakai data merged yang sama
        \Illuminate\Support\Facades\DB::table($tableTemp)->updateOrInsert(
            ['id_logger' => $id],
            $merged
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

        $mqttHost = env('MQTT_HOST', '72.60.78.159');
        $mqttPort = (int) env('MQTT_PORT', 1883);
        $mqttUser = env('MQTT_USER', 'beacon');
        $mqttPass = env('MQTT_PASS', 'be_jogja');
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
        if (!preg_match('/^t_s(16|19)_\d{2,}$/', $tableName)) {
            return false;
        }

        return Schema::hasTable($tableName);
    }

    private function buildFallbackTableName(string $tableMain, int $sensorCount): string
    {
        if (preg_match('/^t_s(16|19)_(\d{2,})$/', $tableMain, $m)) {
            $otherFamily = ((int) $m[1] === 19) ? 16 : 19;
            return 't_s' . $otherFamily . '_' . $m[2];
        }

        return $sensorCount >= 19 ? 't_s16_01' : 't_s19_01';
    }
}
