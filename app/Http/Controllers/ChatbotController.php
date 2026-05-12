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
        $context = $this->buildMonitoringContext($request, $message);
        $fallback = $this->fallbackReply($message, $context);

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
            ->limit(30)
            ->get();

        return [
            'user_name' => $user?->nama ?? $user?->username ?? 'User',
            'logger_total_visible' => $loggers->count(),
            'categories' => $loggers
                ->groupBy(fn ($logger) => $logger->kategori?->nama_kategori ?? $logger->jenis_alat ?? 'Lainnya')
                ->map->count()
                ->sortKeys()
                ->all(),
            'maintenance_loggers' => $loggers
                ->filter(fn ($logger) => ($logger->status_perbaikan ?? 'normal') !== 'normal')
                ->map(fn ($logger) => $logger->nama_logger.' ('.$logger->id_logger.')')
                ->values()
                ->take(8)
                ->all(),
            'sample_loggers' => $loggers
                ->take(10)
                ->map(fn ($logger) => [
                    'id_logger' => $logger->id_logger,
                    'nama' => $logger->nama_logger,
                    'kategori' => $logger->kategori?->nama_kategori ?? $logger->jenis_alat ?? '-',
                    'lokasi' => $logger->lokasi?->nama_lokasi ?? '-',
                    'status_perbaikan' => $logger->status_perbaikan ?? 'normal',
                    'node_skema' => $logger->node_skema_id,
                ])
                ->values()
                ->all(),
            'matched_logger' => $this->resolveLoggerMention($request, $message),
        ];
    }

    private function systemPrompt(array $context): string
    {
        return "Anda adalah STESY Assistant untuk aplikasi Smart Telemetry System. "
            ."Jawab singkat, praktis, dan dalam Bahasa Indonesia. "
            ."Gunakan hanya konteks sistem yang diberikan; jika data tidak ada, katakan perlu membuka halaman terkait. "
            ."Jangan mengarang angka sensor real-time. "
            ."Konteks sistem: ".json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function fallbackReply(string $message, array $context, bool $configured = false): string
    {
        $query = Str::lower($message);

        if (!empty($context['matched_logger'])) {
            return $this->formatLoggerSummary($context['matched_logger']);
        }

        if (Str::contains($query, ['real', 'monitoring', 'data'])) {
            return 'Untuk data real-time, buka menu Realtime Monitoring. Pilih pos atau kategori logger, lalu cek nilai sensor terakhir, waktu update, dan status koneksinya.';
        }

        if (Str::contains($query, ['offline', 'putus', 'status'])) {
            return 'Status offline biasanya berarti logger belum mengirim data terbaru atau koneksi perangkat terputus. Cek waktu data terakhir, baterai, dan jaringan di halaman detail perangkat.';
        }

        if (Str::contains($query, ['peta', 'lokasi', 'pos'])) {
            $count = $context['logger_total_visible'] ?? 0;
            return "Untuk melihat lokasi pos, buka menu Peta. Akun ini memiliki akses ke {$count} logger yang bisa ditinjau sesuai izin pengguna.";
        }

        if (Str::contains($query, ['siaga', 'banjir', 'hujan'])) {
            return 'Level siaga mengikuti ambang batas yang dikonfigurasi pada data AWLR atau ARR. Cek halaman detail pos untuk melihat klasifikasi dan parameter pendukung.';
        }

        if ($configured) {
            return 'Konfigurasi AI sudah terbaca, tetapi koneksi ke provider AI gagal sehingga saya memakai jawaban panduan lokal. Cek log server untuk detail koneksi, model, atau billing API.';
        }

        return 'Saya bisa bantu panduan STESY seperti menu real-time, status logger, peta lokasi, tingkat siaga, dan data perangkat. Untuk jawaban AI yang membaca konteks lebih dalam, isi konfigurasi AI_CHATBOT_API_KEY dan AI_CHATBOT_MODEL di server.';
    }

    private function resolveLoggerMention(Request $request, string $message): ?array
    {
        $normalized = trim(Str::of($message)
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]+/i', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->toString());

        $stopWords = [
            'bisa', 'tolong', 'tampilkan', 'lihat', 'lihatkan', 'data', 'pos',
            'logger', 'untuk', 'yang', 'di', 'ke', 'dari', 'dong', 'ya', 'nya',
        ];
        $tokens = collect(explode(' ', $normalized))
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => Str::length($token) >= 3 && !in_array($token, $stopWords, true))
            ->unique()
            ->values();

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
            ->limit(8)
            ->get();

        $logger = $loggerCandidates
            ->sortByDesc(function ($candidate) use ($tokens) {
                $name = Str::lower($candidate->nama_logger.' '.$candidate->id_logger);
                return $tokens->sum(fn ($token) => Str::contains($name, $token) ? 1 : 0);
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
