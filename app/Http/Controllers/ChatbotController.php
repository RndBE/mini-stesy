<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotController extends Controller
{
    public function __construct(
        private \App\Services\Chatbot\ChatbotAgent $agent,
    ) {}

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:700'],
            'messages' => ['sometimes', 'array', 'max:12'],
            'messages.*.role' => ['required_with:messages', 'in:user,assistant'],
            'messages.*.text' => ['nullable', 'string', 'max:700'],
        ]);

        $out = $this->agent->ask($request->user(), trim($validated['message']), $validated['messages'] ?? []);

        return response()->json([
            'reply' => Str::limit($out['reply'], 1600, '...'),
            'source' => $out['source'],
            'configured' => $out['configured'],
            'chart' => $out['chart'],
        ]);
    }

    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:700'],
            'messages' => ['sometimes', 'array', 'max:12'],
            'messages.*.role' => ['required_with:messages', 'in:user,assistant'],
            'messages.*.text' => ['nullable', 'string', 'max:700'],
        ]);

        $user = $request->user();
        $message = trim($validated['message']);
        $turns = $validated['messages'] ?? [];

        $response = new StreamedResponse(function () use ($user, $message, $turns) {
            $emit = function (string $event, array $data) {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
                if (function_exists('ob_get_level') && ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            };

            try {
                $meta = $this->agent->stream(
                    $user,
                    $message,
                    $turns,
                    fn (string $token) => $emit('token', ['text' => $token]),
                    fn (array $chart) => $emit('chart', ['chart' => $chart]),
                );
                $emit('done', ['source' => $meta['source'] ?? 'ai']);
            } catch (\Throwable $e) {
                report($e);
                $emit('error', ['message' => 'Maaf, terjadi gangguan. Coba lagi sebentar.']);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
