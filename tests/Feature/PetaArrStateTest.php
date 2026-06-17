<?php

namespace Tests\Feature;

use App\Http\Controllers\PetaController;
use App\Models\KlasifikasiThreshold;
use App\Models\t_Logger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class PetaArrStateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-06-17 10:30:00'));

        foreach (['t_logger', 'parameter_sensor', 'klasifikasi_threshold', 't_s16_77'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->string('nama_logger')->nullable();
            $table->string('tabel_main')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedTinyInteger('sensor_count')->nullable();
        });
        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id', 15);
            $table->string('nama_parameter');
            $table->string('kolom_sensor');
            $table->string('parameter_utama')->nullable();
        });
        Schema::create('klasifikasi_threshold', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_kategori');
            $table->string('state_key');
            $table->string('state_label');
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
        });
        Schema::create('t_s16_77', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');
            $table->double('sensor5')->nullable();
        });

        DB::table('t_logger')->insert([
            'id_logger' => '10002', 'nama_logger' => 'ARR Pogung', 'tabel_main' => 't_s16_77', 'id_katlogger' => 2, 'sensor_count' => 16,
        ]);
        DB::table('parameter_sensor')->insert([
            'logger_id' => '10002', 'nama_parameter' => 'Curah Hujan', 'kolom_sensor' => 'sensor5', 'parameter_utama' => 'hujan',
        ]);
        DB::table('klasifikasi_threshold')->insert([
            ['id_kategori' => 2, 'state_key' => 'tidak_hujan',         'state_label' => 'Tidak Hujan',         'min_value' => null, 'max_value' => 0.10,  'sort_order' => 1],
            ['id_kategori' => 2, 'state_key' => 'hujan_sangat_ringan', 'state_label' => 'Hujan Sangat Ringan', 'min_value' => 0.10, 'max_value' => 1.00,  'sort_order' => 2],
            ['id_kategori' => 2, 'state_key' => 'hujan_ringan',        'state_label' => 'Hujan Ringan',        'min_value' => 1.00, 'max_value' => 2.50,  'sort_order' => 3],
            ['id_kategori' => 2, 'state_key' => 'hujan_sedang',        'state_label' => 'Hujan Sedang',        'min_value' => 2.50, 'max_value' => 7.60,  'sort_order' => 4],
            ['id_kategori' => 2, 'state_key' => 'hujan_lebat',         'state_label' => 'Hujan Lebat',         'min_value' => 7.60, 'max_value' => 15.60, 'sort_order' => 5],
            ['id_kategori' => 2, 'state_key' => 'hujan_sangat_lebat',  'state_label' => 'Hujan Sangat Lebat',  'min_value' => 15.60, 'max_value' => null, 'sort_order' => 6],
        ]);
        // Current-hour readings sum to 8mm; latest instantaneous reading is 0.
        DB::table('t_s16_77')->insert([
            ['id_logger' => '10002', 'waktu' => '2026-06-17 10:10:00', 'sensor5' => 5],
            ['id_logger' => '10002', 'waktu' => '2026-06-17 10:20:00', 'sensor5' => 3],
            ['id_logger' => '10002', 'waktu' => '2026-06-17 10:29:00', 'sensor5' => 0],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function callState($latest): string
    {
        $logger = t_Logger::with('params')->where('id_logger', '10002')->first();
        $thresholds = KlasifikasiThreshold::where('id_kategori', 2)->orderBy('sort_order')->get();

        $m = new ReflectionMethod(PetaController::class, 'getStateFromThreshold');
        $m->setAccessible(true);
        return $m->invoke(app(PetaController::class), $logger, 'online', $latest, $thresholds);
    }

    public function test_arr_pin_uses_hourly_accumulation_not_instantaneous(): void
    {
        // Latest instantaneous reading is 0 (would classify as tidak_hujan),
        // but the running-hour accumulation is 8mm → Hujan Lebat.
        $latest = (object) ['sensor5' => 0];

        $this->assertSame('hujan_lebat', $this->callState($latest));
    }

    public function test_arr_pin_is_tidak_hujan_when_no_rain_this_hour(): void
    {
        DB::table('t_s16_77')->truncate();
        DB::table('t_s16_77')->insert([
            ['id_logger' => '10002', 'waktu' => '2026-06-17 10:15:00', 'sensor5' => 0],
        ]);

        $this->assertSame('tidak_hujan', $this->callState((object) ['sensor5' => 0]));
    }
}
