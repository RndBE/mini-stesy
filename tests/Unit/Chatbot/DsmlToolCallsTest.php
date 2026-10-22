<?php
namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\DsmlStreamFilter;
use App\Services\Chatbot\DsmlToolCalls;
use App\Services\Chatbot\ProviderClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DsmlToolCallsTest extends TestCase
{
    /** The exact markup that showed up in the chat window (doubled U+FF5C bars). */
    private function leaked(): string
    {
        $b = "\u{FF5C}\u{FF5C}";
        return "<{$b}DSML{$b} calls>\n"
            ."<{$b}DSML{$b} invoke name=\"get_logger_detail\">\n"
            ."<{$b}DSML{$b} parameter name=\"logger_id\" string=\"true\">10373</{$b}DSML{$b} parameter>\n"
            ."</{$b}DSML{$b} invoke>\n"
            ."</{$b}DSML{$b} calls>";
    }

    private function configure(): void
    {
        config(['services.ai_chatbot.endpoint' => 'https://api.test/v1/chat/completions',
                'services.ai_chatbot.key' => 'k', 'services.ai_chatbot.model' => 'Chatbot']);
    }

    public function test_parse_turns_leaked_markup_into_tool_call(): void
    {
        $calls = DsmlToolCalls::parse($this->leaked());

        $this->assertCount(1, $calls);
        $this->assertSame('get_logger_detail', $calls[0]['function']['name']);
        $this->assertSame(['logger_id' => '10373'], json_decode($calls[0]['function']['arguments'], true));
    }

    public function test_parse_handles_single_bar_format_and_json_params(): void
    {
        $t = "<\u{FF5C}DSML\u{FF5C}function_calls><\u{FF5C}DSML\u{FF5C}invoke name=\"x\">"
            ."<\u{FF5C}DSML\u{FF5C}parameter name=\"n\" string=\"false\">3</\u{FF5C}DSML\u{FF5C}parameter>"
            ."</\u{FF5C}DSML\u{FF5C}invoke></\u{FF5C}DSML\u{FF5C}function_calls>";

        $calls = DsmlToolCalls::parse($t);
        $this->assertSame(['n' => 3], json_decode($calls[0]['function']['arguments'], true));
    }

    public function test_strip_removes_block_and_keeps_surrounding_text(): void
    {
        $this->assertSame('', DsmlToolCalls::strip($this->leaked()));
        $this->assertSame('Sebentar ya.', DsmlToolCalls::strip("Sebentar ya.\n".$this->leaked()));
        $this->assertSame('Halo', DsmlToolCalls::strip('Halo'));
    }

    public function test_strip_cuts_an_unclosed_block(): void
    {
        $this->assertSame('Cek', DsmlToolCalls::strip("Cek <\u{FF5C}DSML\u{FF5C}function_calls><\u{FF5C}DSML\u{FF5C}inv"));
    }

    public function test_chat_executes_leaked_tool_call_when_tools_offered(): void
    {
        $this->configure();
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => $this->leaked()]]]], 200)]);

        $msg = app(ProviderClient::class)->chat([['role' => 'user', 'content' => 'cek 10373']], [['type' => 'function']]);

        $this->assertSame('get_logger_detail', $msg['tool_calls'][0]['function']['name']);
        $this->assertNull($msg['content']);
    }

    public function test_chat_without_tools_drops_leaked_markup_from_reply(): void
    {
        $this->configure();
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => $this->leaked()]]]], 200)]);

        $msg = app(ProviderClient::class)->chat([['role' => 'user', 'content' => 'cek 10373']]);

        $this->assertSame('', $msg['content']);
        $this->assertArrayNotHasKey('tool_calls', $msg);
    }

    public function test_stream_filter_hides_markup_split_across_tokens(): void
    {
        $out = [];
        $f = new DsmlStreamFilter(function (string $t) use (&$out) { $out[] = $t; });

        foreach (['Cek ', 'dulu <', "\u{FF5C}\u{FF5C}", 'DSML', "\u{FF5C}\u{FF5C} calls>", '<x>'] as $tok) {
            $f->push($tok);
        }
        $f->flush();

        $this->assertSame('Cek dulu ', $f->emitted());
        $this->assertSame('Cek dulu ', implode('', $out));
    }

    public function test_stream_filter_releases_lone_angle_bracket(): void
    {
        $f = new DsmlStreamFilter(fn () => null);
        foreach (['suhu ', '<', ' 5 derajat'] as $tok) {
            $f->push($tok);
        }
        $f->flush();

        $this->assertSame('suhu < 5 derajat', $f->emitted());
    }

    public function test_provider_stream_strips_leaked_markup(): void
    {
        $this->configure();
        $b = "\u{FF5C}\u{FF5C}";
        $body = '';
        foreach (['Data pos ', "<{$b}DSML{$b} calls>", '...'] as $d) {
            $body .= 'data: '.json_encode(['choices' => [['delta' => ['content' => $d]]]], JSON_UNESCAPED_UNICODE)."\n\n";
        }
        Http::fake(['*' => Http::response($body."data: [DONE]\n\n", 200)]);

        $got = '';
        $result = app(ProviderClient::class)->stream([['role' => 'user', 'content' => 'hi']], function ($t) use (&$got) { $got .= $t; });

        $this->assertSame('Data pos ', $got);
        $this->assertSame('Data pos ', $result);
    }
}
