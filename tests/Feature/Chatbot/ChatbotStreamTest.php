<?php

namespace Tests\Feature\Chatbot;

use App\Models\t_User;
use App\Services\Chatbot\ProviderClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatbotStreamTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Drop tables in dependency order.
        Schema::dropIfExists('user_logger_access');
        Schema::dropIfExists('t_logger');
        Schema::dropIfExists('kategori_logger');
        Schema::dropIfExists('t_lokasi');
        Schema::dropIfExists('t_user');
        Schema::dropIfExists('temp_s16_latest');
        Schema::dropIfExists('temp_s19_latest');
        Schema::dropIfExists('temp_s50_latest');

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('level_user')->default('pegawai');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->default('aktif');
            $table->string('suspend_reason')->nullable();
        });

        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->increments('id_katlogger');
            $table->string('nama_kategori');
        });

        Schema::create('t_lokasi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idlokasi', 20)->unique();
            $table->string('nama_lokasi');
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
            $table->string('tabel_main')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->string('idlokasi', 20)->nullable();
            $table->unsignedTinyInteger('sensor_count')->default(16);
            $table->string('status_perbaikan')->default('normal');
            $table->string('jenis_alat')->nullable();
            $table->string('node_skema_id')->nullable();
        });

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15);
        });

        Schema::create('temp_s16_latest', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu')->nullable();
        });

        Schema::create('temp_s19_latest', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu')->nullable();
        });

        Schema::create('temp_s50_latest', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('user_logger_access');
        Schema::dropIfExists('t_logger');
        Schema::dropIfExists('kategori_logger');
        Schema::dropIfExists('t_lokasi');
        Schema::dropIfExists('t_user');
        Schema::dropIfExists('temp_s16_latest');
        Schema::dropIfExists('temp_s19_latest');
        Schema::dropIfExists('temp_s50_latest');
        parent::tearDown();
    }

    public function test_stream_emits_token_and_done(): void
    {
        // Mock ProviderClient so it is "configured" and stream() calls $onToken directly.
        $mockProvider = $this->mock(ProviderClient::class);
        $mockProvider->shouldReceive('configured')->andReturn(true);
        // chat() is called for pass-1 (tool detection); return content with no tool_calls.
        $mockProvider->shouldReceive('chat')->andReturn(['content' => 'Halo.', 'role' => 'assistant']);
        // stream() is called for the no-tool pass; invoke $onToken and return the full text.
        $mockProvider->shouldReceive('stream')->andReturnUsing(function (array $messages, callable $onToken) {
            $onToken('Halo.');
            return 'Halo.';
        });

        $user = t_User::factory()->create();

        $res = $this->actingAs($user)
            ->post(route('chatbot.stream'), ['message' => 'halo']);

        $res->assertOk();
        $res->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');

        $content = $res->streamedContent();
        $this->assertStringContainsString('event: token', $content);
        $this->assertStringContainsString('event: done', $content);
    }

    public function test_stream_validates_message_min_length(): void
    {
        $user = t_User::factory()->create();

        $res = $this->actingAs($user)
            ->postJson(route('chatbot.stream'), ['message' => 'h']);

        $res->assertUnprocessable()->assertJsonValidationErrors(['message']);
    }

    public function test_stream_rejects_unauthenticated(): void
    {
        $res = $this->postJson(route('chatbot.stream'), ['message' => 'halo']);

        $res->assertUnauthorized();
    }
}
