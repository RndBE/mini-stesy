<?php

namespace Tests\Feature;

use App\Http\Controllers\AnalisaController;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalisaRainfallLegendTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('t_s16_01');
        Schema::dropIfExists('klasifikasi_hujan');
        Schema::dropIfExists('klasifikasi_threshold');
        Schema::dropIfExists('parameter_sensor');
        Schema::dropIfExists('t_logger');
        Schema::dropIfExists('t_user');
        Schema::dropIfExists('kategori_logger');

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->increments('id_katlogger');
            $table->string('nama_kategori');
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
            $table->string('tabel_main');
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedTinyInteger('sensor_count');
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id', 15);
            $table->string('nama_parameter');
            $table->string('kolom_sensor');
            $table->string('satuan')->nullable();
            $table->string('tipe_graf')->nullable();
        });

        Schema::create('klasifikasi_hujan', function (Blueprint $table) {
            $table->increments('id_klasifikasi');
            $table->string('logger_id', 15);
            $table->string('waktu', 10);
            $table->string('debit_air');
            $table->string('intensitas');
        });

        Schema::create('klasifikasi_threshold', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_kategori');
            $table->string('state_key');
            $table->string('state_label');
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->string('icon_path')->nullable();
            $table->string('color_hex')->nullable();
            $table->integer('sort_order')->default(0);
        });

        Schema::create('t_s16_01', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');
            for ($i = 1; $i <= 16; $i++) {
                $table->float('sensor' . $i)->nullable();
            }
        });
    }

    public function test_rainfall_legend_falls_back_to_category_thresholds_when_logger_rows_are_missing(): void
    {
        $this->actingAs(t_User::create([
            'nama' => 'Super Admin',
            'username' => 'super',
            'password' => 'secret',
            'level_user' => 'superadmin',
        ]));

        DB::table('kategori_logger')->insert([
            'id_katlogger' => 2,
            'nama_kategori' => 'ARR',
        ]);

        DB::table('t_logger')->insert([
            'id_logger' => '20092',
            'nama_logger' => 'ARR Demo DKI',
            'tabel_main' => 't_s16_01',
            'id_katlogger' => 2,
            'sensor_count' => 16,
        ]);

        DB::table('parameter_sensor')->insert([
            'logger_id' => '20092',
            'nama_parameter' => 'Curah Hujan',
            'kolom_sensor' => 'sensor8',
            'satuan' => 'mm',
            'tipe_graf' => null,
        ]);

        DB::table('klasifikasi_threshold')->insert([
            ['id_kategori' => 2, 'state_key' => 'tidak_hujan', 'state_label' => 'Tidak Hujan', 'min_value' => null, 'max_value' => 0.10, 'sort_order' => 1],
            ['id_kategori' => 2, 'state_key' => 'hujan_sangat_ringan', 'state_label' => 'Hujan Sangat Ringan', 'min_value' => 0.10, 'max_value' => 1.00, 'sort_order' => 2],
            ['id_kategori' => 2, 'state_key' => 'hujan_ringan', 'state_label' => 'Hujan Ringan', 'min_value' => 1.00, 'max_value' => 2.50, 'sort_order' => 3],
        ]);

        DB::table('t_s16_01')->insert([
            [
                'id_logger' => '20092',
                'waktu' => '2026-06-08 09:00:00',
                'sensor8' => 0.03,
            ],
            [
                'id_logger' => '20092',
                'waktu' => '2026-06-08 09:15:00',
                'sensor8' => 0.02,
            ],
        ]);

        $request = Request::create('/api/analisa/data/20092', 'GET', [
            'parameter' => 'Curah Hujan',
            'range' => 'day',
            'date' => '2026-06-08',
        ]);

        $payload = app(AnalisaController::class)->getChartData($request, '20092')->getData(true);

        $this->assertSame('bar', $payload['tipe_graf']);
        $this->assertEquals(0.05, $payload['chartData'][9]);
        $this->assertEquals(0.05, $payload['tableData'][9]['rerata']);
        $this->assertEquals(0.05, $payload['akumulasi']);
        $this->assertEquals([
            ['debit_air' => 0.0, 'intensitas' => 'Tidak Hujan'],
            ['debit_air' => 0.1, 'intensitas' => 'Hujan Sangat Ringan'],
            ['debit_air' => 1.0, 'intensitas' => 'Hujan Ringan'],
        ], $payload['klasifikasi']);
    }
}
