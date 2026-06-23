<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MultiInstansiUserControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user_logger_access', 't_logger', 't_user', 'instansi', 'roles'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role_name')->unique();
        });

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
            $table->string('suspend_reason')->nullable();
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

        DB::table('roles')->insert([
            ['role_name' => 'superadmin'],
            ['role_name' => 'instansi_admin'],
            ['role_name' => 'pegawai'],
        ]);
        DB::table('instansi')->insert([
            ['id' => 1, 'nama' => 'Instansi Satu'],
            ['id' => 2, 'nama' => 'Instansi Dua'],
        ]);
        DB::table('t_logger')->insert([
            ['id_logger' => 'L1', 'instansi_id' => 1, 'nama_logger' => 'Logger 1'],
            ['id_logger' => 'L3', 'instansi_id' => 2, 'nama_logger' => 'Logger 3'],
        ]);
    }

    private function actor(string $level, ?int $instansiId): t_User
    {
        $id = DB::table('t_user')->insertGetId([
            'nama' => $level,
            'username' => 'actor_' . $level . '_' . uniqid(),
            'password' => 'x',
            'level_user' => $level,
            'instansi_id' => $instansiId,
            'status' => 'aktif',
        ]);

        return t_User::query()->findOrFail($id);
    }

    private function storeRequest(array $overrides): Request
    {
        return Request::create('/users', 'POST', array_merge([
            'nama' => 'RND',
            'username' => 'rnd_' . uniqid(),
            'password' => 'secret123',
            'level_user' => 'pegawai',
            'instansi_id' => 1,
        ], $overrides));
    }

    public function test_superadmin_can_grant_cross_instansi_loggers_to_pegawai(): void
    {
        $this->actingAs($this->actor('superadmin', null));

        $request = $this->storeRequest([
            'level_user' => 'pegawai',
            'instansi_id' => 1,
            'logger_access' => ['L1', 'L3'], // L3 is cross-instansi
        ]);

        (new UserController())->store($request);

        $user = t_User::query()->where('nama', 'RND')->firstOrFail();
        $granted = DB::table('user_logger_access')
            ->where('user_id', $user->id_user)
            ->pluck('logger_id')->sort()->values()->all();

        $this->assertSame(['L1', 'L3'], $granted);
    }

    public function test_superadmin_can_grant_extra_loggers_to_instansi_admin(): void
    {
        $this->actingAs($this->actor('superadmin', null));

        $request = $this->storeRequest([
            'level_user' => 'instansi_admin',
            'instansi_id' => 1,
            'logger_access' => ['L3'], // extra cross-instansi logger
        ]);

        (new UserController())->store($request);

        $user = t_User::query()->where('nama', 'RND')->firstOrFail();
        $granted = DB::table('user_logger_access')
            ->where('user_id', $user->id_user)
            ->pluck('logger_id')->all();

        $this->assertSame(['L3'], $granted);
    }

    public function test_instansi_admin_target_with_empty_logger_access_saves_clean(): void
    {
        $this->actingAs($this->actor('superadmin', null));

        $request = $this->storeRequest([
            'level_user' => 'instansi_admin',
            'instansi_id' => 1,
            'logger_access' => [],
        ]);

        (new UserController())->store($request);

        $user = t_User::query()->where('nama', 'RND')->firstOrFail();

        $this->assertSame(
            0,
            DB::table('user_logger_access')->where('user_id', $user->id_user)->count()
        );
    }

    public function test_pegawai_without_logger_is_rejected(): void
    {
        $this->actingAs($this->actor('superadmin', null));

        $request = $this->storeRequest([
            'level_user' => 'pegawai',
            'instansi_id' => 1,
            'logger_access' => [],
        ]);

        $this->expectException(ValidationException::class);
        (new UserController())->store($request);
    }

    public function test_instansi_admin_actor_cannot_grant_cross_instansi(): void
    {
        $this->actingAs($this->actor('instansi_admin', 1));

        $request = $this->storeRequest([
            'level_user' => 'pegawai',
            'instansi_id' => 1,
            'logger_access' => ['L3'], // L3 is instansi 2 — outside actor's authority
        ]);

        $this->expectException(ValidationException::class);
        (new UserController())->store($request);
    }
}
