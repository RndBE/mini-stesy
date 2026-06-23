<?php

namespace Tests\Feature;

use App\Models\t_Logger;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MultiInstansiScopeForUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user_logger_access', 't_logger', 't_user'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password')->nullable();
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

        // Instansi 1: L1, L2 ; Instansi 2: L3, L4
        DB::table('t_logger')->insert([
            ['id_logger' => 'L1', 'instansi_id' => 1, 'nama_logger' => 'Logger 1'],
            ['id_logger' => 'L2', 'instansi_id' => 1, 'nama_logger' => 'Logger 2'],
            ['id_logger' => 'L3', 'instansi_id' => 2, 'nama_logger' => 'Logger 3'],
            ['id_logger' => 'L4', 'instansi_id' => 2, 'nama_logger' => 'Logger 4'],
        ]);
    }

    private function makeUser(string $level, ?int $instansiId): t_User
    {
        $id = DB::table('t_user')->insertGetId([
            'nama' => $level,
            'username' => $level . '_' . ($instansiId ?? 'x') . '_' . uniqid(),
            'password' => 'x',
            'level_user' => $level,
            'instansi_id' => $instansiId,
            'status' => 'aktif',
        ]);

        return t_User::query()->findOrFail($id);
    }

    private function grant(t_User $user, array $loggerIds): void
    {
        foreach ($loggerIds as $logger) {
            DB::table('user_logger_access')->insert([
                'user_id' => $user->id_user,
                'logger_id' => $logger,
            ]);
        }
    }

    private function visible(t_User $user): array
    {
        return t_Logger::query()->forUser($user)->pluck('id_logger')->sort()->values()->all();
    }

    public function test_superadmin_sees_all_loggers(): void
    {
        $user = $this->makeUser('superadmin', null);
        $this->assertSame(['L1', 'L2', 'L3', 'L4'], $this->visible($user));
    }

    public function test_instansi_admin_sees_own_instansi_plus_cross_instansi_grant(): void
    {
        $user = $this->makeUser('instansi_admin', 1);
        $this->grant($user, ['L4']); // cross-instansi extra
        $this->assertSame(['L1', 'L2', 'L4'], $this->visible($user));
    }

    public function test_instansi_admin_without_grant_sees_only_own_instansi(): void
    {
        $user = $this->makeUser('instansi_admin', 1);
        $this->assertSame(['L1', 'L2'], $this->visible($user));
    }

    public function test_pegawai_sees_only_granted_loggers_including_cross_instansi(): void
    {
        $user = $this->makeUser('pegawai', 1);
        $this->grant($user, ['L1', 'L3']); // one own-instansi, one cross-instansi
        $this->assertSame(['L1', 'L3'], $this->visible($user));
    }

    public function test_pegawai_without_grant_sees_nothing(): void
    {
        $user = $this->makeUser('pegawai', 1);
        $this->assertSame([], $this->visible($user));
    }

    public function test_null_user_sees_nothing(): void
    {
        $this->assertSame([], t_Logger::query()->forUser(null)->pluck('id_logger')->all());
    }
}
