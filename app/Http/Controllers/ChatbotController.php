<?php

namespace App\Http\Controllers;

use App\Models\t_Logger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:700'],
            'messages' => ['sometimes', 'array', 'max:12'],
            'messages.*.role' => ['required_with:messages', 'in:user,assistant'],
            'messages.*.text' => ['required_with:messages', 'string', 'max:700'],
        ]);

        $message = trim($validated['message']);

        if ($this->isGreetingMessage($message)) {
            $user = $request->user();
            $userName = $user?->nama ?? $user?->username ?? 'User';

            return response()->json([
                'reply' => "Halo, {$userName}! Saya STESY Assistant. Ada yang bisa saya bantu?",
                'source' => 'local',
                'configured' => (bool) config('services.ai_chatbot.key'),
            ]);
        }

        $context = $this->buildMonitoringContext($request, $message);
        $fallback = $this->fallbackReply($message, $context);

        if ($categoryReply = $this->categoryReply($message, $context)) {
            return response()->json([
                'reply' => $categoryReply,
                'source' => 'local',
                'configured' => (bool) config('services.ai_chatbot.key'),
            ]);
        }

        if (!empty($context['missing_logger_reference'])) {
            return response()->json([
                'reply' => 'Logger atau pos yang diminta tidak ditemukan dalam akses akun ini. Pastikan ID/nama logger benar, atau minta admin memberi akses ke logger tersebut.',
                'source' => 'local',
                'configured' => (bool) config('services.ai_chatbot.key'),
            ]);
        }

        $key = config('services.ai_chatbot.key');
        $model = config('services.ai_chatbot.model');
        $endpoint = config('services.ai_chatbot.endpoint');
        $verifySsl = filter_var(config('services.ai_chatbot.verify_ssl', true), FILTER_VALIDATE_BOOL);

        if (!$key || !$model || !$endpoint) {
            return response()->json([
                'reply' => $this->fallbackReply($message, $context, false),
                'source' => 'local',
                'configured' => false,
            ]);
        }

        try {
            $history = collect($validated['messages'] ?? [])
                ->take(-8)
                ->map(fn ($item) => [
                    'role' => $item['role'],
                    'content' => trim($item['text']),
                ])
                ->filter(fn ($item) => $item['content'] !== '')
                ->values()
                ->all();

            $pendingRequest = Http::timeout(25)
                ->withToken($key)
                ->acceptJson();

            if (!$verifySsl) {
                $pendingRequest = $pendingRequest->withoutVerifying();
            }

            $response = $pendingRequest->post($endpoint, [
                    'model' => $model,
                    'max_completion_tokens' => 420,
                    'messages' => [
                        [
                            'role' => 'developer',
                            'content' => $this->systemPrompt($context),
                        ],
                        ...$history,
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                report(new \RuntimeException('Chatbot provider error: '.$response->body()));

                return response()->json([
                    'reply' => $this->fallbackReply($message, $context, true),
                    'source' => 'local',
                    'configured' => true,
                ]);
            }

            $reply = data_get($response->json(), 'choices.0.message.content');

            if (!is_string($reply) || trim($reply) === '') {
                return response()->json([
                    'reply' => $this->fallbackReply($message, $context, true),
                    'source' => 'local',
                    'configured' => true,
                ]);
            }

            return response()->json([
                'reply' => Str::limit(trim($reply), 1600, '...'),
                'source' => 'ai',
                'configured' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => $this->fallbackReply($message, $context, true),
                'source' => 'local',
                'configured' => true,
            ]);
        }
    }

    private function buildMonitoringContext(Request $request, string $message = ''): array
    {
        $user = $request->user();
        $loggers = t_Logger::query()
            ->forUser($user)
            ->with([
                'kategori:id_katlogger,nama_kategori',
                'lokasi:idlokasi,nama_lokasi',
            ])
            ->select([
                'id',
                'id_logger',
                'nama_logger',
                'tabel_main',
                'id_katlogger',
                'idlokasi',
                'status_perbaikan',
                'jenis_alat',
                'node_skema_id',
                'sensor_count',
            ])
            ->orderBy('nama_logger')
            ->get();

        // Hitung status online/offline — ambil waktu terbaru dari tabel_main per logger
        // (sama dengan resolveLatestSensorSnapshot di formatLoggerSummary)
        // Group by tabel_main untuk batch query per tabel
        $loggerIds = $loggers->pluck('id_logger')->all();

        $snap16 = DB::table('temp_s16_latest')
            ->whereIn('id_logger', $loggerIds)
            ->get(['id_logger', 'waktu'])
            ->keyBy('id_logger');

        $snap19 = DB::table('temp_s19_latest')
            ->whereIn('id_logger', $loggerIds)
            ->get(['id_logger', 'waktu'])
            ->keyBy('id_logger');

        // Batch query waktu terakhir dari tabel_main per grup tabel
        $mainTableLatest = collect();
        $loggersByTable  = $loggers->filter(fn ($l) => !empty($l->tabel_main))
                                   ->groupBy('tabel_main');

        foreach ($loggersByTable as $tableName => $tableLoggers) {
            $ids = $tableLoggers->pluck('id_logger')->all();
            // Subquery: MAX(id) per logger → lalu ambil waktu dari row tersebut
            $rows = DB::table($tableName)
                ->whereIn('id_logger', $ids)
                ->select('id_logger', DB::raw('MAX(id) as max_id'))
                ->groupBy('id_logger')
                ->get()
                ->keyBy('id_logger');

            foreach ($rows as $lid => $r) {
                $row = DB::table($tableName)->where('id', $r->max_id)->first();
                if ($row) {
                    // Gunakan kolom waktu saja — temp_s19/temp_s16 adalah nilai sensor (float), bukan timestamp
                    $wt = $row->waktu ?? null;
                    if ($wt) {
                        $mainTableLatest[$lid] = $wt;
                    }
                }
            }
        }

        $onlineLoggers  = [];
        $offlineLoggers = [];
        $allLoggers     = [];
        foreach ($loggers as $logger) {
            $lid = $logger->id_logger;

            $w16   = $snap16[$lid]->waktu ?? null;
            $w19   = $snap19[$lid]->waktu ?? null;
            $wMain = $mainTableLatest[$lid] ?? null;

            // Ambil waktu paling baru dari semua sumber
            $lastTime = collect([$w16, $w19, $wMain])->filter()->sortDesc()->first();

            $diff  = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
            $entry = $logger->nama_logger . ' (' . $lid . ')';
            $status = $diff !== null && $diff < 60 ? 'online' : 'offline';

            $allLoggers[] = [
                'id_logger' => $lid,
                'nama' => $logger->nama_logger,
                'kategori' => $logger->kategori?->nama_kategori ?? $logger->jenis_alat ?? '-',
                'lokasi' => $logger->lokasi?->nama_lokasi ?? '-',
                'status' => $status,
                'last_time' => $lastTime,
            ];

            if ($status === 'online') {
                $onlineLoggers[]  = $entry;
            } else {
                $offlineLoggers[] = $entry;
            }
        }

        $matchedLogger = $this->resolveLoggerMention($request, $message);

        return [
            'user_name'            => $user?->nama ?? $user?->username ?? 'User',
            'logger_total_visible' => $loggers->count(),
            'logger_online_count'  => count($onlineLoggers),
            'logger_offline_count' => count($offlineLoggers),
            'online_loggers'       => array_slice($onlineLoggers,  0, 20),
            'offline_loggers'      => array_slice($offlineLoggers, 0, 20),
            'all_loggers'          => $allLoggers,
            'category_definitions'  => $this->categoryDefinitions(),
            'categories'           => $loggers
                ->groupBy(fn ($logger) => $logger->kategori?->nama_kategori ?? $logger->jenis_alat ?? 'Lainnya')
                ->map->count()
                ->sortKeys()
                ->all(),
            'maintenance_loggers'  => $loggers
                ->filter(fn ($logger) => ($logger->status_perbaikan ?? 'normal') !== 'normal')
                ->map(fn ($logger) => $logger->nama_logger . ' (' . $logger->id_logger . ')')
                ->values()
                ->take(8)
                ->all(),
            'sample_loggers'       => $loggers
                ->take(10)
                ->map(fn ($logger) => [
                    'id_logger'        => $logger->id_logger,
                    'nama'             => $logger->nama_logger,
                    'kategori'         => $logger->kategori?->nama_kategori ?? $logger->jenis_alat ?? '-',
                    'lokasi'           => $logger->lokasi?->nama_lokasi ?? '-',
                    'status_perbaikan' => $logger->status_perbaikan ?? 'normal',
                    'node_skema'       => $logger->node_skema_id,
                ])
                ->values()
                ->all(),
            'matched_logger'       => $matchedLogger,
            'missing_logger_reference' => !$matchedLogger && $this->hasSpecificLoggerReference($message),
        ];
    }


    private function systemPrompt(array $context): string
    {
        return "Anda adalah STESY Assistant untuk aplikasi Smart Telemetry System. "
            ."Jawab singkat, praktis, dan dalam Bahasa Indonesia. "
            ."Gunakan konteks sistem yang diberikan untuk data logger dan definisi kategori; jika data tidak ada, katakan perlu membuka halaman terkait. "
            ."Jangan mengarang angka sensor real-time. "
            ."Konteks sistem: ".json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function categoryDefinitions(): array
    {
        return [
            'AWLR' => [
                'name' => 'Automatic Water Level Recorder',
                'description' => 'logger untuk memantau tinggi muka air secara otomatis, misalnya sungai, saluran, bendung, atau sumur.',
                'common_parameters' => ['tinggi muka air', 'elevasi muka air', 'debit', 'baterai', 'humidity logger', 'temperature logger'],
            ],
            'ARR' => [
                'name' => 'Automatic Rain Recorder',
                'description' => 'logger untuk mencatat curah hujan otomatis dan membantu melihat intensitas hujan per periode.',
                'common_parameters' => ['curah hujan', 'intensitas hujan', 'baterai', 'humidity logger', 'temperature logger'],
            ],
            'AFMR' => [
                'name' => 'Automatic Flow Measurement Recorder',
                'description' => 'logger untuk memantau aliran air, seperti debit, kecepatan aliran, dan luas penampang.',
                'common_parameters' => ['debit', 'flow velocity', 'luas penampang air', 'elevasi muka air', 'jarak sensor'],
            ],
            'AWR' => [
                'name' => 'Automatic Weather Recorder',
                'description' => 'logger untuk memantau kondisi cuaca otomatis.',
                'common_parameters' => ['suhu udara', 'kelembapan', 'tekanan udara', 'kecepatan angin', 'arah angin'],
            ],
            'AWQR' => [
                'name' => 'Automatic Water Quality Recorder',
                'description' => 'logger untuk memantau kualitas air secara otomatis.',
                'common_parameters' => ['pH air', 'suhu air', 'turbidity', 'conductivity', 'salinity', 'TDS', 'ORP'],
            ],
        ];
    }

    private function categoryReply(string $message, array $context): ?string
    {
        $query = Str::lower($message);
        $definitions = $context['category_definitions'] ?? $this->categoryDefinitions();
        $mentioned = collect(array_keys($definitions))
            ->filter(fn ($code) => preg_match('/\b'.preg_quote(Str::lower($code), '/').'\b/i', $message))
            ->values();

        $isCategoryQuestion = Str::contains($query, [
            'apa itu',
            'jelaskan',
            'pengertian',
            'maksud',
            'fungsi',
            'kategori',
        ]);

        if (!$isCategoryQuestion || $mentioned->isEmpty()) {
            return null;
        }

        return $mentioned
            ->map(function ($code) use ($definitions) {
                $item = $definitions[$code];
                $params = implode(', ', $item['common_parameters']);

                return "{$code} ({$item['name']}) adalah {$item['description']} Parameter yang umum dipantau: {$params}.";
            })
            ->implode("\n\n");
    }

    private function isGreetingMessage(string $message): bool
    {
        $query = Str::lower(trim($message));

        return in_array($query, ['halo', 'helo', 'hello', 'hi', 'hai'], true)
            || Str::contains($query, [
                'selamat pagi',
                'selamat siang',
                'selamat sore',
                'selamat malam',
                'assalam',
            ]);
    }

    private function fallbackReply(string $message, array $context, bool $configured = false): string
    {
        $query = Str::lower($message);
        $userName = $context['user_name'] ?? 'Anda';

        // Sapaan / greeting — selalu jawab ramah tanpa ekspos error AI
        if (Str::contains($query, ['halo', 'helo', 'hello', 'hi ', 'hai', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'assalam', 'permisi', 'apa kabar', 'terima kasih', 'makasih', 'thanks'])
            || trim($query) === 'hi' || trim($query) === 'halo' || trim($query) === 'hai') {
            $total  = $context['logger_total_visible'] ?? 0;
            $online = $context['logger_online_count']  ?? 0;
            return "Halo, {$userName}! Saya STESY Assistant. Ada yang bisa saya bantu?";
        }

        if (!empty($context['matched_logger'])) {
            return $this->formatLoggerSummary($context['matched_logger']);
        }

        // Pertanyaan daftar semua logger
        if (Str::contains($query, ['semua logger', 'daftar logger', 'list logger', 'tampilkan logger', 'tampilkan semua'])
            && !Str::contains($query, ['online', 'offline', 'putus', 'terhubung'])) {
            $loggers = $context['all_loggers'] ?? [];
            $total   = $context['logger_total_visible'] ?? count($loggers);
            $online  = $context['logger_online_count']  ?? 0;
            $offline = $context['logger_offline_count'] ?? 0;

            if (empty($loggers)) {
                return 'Tidak ada logger yang dapat diakses oleh akun ini.';
            }

            $listStr = collect($loggers)
                ->map(function ($logger, $index) {
                    $number = $index + 1;
                    $status = strtoupper((string) ($logger['status'] ?? '-'));
                    $time = $logger['last_time'] ?? '-';

                    return "{$number}. {$logger['nama']} ({$logger['id_logger']}) - {$status} - update: {$time}";
                })
                ->implode("\n");

            return "Daftar semua logger yang dapat diakses ({$total} unit):\n"
                ."- Online: {$online}\n"
                ."- Offline: {$offline}\n\n"
                .$listStr;
        }

        // Pertanyaan tentang logger yang online / koneksi terhubung
        if (Str::contains($query, ['online', 'terhubung', 'aktif', 'nyala', 'hidup'])
            && !Str::contains($query, ['offline', 'putus', 'mati'])) {
            $count   = $context['logger_online_count'] ?? 0;
            $list    = $context['online_loggers']      ?? [];
            $total   = $context['logger_total_visible'] ?? 0;
            if ($count === 0) {
                return "Saat ini tidak ada logger yang terdeteksi online (dari {$total} logger yang dapat diakses).";
            }
            $listStr = implode("\n  - ", $list);
            $extra   = $count > count($list) ? "\n  - ..." : '';
            return "Logger yang saat ini koneksi terhubung (online) — {$count} dari {$total} logger:\n  - {$listStr}{$extra}";
        }

        // Pertanyaan tentang logger yang offline / terputus
        if (Str::contains($query, ['offline', 'putus', 'mati', 'tidak terhubung', 'tidak aktif'])) {
            $count = $context['logger_offline_count'] ?? 0;
            $list  = $context['offline_loggers']      ?? [];
            $total = $context['logger_total_visible']  ?? 0;
            if ($count === 0) {
                return "Semua logger ({$total}) saat ini terdeteksi online. Tidak ada yang offline.";
            }
            $listStr = implode("\n  - ", $list);
            $extra   = $count > count($list) ? "\n  - ..." : '';
            return "Logger yang koneksi terputus (offline) — {$count} dari {$total} logger:\n  - {$listStr}{$extra}\n\nCek waktu data terakhir, baterai, dan jaringan di halaman detail perangkat.";
        }

        // Pertanyaan jumlah / status semua logger
        if (Str::contains($query, ['berapa', 'jumlah', 'total', 'semua', 'daftar', 'list', 'status'])) {
            $total   = $context['logger_total_visible'] ?? 0;
            $online  = $context['logger_online_count']  ?? 0;
            $offline = $context['logger_offline_count'] ?? 0;
            return "Total logger yang dapat diakses: {$total} unit.\n- Online (koneksi terhubung): {$online}\n- Offline (koneksi terputus): {$offline}\n\nBuka menu Peta atau Realtime Monitoring untuk detail masing-masing pos.";
        }

        if (Str::contains($query, ['real', 'monitoring', 'data'])) {
            return 'Untuk data real-time, buka menu Realtime Monitoring. Pilih pos atau kategori logger, lalu cek nilai sensor terakhir, waktu update, dan status koneksinya.';
        }

        if (Str::contains($query, ['peta', 'lokasi', 'pos'])) {
            $count = $context['logger_total_visible'] ?? 0;
            return "Untuk melihat lokasi pos, buka menu Peta. Akun ini memiliki akses ke {$count} logger yang bisa ditinjau sesuai izin pengguna.";
        }

        if (Str::contains($query, ['siaga', 'banjir', 'hujan'])) {
            return 'Level siaga mengikuti ambang batas yang dikonfigurasi pada data AWLR atau ARR. Cek halaman detail pos untuk melihat klasifikasi dan parameter pendukung.';
        }

        if ($configured) {
            // Jangan tampilkan pesan error teknis — tetap berikan balasan yang helpful
            return 'Saya STESY Assistant. Saya bisa membantu info status logger, monitoring real-time, peta lokasi, tingkat siaga, dan panduan menu. Silakan tanyakan lebih spesifik!';
        }

        return 'Saya STESY Assistant. Saya bisa bantu panduan seperti status logger, menu real-time, peta lokasi, dan tingkat siaga. Silakan tanyakan lebih spesifik!';
    }

    private function hasSpecificLoggerReference(string $message): bool
    {
        $query = Str::lower($message);

        if (!Str::contains($query, ['logger', 'pos']) && !preg_match('/\b([a-z]+[-_]?\d{2,}|\d{4,})\b/i', $message)) {
            return false;
        }

        return $this->loggerMentionTokens($message)->isNotEmpty();
    }

    private function loggerMentionTokens(string $message)
    {
        $normalized = trim(Str::of($message)
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]+/i', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->toString());

        $stopWords = [
            'bisa', 'tolong', 'tampilkan', 'lihat', 'lihatkan', 'data', 'pos',
            'logger', 'untuk', 'yang', 'di', 'ke', 'dari', 'dong', 'ya', 'nya',
            'apa', 'arti', 'maksud', 'status', 'koneksi', 'online', 'offline',
            'terhubung', 'putus', 'aktif', 'tidak', 'semua', 'daftar', 'list',
            'jumlah', 'total', 'buka', 'halaman', 'detail',
        ];

        return collect(explode(' ', $normalized))
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => Str::length($token) >= 3 && !in_array($token, $stopWords, true))
            ->unique()
            ->values();
    }

    private function resolveLoggerMention(Request $request, string $message): ?array
    {
        $tokens = $this->loggerMentionTokens($message);

        if ($tokens->isEmpty()) {
            return null;
        }

        $loggerCandidates = t_Logger::query()
            ->forUser($request->user())
            ->with([
                'kategori:id_katlogger,nama_kategori',
                'lokasi:idlokasi,nama_lokasi,alamat,latitude,longitude',
                'params:id_param,logger_id,nama_parameter,kolom_sensor,satuan',
                'temp16',
                'temp19',
            ])
            ->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $builder
                        ->orWhereRaw('LOWER(nama_logger) LIKE ?', ['%'.$token.'%'])
                        ->orWhereRaw('LOWER(id_logger) LIKE ?', ['%'.$token.'%']);
                }
            })
            ->orderBy('nama_logger')
            ->limit(30)   // Naikkan limit agar logger yang jauh secara alfabet tetap masuk
            ->get();

        $logger = $loggerCandidates
            ->sortByDesc(function ($candidate) use ($tokens) {
                $name = Str::lower($candidate->nama_logger.' '.$candidate->id_logger);
                // Bobot berdasarkan panjang token: token lebih panjang = lebih spesifik = bobot lebih tinggi
                return $tokens->sum(fn ($token) => Str::contains($name, $token) ? Str::length($token) : 0);
            })
            ->first();

        if (!$logger) {
            return null;
        }

        $latest = $this->resolveLatestSensorSnapshot($logger);
        $lastTime = collect([
            optional($logger->temp16)->waktu,
            optional($logger->temp19)->waktu,
            $latest['waktu'] ?? null,
        ])->filter()->sortDesc()->first();
        $diffMinutes = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;

        return [
            'id_logger' => $logger->id_logger,
            'nama_logger' => $logger->nama_logger,
            'kategori' => $logger->kategori?->nama_kategori ?? $logger->jenis_alat ?? '-',
            'lokasi' => $logger->lokasi?->nama_lokasi ?? '-',
            'alamat' => $logger->lokasi?->alamat,
            'status' => $diffMinutes !== null && $diffMinutes < 60 ? 'online' : 'offline',
            'last_time' => $lastTime,
            'selisih_menit' => $diffMinutes,
            'status_perbaikan' => $logger->status_perbaikan ?? 'normal',
            'sensor_values' => $latest['values'] ?? [],
        ];
    }

    private function resolveLatestSensorSnapshot(t_Logger $logger): array
    {
        $table = trim((string) ($logger->tabel_main ?? ''));
        $sensorCount = (int) ($logger->sensor_count ?? 0);
        $candidateTables = array_values(array_unique(array_filter([
            $this->isSupportedSensorTable($table) ? $table : null,
            $sensorCount >= 19 ? 't_s19_01' : 't_s16_01',
            $sensorCount >= 19 ? 't_s16_01' : 't_s19_01',
        ])));

        $row = null;
        $tableUsed = null;
        foreach ($candidateTables as $candidate) {
            $row = DB::table($candidate)
                ->where('id_logger', $logger->id_logger)
                ->orderByDesc('waktu')
                ->first();

            if ($row) {
                $tableUsed = $candidate;
                break;
            }
        }

        if (!$row) {
            return ['values' => []];
        }

        $params = $logger->params->keyBy('kolom_sensor');
        $values = [];
        foreach ($params as $column => $param) {
            if (!property_exists($row, $column)) {
                continue;
            }

            $value = $row->{$column};
            if ($value === null || $value === '') {
                continue;
            }

            $values[] = [
                'nama' => $param->nama_parameter,
                'nilai' => is_numeric($value) ? round((float) $value, 3) : $value,
                'satuan' => $param->satuan,
            ];
        }

        return [
            'table' => $tableUsed,
            'waktu' => $row->waktu ?? null,
            'values' => array_slice($values, 0, 6),
        ];
    }

    private function isSupportedSensorTable(string $table): bool
    {
        return (bool) preg_match('/^t_s(16|19)_\d{2,}$/', $table) && Schema::hasTable($table);
    }

    private function formatLoggerSummary(array $logger): string
    {
        $lines = [
            "Data pos {$logger['nama_logger']} ({$logger['id_logger']}):",
            "- Kategori: {$logger['kategori']}",
            "- Lokasi: {$logger['lokasi']}",
            "- Status: {$logger['status']}",
            "- Update terakhir: ".($logger['last_time'] ?? '-'),
            "- Status perbaikan: {$logger['status_perbaikan']}",
        ];

        if (!empty($logger['sensor_values'])) {
            $sensorText = collect($logger['sensor_values'])
                ->map(fn ($sensor) => trim($sensor['nama'].': '.$sensor['nilai'].' '.$sensor['satuan']))
                ->implode(', ');
            $lines[] = "- Sensor terakhir: {$sensorText}";
        } else {
            $lines[] = '- Sensor terakhir: belum ada pembacaan yang bisa ditampilkan.';
        }

        return implode("\n", $lines);
    }
}
