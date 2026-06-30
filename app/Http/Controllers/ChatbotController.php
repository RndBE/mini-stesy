<?php

namespace App\Http\Controllers;

use App\Models\t_Logger;
use App\Support\SensorFamily;
use App\Services\ChatbotPersona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function __construct(private \App\Services\Chatbot\MonitoringData $data) {}

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:700'],
            'messages' => ['sometimes', 'array', 'max:12'],
            'messages.*.role' => ['required_with:messages', 'in:user,assistant'],
            'messages.*.text' => ['required_with:messages', 'string', 'max:700'],
        ]);

        $message = trim($validated['message']);
        $configured = (bool) config('services.ai_chatbot.key')
            && (bool) config('services.ai_chatbot.model')
            && (bool) config('services.ai_chatbot.endpoint');

        // Jalur cepat: sapaan murni dijawab instan tanpa panggilan API.
        if ($this->isGreetingMessage($message)) {
            $user = $request->user();
            $userName = $user?->nama ?? $user?->username ?? 'Pengguna';

            return $this->reply(
                "Selamat datang, {$userName}. Saya STESY Assistant, siap membantu pemantauan logger Anda. Ada yang bisa dibantu?",
                'local',
                $configured
            );
        }

        $context = $this->buildMonitoringContext($request, $message);
        $dateRange = $this->requestedDateRangeFromMessage($message);

        // Komparasi antar pos dijawab deterministik (akurasi tabular kritikal).
        if ($this->isComparisonQuestion($message)) {
            $multi = $this->resolveLoggerMentionsMulti($request, $message);
            if (count($multi) >= 2) {
                return $this->reply(
                    $this->formatLoggerComparison($multi, $dateRange),
                    'local',
                    $configured
                );
            }
        }

        // Permintaan grafik/visualisasi satu pos → kirim data deret waktu
        // (deterministik, ter-scope per user) + penjelasan singkat.
        if ($this->isChartQuestion($message) && !empty($context['matched_logger'])) {
            $range = $dateRange ?: $this->defaultWeekRange();
            $chart = $this->buildLoggerChart(
                $context['matched_logger'],
                $range,
                $message,
                $this->requestedGranularity($message)
            );

            if ($chart) {
                return $this->reply($chart['explanation'], 'local', $configured, $chart['chart']);
            }
        }

        // Data historis/agregat satu pos pada rentang tanggal dijawab
        // deterministik — akurasi angka/record bersifat kritikal.
        if (!empty($context['matched_logger']) && $dateRange) {
            return $this->reply(
                $this->formatLoggerHistoricalData(
                    $context['matched_logger'],
                    $dateRange,
                    $this->requestedGranularity($message)
                ),
                'local',
                $configured
            );
        }

        // Jalur utama: pemahaman bahasa & intent diserahkan ke AI, dengan
        // FAKTA SISTEM yang sudah di-ground dari basis data sebagai sumber data.
        if ($configured) {
            $facts = $this->groundedFacts($context);

            if ($this->isRainQuestion($message)) {
                $facts['rain_overview'] = $this->rainOverview($request);
            }

            $aiReply = $this->aiReply($message, $validated['messages'] ?? [], $facts);

            if ($aiReply !== null) {
                return $this->reply($aiReply, 'ai', true);
            }
        }

        // Fallback deterministik (AI mati / belum dikonfigurasi) — tetap
        // formal, ringkas, dan ter-ground; tidak menampilkan galat teknis.
        return $this->reply(
            $this->composeGroundedFallback($request, $message, $context),
            'local',
            $configured
        );
    }

    private function reply(string $text, string $source, bool $configured, ?array $chart = null): JsonResponse
    {
        return response()->json([
            'reply' => Str::limit(trim($text), 1600, '...'),
            'source' => $source,
            'configured' => $configured,
            'chart' => $chart,
        ]);
    }

    /**
     * Satu round-trip ke penyedia AI memakai system prompt modular
     * (SOUL/AGENT/SKILLS) + FAKTA SISTEM. Mengembalikan null bila gagal
     * agar pemanggil bisa fallback deterministik.
     */
    private function aiReply(string $message, array $history, array $facts): ?string
    {
        $key = config('services.ai_chatbot.key');
        $model = config('services.ai_chatbot.model');
        $endpoint = config('services.ai_chatbot.endpoint');
        $verifySsl = filter_var(config('services.ai_chatbot.verify_ssl', true), FILTER_VALIDATE_BOOL);

        try {
            $turns = collect($history)
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
                'max_completion_tokens' => 600,
                'messages' => [
                    [
                        'role' => 'developer',
                        'content' => app(ChatbotPersona::class)->systemPrompt($facts),
                    ],
                    ...$turns,
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
            ]);

            if (!$response->successful()) {
                report(new \RuntimeException('Chatbot provider error: '.$response->body()));

                return null;
            }

            $reply = data_get($response->json(), 'choices.0.message.content');

            return is_string($reply) && trim($reply) !== '' ? trim($reply) : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Subset konteks yang di-ground untuk disuntik ke AI sebagai SYSTEM FACTS.
     */
    private function groundedFacts(array $context): array
    {
        return $this->data->groundedFacts($context);
    }

    /**
     * Fallback deterministik bernada formal saat AI tidak tersedia.
     * Cek intent generik (status/daftar/kategori/panduan) lebih dulu agar
     * pertanyaan umum tidak salah dianggap "logger tidak ditemukan".
     */
    private function composeGroundedFallback(Request $request, string $message, array $context): string
    {
        if ($this->isComparisonQuestion($message)) {
            $multi = $this->resolveLoggerMentionsMulti($request, $message);
            if (count($multi) >= 2) {
                return $this->formatLoggerComparison($multi, $this->requestedDateRangeFromMessage($message));
            }
        }

        if ($this->isRainQuestion($message)) {
            return $this->formatRainOverview($this->rainOverview($request));
        }

        if ($categoryReply = $this->categoryReply($message, $context)) {
            return $categoryReply;
        }

        if ($localReply = $this->localIntentReply($message, $context)) {
            return $localReply;
        }

        if (!empty($context['missing_logger_reference'])) {
            return $this->missingLoggerReply();
        }

        return $this->defaultGuideReply();
    }

    private function buildMonitoringContext(Request $request, string $message = ''): array
    {
        return $this->data->context($request->user(), $message);
    }


    private function categoryDefinitions(): array
    {
        return $this->data->categoryDefinitions();
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
            'beda',
            'bedanya',
            'perbedaan',
            'mengukur apa',
            'untuk apa',
        ]);

        if (!$isCategoryQuestion) {
            return null;
        }

        if ($mentioned->isEmpty() && $this->isAvailableCategoryQuestion($query)) {
            return $this->availableCategoryReply($context, $definitions);
        }

        if ($mentioned->isEmpty()) {
            return null;
        }

        return $mentioned
            ->map(function ($code) use ($context, $definitions) {
                return $this->categoryDefinitionReply($code, $context, $definitions);
            })
            ->implode("\n\n");
    }

    private function categoryDefinitionReply(string $code, array $context, array $definitions): string
    {
        $item = $definitions[$code];
        $params = implode(', ', $item['common_parameters']);
        $functions = collect($item['main_functions'] ?? [])
            ->map(fn ($function) => "- {$function}")
            ->implode("\n");
        $examples = $this->categoryExamples($code, $context, $definitions);

        $lines = [
            "{$code} adalah singkatan dari {$item['name']}.",
            "{$code} merupakan {$item['description']} Pada beberapa pos, data {$code} juga dapat dikaitkan dengan parameter seperti {$params}.",
        ];

        if ($functions !== '') {
            $lines[] = "Fungsi utama {$code}:";
            $lines[] = $functions;
        }

        if (!empty($examples)) {
            $exampleText = implode(', ', $examples);
            $suffix = count($examples) >= 4 ? ', dan lainnya.' : '.';
            $lines[] = "Dalam akses akun ini, contoh pos {$code} adalah {$exampleText}{$suffix}";
        } else {
            $lines[] = "Dalam akses akun ini, belum ada contoh pos {$code} yang dapat ditampilkan.";
        }

        return implode("\n", $lines);
    }

    private function categoryExamples(string $code, array $context, array $definitions): array
    {
        return collect($context['all_loggers'] ?? [])
            ->filter(function ($logger) use ($code, $definitions) {
                $category = (string) ($logger['kategori'] ?? '');

                return $this->categoryCodeFromName($category, $definitions) === $code;
            })
            ->pluck('nama')
            ->filter()
            ->unique()
            ->take(4)
            ->values()
            ->all();
    }

    private function isAvailableCategoryQuestion(string $query): bool
    {
        return Str::contains($query, [
            'kategori logger',
            'kategori apa',
            'kategori yang ada',
            'kategori tersedia',
            'kategori di logger',
            'kategori pada logger',
            'jenis logger',
            'jenis yang ada',
            'macam logger',
            'logger apa aja',
            'logger apa saja',
        ]);
    }

    private function availableCategoryReply(array $context, array $definitions): string
    {
        $categories = collect($context['categories'] ?? [])
            ->filter(fn ($count) => (int) $count > 0);

        if ($categories->isEmpty()) {
            return 'Belum ada kategori logger yang dapat diakses oleh akun ini.';
        }

        $lines = ['Kategori logger yang ada pada daftar akses akun ini:'];
        $index = 1;

        foreach ($categories as $rawName => $count) {
            $code = $this->categoryCodeFromName((string) $rawName, $definitions);
            $definition = $code ? ($definitions[$code] ?? null) : null;
            $title = $code ?: (string) $rawName;

            if ($definition) {
                $params = implode(', ', array_slice($definition['common_parameters'], 0, 4));
                $lines[] = "{$index}. {$title} - {$definition['name']}";
                $lines[] = "   - Fungsi: {$definition['description']}";
                $lines[] = "   - Parameter umum: {$params}.";
            } else {
                $lines[] = "{$index}. {$title}";
            }

            $lines[] = "   - Jumlah pada akses akun: {$count} pos.";
            $index++;
        }

        return implode("\n", $lines);
    }

    private function categoryCodeFromName(string $name, array $definitions): ?string
    {
        $normalized = Str::lower($name);

        foreach (array_keys($definitions) as $code) {
            if (preg_match('/\b'.preg_quote(Str::lower($code), '/').'\b/i', $name)) {
                return $code;
            }
        }

        return match (true) {
            Str::contains($normalized, ['water level', 'tinggi muka', 'muka air']) => 'AWLR',
            Str::contains($normalized, ['rainfall', 'rain recorder', 'curah hujan', 'hujan']) => 'ARR',
            Str::contains($normalized, ['flow measurement', 'flow meter', 'debit', 'aliran']) => 'AFMR',
            Str::contains($normalized, ['weather', 'cuaca']) => 'AWR',
            Str::contains($normalized, ['water quality', 'kualitas air']) => 'AWQR',
            default => null,
        };
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

    private function localIntentReply(string $message, array $context): ?string
    {
        $query = Str::lower($message);

        if (!empty($context['matched_logger'])) {
            if ($dateRange = $this->requestedDateRangeFromMessage($message)) {
                return $this->formatLoggerHistoricalData(
                    $context['matched_logger'],
                    $dateRange,
                    $this->requestedGranularity($message)
                );
            }

            return $this->formatLoggerSummary($context['matched_logger']);
        }

        if ($this->isLoggerListQuestion($message)
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

            return "Logger yang saat ini koneksi terhubung (online) - {$count} dari {$total} logger:\n  - {$listStr}{$extra}";
        }

        if (Str::contains($query, ['offline', 'putus', 'mati', 'tidak terhubung', 'tidak aktif'])) {
            $count = $context['logger_offline_count'] ?? 0;
            $list  = $context['offline_loggers']      ?? [];
            $total = $context['logger_total_visible']  ?? 0;

            if ($count === 0) {
                return "Semua logger ({$total}) saat ini terdeteksi online. Tidak ada yang offline.";
            }

            $listStr = implode("\n  - ", $list);
            $extra   = $count > count($list) ? "\n  - ..." : '';

            return "Logger yang koneksi terputus (offline) - {$count} dari {$total} logger:\n  - {$listStr}{$extra}\n\nCek waktu data terakhir, baterai, dan jaringan di halaman detail perangkat.";
        }

        if (Str::contains($query, ['berapa', 'jumlah', 'total', 'semua', 'daftar', 'list', 'status'])) {
            $total   = $context['logger_total_visible'] ?? 0;
            $online  = $context['logger_online_count']  ?? 0;
            $offline = $context['logger_offline_count'] ?? 0;

            return "Total logger yang dapat diakses: {$total} unit.\n- Online (koneksi terhubung): {$online}\n- Offline (koneksi terputus): {$offline}\n\nBuka menu Peta atau Realtime Monitoring untuk detail masing-masing pos.";
        }

        if (Str::contains($query, ['real', 'realtime', 'monitoring', 'sensor terbaru', 'data terbaru', 'data sensor'])) {
            return "Untuk data real-time, buka menu Realtime Monitoring. Pilih pos/logger, lalu cek nilai sensor terakhir, waktu update, dan status koneksinya.\n\nJika data kosong atau tidak berubah, cek waktu update terakhir, baterai, jaringan alat, dan konfigurasi parameter logger.";
        }

        if (Str::contains($query, ['peta', 'lokasi', 'pos'])) {
            $count = $context['logger_total_visible'] ?? 0;

            return "Untuk melihat lokasi pos, buka menu Peta. Akun ini memiliki akses ke {$count} logger yang bisa ditinjau sesuai izin pengguna. Marker hijau biasanya berarti koneksi terhubung, sedangkan marker merah/abu menandakan koneksi terputus atau data tidak terbaru.";
        }

        if (Str::contains($query, ['analisa', 'grafik', 'chart', 'export', 'unduh', 'download', 'rekap'])) {
            return "Untuk analisa data, buka menu Analisa Data, pilih logger, pilih parameter, lalu tentukan rentang tanggal. Gunakan tombol export/unduh jika ingin mengambil data dalam bentuk file.\n\nJika logger tidak muncul, biasanya akun belum diberi akses ke logger tersebut.";
        }

        if (Str::contains($query, ['data masuk', 'tidak masuk', 'data kosong', 'sensor 0', 'baterai 0', 'update terakhir'])) {
            return "Kalau data logger tidak masuk atau nilainya aneh, cek waktu update terakhir, status koneksi, baterai/power, jaringan SIM/modem, kondisi sensor, dan mapping parameter. Untuk riwayat mentahnya, buka menu Data Masuk atau halaman detail logger.";
        }

        if (Str::contains($query, ['siaga', 'banjir', 'hujan'])) {
            return 'Level siaga mengikuti ambang batas yang dikonfigurasi pada data AWLR atau ARR. Cek halaman detail pos untuk melihat klasifikasi dan parameter pendukung.';
        }

        if (Str::contains($query, ['akses', 'tidak muncul', 'tidak ada', 'user', 'akun', 'admin lihat', 'kenapa cuma'])) {
            $total = $context['logger_total_visible'] ?? 0;

            return "Akses logger mengikuti role dan izin akun. Superadmin bisa melihat semua logger, admin instansi melihat logger instansinya, sedangkan user/pegawai hanya melihat logger yang diberikan lewat akses user.\n\nAkun ini saat ini dapat mengakses {$total} logger. Jika logger tertentu tidak muncul, minta admin menambahkan akses logger tersebut.";
        }

        if (Str::contains($query, ['tambah logger', 'edit logger', 'setting logger', 'data perangkat', 'parameter', 'instansi'])) {
            return 'Pengaturan logger, parameter, kategori, dan instansi ada di menu master/perangkat sesuai hak akses akun. Jika menu tidak terlihat, berarti akun belum memiliki permission untuk mengelola data tersebut.';
        }

        if (Str::contains($query, ['notifikasi', 'notif', 'peringatan', 'fcm'])) {
            return 'Notifikasi dipakai untuk memberi peringatan kondisi logger, seperti siaga, hujan, atau gangguan koneksi. Jika notifikasi tidak masuk, cek akses user ke logger, token perangkat/login mobile, jeda notifikasi, dan riwayat notifikasi.';
        }

        if (Str::contains($query, ['bisa bantu', 'panduan', 'cara pakai', 'menu apa', 'fitur'])) {
            return $this->defaultGuideReply();
        }

        return null;
    }

    private function isLoggerListQuestion(string $message): bool
    {
        $query = Str::lower($message);

        return Str::contains($query, [
            'semua logger',
            'daftar logger',
            'list logger',
            'tampilkan logger',
            'tampilkan semua',
            'logger yang ada',
            'logger di akun',
            'logger akun',
            'logger saya',
            'logger apa aja',
            'logger apa saja',
            'logger yang bisa diakses',
            'logger yang dapat diakses',
        ]);
    }

    private function defaultGuideReply(): string
    {
        return "Saya STESY Assistant. Saya bisa membantu:\n"
            ."- status logger online/offline\n"
            ."- detail logger dan sensor terakhir\n"
            ."- penjelasan kategori AWLR, ARR, AFMR, AWR, AWQR\n"
            ."- panduan Peta, Realtime Monitoring, Analisa Data, Data Masuk, dan Notifikasi\n"
            ."- penjelasan akses user jika logger tidak muncul\n\n"
            ."Silakan tanyakan lebih spesifik, misalnya: tampilkan logger online, apa itu AWLR, atau kenapa logger saya tidak muncul.";
    }

    private function missingLoggerReply(): string
    {
        return "Saya belum menemukan logger itu di daftar akses akun ini.\n\n"
            ."Coba cek lagi nama atau ID logger yang diketik. Jika loggernya memang ada tetapi belum muncul di akun ini, minta admin menambahkan akses logger tersebut.";
    }

    private function resolveLoggerMention(Request $request, string $message): ?array
    {
        return $this->data->resolveLogger($request->user(), $message);
    }

    private function resolveLoggerMentionsMulti(Request $request, string $message, int $max = 3): array
    {
        return $this->data->resolveLoggers($request->user(), $message, $max);
    }

    private function requestedDateRangeFromMessage(string $message): ?array
    {
        return $this->data->dateRange($message);
    }

    private function requestedGranularity(string $message): ?string
    {
        return $this->data->granularity($message);
    }

    private function candidateSensorTables(?string $table, int $sensorCount): array
    {
        return $this->data->candidateSensorTables($table, $sensorCount);
    }

    private function isSensorColumn(?string $column): bool
    {
        return $this->data->isSensorColumn($column);
    }

    private function isRainfallParam(array $param): bool
    {
        return $this->data->isRainfallParam($param);
    }

    private function formatLoggerHistoricalData(array $logger, array $dateRange, ?string $granularity = null): string
    {
        $candidateTables = $this->candidateSensorTables(
            $logger['tabel_main'] ?? '',
            (int) ($logger['sensor_count'] ?? 0)
        );

        $tableUsed = null;
        foreach ($candidateTables as $candidate) {
            $exists = DB::table($candidate)
                ->where('id_logger', $logger['id_logger'])
                ->whereBetween('waktu', [$dateRange['from'], $dateRange['to']])
                ->exists();

            if ($exists) {
                $tableUsed = $candidate;
                break;
            }
        }

        if (!$tableUsed) {
            return "Belum ada data pos {$logger['nama_logger']} ({$logger['id_logger']}) pada {$dateRange['label']}. "
                ."Silakan coba rentang tanggal lain, atau buka menu Analisa Data untuk peninjauan yang lebih luas.";
        }

        $params = collect($logger['params'] ?? [])
            ->filter(fn ($param) => $this->isSensorColumn($param['kolom'] ?? null))
            ->take(6)
            ->values();

        $base = fn () => DB::table($tableUsed)
            ->where('id_logger', $logger['id_logger'])
            ->whereBetween('waktu', [$dateRange['from'], $dateRange['to']]);

        $total = $base()->count();
        $numRe = '^-?[0-9]+([.][0-9]+)?$';

        // Agregat numerik aman (abaikan nilai non-numerik/kosong) per parameter.
        $selects = ['MIN(waktu) as _first_t', 'MAX(waktu) as _last_t'];
        foreach ($params as $i => $param) {
            $col = $param['kolom'];
            $num = "CASE WHEN `{$col}` REGEXP '{$numRe}' THEN CAST(`{$col}` AS DECIMAL(18,4)) END";
            $selects[] = "COUNT($num) as c{$i}";
            $selects[] = "MIN($num) as mn{$i}";
            $selects[] = "MAX($num) as mx{$i}";
            $selects[] = "AVG($num) as av{$i}";
            $selects[] = "SUM($num) as sm{$i}";
        }
        $agg = $base()->selectRaw(implode(', ', $selects))->first();

        $lastRow = $base()->orderByDesc('waktu')->first();

        $num = fn ($v, $d = 3) => $v === null ? null : round((float) $v, $d);

        $lines = [
            "Ringkasan data pos {$logger['nama_logger']} ({$logger['id_logger']}) — {$dateRange['label']}:",
            "- Total record: {$total}",
            '- Rentang data tersedia: '.($agg->_first_t ?? '-').' s.d. '.($agg->_last_t ?? '-'),
            '',
            'Statistik per parameter:',
        ];

        foreach ($params as $i => $param) {
            $cnt = (int) ($agg->{"c{$i}"} ?? 0);
            $unit = trim((string) ($param['satuan'] ?? ''));

            if ($cnt === 0) {
                $lines[] = "- {$param['nama']}: tidak ada data numerik pada rentang ini.";
                continue;
            }

            $min = $num($agg->{"mn{$i}"});
            $max = $num($agg->{"mx{$i}"});
            $avg = $num($agg->{"av{$i}"}, 2);
            $last = $lastRow && isset($lastRow->{$param['kolom']}) && is_numeric($lastRow->{$param['kolom']})
                ? $num($lastRow->{$param['kolom']})
                : null;

            if ($this->isRainfallParam($param)) {
                // Curah hujan = akumulasi (penjumlahan), bukan rata-rata.
                $sum = $num($agg->{"sm{$i}"}, 2);
                $lines[] = "- {$param['nama']}: akumulasi {$sum} {$unit} (puncak {$max} {$unit}, {$cnt} bacaan).";
            } else {
                // Parameter lain = rata-rata sebagai metrik utama.
                $lastTxt = $last !== null ? ", terakhir {$last} {$unit}" : '';
                $lines[] = "- {$param['nama']}: rata-rata {$avg} {$unit} (min {$min} / maks {$max}{$lastTxt}, {$cnt} bacaan).";
            }
        }

        // Auto-pilih granularitas bila tidak diminta eksplisit: rentang panjang
        // → harian, rentang sehari → per jam.
        $spanHours = $dateRange['from']->diffInHours($dateRange['to']);
        $granularity = $granularity ?: ($spanHours <= 26 ? 'hourly' : ($spanHours <= 24 * 14 ? 'daily' : null));

        if ($granularity) {
            $breakdown = $this->periodBreakdown($base, $params, $granularity, $numRe);
            if ($breakdown !== '') {
                $lines[] = '';
                $lines[] = $granularity === 'hourly' ? 'Rincian per jam (terbaru):' : 'Rincian per hari (terbaru):';
                $lines[] = $breakdown;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Rincian agregat per jam/hari untuk parameter kunci (hujan→akumulasi,
     * lainnya→rata-rata). Dibatasi agar balasan tetap ringkas.
     */
    private function periodBreakdown(\Closure $base, $params, string $granularity, string $numRe): string
    {
        $key = collect($params)->first(fn ($p) => $this->isRainfallParam($p)) ?? collect($params)->first();
        if (!$key || !$this->isSensorColumn($key['kolom'] ?? null)) {
            return '';
        }

        $col = $key['kolom'];
        $isRain = $this->isRainfallParam($key);
        $bucketExpr = $granularity === 'hourly'
            ? "DATE_FORMAT(waktu, '%Y-%m-%d %H:00')"
            : 'DATE(waktu)';
        $num = "CASE WHEN `{$col}` REGEXP '{$numRe}' THEN CAST(`{$col}` AS DECIMAL(18,4)) END";
        $aggExpr = $isRain ? "SUM($num)" : "AVG($num)";
        $limit = $granularity === 'hourly' ? 24 : 31;
        $unit = trim((string) ($key['satuan'] ?? ''));
        $label = $isRain ? 'akumulasi' : 'rata-rata';

        $rows = $base()
            ->selectRaw("{$bucketExpr} as bucket, {$aggExpr} as val, MAX($num) as mx, COUNT($num) as c")
            ->groupByRaw($bucketExpr)
            ->orderByRaw('bucket DESC')
            ->limit($limit)
            ->get()
            ->reverse();

        if ($rows->isEmpty()) {
            return '';
        }

        return $rows
            ->map(function ($r) use ($key, $label, $unit, $isRain) {
                $v = $r->val === null ? '-' : round((float) $r->val, $isRain ? 2 : 3);
                $extra = $isRain ? '' : ' (maks '.($r->mx === null ? '-' : round((float) $r->mx, 3))." {$unit})";

                return "- {$r->bucket} | {$key['nama']} {$label}: {$v} {$unit}{$extra}";
            })
            ->implode("\n");
    }

    private function isChartQuestion(string $message): bool
    {
        $query = Str::lower($message);

        return Str::contains($query, [
            'grafik', 'grafiknya', 'chart', 'visualisasi', 'visualkan',
            'plot', 'diagram', 'kurva', 'tren ', 'trend', 'tampilkan grafik',
        ]);
    }

    private function defaultWeekRange(): array
    {
        return $this->data->defaultWeekRange();
    }

    /**
     * Pilih parameter yang dimaksud pengguna dari daftar parameter logger,
     * berdasarkan sinonim umum. Null bila tidak ada yang cocok.
     */
    private function matchParamFromMessage(string $message, array $params): ?array
    {
        $query = Str::lower($message);

        $synonyms = [
            ['tinggi muka air', 'muka air', 'tma', 'water level', 'level air', 'elevasi', 'ketinggian air'],
            ['curah hujan', 'hujan', 'rainfall', 'rain'],
            ['debit', 'flow', 'aliran', 'discharge'],
            ['suhu', 'temperatur', 'temperature'],
            ['kelembapan', 'kelembaban', 'humidity', 'lembap'],
            ['baterai', 'battery', 'batre', 'tegangan', 'voltage'],
            ['kecepatan angin', 'angin', 'wind'],
            ['ph', 'turbidity', 'kekeruhan', 'salinitas', 'conductivity', 'tds'],
        ];

        $usable = collect($params)->filter(fn ($p) => $this->isSensorColumn($p['kolom'] ?? null))->values();
        if ($usable->isEmpty()) {
            return null;
        }

        $best = null;
        $bestScore = 0;
        foreach ($usable as $param) {
            $nameTokens = collect(preg_split('/[^a-z0-9]+/i', Str::lower($param['nama'])))
                ->filter(fn ($t) => Str::length($t) >= 3);

            $score = 0;
            foreach ($nameTokens as $t) {
                if (Str::contains($query, $t)) {
                    $score += Str::length($t);
                }
            }
            foreach ($synonyms as $group) {
                $nameHitsGroup = $nameTokens->contains(fn ($t) => collect($group)->contains(fn ($g) => Str::contains($g, $t) || Str::contains($t, $g)));
                if ($nameHitsGroup && collect($group)->contains(fn ($g) => Str::contains($query, $g))) {
                    $score += 10;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $param;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    /**
     * Bangun deret waktu satu parameter satu pos untuk ditampilkan sebagai
     * grafik di widget chatbot, beserta penjelasan ringkas. Logger sudah
     * ter-scope per user oleh pemanggil (matched_logger / forUser).
     */
    private function buildLoggerChart(array $logger, array $dateRange, string $message, ?string $granularity = null): ?array
    {
        $params = collect($logger['params'] ?? [])
            ->filter(fn ($p) => $this->isSensorColumn($p['kolom'] ?? null))
            ->values()
            ->all();

        if (empty($params)) {
            return null;
        }

        $param = $this->matchParamFromMessage($message, $params) ?? $params[0];
        $col = $param['kolom'];
        $unit = trim((string) ($param['satuan'] ?? ''));
        $isRain = $this->isRainfallParam($param);

        $tableUsed = null;
        foreach ($this->candidateSensorTables($logger['tabel_main'] ?? '', (int) ($logger['sensor_count'] ?? 0)) as $cand) {
            if (DB::table($cand)
                ->where('id_logger', $logger['id_logger'])
                ->whereBetween('waktu', [$dateRange['from'], $dateRange['to']])
                ->exists()) {
                $tableUsed = $cand;
                break;
            }
        }

        if (!$tableUsed) {
            return null;
        }

        $spanHours = $dateRange['from']->diffInHours($dateRange['to']);
        $granularity = $granularity ?: ($spanHours <= 26 ? 'hourly' : 'daily');
        $bucketExpr = $granularity === 'hourly'
            ? "DATE_FORMAT(waktu, '%Y-%m-%d %H:00')"
            : 'DATE(waktu)';
        $limit = $granularity === 'hourly' ? 72 : 62;

        $numRe = '^-?[0-9]+([.][0-9]+)?$';
        $num = "CASE WHEN `{$col}` REGEXP '{$numRe}' THEN CAST(`{$col}` AS DECIMAL(18,4)) END";
        $aggExpr = $isRain ? "SUM($num)" : "AVG($num)";

        $rows = DB::table($tableUsed)
            ->where('id_logger', $logger['id_logger'])
            ->whereBetween('waktu', [$dateRange['from'], $dateRange['to']])
            ->selectRaw("{$bucketExpr} as bucket, {$aggExpr} as v, MIN($num) as mn, MAX($num) as mx, COUNT($num) as c")
            ->groupByRaw($bucketExpr)
            ->orderByRaw('bucket ASC')
            ->limit($limit)
            ->get();

        $rows = $rows->filter(fn ($r) => $r->v !== null && (int) $r->c > 0)->values();

        if ($rows->isEmpty()) {
            return null;
        }

        $round = $isRain ? 2 : 3;
        $labels = $rows->map(fn ($r) => (string) $r->bucket)->all();
        $values = $rows->map(fn ($r) => round((float) $r->v, $round))->all();

        $aggLabel = $isRain ? 'akumulasi' : 'rata-rata';
        $peak = round((float) $rows->max('mx'), $round);
        $low = round((float) $rows->min('mn'), $round);
        $headline = $isRain
            ? 'total akumulasi '.round((float) $rows->sum('v'), 2)." {$unit}"
            : 'rata-rata '.round((float) collect($values)->avg(), 2)." {$unit}";

        $explanation = "Grafik {$param['nama']} pos {$logger['nama_logger']} ({$logger['id_logger']}) — {$dateRange['label']}.\n"
            ."Agregasi {$aggLabel} per ".($granularity === 'hourly' ? 'jam' : 'hari').", {$rows->count()} titik data.\n"
            ."Ringkasan: {$headline}, nilai terendah {$low} {$unit}, tertinggi {$peak} {$unit}.\n"
            .'Status pos: '.($logger['status'] ?? '-').', update terakhir '.($logger['last_time'] ?? '-').'.';

        return [
            'explanation' => $explanation,
            'chart' => [
                'type' => $isRain ? 'bar' : 'line',
                'title' => "{$param['nama']} — {$logger['nama_logger']}",
                'param' => $param['nama'],
                'unit' => $unit,
                'agg' => $aggLabel,
                'granularity' => $granularity,
                'labels' => $labels,
                'values' => $values,
            ],
        ];
    }

    private function isSupportedSensorTable(string $table): bool
    {
        return $this->data->isSupportedSensorTable($table);
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

    private function isComparisonQuestion(string $message): bool
    {
        $query = Str::lower($message);

        return Str::contains($query, [
            'banding', 'bandingkan', 'perbandingan', 'komparasi', 'komparasikan',
            'dibanding', 'dibandingkan', ' vs ', 'versus', 'lebih tinggi',
            'lebih rendah', 'lebih besar', 'lebih kecil', 'mana yang lebih',
            'selisih antara',
        ]);
    }

    private function isRainQuestion(string $message): bool
    {
        $query = Str::lower($message);

        return Str::contains($query, [
            'pos mana yang hujan', 'mana yang hujan', 'pos hujan', 'pos yang hujan',
            'ada hujan', 'sedang hujan', 'lagi hujan', 'turun hujan',
            'hujan dimana', 'hujan di mana', 'dimana hujan', 'di mana hujan',
            'curah hujan tertinggi', 'hujan terlebat', 'hujan terbesar',
            'hujan terbanyak', 'status hujan', 'rekap hujan', 'kondisi hujan',
            'pos mana saja yang hujan',
        ]);
    }

    /**
     * Statistik agregat numerik-aman per parameter satu pos pada rentang.
     */
    private function loggerPeriodStats(array $logger, array $dateRange): array
    {
        $candidateTables = $this->candidateSensorTables(
            $logger['tabel_main'] ?? '',
            (int) ($logger['sensor_count'] ?? 0)
        );

        $tableUsed = null;
        foreach ($candidateTables as $candidate) {
            if (DB::table($candidate)
                ->where('id_logger', $logger['id_logger'])
                ->whereBetween('waktu', [$dateRange['from'], $dateRange['to']])
                ->exists()) {
                $tableUsed = $candidate;
                break;
            }
        }

        if (!$tableUsed) {
            return [];
        }

        $params = collect($logger['params'] ?? [])
            ->filter(fn ($p) => $this->isSensorColumn($p['kolom'] ?? null))
            ->take(6)
            ->values();

        $numRe = '^-?[0-9]+([.][0-9]+)?$';
        $selects = [];
        foreach ($params as $i => $param) {
            $col = $param['kolom'];
            $num = "CASE WHEN `{$col}` REGEXP '{$numRe}' THEN CAST(`{$col}` AS DECIMAL(18,4)) END";
            $selects[] = "COUNT($num) as c{$i}";
            $selects[] = "MIN($num) as mn{$i}";
            $selects[] = "MAX($num) as mx{$i}";
            $selects[] = "AVG($num) as av{$i}";
            $selects[] = "SUM($num) as sm{$i}";
        }

        $agg = DB::table($tableUsed)
            ->where('id_logger', $logger['id_logger'])
            ->whereBetween('waktu', [$dateRange['from'], $dateRange['to']])
            ->selectRaw(implode(', ', $selects))
            ->first();

        $stats = [];
        foreach ($params as $i => $param) {
            if ((int) ($agg->{"c{$i}"} ?? 0) === 0) {
                continue;
            }
            $stats[Str::lower($param['nama'])] = [
                'nama' => $param['nama'],
                'satuan' => trim((string) ($param['satuan'] ?? '')),
                'is_rain' => $this->isRainfallParam($param),
                'min' => round((float) $agg->{"mn{$i}"}, 3),
                'max' => round((float) $agg->{"mx{$i}"}, 3),
                'avg' => round((float) $agg->{"av{$i}"}, 2),
                'sum' => round((float) $agg->{"sm{$i}"}, 2),
            ];
        }

        return $stats;
    }

    private function formatLoggerComparison(array $loggers, ?array $dateRange): string
    {
        $loggers = array_slice($loggers, 0, 3);
        $lines = ['Perbandingan pos:'];

        foreach ($loggers as $i => $lg) {
            $n = $i + 1;
            $lines[] = "{$n}. {$lg['nama_logger']} ({$lg['id_logger']}) — {$lg['kategori']}, "
                .'status '.($lg['status'] ?? '-').', update terakhir '.($lg['last_time'] ?? '-').'.';
        }

        // Pembacaan sensor terbaru, disejajarkan per nama parameter.
        $paramNames = collect($loggers)
            ->flatMap(fn ($lg) => collect($lg['sensor_values'] ?? [])->pluck('nama'))
            ->unique()
            ->take(8);

        if ($paramNames->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Pembacaan sensor terbaru:';
            foreach ($paramNames as $pname) {
                $cells = collect($loggers)->map(function ($lg) use ($pname) {
                    $hit = collect($lg['sensor_values'] ?? [])
                        ->first(fn ($s) => $s['nama'] === $pname);
                    $val = $hit ? trim($hit['nilai'].' '.($hit['satuan'] ?? '')) : 'n/a';

                    return $lg['nama_logger'].': '.$val;
                })->implode(' | ');
                $lines[] = "- {$pname} → {$cells}";
            }
        }

        if ($dateRange) {
            $statsPer = collect($loggers)->mapWithKeys(fn ($lg) => [
                $lg['id_logger'] => $this->loggerPeriodStats($lg, $dateRange),
            ]);

            $allParams = $statsPer->flatMap(fn ($s) => array_keys($s))->unique()->take(8);

            if ($allParams->isNotEmpty()) {
                $lines[] = '';
                $lines[] = "Ringkasan {$dateRange['label']}:";
                foreach ($allParams as $pkey) {
                    $cells = collect($loggers)->map(function ($lg) use ($statsPer, $pkey) {
                        $s = $statsPer[$lg['id_logger']][$pkey] ?? null;
                        if (!$s) {
                            return $lg['nama_logger'].': n/a';
                        }
                        $detail = $s['is_rain']
                            ? "akumulasi {$s['sum']} {$s['satuan']}"
                            : "rata-rata {$s['avg']} {$s['satuan']} (min {$s['min']} / maks {$s['max']})";

                        return $lg['nama_logger'].': '.$detail;
                    })->implode(' | ');

                    $label = $statsPer->flatMap(fn ($s) => $s)[$pkey]['nama'] ?? $pkey;
                    $lines[] = "- {$label} → {$cells}";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Ringkasan curah hujan seluruh pos hujan yang dapat diakses akun ini:
     * nilai terakhir dan akumulasi hari ini.
     */
    private function rainOverview(Request $request): array
    {
        $loggers = t_Logger::query()
            ->forUser($request->user())
            ->with(['params:id_param,logger_id,nama_parameter,kolom_sensor,satuan'])
            ->select(['id', 'id_logger', 'nama_logger', 'tabel_main', 'sensor_count'])
            ->orderBy('nama_logger')
            ->get();

        $todayStart = now()->startOfDay();
        $entries = [];

        foreach ($loggers as $logger) {
            $rainParam = $logger->params->first(
                fn ($p) => preg_match('/hujan|rain/i', (string) $p->nama_parameter)
                    && $this->isSensorColumn($p->kolom_sensor)
            );

            if (!$rainParam) {
                continue;
            }

            $col = $rainParam->kolom_sensor;
            $snapTable = 'temp_s' . SensorFamily::familyFor((int) $logger->sensor_count) . '_latest';
            $snap = DB::table($snapTable)->where('id_logger', $logger->id_logger)->first();

            $lastVal = $snap && isset($snap->{$col}) && is_numeric($snap->{$col})
                ? round((float) $snap->{$col}, 2)
                : null;
            $lastTime = $snap->waktu ?? null;

            $accumToday = null;
            foreach ($this->candidateSensorTables($logger->tabel_main ?? '', (int) $logger->sensor_count) as $tbl) {
                $numRe = '^-?[0-9]+([.][0-9]+)?$';
                $sum = DB::table($tbl)
                    ->where('id_logger', $logger->id_logger)
                    ->where('waktu', '>=', $todayStart)
                    ->selectRaw("SUM(CASE WHEN `{$col}` REGEXP '{$numRe}' THEN CAST(`{$col}` AS DECIMAL(18,4)) END) as s, COUNT(*) as c")
                    ->first();
                if ($sum && (int) $sum->c > 0) {
                    $accumToday = round((float) $sum->s, 2);
                    break;
                }
            }

            $minutesAgo = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
            $isFresh = $minutesAgo !== null && $minutesAgo < 60;

            // "Sedang hujan" hanya jika datanya masih terbaru — nilai lama
            // tidak boleh diklaim sebagai hujan saat ini.
            $isRaining = ($isFresh && $lastVal !== null && $lastVal > 0)
                || ($accumToday !== null && $accumToday > 0);

            $entries[] = [
                'nama' => $logger->nama_logger,
                'id_logger' => $logger->id_logger,
                'satuan' => trim((string) ($rainParam->satuan ?? 'mm')),
                'curah_terakhir' => $lastVal,
                'waktu_terakhir' => $lastTime,
                'menit_lalu' => $minutesAgo,
                'data_terbaru' => $isFresh,
                'akumulasi_hari_ini' => $accumToday,
                'sedang_hujan' => $isRaining,
            ];
        }

        usort($entries, function ($a, $b) {
            return ($b['akumulasi_hari_ini'] ?? -1) <=> ($a['akumulasi_hari_ini'] ?? -1)
                ?: (($b['curah_terakhir'] ?? -1) <=> ($a['curah_terakhir'] ?? -1));
        });

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'total_pos_hujan' => count($entries),
            'pos_sedang_hujan' => collect($entries)->where('sedang_hujan', true)->count(),
            'list' => array_slice($entries, 0, 20),
        ];
    }

    private function formatRainOverview(array $data): string
    {
        $list = $data['list'] ?? [];

        if (empty($list)) {
            return 'Tidak ada pos hujan (ARR/curah hujan) yang dapat diakses oleh akun ini, '
                .'atau data curah hujannya belum tersedia.';
        }

        $raining = collect($list)->where('sedang_hujan', true)->values();
        $lines = [];

        if ($raining->isEmpty()) {
            $lines[] = "Saat ini tidak ada pos yang terdeteksi hujan dari {$data['total_pos_hujan']} pos hujan "
                .'(berdasarkan data terbaru < 60 menit).';

            $recent = collect($list)
                ->filter(fn ($e) => ($e['curah_terakhir'] ?? 0) > 0)
                ->take(3)
                ->values();

            if ($recent->isNotEmpty()) {
                $lines[] = '';
                $lines[] = 'Curah hujan tercatat terakhir (data sudah tidak terbaru):';
                foreach ($recent as $i => $e) {
                    $lines[] = ($i + 1).". {$e['nama']} ({$e['id_logger']}) — {$e['curah_terakhir']} {$e['satuan']} pada {$e['waktu_terakhir']}.";
                }
            }
        } else {
            $lines[] = 'Pos yang terdeteksi hujan saat ini — '
                .$raining->count().' dari '.$data['total_pos_hujan'].' pos hujan:';
            foreach ($raining as $i => $e) {
                $n = $i + 1;
                $last = $e['curah_terakhir'] !== null ? "{$e['curah_terakhir']} {$e['satuan']}" : 'n/a';
                $acc = $e['akumulasi_hari_ini'] !== null ? "{$e['akumulasi_hari_ini']} {$e['satuan']}" : 'belum ada data hari ini';
                $lines[] = "{$n}. {$e['nama']} ({$e['id_logger']}) — terakhir {$last}, akumulasi hari ini {$acc} (update {$e['waktu_terakhir']}).";
            }
        }

        $lines[] = '';
        $lines[] = 'Buka menu Realtime Monitoring atau Analisa Data untuk rincian per pos.';

        return implode("\n", $lines);
    }
}
