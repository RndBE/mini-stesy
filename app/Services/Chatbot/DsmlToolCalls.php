<?php
namespace App\Services\Chatbot;

/**
 * DeepSeek sometimes writes its native tool-call markup (DSML) into message.content instead of
 * returning structured tool_calls, e.g.
 *   <｜DSML｜function_calls><｜DSML｜invoke name="x"><｜DSML｜parameter name="a" string="true">1</｜DSML｜parameter>…
 * The router passes it through verbatim, so without this the user sees the raw markup.
 * The bars are U+FF5C (sometimes doubled or plain "|"), so every pattern is lenient about them.
 */
final class DsmlToolCalls
{
    public const MARKER = '/<\s*[\x{FF5C}|]+\s*DSML/u';

    private const BAR = '[\x{FF5C}|]';

    public static function contains(string $text): bool
    {
        return (bool) preg_match(self::MARKER, $text);
    }

    /** Text with every DSML tool-call block removed (an unclosed block is cut to the end). */
    public static function strip(string $text): string
    {
        $closing = '/<\s*\/\s*'.self::BAR.'+\s*DSML[\x{FF5C}|\s]*(?:function_)?calls\s*>/u';

        while (preg_match(self::MARKER, $text, $start, PREG_OFFSET_CAPTURE)) {
            $before = substr($text, 0, $start[0][1]);
            $after = substr($text, $start[0][1]);
            $rest = '';
            if (preg_match($closing, $after, $end, PREG_OFFSET_CAPTURE)) {
                $rest = substr($after, $end[0][1] + strlen($end[0][0]));
            }
            $text = $before.$rest;
        }

        return trim($text);
    }

    /**
     * OpenAI-style tool_calls parsed out of DSML invoke blocks.
     *
     * @return array<int,array{id:string,type:string,function:array{name:string,arguments:string}}>
     */
    public static function parse(string $text): array
    {
        $bar = self::BAR;
        $invoke = '/<\s*'.$bar.'+\s*DSML[\x{FF5C}|\s]*invoke\s+name\s*=\s*"([^"]+)"\s*>(.*?)<\s*\/\s*'.$bar.'+\s*DSML[\x{FF5C}|\s]*invoke\s*>/us';
        $param = '/<\s*'.$bar.'+\s*DSML[\x{FF5C}|\s]*parameter\s+name\s*=\s*"([^"]+)"(?:\s+string\s*=\s*"(true|false)")?\s*>(.*?)<\s*\/\s*'.$bar.'+\s*DSML[\x{FF5C}|\s]*parameter\s*>/us';

        if (! preg_match_all($invoke, $text, $invokes, PREG_SET_ORDER)) {
            return [];
        }

        $calls = [];
        foreach ($invokes as $i => $inv) {
            $args = [];
            if (preg_match_all($param, $inv[2], $params, PREG_SET_ORDER)) {
                foreach ($params as $p) {
                    $value = trim($p[3]);
                    if (($p[2] ?? 'true') === 'false') {
                        $decoded = json_decode($value, true);
                        $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
                    }
                    $args[$p[1]] = $value;
                }
            }

            $calls[] = [
                'id' => 'dsml_'.$i.'_'.substr(md5($inv[0]), 0, 8),
                'type' => 'function',
                'function' => [
                    'name' => $inv[1],
                    'arguments' => json_encode((object) $args, JSON_UNESCAPED_UNICODE),
                ],
            ];
        }

        return $calls;
    }

    /**
     * Clean a provider message: DSML in content becomes real tool_calls (only when the caller
     * offered tools and the provider returned none) and is always removed from the visible text.
     */
    public static function normalize(array $message, bool $toolsOffered): array
    {
        $content = $message['content'] ?? null;
        if (! is_string($content) || ! self::contains($content)) {
            return $message;
        }

        if ($toolsOffered && empty($message['tool_calls'])) {
            $calls = self::parse($content);
            if ($calls) {
                $message['tool_calls'] = $calls;
            }
        }

        $clean = self::strip($content);
        $message['content'] = ($clean === '' && ! empty($message['tool_calls'])) ? null : $clean;

        return $message;
    }
}
