<?php

namespace Tests\Feature;

use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The mobile read APIs (Realtime, Analisa) must accept a logger whose storage
 * is the t_s50 family and read its real table — not reject the table name and
 * fall back to t_s16/t_s19. Manual-schema pattern (no RefreshDatabase).
 */
class Api50SensorReadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            't_user', 't_logger', 'parameter_sensor', 't_s16_01', 't_s19_01', 't_s50_01',
            't_lokasi', 'kategori_logger', 'jiat_data', 'nonjiat_data',
            'temp_s16_latest', 'temp_s19_latest', 'temp_s50_latest',
        ] as $t) {
            Schema::dropIfExists($t);
        }

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
            $table->string('tabel_main')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedInteger('idlokasi')->nullable();
            $table->unsignedTinyInteger('sensor_count')->nullable();
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id', 15);
            $table->string('nama_parameter');
            $table->string('kolom_sensor');
            $table->string('satuan')->nullable();
            $table->string('tipe_graf')->nullable();
            $table->string('parameter_utama')->nullable();
            $table->string('icon_app')->nullable();
        });

        $this->wide('t_s16_01', 16);
        $this->wide('t_s19_01', 19);
        $this->wide('t_s50_01', 50);

        // Relations eager-loaded by devices() (may stay empty; presence is enough).
        Schema::create('t_lokasi', function (Blueprint $table) {
            $table->increments('idlokasi');
            $table->string('nama_lokasi')->nullable();
        });
        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->increments('id_katlogger');
            $table->string('nama_kategori')->nullable();
        });
        Schema::create('jiat_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
        });
        Schema::create('nonjiat_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
        });

        $this->tempLatest('temp_s16_latest', 16);
        $this->tempLatest('temp_s19_latest', 19);
        $this->tempLatest('temp_s50_latest', 50);
    }

    private function wide(string $name, int $sensors): void
    {
        Schema::create($name, function (Blueprint $table) use ($sensors) {
            $table->bigIncrements('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');
            for ($i = 1; $i <= $sensors; $i++) {
                $table->float('sensor' . $i)->nullable();
            }
        });
    }

    private function tempLatest(string $name, int $sensors): void
    {
        Schema::create($name, function (Blueprint $table) use ($sensors) {
            $table->string('id_logger', 15)->primary();
            $table->dateTime('waktu');
            for ($i = 1; $i <= $sensors; $i++) {
                $table->float('sensor' . $i)->nullable();
            }
            $table->timestamp('updated_at')->nullable();
        });
    }

    private function seedLogger50(): void
    {
        DB::table('t_logger')->insert([
            'id_logger'    => '99001',
            'nama_logger'  => 'Logger 50 Sensor',
            'tabel_main'   => 't_s50_01',
            'sensor_count' => 50,
        ]);

        DB::table('parameter_sensor')->insert([
            'logger_id'       => '99001',
            'nama_parameter'  => 'Sensor 50',
            'kolom_sensor'    => 'sensor50',
            'satuan'          => 'x',
            'parameter_utama' => 'sensor50',
        ]);

        $row = ['id_logger' => '99001', 'waktu' => now()->format('Y-m-d H:i:s')];
        for ($i = 1; $i <= 50; $i++) {
            $row['sensor' . $i] = $i;
        }
        DB::table('t_s50_01')->insert($row);
    }

    private function superadmin(): t_User
    {
        return t_User::create([
            'nama'       => 'Super',
            'username'   => 'super',
            'password'   => 'secret',
            'level_user' => 'superadmin',
            'status'     => 'aktif',
        ]);
    }

    public function test_realtime_reads_t_s50_table_for_50_sensor_logger(): void
    {
        $this->seedLogger50();

        $response = $this->actingAs($this->superadmin(), 'sanctum')
            ->getJson('/api/v1/mobile/realtime/data/99001');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('device.tabel_main', 't_s50_01');

        $this->assertNotEmpty($response->json('data'), 'Realtime returned no rows for the t_s50 logger');
        $this->assertEquals(50, $response->json('data.0.sensor50'));
    }

    public function test_realtime_devices_marks_50_sensor_logger_online_from_temp50(): void
    {
        DB::table('t_logger')->insert([
            'id_logger'    => '99001',
            'nama_logger'  => 'Logger 50 Sensor',
            'tabel_main'   => 't_s50_01',
            'sensor_count' => 50,
        ]);
        DB::table('temp_s50_latest')->insert([
            'id_logger' => '99001',
            'waktu'     => now()->format('Y-m-d H:i:s'),
            'sensor1'   => 1,
        ]);

        $response = $this->actingAs($this->superadmin(), 'sanctum')
            ->getJson('/api/v1/mobile/realtime/devices');

        $response->assertOk()->assertJson(['success' => true]);

        $device = collect($response->json('data'))->firstWhere('id_logger', '99001');
        $this->assertNotNull($device, 'Logger missing from devices list');
        $this->assertSame('online', $device['status'], '50-sensor logger with a fresh temp_s50_latest row must read online, not offline');
    }

    public function test_analisa_reads_t_s50_table_for_50_sensor_logger(): void
    {
        $this->seedLogger50();

        $response = $this->actingAs($this->superadmin(), 'sanctum')
            ->getJson('/api/v1/mobile/analisa/99001/data');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('meta.tabel', 't_s50_01');

        $this->assertEquals(50, $response->json('data.0.sensor50'));
    }

    public function test_data_perangkat_list_marks_50_sensor_logger_online_and_50_count(): void
    {
        DB::table('t_logger')->insert([
            'id_logger'    => '99001',
            'nama_logger'  => 'Logger 50 Sensor',
            'tabel_main'   => 't_s50_01',
            'sensor_count' => 50,
        ]);
        DB::table('temp_s50_latest')->insert([
            'id_logger' => '99001',
            'waktu'     => now()->format('Y-m-d H:i:s'),
            'sensor1'   => 1,
        ]);

        $response = $this->actingAs($this->superadmin(), 'sanctum')
            ->getJson('/api/v1/mobile/data-perangkat');

        $response->assertOk()->assertJson(['success' => true]);
        $device = collect($response->json('data'))->firstWhere('id_logger', '99001');
        $this->assertNotNull($device, 'Logger missing from data-perangkat list');
        $this->assertSame('online', $device['status'], '50-sensor logger must read online from temp_s50_latest');
        $this->assertSame(50, $device['sensor_count']);
    }

    public function test_getdata_emits_all_50_columns_for_50_sensor_logger(): void
    {
        $this->seedLogger50();

        $response = $this->getJson('/api/data-masuk?logger_id=99001&tanggal=' . now()->toDateString());

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertCount(50, $response->json('columns'), 'getData must emit 50 sensor columns, not 16');
    }
}
