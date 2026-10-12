<?php

namespace Tests\Feature;

use App\Models\t_User;
use App\Services\PumpMqttCommandService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FakeWebPumpMqttCommandService extends PumpMqttCommandService
{
    public array $calls = [];

    public function send(string $idLogger, string $action): array
    {
        $this->calls[] = compact('idLogger', 'action');

        return [
            'status' => 'OK',
            'state' => $action === 'off' ? 0 : 1,
            'msg' => $action === 'off' ? 'Pump OFF' : 'Pump ON',
        ];
    }
}

class WebPumpControlPinTest extends TestCase
{
    private FakeWebPumpMqttCommandService $pumpService;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'pump_control_logs',
            'user_logger_access',
            'jiat_data',
            't_logger',
            't_user',
            'instansi',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
            $table->string('control_pin_hash')->nullable();
            $table->boolean('control_pin_enabled')->default(false);
            $table->timestamp('control_pin_updated_at')->nullable();
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger')->nullable();
        });

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15);
        });

        Schema::create('jiat_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->boolean('has_pump')->default(false);
        });

        Schema::create('pump_control_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('id_logger', 15);
            $table->string('action', 10);
            $table->string('status', 30);
            $table->text('message')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_permission_status', 30)->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->pumpService = new FakeWebPumpMqttCommandService();
        $this->app->instance(PumpMqttCommandService::class, $this->pumpService);
    }

    public function test_on_off_rejected_before_pin_is_verified(): void
    {
        $user = $this->seedControllableLogger(controlPin: '123456');

        $response = $this->actingAs($user)->postJson('/api/pump/command', [
            'id_logger' => '99001',
            'action' => 'on',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('step', 'pin_required');

        $this->assertSame([], $this->pumpService->calls);
    }

    public function test_wrong_pin_is_rejected_and_logged_and_does_not_unlock(): void
    {
        $user = $this->seedControllableLogger(controlPin: '123456');

        $this->actingAs($user)->postJson('/api/pump/verify-pin', [
            'id_logger' => '99001',
            'control_pin' => '000000',
        ])->assertForbidden()
            ->assertJsonPath('message', 'PIN kontrol tidak sesuai.');

        $this->assertDatabaseHas('pump_control_logs', [
            'user_id' => $user->id_user,
            'id_logger' => '99001',
            'status' => 'pin_failed',
        ]);

        $this->postJson('/api/pump/command', [
            'id_logger' => '99001',
            'action' => 'on',
        ])->assertForbidden()->assertJsonPath('step', 'pin_required');

        $this->assertSame([], $this->pumpService->calls);
    }

    public function test_correct_pin_unlocks_control_and_logs_success(): void
    {
        $user = $this->seedControllableLogger(controlPin: '123456');

        $this->actingAs($user)->postJson('/api/pump/verify-pin', [
            'id_logger' => '99001',
            'control_pin' => '123456',
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/pump/command', [
            'id_logger' => '99001',
            'action' => 'on',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pump.state', 1);

        $this->assertSame([
            ['idLogger' => '99001', 'action' => 'on'],
        ], $this->pumpService->calls);

        $this->assertDatabaseHas('pump_control_logs', [
            'user_id' => $user->id_user,
            'id_logger' => '99001',
            'action' => 'on',
            'status' => 'success',
        ]);
    }

    public function test_unlock_expires(): void
    {
        $user = $this->seedControllableLogger(controlPin: '123456');

        $this->actingAs($user)->postJson('/api/pump/verify-pin', [
            'id_logger' => '99001',
            'control_pin' => '123456',
        ])->assertOk();

        $this->travel(6)->minutes();

        $this->postJson('/api/pump/command', [
            'id_logger' => '99001',
            'action' => 'off',
        ])->assertForbidden()->assertJsonPath('step', 'pin_required');

        $this->assertSame([], $this->pumpService->calls);
    }

    public function test_status_check_does_not_need_pin(): void
    {
        $user = $this->seedControllableLogger(controlPin: '123456');

        $this->actingAs($user)->postJson('/api/pump/command', [
            'id_logger' => '99001',
            'action' => 'get',
        ])->assertOk()->assertJsonPath('success', true);
    }

    public function test_instansi_without_pin_cannot_unlock(): void
    {
        $user = $this->seedControllableLogger(controlPin: null);

        $this->actingAs($user)->postJson('/api/pump/verify-pin', [
            'id_logger' => '99001',
            'control_pin' => '123456',
        ])->assertForbidden()
            ->assertJsonPath('message', 'PIN kontrol belum dikonfigurasi untuk instansi ini.');

        $this->assertSame([], $this->pumpService->calls);
    }

    public function test_user_outside_instansi_cannot_verify_or_control(): void
    {
        $this->seedControllableLogger(controlPin: '123456');

        $outsider = t_User::create([
            'nama' => 'Luar',
            'username' => 'outsider',
            'password' => bcrypt('password'),
            'level_user' => 'instansi_admin',
            'instansi_id' => 2,
            'status' => 'aktif',
        ]);

        $this->actingAs($outsider)->postJson('/api/pump/verify-pin', [
            'id_logger' => '99001',
            'control_pin' => '123456',
        ])->assertForbidden();

        $this->postJson('/api/pump/command', [
            'id_logger' => '99001',
            'action' => 'on',
        ])->assertForbidden();

        $this->assertSame([], $this->pumpService->calls);
    }

    private function seedControllableLogger(?string $controlPin): t_User
    {
        DB::table('instansi')->insert([
            'id' => 1,
            'nama' => 'Instansi Uji',
            'control_pin_hash' => $controlPin ? Hash::make($controlPin) : null,
            'control_pin_enabled' => $controlPin !== null,
            'control_pin_updated_at' => $controlPin ? now() : null,
        ]);

        $user = t_User::create([
            'nama' => 'Admin Instansi',
            'username' => 'admin_instansi',
            'password' => bcrypt('password'),
            'level_user' => 'instansi_admin',
            'instansi_id' => 1,
            'status' => 'aktif',
        ]);

        DB::table('t_logger')->insert([
            'id_logger' => '99001',
            'instansi_id' => 1,
            'nama_logger' => 'Logger Pompa',
        ]);

        DB::table('jiat_data')->insert([
            'id_logger' => '99001',
            'has_pump' => true,
        ]);

        return $user;
    }
}
