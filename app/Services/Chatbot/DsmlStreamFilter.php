<?php
namespace App\Services\Chatbot;

/**
 * Forwards streamed tokens to $emit but drops everything from the first DSML tool-call marker on.
 * A trailing "<", "<｜", "<｜DS" … is held back until the next token shows whether it is the marker.
 */
final class DsmlStreamFilter
{
    private string $pending = '';
    private string $emitted = '';
    private bool $dropping = false;

    /** @param callable(string):void $emit */
    public function __construct(private $emit) {}

    public function push(string $token): void
    {
        if ($this->dropping) {
            return;
        }

        $this->pending .= $token;

        if (preg_match(DsmlToolCalls::MARKER, $this->pending, $m, PREG_OFFSET_CAPTURE)) {
            $this->send(substr($this->pending, 0, $m[0][1]));
            $this->pending = '';
            $this->dropping = true;
            return;
        }

        $cut = strrpos($this->pending, '<');
        if ($cut !== false && preg_match('/^<\s*[\x{FF5C}|]*\s*(?:D|DS|DSM)?$/u', substr($this->pending, $cut))) {
            $this->send(substr($this->pending, 0, $cut));
            $this->pending = substr($this->pending, $cut);
            return;
        }

        $this->send($this->pending);
        $this->pending = '';
    }

    /** Release a held-back tail that turned out not to be the marker. */
    public function flush(): void
    {
        if (! $this->dropping) {
            $this->send($this->pending);
        }
        $this->pending = '';
    }

    /** Everything that reached the user. */
    public function emitted(): string
    {
        return $this->emitted;
    }

    private function send(string $text): void
    {
        if ($text === '') {
            return;
        }
        $this->emitted .= $text;
        ($this->emit)($text);
    }
}
