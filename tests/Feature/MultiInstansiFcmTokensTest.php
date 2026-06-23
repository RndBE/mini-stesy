<?php

namespace Tests\Feature;

use App\Services\FcmService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class MultiInstansiFcmTokensTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['fcm_tokens', 'user_logger_access', 't_logger', 't_user'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
        });

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15);
            $table->unique(['user_id', 'logger_id']);
        });

        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('fcm_token');
        });

        // Logger L3 lives in instansi 2.
        DB::table('t_logger')->insert([
            ['id_logger' => 'L3', 'instansi_id' => 2, 'nama_logger' => 'Logger 3'],
        ]);
    }

    private function user(string $level, ?int $instansiId, string $token, array $grants = []): void
    {
        $id = DB::table('t_user')->insertGetId([
            'nama' => $level,
            'username' => $level . '_' . uniqid(),
            'level_user' => $level,
            'instansi_id' => $instansiId,
            'status' => 'aktif',
        ]);
        DB::table('fcm_tokens')->insert(['user_id' => $id, 'fcm_token' => $token]);
        foreach ($grants as $logger) {
            DB::table('user_logger_access')->insert(['user_id' => $id, 'logger_id' => $logger]);
        }
    }

    private function tokensFor(string $idLogger): array
    {
        $method = new ReflectionMethod(FcmService::class, 'getLoggerWarningTokens');
        $method->setAccessible(true);

        return collect($method->invoke(new FcmService(), $idLogger))->sort()->values()->all();
    }

    public function test_warning_audience_includes_cross_instansi_grants(): void
    {
        $this->user('superadmin', null, 'tok-super');                 // always
        $this->user('instansi_admin', 2, 'tok-admin-same');           // same instansi as L3
        $this->user('instansi_admin', 1, 'tok-admin-grant', ['L3']);  // cross-instansi grant
        $this->user('instansi_admin', 1, 'tok-admin-none');           // no grant, other instansi -> excluded
        $this->user('pegawai', 1, 'tok-pegawai-grant', ['L3']);       // cross-instansi grant
        $this->user('pegawai', 2, 'tok-pegawai-none');                // no grant -> excluded

        $this->assertSame(
            ['tok-admin-grant', 'tok-admin-same', 'tok-pegawai-grant', 'tok-super'],
            $this->tokensFor('L3')
        );
    }
}
