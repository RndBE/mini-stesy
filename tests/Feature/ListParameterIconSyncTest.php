<?php

namespace Tests\Feature;

use App\Models\ListParameter;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ListParameterIconSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['parameter_sensor', 'list_parameter', 't_user'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('level_user');
            $table->unsignedBigInteger('instansi_id')->nullable();
        });

        Schema::create('list_parameter', function (Blueprint $table) {
            $table->id();
            $table->string('nama_parameter')->unique();
            $table->string('parameter_utama')->nullable();
            $table->string('default_satuan')->nullable();
            $table->string('default_kolom_sensor')->nullable();
            $table->string('icon_app')->nullable();
            $table->unsignedBigInteger('default_parameter_group_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id');
            $table->string('nama_parameter');
            $table->string('parameter_utama')->nullable();
            $table->string('icon_app')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (['parameter_sensor', 'list_parameter', 't_user'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_updating_default_icon_syncs_matching_device_parameters_without_overwriting_custom_icons(): void
    {
        $oldIcon = 'icons/beranda/battery_online.svg';
        $newIcon = 'icons/awlr/debit.svg';
        $customIcon = 'icons/awgr/salinity.svg';

        $user = t_User::create([
            'nama' => 'Super Admin',
            'username' => 'superadmin-icon-sync',
            'password' => bcrypt('password'),
            'level_user' => 'superadmin',
        ]);

        $parameter = ListParameter::create([
            'nama_parameter' => 'Battery Logger',
            'parameter_utama' => 'battery_logger',
            'default_satuan' => 'Volt',
            'default_kolom_sensor' => 'sensor6',
            'icon_app' => $oldIcon,
            'is_active' => true,
        ]);

        DB::table('parameter_sensor')->insert([
            [
                'logger_id' => 'LOGGER-01',
                'nama_parameter' => 'battery_logger',
                'parameter_utama' => 'battery_logger',
                'icon_app' => $oldIcon,
            ],
            [
                'logger_id' => 'LOGGER-02',
                'nama_parameter' => 'battery_logger',
                'parameter_utama' => 'battery_logger',
                'icon_app' => null,
            ],
            [
                'logger_id' => 'LOGGER-03',
                'nama_parameter' => 'battery_logger',
                'parameter_utama' => 'battery_logger',
                'icon_app' => $customIcon,
            ],
            [
                'logger_id' => 'LOGGER-04',
                'nama_parameter' => 'temperature_logger',
                'parameter_utama' => 'temperature_logger',
                'icon_app' => $oldIcon,
            ],
        ]);

        $response = $this->actingAs($user)->put(route('list-parameter.update', $parameter), [
            'nama_parameter' => 'Battery Logger',
            'parameter_utama' => 'battery_logger',
            'default_satuan' => 'Volt',
            'default_kolom_sensor' => 'sensor6',
            'icon_app' => $newIcon,
            'default_parameter_group_id' => null,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('list-parameter.index'));

        $this->assertDatabaseHas('list_parameter', [
            'id' => $parameter->id,
            'icon_app' => $newIcon,
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'LOGGER-01',
            'icon_app' => $newIcon,
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'LOGGER-02',
            'icon_app' => $newIcon,
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'LOGGER-03',
            'icon_app' => $customIcon,
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'LOGGER-04',
            'icon_app' => $oldIcon,
        ]);
    }
}
