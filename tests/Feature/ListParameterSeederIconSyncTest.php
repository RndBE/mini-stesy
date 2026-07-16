<?php

namespace Tests\Feature;

use Database\Seeders\ListParameterSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ListParameterSeederIconSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['parameter_sensor', 't_logger', 'kategori_logger', 'list_parameter'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('list_parameter', function (Blueprint $table) {
            $table->id();
            $table->string('nama_parameter')->unique();
            $table->string('parameter_utama')->nullable();
            $table->string('default_satuan')->nullable();
            $table->string('default_kolom_sensor')->nullable();
            $table->unsignedBigInteger('default_parameter_group_id')->nullable();
            $table->string('icon_app')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->increments('id_katlogger');
            $table->string('nama_kategori');
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->id();
            $table->string('id_logger')->unique();
            $table->unsignedInteger('id_katlogger');
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id');
            $table->string('parameter_utama')->nullable();
            $table->string('icon_app')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (['parameter_sensor', 't_logger', 'kategori_logger', 'list_parameter'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_seeder_preserves_valid_overrides_and_applies_apms_specific_icons(): void
    {
        DB::table('kategori_logger')->insert([
            ['id_katlogger' => 1, 'nama_kategori' => 'AWLR'],
            ['id_katlogger' => 5, 'nama_kategori' => 'AWQR'],
            ['id_katlogger' => 6, 'nama_kategori' => 'APMS'],
        ]);

        DB::table('t_logger')->insert([
            ['id_logger' => 'AWLR-01', 'id_katlogger' => 1],
            ['id_logger' => 'AWQR-01', 'id_katlogger' => 5],
            ['id_logger' => 'APMS-01', 'id_katlogger' => 6],
        ]);

        DB::table('parameter_sensor')->insert([
            [
                'logger_id' => 'APMS-01',
                'parameter_utama' => 'ph_tanah',
                'icon_app' => 'icons/awgr/ph_air.svg',
            ],
            [
                'logger_id' => 'APMS-01',
                'parameter_utama' => 'kelembaban_tanah',
                'icon_app' => 'icons/beranda/humidity_online.svg',
            ],
            [
                'logger_id' => 'APMS-01',
                'parameter_utama' => 'temperature_tanah',
                'icon_app' => 'icons/beranda/temper_online.svg',
            ],
            [
                'logger_id' => 'APMS-01',
                'parameter_utama' => 'salinity',
                'icon_app' => 'icons/awgr/salinity.svg',
            ],
            [
                'logger_id' => 'AWQR-01',
                'parameter_utama' => 'salinity',
                'icon_app' => 'icons/awgr/salinity.svg',
            ],
            [
                'logger_id' => 'AWLR-01',
                'parameter_utama' => 'battery_logger',
                'icon_app' => 'icons/custom/battery.svg',
            ],
            [
                'logger_id' => 'AWLR-01',
                'parameter_utama' => 'tma',
                'icon_app' => 'water',
            ],
        ]);

        $this->assertTrue(Schema::hasColumn('t_logger', 'id_katlogger'));
        $this->assertTrue(Schema::hasColumn('parameter_sensor', 'parameter_utama'));
        $this->assertTrue(Schema::hasColumn('parameter_sensor', 'icon_app'));
        $this->assertSame(
            6,
            DB::table('kategori_logger')->where('nama_kategori', 'APMS')->value('id_katlogger')
        );
        $this->assertSame(
            ['APMS-01'],
            DB::table('t_logger')->where('id_katlogger', 6)->pluck('id_logger')->all()
        );

        app(ListParameterSeeder::class)->run();

        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'APMS-01',
            'parameter_utama' => 'ph_tanah',
            'icon_app' => 'icons/apms/ph_tanah.svg',
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'APMS-01',
            'parameter_utama' => 'kelembaban_tanah',
            'icon_app' => 'icons/apms/soil_moisture.svg',
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'APMS-01',
            'parameter_utama' => 'temperature_tanah',
            'icon_app' => 'icons/apms/soil_temperature.svg',
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'APMS-01',
            'parameter_utama' => 'salinity',
            'icon_app' => 'icons/apms/soil_salinity.svg',
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'AWQR-01',
            'parameter_utama' => 'salinity',
            'icon_app' => 'icons/awgr/salinity.svg',
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'AWLR-01',
            'parameter_utama' => 'battery_logger',
            'icon_app' => 'icons/custom/battery.svg',
        ]);
        $this->assertDatabaseHas('parameter_sensor', [
            'logger_id' => 'AWLR-01',
            'parameter_utama' => 'tma',
            'icon_app' => 'icons/awlr/elevasi_muka_air.svg',
        ]);
    }
}
