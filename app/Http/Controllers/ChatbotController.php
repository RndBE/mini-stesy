<?php

namespace App\Http\Controllers;

use App\Services\ChatbotPersona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        if ($this->data->isGreetingMessage($message)) {
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
        if ($this->data->isComparisonQuestion($message)) {
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
        if ($this->data->isChartQuestion($message) && !empty($context['matched_logger'])) {
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

            if ($this->data->isRainQuestion($message)) {
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
     * Delegates to MonitoringData::groundedFallback.
     */
    private function composeGroundedFallback(Request $request, string $message, array $context): string
    {
        return $this->data->groundedFallback($request->user(), $message, $context);
    }

    private function buildMonitoringContext(Request $request, string $message = ''): array
    {
        return $this->data->context($request->user(), $message);
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
        return $this->data->history($logger, $dateRange, $granularity);
    }

    private function defaultWeekRange(): array
    {
        return $this->data->defaultWeekRange();
    }

    private function buildLoggerChart(array $logger, array $dateRange, string $message, ?string $granularity = null): ?array
    {
        return $this->data->chart($logger, $dateRange, $message, $granularity);
    }

    private function isSupportedSensorTable(string $table): bool
    {
        return $this->data->isSupportedSensorTable($table);
    }

    private function formatLoggerSummary(array $logger): string
    {
        return $this->data->summary($logger);
    }

    private function formatLoggerComparison(array $loggers, ?array $dateRange): string
    {
        return $this->data->comparison($loggers, $dateRange);
    }

    private function rainOverview(Request $request): array
    {
        return $this->data->rainOverview($request->user());
    }
}
