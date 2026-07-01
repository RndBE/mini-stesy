<?php

namespace Tests\Feature;

use App\Http\Middleware\AuditLogMiddleware;
use App\Models\t_User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class BerandaAfmrPressureCardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withoutMiddleware(AuditLogMiddleware::class);

        foreach ([
            'afmr_contact_data',
            'afmr_noncontact_data',
            'nonjiat_data',
            'jiat_data',
            'temp_s50_latest',
            'temp_s19_latest',
            'temp_s16_latest',
            'parameter_sensor',
            't_logger',
            'kategori_logger',
            't_lokasi',
            't_user',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->string('status')->default('aktif');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->unsignedTinyInteger('decimal_places')->nullable();
        });

        Schema::create('t_lokasi', function (Blueprint $table) {
            $table->increments('idlokasi');
            $table->string('nama_lokasi');
        });

        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->increments('id_katlogger');
            $table->string('nama_kategori');
            $table->string('kepanjangan')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger')->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
            $table->string('tabel_main');
            $table->integer('jeda_notif')->default(1);
            $table->unsignedInteger('idlokasi')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedTinyInteger('sensor_count')->default(16);
            $table->string('status_perbaikan')->nullable();
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id');
            $table->string('nama_parameter');
            $table->string('kolom_sensor');
            $table->string('satuan')->nullable();
            $table->string('tipe_graf')->nullable();
            $table->string('icon_app')->nullable();
            $table->string('debit_awlr')->nullable();
            $table->string('parameter_utama')->nullable();
            $table->unsignedInteger('parameter_group_id')->nullable();
        });

        $this->createLatestTable('temp_s16_latest', 16);
        $this->createLatestTable('temp_s19_latest', 19);
        $this->createLatestTable('temp_s50_latest', 50);

        foreach (['jiat_data', 'nonjiat_data', 'afmr_contact_data', 'afmr_noncontact_data'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->string('id_logger');
            });
        }
    }

    public function test_afmr_contact_dashboard_shows_single_and_double_pressure_cards(): void
    {
        $user = t_User::create([
            'nama' => 'Super Admin',
            'username' => 'super',
            'password' => 'secret',
            'level_user' => 'superadmin',
        ]);

        DB::table('kategori_logger')->insert([
            'id_katlogger' => 3,
            'nama_kategori' => 'AFMR',
            'kepanjangan' => 'Automatic Flow Meter Recorder',
        ]);

        DB::table('t_lokasi')->insert([
            ['idlokasi' => 1, 'nama_lokasi' => 'DMA 6 Mojolaban'],
            ['idlokasi' => 2, 'nama_lokasi' => 'DMA 9 Outlet Plesungan'],
        ]);

        DB::table('t_logger')->insert([
            [
                'id_logger' => '10376',
                'nama_logger' => 'DMA 6 MOJOLABAN',
                'tabel_main' => 't_s16_02',
                'idlokasi' => 1,
                'id_katlogger' => 3,
                'sensor_count' => 16,
            ],
            [
                'id_logger' => '10368',
                'nama_logger' => 'DMA 9 OUTLET',
                'tabel_main' => 't_s16_03',
                'idlokasi' => 2,
                'id_katlogger' => 3,
                'sensor_count' => 16,
            ],
        ]);

        DB::table('afmr_contact_data')->insert([
            ['id_logger' => '10376'],
            ['id_logger' => '10368'],
        ]);

        DB::table('temp_s16_latest')->insert([
            [
                'id_logger' => '10376',
                'waktu' => now(),
                'sensor1' => 0.5,
                'sensor2' => 10,
                'sensor3' => 0,
                'sensor4' => 100,
                'sensor6' => 2.4,
                'sensor7' => null,
                'sensor13' => 1,
                'sensor14' => 20,
                'sensor15' => 12,
                'sensor16' => 30,
                'is_online' => true,
            ],
            [
                'id_logger' => '10368',
                'waktu' => now(),
                'sensor1' => 0.7,
                'sensor2' => 12,
                'sensor3' => 0,
                'sensor4' => 100,
                'sensor6' => 1.1,
                'sensor7' => 1.2,
                'sensor13' => 1,
                'sensor14' => 21,
                'sensor15' => 12.1,
                'sensor16' => 31,
                'is_online' => true,
            ],
        ]);

        $parameters = collect([
            ['logger_id' => '10376', 'nama_parameter' => 'Flowrate', 'kolom_sensor' => 'sensor1', 'satuan' => 'liter/s'],
            ['logger_id' => '10376', 'nama_parameter' => 'Totalizer', 'kolom_sensor' => 'sensor2', 'satuan' => 'm3'],
            ['logger_id' => '10376', 'nama_parameter' => 'Fault', 'kolom_sensor' => 'sensor3', 'satuan' => null],
            ['logger_id' => '10376', 'nama_parameter' => 'Flowmeter Battery', 'kolom_sensor' => 'sensor4', 'satuan' => '%', 'parameter_utama' => 'flowmeter_battery'],
            ['logger_id' => '10376', 'nama_parameter' => 'Pressure', 'kolom_sensor' => 'sensor6', 'satuan' => 'bar'],
            ['logger_id' => '10376', 'nama_parameter' => 'Humidity Logger', 'kolom_sensor' => 'sensor14', 'satuan' => '%', 'parameter_utama' => 'humidity_logger'],
            ['logger_id' => '10376', 'nama_parameter' => 'Battery Logger', 'kolom_sensor' => 'sensor15', 'satuan' => 'volt', 'parameter_utama' => 'battery_logger'],
            ['logger_id' => '10376', 'nama_parameter' => 'Temperature Logger', 'kolom_sensor' => 'sensor16', 'satuan' => 'C', 'parameter_utama' => 'temperature_logger'],
            ['logger_id' => '10368', 'nama_parameter' => 'Flowrate', 'kolom_sensor' => 'sensor1', 'satuan' => 'liter/s'],
            ['logger_id' => '10368', 'nama_parameter' => 'Totalizer', 'kolom_sensor' => 'sensor2', 'satuan' => 'm3'],
            ['logger_id' => '10368', 'nama_parameter' => 'Fault', 'kolom_sensor' => 'sensor3', 'satuan' => null],
            ['logger_id' => '10368', 'nama_parameter' => 'Flowmeter Battery', 'kolom_sensor' => 'sensor4', 'satuan' => '%', 'parameter_utama' => 'flowmeter_battery'],
            ['logger_id' => '10368', 'nama_parameter' => 'Pressure 1', 'kolom_sensor' => 'sensor6', 'satuan' => 'bar', 'parameter_utama' => 'pressure_1'],
            ['logger_id' => '10368', 'nama_parameter' => 'Pressure 2', 'kolom_sensor' => 'sensor7', 'satuan' => 'bar', 'parameter_utama' => 'pressure_2'],
            ['logger_id' => '10368', 'nama_parameter' => 'Humidity Logger', 'kolom_sensor' => 'sensor14', 'satuan' => '%', 'parameter_utama' => 'humidity_logger'],
            ['logger_id' => '10368', 'nama_parameter' => 'Battery Logger', 'kolom_sensor' => 'sensor15', 'satuan' => 'volt', 'parameter_utama' => 'battery_logger'],
            ['logger_id' => '10368', 'nama_parameter' => 'Temperature Logger', 'kolom_sensor' => 'sensor16', 'satuan' => 'C', 'parameter_utama' => 'temperature_logger'],
        ])
            ->map(fn (array $row) => array_merge(['satuan' => null, 'parameter_utama' => null], $row))
            ->all();

        DB::table('parameter_sensor')->insert($parameters);

        $html = $this->actingAs($user)->get(route('beranda'))->assertOk()->getContent();

        $this->assertStringContainsString(route('analisa.index', '10376') . '?parameter=Pressure', $html);
        $this->assertStringContainsString(route('analisa.index', '10368') . '?parameter=Pressure+1', $html);
        $this->assertStringContainsString(route('analisa.index', '10368') . '?parameter=Pressure+2', $html);
    }

    private function createLatestTable(string $tableName, int $sensorCount): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($sensorCount) {
            $table->increments('id');
            $table->string('id_logger');
            $table->dateTime('waktu')->nullable();
            for ($i = 1; $i <= $sensorCount; $i++) {
                $table->double('sensor' . $i)->nullable();
            }
            $table->boolean('is_online')->default(true);
        });
    }
}
