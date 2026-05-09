<?php

namespace App\Http\Controllers;

use App\Models\t_Logger;
use App\Models\Parameter_sensor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataMasukController extends Controller
{
    public function index()
    {
        $query = t_Logger::query();
        if (auth()->check()) {
            $query->forUser(auth()->user());
        }
        $loggers = $query->orderBy('nama_logger')->get();

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
            $queryLoggerRow = t_Logger::query()->where('id_logger', $logger_id);
            if (auth()->check()) {
                $queryLoggerRow->forUser(auth()->user());
            }
            $loggerRow = $queryLoggerRow->first();

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
            if ($tanggalParsed->isSameDay($now)) {
                $expectedRecords = max(1, $tanggalAwal->diffInMinutes($now)); // Minutes passed since start of day
            } else {
                $expectedRecords = 1440; // 24 hours * 60 minutes per day
            }
            $actualRecords = count($data);
            $completeness = round(($actualRecords / $expectedRecords) * 100, 2);
            if ($completeness > 100) {
                $completeness = 100;
            }

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

        // ── Bangun waktu: bisa dari 'waktu' (format lama) atau 'hari'+'jam'/'tanggal'+'jam' (format baru) ──
        $waktu = $request->input('waktu');
        if (!$waktu) {
            if ($request->has('hari') && $request->has('jam')) {
                $waktu = $request->input('hari') . ' ' . $request->input('jam');
            } elseif ($request->has('tanggal') && $request->has('jam')) {
                $waktu = $request->input('tanggal') . ' ' . $request->input('jam');
            }
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

        // --- CEK DAN KIRIM NOTIFIKASI FCM ---
        try {
            $this->checkAndSendNotification($logger, $merged);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('FCM Notification Check Error: ' . $e->getMessage());
        }
        // ------------------------------------

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

        $mqttOk = null;
        $mqttErr = null;
        $mqttDebug = [];

        // ── MQTT publish via php-mqtt/laravel-client ──────────────────────────
        // Konfigurasi TLS, sertifikat CA, auth, dan timeout ada di config/mqtt-client.php
        // dan .env. Library ini menangani TLS handshake + timeout dengan benar.
        try {
            // Log config yang dipakai untuk debugging
            $mqttDebug = [
                'host'            => config('mqtt-client.connections.default.host'),
                'port'            => config('mqtt-client.connections.default.port'),
                'protocol'        => config('mqtt-client.connections.default.protocol'),
                'tls_enabled'     => config('mqtt-client.connections.default.connection_settings.tls.enabled'),
                'ca_file'         => config('mqtt-client.connections.default.connection_settings.tls.ca_file'),
                'ca_file_exists'  => (function() { try { return @file_exists((string) config('mqtt-client.connections.default.connection_settings.tls.ca_file', '')); } catch (\Throwable $e) { return 'check_failed: ' . $e->getMessage(); } })(),
                'verify_peer'     => config('mqtt-client.connections.default.connection_settings.tls.verify_peer'),
                'verify_peer_name'=> config('mqtt-client.connections.default.connection_settings.tls.verify_peer_name'),
                'auth_username'   => config('mqtt-client.connections.default.connection_settings.auth.username') ? '***set***' : '***empty***',
                'connect_timeout' => config('mqtt-client.connections.default.connection_settings.connect_timeout'),
                'socket_timeout'  => config('mqtt-client.connections.default.connection_settings.socket_timeout'),
            ];

            \PhpMqtt\Client\Facades\MQTT::publish(
                (string) $id,                // topic = id_logger
                json_encode($payload),       // payload JSON
                0,                           // QoS 0 = fire and forget
            );
            $mqttOk = true;
        } catch (\Throwable $e) {
            $mqttOk = false;
            $mqttErr = $e->getMessage();
            $mqttDebug['exception_class'] = get_class($e);
            $mqttDebug['exception_file']  = $e->getFile() . ':' . $e->getLine();
            $mqttDebug['trace_short']     = array_slice(
                array_map(fn($f) => ($f['file'] ?? '?') . ':' . ($f['line'] ?? '?') . ' ' . ($f['class'] ?? '') . ($f['type'] ?? '') . ($f['function'] ?? ''),
                    $e->getTrace()),
                0, 5  // top 5 frames saja
            );
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
                'debug' => $mqttDebug,
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

    private function checkAndSendNotification($logger, array $merged)
    {
        $kategori = strtolower($logger->kategori ?? '');
        $id_logger = $logger->id_logger;
        $nama_logger = $logger->nama_logger ?? $id_logger;
        
        $stateKritis = null;
        $bodyMsg = '';

        // Deteksi AWLR Siaga (id_katlogger = 1)
        if (str_contains($kategori, 'awlr') || (isset($logger->id_katlogger) && $logger->id_katlogger == 1)) {
            // Ambil parameter Tinggi Muka Air
            $pTma = \Illuminate\Support\Facades\DB::table('parameter_sensor')
                ->where('logger_id', $id_logger)
                ->where(function($q) {
                    $q->where('nama_parameter', 'like', '%tma%')
                      ->orWhere('nama_parameter', 'like', '%muka air%')
                      ->orWhere('parameter_utama', 'like', '%tma%');
                })->first();

            if ($pTma && isset($merged[$pTma->kolom_sensor])) {
                $tma = (float) $merged[$pTma->kolom_sensor];
                
                // Cek dengan tabel tingkat_siaga_awlr
                $siagaLevels = \Illuminate\Support\Facades\DB::table('tingkat_siaga_awlr')->get();
                foreach ($siagaLevels as $siaga) {
                    if ($siaga->min_value === null && $siaga->max_value === null) continue;
                    $matchMin = $siaga->min_value === null || $tma >= $siaga->min_value;
                    $matchMax = $siaga->max_value === null || $tma < $siaga->max_value;
                    
                    if ($matchMin && $matchMax) {
                        $s = strtolower($siaga->tingkat_siaga);
                        if (str_contains($s, 'siaga 1') || str_contains($s, 'siaga 2') || str_contains($s, 'siaga 3')) {
                            $stateKritis = $siaga->tingkat_siaga;
                            $bodyMsg = "Tinggi Muka Air mencapai {$tma}m ({$siaga->tingkat_siaga}).";
                        }
                        break;
                    }
                }
            }
        } 
        // Deteksi ARR Intensitas Hujan (id_katlogger = 2)
        elseif (str_contains($kategori, 'arr') || (isset($logger->id_katlogger) && $logger->id_katlogger == 2)) {
            $pRain = \Illuminate\Support\Facades\DB::table('parameter_sensor')
                ->where('logger_id', $id_logger)
                ->where(function($q) {
                    $q->where('nama_parameter', 'like', '%hujan%')
                      ->orWhere('parameter_utama', 'like', '%hujan%');
                })->first();

            if ($pRain && isset($merged[$pRain->kolom_sensor])) {
                $rain = (float) $merged[$pRain->kolom_sensor];
                
                $thresholds = \Illuminate\Support\Facades\DB::table('klasifikasi_threshold')->orderBy('sort_order')->get();
                foreach ($thresholds as $t) {
                    if ($t->min_value === null && $t->max_value === null) continue;
                    $matchMin = $t->min_value === null || $rain >= $t->min_value;
                    $matchMax = $t->max_value === null || $rain < $t->max_value;
                    
                    if ($matchMin && $matchMax) {
                        $s = strtolower($t->state_key);
                        if (str_contains($s, 'lebat') || str_contains($s, 'sangat_lebat') || str_contains($s, 'sedang') || str_contains($s, 'sangat lebat')) {
                            $stateKritis = str_replace('_', ' ', ucwords($t->state_key));
                            $bodyMsg = "Intensitas hujan mencapai {$rain}mm/jam ({$stateKritis}).";
                        }
                        break;
                    }
                }
            }
        }

        // Jika ada status kritis, kita cek logger_notification_states
        if ($stateKritis) {
            $lastState = \Illuminate\Support\Facades\DB::table('logger_notification_states')
                ->where('id_logger', $id_logger)->first();

            $shouldNotify = false;
            
            if (!$lastState) {
                $shouldNotify = true;
            } else {
                // Notifikasi dikirim jika statusnya BERUBAH atau sudah melewati jeda_notif logger
                $lastTime = \Carbon\Carbon::parse($lastState->last_notified_at);
                $jedaNotifMenit = max(1, (int) ($logger->jeda_notif ?? 60));
                if ($lastState->last_state !== $stateKritis || now()->diffInMinutes($lastTime) >= $jedaNotifMenit) {
                    $shouldNotify = true;
                }
            }

            if ($shouldNotify) {
                \Illuminate\Support\Facades\Log::info('FCM notification triggered', [
                    'id_logger' => $id_logger,
                    'state' => $stateKritis,
                    'jeda_notif' => $logger->jeda_notif ?? null,
                ]);

                \Illuminate\Support\Facades\DB::table('logger_notification_states')->updateOrInsert(
                    ['id_logger' => $id_logger],
                    [
                        'last_state' => $stateKritis,
                        'last_notified_at' => now(),
                        'updated_at' => now()
                    ]
                );

                $namaKategori = '';
                if (isset($logger->id_katlogger)) {
                    if ($logger->id_katlogger == 1) $namaKategori = 'AWLR';
                    elseif ($logger->id_katlogger == 2) $namaKategori = 'ARR';
                    elseif ($logger->id_katlogger == 3) $namaKategori = 'AFMR';
                    elseif ($logger->id_katlogger == 4) $namaKategori = 'AWR';
                    elseif ($logger->id_katlogger == 5) $namaKategori = 'AWQR';
                }
                if (empty($namaKategori)) {
                    $namaKategori = strtoupper($kategori ?? 'LOGGER');
                }

                $fcm = new \App\Services\FcmService();
                
                // Membuat format judul yang lebih spesifik
                $titlePrefix = "Peringatan ";
                if ($namaKategori === 'AWLR') {
                    $titlePrefix .= ucwords(strtolower($stateKritis)); // Contoh: "Peringatan Siaga 1"
                } elseif ($namaKategori === 'ARR') {
                    $titlePrefix .= "Hujan " . ucwords(strtolower($stateKritis)); // Contoh: "Peringatan Hujan Sangat Lebat"
                } else {
                    $titlePrefix .= ucwords(strtolower($stateKritis));
                }
                $title = $titlePrefix . " - " . $nama_logger;

                $fcm->broadcastNotification($title, $bodyMsg, [
                    'id_logger' => $id_logger,
                    'kategori' => $namaKategori,
                    'state' => $stateKritis,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]);
            } else {
                \Illuminate\Support\Facades\Log::info('FCM notification skipped by throttle', [
                    'id_logger' => $id_logger,
                    'state' => $stateKritis,
                    'jeda_notif' => $logger->jeda_notif ?? null,
                    'last_state' => $lastState->last_state ?? null,
                    'last_notified_at' => $lastState->last_notified_at ?? null,
                ]);
            }
        } else {
            // Jika normal, hapus state atau ubah ke normal agar jika siaga lagi bisa dinotif ulang
            \Illuminate\Support\Facades\DB::table('logger_notification_states')
                ->where('id_logger', $id_logger)
                ->update(['last_state' => 'Normal']);
        }
    }
}
