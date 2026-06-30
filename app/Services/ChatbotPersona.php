<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Memuat konfigurasi chatbot modular (gaya OpenClaw) dari berkas markdown
 * di resources/chatbot/ — SOUL.md (kepribadian), AGENT.md (perilaku/aturan),
 * SKILLS.md (kemampuan data) — lalu menyusunnya menjadi system prompt
 * bersama FAKTA SISTEM yang sudah di-ground dari basis data.
 *
 * Berkas markdown bisa diubah tanpa menyentuh kode; cache otomatis tersegar
 * mengikuti mtime berkas.
 */
class ChatbotPersona
{
    /** Urutan berkas penyusun system prompt. */
    private const MODULES = ['SOUL.md', 'AGENT.md', 'SKILLS.md'];

    private function basePath(): string
    {
        return resource_path('chatbot');
    }

    /**
     * Gabungan isi seluruh modul markdown, dicache berdasarkan mtime
     * agar perubahan berkas langsung terbaca tanpa clear cache manual.
     */
    public function persona(): string
    {
        $files = collect(self::MODULES)
            ->map(fn ($name) => $this->basePath().DIRECTORY_SEPARATOR.$name);

        $signature = $files
            ->map(fn ($path) => is_file($path) ? (string) filemtime($path) : '0')
            ->implode('-');

        return Cache::remember(
            'chatbot.persona.'.md5($signature),
            now()->addHour(),
            function () use ($files) {
                return $files
                    ->map(fn ($path) => is_file($path) ? trim((string) file_get_contents($path)) : '')
                    ->filter()
                    ->implode("\n\n---\n\n");
            }
        );
    }

    /**
     * System prompt final: persona modular + FAKTA SISTEM (JSON ter-ground).
     */
    public function systemPrompt(array $facts): string
    {
        $persona = $this->persona();

        $json = json_encode(
            $facts,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        return $persona
            ."\n\n---\n\n"
            ."# SYSTEM FACTS\n\n"
            ."Konteks ringan berikut disuntik untuk membantu memahami pertanyaan "
            ."dan memilih argumen tool. Untuk angka, nama, ID, status, dan nilai "
            ."sensor yang akurat, PANGGIL tool yang sesuai — jangan mengarang data.\n\n"
            ."```json\n".$json."\n```";
    }
}
