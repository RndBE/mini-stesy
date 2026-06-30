<?php

namespace App\Services\Chatbot;

use App\Models\t_Logger;
use App\Models\t_User;
use App\Support\SensorFamily;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MonitoringData
{
    /**
     * Build the full monitoring context array for a user + message.
     * Replaces ChatbotController::buildMonitoringContext(Request, string).
     */
    public function context(t_User $user, string $message = ''): array
    {
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

        $loggerIds = $loggers->pluck('id_logger')->all();

        $snap16 = DB::table('temp_s16_latest')
            ->whereIn('id_logger', $loggerIds)
            ->get(['id_logger', 'waktu'])
            ->keyBy('id_logger');

        $snap19 = DB::table('temp_s19_latest')
            ->whereIn('id_logger', $loggerIds)
            ->get(['id_logger', 'waktu'])
            ->keyBy('id_logger');

        $snap50 = DB::table('temp_s50_latest')
            ->whereIn('id_logger', $loggerIds)
            ->get(['id_logger', 'waktu'])
            ->keyBy('id_logger');

        $mainTableLatest = collect();
        $loggersByTable  = $loggers->filter(fn ($l) => !empty($l->tabel_main))
                                   ->groupBy('tabel_main');

        foreach ($loggersByTable as $tableName => $tableLoggers) {
            $ids = $tableLoggers->pluck('id_logger')->all();
            $rows = DB::table($tableName)
                ->whereIn('id_logger', $ids)
                ->select('id_logger', DB::raw('MAX(id) as max_id'))
                ->groupBy('id_logger')
                ->get()
                ->keyBy('id_logger');

            foreach ($rows as $lid => $r) {
                $row = DB::table($tableName)->where('id', $r->max_id)->first();
                if ($row) {
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
            $w50   = $snap50[$lid]->waktu ?? null;
            $wMain = $mainTableLatest[$lid] ?? null;

            $lastTime = collect([$w16, $w19, $w50, $wMain])->filter()->sortDesc()->first();

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

        $matchedLogger = $this->resolveLogger($user, $message);

        return [
            'user_name'            => $user?->nama ?? $user?->username ?? 'User',
            'logger_total_visible' => $loggers->count(),
            'logger_online_count'  => count($onlineLoggers),
            'logger_offline_count' => count($offlineLoggers),
            'online_loggers'       => array_slice($onlineLoggers,  0, 20),
            'offline_loggers'      => array_slice($offlineLoggers, 0, 20),
            'all_loggers'          => $allLoggers,
            'category_definitions' => $this->categoryDefinitions(),
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

    /**
     * Resolve a single logger match for the user based on a query string.
     * Replaces ChatbotController::resolveLoggerMention(Request, string).
     */
    public function resolveLogger(t_User $user, string $query): ?array
    {
        return $this->resolveLoggerByTokens($user, $this->loggerMentionTokens($query));
    }

    /**
     * Resolve multiple logger matches (up to $max) from a query string.
     * Replaces ChatbotController::resolveLoggerMentionsMulti(Request, string, int).
     */
    public function resolveLoggers(t_User $user, string $query, int $max = 3): array
    {
        $segments = preg_split(
            '/\b(?:vs|versus|dan|atau|dengan|dibandingkan|dibanding|sama dengan)\b|[\/&,]+/i',
            $query
        ) ?: [];

        $found = [];
        foreach ($segments as $segment) {
            $tokens = $this->loggerMentionTokens($segment);
            if ($tokens->isEmpty()) {
                continue;
            }

            $logger = $this->resolveLoggerByTokens($user, $tokens);
            if ($logger && !isset($found[$logger['id_logger']])) {
                $found[$logger['id_logger']] = $logger;
            }

            if (count($found) >= $max) {
                break;
            }
        }

        return array_values($found);
    }

    /**
     * Parse a date range from a natural-language message.
     * Replaces ChatbotController::requestedDateRangeFromMessage(string).
     */
    public function dateRange(string $message): ?array
    {
        if ($relativeRange = $this->relativeDateRangeFromMessage($message)) {
            return $relativeRange;
        }

        $dates = $this->extractDatesFromMessage($message);

        if (empty($dates)) {
            return null;
        }

        $from = $dates[0]->copy()->startOfDay();
        $to = ($dates[1] ?? $dates[0])->copy()->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $sameDay = $from->isSameDay($to);

        return [
            'from' => $from,
            'to' => $to,
            'label' => $sameDay
                ? $from->translatedFormat('d F Y')
                : $from->translatedFormat('d F Y').' sampai '.$to->translatedFormat('d F Y'),
        ];
    }

    /**
     * Parse granularity ('hourly'|'daily'|null) from a natural-language message.
     * Replaces ChatbotController::requestedGranularity(string).
     */
    public function granularity(string $message): ?string
    {
        $query = Str::lower($message);

        if (Str::contains($query, ['per jam', 'tiap jam', 'setiap jam', 'jam-jaman', 'jam jaman', 'per-jam', 'hourly', 'rincian jam'])) {
            return 'hourly';
        }

        if (Str::contains($query, ['per hari', 'tiap hari', 'setiap hari', 'harian', 'daily', 'per-hari', 'rincian harian'])) {
            return 'daily';
        }

        return null;
    }

    /**
     * Return the default 7-day date range for when no date is specified.
     * Replaces ChatbotController::defaultWeekRange().
     */
    public function defaultWeekRange(): array
    {
        $from = now()->subDays(7)->startOfDay();
        $to = now()->endOfDay();

        return [
            'from' => $from,
            'to' => $to,
            'label' => '7 hari terakhir ('.$from->translatedFormat('d M Y').' s.d. '.$to->translatedFormat('d M Y').')',
        ];
    }

    /**
     * Return category definitions for AWLR, ARR, AFMR, AWR, AWQR.
     * Replaces ChatbotController::categoryDefinitions().
     */
    public function categoryDefinitions(): array
    {
        return [
            'AWLR' => [
                'name' => 'Automatic Water Level Recorder',
                'description' => 'logger untuk memantau tinggi muka air secara otomatis, misalnya sungai, saluran, bendung, atau sumur.',
                'common_parameters' => ['tinggi muka air', 'elevasi muka air', 'debit', 'baterai', 'humidity logger', 'temperature logger'],
                'main_functions' => [
                    'Mencatat tinggi muka air secara berkala.',
                    'Mengirim data secara otomatis ke sistem monitoring.',
                    'Membantu pemantauan kondisi sungai, saluran, bendung, atau sumur.',
                    'Mendukung peringatan dini jika terjadi kenaikan muka air yang signifikan.',
                ],
            ],
            'ARR' => [
                'name' => 'Automatic Rainfall Recorder',
                'description' => 'logger untuk mencatat curah hujan otomatis dan membantu melihat intensitas hujan per periode.',
                'common_parameters' => ['curah hujan', 'intensitas hujan', 'baterai', 'humidity logger', 'temperature logger'],
                'main_functions' => [
                    'Mencatat curah hujan secara otomatis.',
                    'Memantau intensitas hujan per periode.',
                    'Membantu analisa kondisi hujan di area pos.',
                    'Mendukung peringatan dini saat hujan tinggi atau ekstrem.',
                ],
            ],
            'AFMR' => [
                'name' => 'Automatic Flow Measurement Recorder',
                'description' => 'logger untuk memantau aliran air, seperti debit, kecepatan aliran, dan luas penampang.',
                'common_parameters' => ['debit', 'flow velocity', 'luas penampang air', 'elevasi muka air', 'jarak sensor'],
                'main_functions' => [
                    'Mengukur debit atau aliran air secara otomatis.',
                    'Memantau kecepatan aliran dan kondisi penampang air.',
                    'Membantu evaluasi kapasitas saluran atau sungai.',
                    'Menyediakan data pendukung untuk analisa hidrologi.',
                ],
            ],
            'AWR' => [
                'name' => 'Automatic Weather Recorder',
                'description' => 'logger untuk memantau kondisi cuaca otomatis.',
                'common_parameters' => ['suhu udara', 'kelembapan', 'tekanan udara', 'kecepatan angin', 'arah angin'],
                'main_functions' => [
                    'Mencatat parameter cuaca secara otomatis.',
                    'Memantau suhu, kelembapan, tekanan udara, dan angin.',
                    'Membantu membaca kondisi cuaca di sekitar pos.',
                    'Menyediakan data pendukung untuk monitoring hidrologi dan lingkungan.',
                ],
            ],
            'AWQR' => [
                'name' => 'Automatic Water Quality Recorder',
                'description' => 'logger untuk memantau kualitas air secara otomatis.',
                'common_parameters' => ['pH air', 'suhu air', 'turbidity', 'conductivity', 'salinity', 'TDS', 'ORP'],
                'main_functions' => [
                    'Mencatat parameter kualitas air secara otomatis.',
                    'Memantau pH, suhu air, kekeruhan, konduktivitas, salinitas, TDS, dan ORP.',
                    'Membantu deteksi perubahan kondisi kualitas air.',
                    'Menyediakan data pendukung untuk pemantauan lingkungan perairan.',
                ],
            ],
        ];
    }

    /**
     * Returns true if the param array represents a rainfall parameter.
     * Replaces ChatbotController::isRainfallParam(array).
     */
    public function isRainfallParam(array $param): bool
    {
        return (bool) preg_match('/hujan|rain/i', (string) ($param['nama'] ?? ''));
    }

    /**
     * Build a grounded facts array from the monitoring context for AI injection.
     * Replaces ChatbotController::groundedFacts(array).
     */
    public function groundedFacts(array $context): array
    {
        $online = $context['online_loggers'] ?? [];
        $offline = $context['offline_loggers'] ?? [];

        $truncated = (count($online) < ($context['logger_online_count'] ?? 0))
            || (count($offline) < ($context['logger_offline_count'] ?? 0));

        return [
            'user_name'            => $context['user_name'] ?? 'Pengguna',
            'server_time'          => now()->format('Y-m-d H:i:s'),
            'logger_total_visible' => $context['logger_total_visible'] ?? 0,
            'logger_online_count'  => $context['logger_online_count'] ?? 0,
            'logger_offline_count' => $context['logger_offline_count'] ?? 0,
            'online_loggers'       => $online,
            'offline_loggers'      => $offline,
            'loggers_truncated'    => $truncated,
            'all_loggers'          => array_slice($context['all_loggers'] ?? [], 0, 60),
            'matched_logger'       => $context['matched_logger'] ?? null,
            'missing_logger_reference' => !empty($context['missing_logger_reference']),
            'categories'           => $context['categories'] ?? [],
            'category_definitions' => $context['category_definitions'] ?? $this->categoryDefinitions(),
            'maintenance_loggers'  => $context['maintenance_loggers'] ?? [],
        ];
    }

    /**
     * Returns true if the given string is a sensor column name (sensor1..sensor50).
     */
    public function isSensorColumn(?string $column): bool
    {
        return is_string($column) && (bool) preg_match('/^sensor\d{1,2}$/', $column);
    }

    /**
     * Return candidate sensor table names for a logger (tabel_main first, then family defaults).
     */
    public function candidateSensorTables(?string $table, int $sensorCount): array
    {
        $primary = SensorFamily::mainTablePrefix(SensorFamily::familyFor($sensorCount)) . '01';
        return array_values(array_unique(array_filter([
            $this->isSupportedSensorTable((string) $table) ? $table : null,
            $primary,
            't_s50_01',
            't_s19_01',
            't_s16_01',
        ])));
    }

    /**
     * Returns true if the given table name is a supported sensor family table that exists.
     */
    public function isSupportedSensorTable(string $table): bool
    {
        return SensorFamily::isFamilyTable($table) && Schema::hasTable($table);
    }

    // -------------------------------------------------------------------------
    // Private helpers (moved from controller)
    // -------------------------------------------------------------------------

    private function resolveLoggerByTokens(t_User $user, $tokens): ?array
    {
        if ($tokens->isEmpty()) {
            return null;
        }

        $loggerCandidates = t_Logger::query()
            ->forUser($user)
            ->with([
                'kategori:id_katlogger,nama_kategori',
                'lokasi:idlokasi,nama_lokasi,alamat,latitude,longitude',
                'params:id_param,logger_id,nama_parameter,kolom_sensor,satuan',
                'temp16',
                'temp19',
                'temp50',
            ])
            ->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $builder
                        ->orWhereRaw('LOWER(nama_logger) LIKE ?', ['%'.$token.'%'])
                        ->orWhereRaw('LOWER(id_logger) LIKE ?', ['%'.$token.'%']);
                }
            })
            ->orderBy('nama_logger')
            ->limit(30)
            ->get();

        $scoreOf = function ($candidate) use ($tokens) {
            $name = Str::lower($candidate->nama_logger.' '.$candidate->id_logger);

            return $tokens->sum(function ($token) use ($name, $candidate) {
                if (Str::lower((string) $candidate->id_logger) === $token) {
                    return 100;
                }
                if (preg_match('/\b'.preg_quote($token, '/').'\b/', $name)) {
                    return Str::length($token) + 2;
                }

                return Str::contains($name, $token) ? Str::length($token) : 0;
            });
        };

        $ranked = $loggerCandidates
            ->map(fn ($candidate) => ['logger' => $candidate, 'score' => $scoreOf($candidate)])
            ->sortByDesc('score')
            ->values();

        $best = $ranked->first();

        if (!$best || $best['score'] < 5) {
            return null;
        }

        $topScore = $best['score'];
        $tiedCount = $ranked->where('score', $topScore)->count();
        if ($topScore < 100 && $tiedCount > 3) {
            return null;
        }

        $logger = $best['logger'];

        $latest = $this->resolveLatestSensorSnapshot($logger);
        $lastTime = collect([
            optional($logger->temp16)->waktu,
            optional($logger->temp19)->waktu,
            optional($logger->temp50)->waktu,
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
            'tabel_main' => $logger->tabel_main,
            'sensor_count' => $logger->sensor_count,
            'params' => $logger->params
                ->map(fn ($param) => [
                    'nama' => $param->nama_parameter,
                    'kolom' => $param->kolom_sensor,
                    'satuan' => $param->satuan,
                ])
                ->values()
                ->all(),
        ];
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
            'jumlah', 'total', 'buka', 'halaman', 'detail', 'ada', 'ini', 'aja',
            'saja', 'punya', 'milik',
            'cara', 'menu', 'panduan', 'fitur', 'peta', 'lokasi', 'monitoring',
            'realtime', 'real', 'analisa', 'grafik', 'chart', 'export', 'unduh',
            'download', 'notifikasi', 'notif', 'akses', 'akun', 'user',
            'awlr', 'arr', 'afmr', 'awr', 'awqr', 'awgc', 'jiat', 'nonjiat',
            'berapa', 'berapakah', 'bagaimana', 'gimana', 'gmn', 'kenapa',
            'mengapa', 'knp', 'kapan', 'mana', 'dimana', 'adakah', 'apakah',
            'kah', 'sih', 'kok', 'deh', 'nih', 'gitu', 'yg', 'sdh', 'udah',
            'blm', 'belum', 'itu', 'tadi', 'barusan', 'banyak', 'sedikit',
            'mati', 'hidup', 'nyala', 'kondisi', 'keadaan', 'sekarang', 'saat',
            'info', 'informasi', 'cek', 'mohon', 'minta', 'pengen', 'pingin',
            'mau', 'ingin', 'jelaskan', 'sebutkan', 'kasih', 'tahu', 'tau',
            'kabar', 'permisi', 'jam', 'waktu', 'update', 'terakhir', 'terbaru',
            'baru', 'seluruh', 'seputar', 'tentang', 'soal', 'perihal',
            'grafik', 'grafiknya', 'chart', 'visualisasi', 'visualkan', 'plot',
            'diagram', 'kurva', 'tren', 'trend', 'tinggi', 'muka', 'air',
            'debit', 'suhu', 'temperatur', 'temperature', 'kelembapan',
            'kelembaban', 'humidity', 'baterai', 'battery', 'curah', 'angin',
            'dalam', 'selama', 'seminggu', 'sepekan', 'sebulan', 'minggu',
            'bulan', 'hari', 'pekan', 'rata', 'akumulasi', 'periode', 'rentang',
        ];

        return collect(explode(' ', $normalized))
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => Str::length($token) >= 3 && !in_array($token, $stopWords, true))
            ->unique()
            ->values();
    }

    private function relativeDateRangeFromMessage(string $message): ?array
    {
        $query = Str::lower($message);

        $range = function (Carbon $from, Carbon $to, string $label) {
            return [
                'from' => $from,
                'to' => $to,
                'label' => $label.' ('.$from->translatedFormat('d M Y').' s.d. '.$to->translatedFormat('d M Y').')',
            ];
        };

        if (preg_match('/\b(\d{1,3})\s*jam\s+terakhir\b/i', $message, $m)) {
            $hours = min(max((int) $m[1], 1), 168);
            $from = now()->subHours($hours);

            return [
                'from' => $from,
                'to' => now(),
                'label' => "{$hours} jam terakhir (".$from->translatedFormat('d M Y H:i').' s.d. '.now()->translatedFormat('H:i').')',
            ];
        }

        if (Str::contains($query, ['jam terakhir', 'sejam terakhir', 'satu jam terakhir'])) {
            return [
                'from' => now()->subHour(),
                'to' => now(),
                'label' => '1 jam terakhir (sampai '.now()->translatedFormat('d M Y H:i').')',
            ];
        }

        if (Str::contains($query, ['seminggu terakhir', 'minggu terakhir', 'sepekan terakhir', '7 hari terakhir', 'tujuh hari terakhir'])) {
            return $range(now()->subDays(7)->startOfDay(), now()->endOfDay(), '7 hari terakhir');
        }

        if (Str::contains($query, ['minggu ini', 'pekan ini'])) {
            return $range(now()->startOfWeek()->startOfDay(), now()->endOfDay(), 'minggu ini');
        }

        if (Str::contains($query, ['minggu lalu', 'minggu kemarin', 'pekan lalu'])) {
            return $range(
                now()->subWeek()->startOfWeek()->startOfDay(),
                now()->subWeek()->endOfWeek()->endOfDay(),
                'minggu lalu'
            );
        }

        if (Str::contains($query, ['bulan ini'])) {
            return $range(now()->startOfMonth()->startOfDay(), now()->endOfDay(), 'bulan ini');
        }

        if (Str::contains($query, ['bulan lalu', 'bulan kemarin', 'sebulan lalu'])) {
            return $range(
                now()->subMonthNoOverflow()->startOfMonth()->startOfDay(),
                now()->subMonthNoOverflow()->endOfMonth()->endOfDay(),
                'bulan lalu'
            );
        }

        if (Str::contains($query, ['sebulan terakhir', 'satu bulan terakhir', '1 bulan terakhir', '30 hari terakhir', 'sebulan ini'])) {
            return $range(now()->subDays(30)->startOfDay(), now()->endOfDay(), '30 hari terakhir');
        }

        if (preg_match('/\b(\d{1,2})\s+bulan\s+terakhir\b/i', $message, $m)) {
            $months = min(max((int) $m[1], 1), 6);
            return $range(now()->subMonthsNoOverflow($months)->startOfDay(), now()->endOfDay(), "{$months} bulan terakhir");
        }

        if (preg_match('/\b(\d{1,3})\s+hari\s+terakhir\b/i', $message, $match)) {
            $days = min(max((int) $match[1], 1), 92);
            return $range(now()->subDays($days)->startOfDay(), now()->endOfDay(), "{$days} hari terakhir");
        }

        return null;
    }

    private function extractDatesFromMessage(string $message): array
    {
        $query = Str::lower($message);
        $dates = [];

        if (Str::contains($query, ['hari ini', 'today'])) {
            $dates[] = now();
        }

        if (Str::contains($query, ['kemarin', 'yesterday'])) {
            $dates[] = now()->subDay();
        }

        preg_match_all('/\b(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})\b/', $message, $isoMatches, PREG_SET_ORDER);
        foreach ($isoMatches as $match) {
            $date = $this->makeDate((int) $match[1], (int) $match[2], (int) $match[3]);
            if ($date) {
                $dates[] = $date;
            }
        }

        preg_match_all('/\b(\d{1,2})[-\/](\d{1,2})[-\/](\d{2,4})\b/', $message, $numericMatches, PREG_SET_ORDER);
        foreach ($numericMatches as $match) {
            $year = (int) $match[3];
            $year = $year < 100 ? 2000 + $year : $year;
            $date = $this->makeDate($year, (int) $match[2], (int) $match[1]);
            if ($date) {
                $dates[] = $date;
            }
        }

        $monthPattern = 'januari|jan|februari|feb|maret|mar|april|apr|mei|juni|jun|juli|jul|agustus|agu|aug|september|sep|oktober|okt|oct|november|nov|desember|des|dec';
        preg_match_all('/\b(\d{1,2})\s+('.$monthPattern.')(?:\s+(\d{4}))?\b/i', $message, $monthMatches, PREG_SET_ORDER);
        foreach ($monthMatches as $match) {
            $month = $this->monthNumber($match[2]);
            $year = isset($match[3]) && $match[3] !== '' ? (int) $match[3] : (int) now()->format('Y');
            $date = $this->makeDate($year, $month, (int) $match[1]);
            if ($date) {
                $dates[] = $date;
            }
        }

        return collect($dates)
            ->unique(fn (Carbon $date) => $date->format('Y-m-d'))
            ->take(2)
            ->values()
            ->all();
    }

    private function makeDate(int $year, int $month, int $day): ?Carbon
    {
        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return Carbon::create($year, $month, $day);
    }

    private function monthNumber(string $month): int
    {
        return [
            'januari' => 1, 'jan' => 1,
            'februari' => 2, 'feb' => 2,
            'maret' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5,
            'juni' => 6, 'jun' => 6,
            'juli' => 7, 'jul' => 7,
            'agustus' => 8, 'agu' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12,
        ][Str::lower($month)] ?? 0;
    }

    private function hasSpecificLoggerReference(string $message): bool
    {
        $query = Str::lower($message);

        if ($this->isGenericMonitoringQuestion($message)) {
            return false;
        }

        if (!Str::contains($query, ['logger', 'pos']) && !preg_match('/\b([a-z]+[-_]?\d{2,}|\d{4,})\b/i', $message)) {
            return false;
        }

        return $this->loggerMentionTokens($message)->isNotEmpty();
    }

    private function isGenericMonitoringQuestion(string $message): bool
    {
        $query = Str::lower($message);

        return Str::contains($query, [
            'berapa', 'jumlah', 'total', 'semua', 'seluruh', 'daftar', 'list',
            'online', 'offline', 'terhubung', 'putus', 'aktif', 'mati', 'nyala',
            'hidup', 'tidak aktif', 'kategori', 'jenis', 'macam', 'apa itu',
            'jelaskan', 'pengertian', 'fungsi', 'beda', 'bedanya', 'perbedaan',
            'panduan', 'cara pakai', 'menu', 'fitur', 'peta', 'realtime',
            'monitoring', 'analisa', 'grafik', 'export', 'unduh', 'download',
            'notifikasi', 'notif', 'siaga', 'banjir', 'hujan',
            'pos mana', 'mana yang', 'mana saja', 'banding', 'komparasi',
            'perbandingan', 'versus', 'grafik', 'chart', 'visualisasi',
            'diagram', 'kurva', 'tren', 'trend',
        ]);
    }

    private function resolveLatestSensorSnapshot(t_Logger $logger): array
    {
        $sensorCount = (int) ($logger->sensor_count ?? 0);

        $primaryTemp = 'temp_s' . SensorFamily::familyFor($sensorCount) . '_latest';
        $snapshotTables = array_values(array_unique([
            $primaryTemp, 'temp_s50_latest', 'temp_s19_latest', 'temp_s16_latest',
        ]));

        $row = null;
        $tableUsed = null;
        foreach ($snapshotTables as $candidate) {
            $row = DB::table($candidate)
                ->where('id_logger', $logger->id_logger)
                ->first();

            if ($row) {
                $tableUsed = $candidate;
                break;
            }
        }

        if (!$row) {
            foreach ($this->candidateSensorTables($logger->tabel_main ?? '', $sensorCount) as $candidate) {
                $row = DB::table($candidate)
                    ->where('id_logger', $logger->id_logger)
                    ->orderByDesc('waktu')
                    ->first();

                if ($row) {
                    $tableUsed = $candidate;
                    break;
                }
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
}
