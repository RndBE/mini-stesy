<?php
namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;

class ProviderClient
{
    public function configured(): bool
    {
        return (bool) config('services.ai_chatbot.key')
            && (bool) config('services.ai_chatbot.model')
            && (bool) config('services.ai_chatbot.endpoint');
    }

    public function chat(array $messages, array $tools = []): ?array
    {
        try {
            $payload = [
                'model' => config('services.ai_chatbot.model'),
                'max_completion_tokens' => 600,
                'messages' => $messages,
            ];
            if ($tools) { $payload['tools'] = $tools; $payload['tool_choice'] = 'auto'; }

            $res = $this->request()->post(config('services.ai_chatbot.endpoint'), $payload);
            if (! $res->successful()) { report(new \RuntimeException('Chatbot provider error: '.$res->body())); return null; }

            return data_get($res->json(), 'choices.0.message');
        } catch (\Throwable $e) { report($e); return null; }
    }

    public function stream(array $messages, callable $onToken): ?string
    {
        try {
            $res = $this->request()->withOptions(['stream'=>true])->post(
                config('services.ai_chatbot.endpoint'),
                ['model'=>config('services.ai_chatbot.model'),'max_completion_tokens'=>600,'messages'=>$messages,'stream'=>true]
            );
            if (! $res->successful()) { report(new \RuntimeException('Chatbot stream error: '.$res->status())); return null; }

            $full = '';
            $body = $res->toPsrResponse()->getBody();
            $buffer = '';
            while (! $body->eof()) {
                $buffer .= $body->read(1024);
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $pos));
                    $buffer = substr($buffer, $pos + 1);
                    if (! str_starts_with($line, 'data:')) { continue; }
                    $data = trim(substr($line, 5));
                    if ($data === '' || $data === '[DONE]') { continue; }
                    $delta = data_get(json_decode($data, true), 'choices.0.delta.content');
                    if (is_string($delta) && $delta !== '') { $full .= $delta; $onToken($delta); }
                }
            }
            return $full;
        } catch (\Throwable $e) { report($e); return null; }
    }

    private function request()
    {
        $verify = filter_var(config('services.ai_chatbot.verify_ssl', true), FILTER_VALIDATE_BOOL);
        $req = Http::timeout(25)->withToken(config('services.ai_chatbot.key'))->acceptJson();
        return $verify ? $req : $req->withoutVerifying();
    }
}
