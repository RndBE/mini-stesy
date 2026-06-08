<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Ingestion must accept and persist all sensors for a 50-sensor logger
 * (family t_s50), not silently cap at 19. Uses the repo's manual-schema
 * pattern (no RefreshDatabase) because the real timeseries migration uses
 * MySQL-only triggers/views that sqlite cannot run.
 */
class DataMasuk50SensorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['t_logger', 't_s50_01', 'temp_s50_latest', 't_s19_01', 'temp_s19_latest'] as $t) {
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

        $this->createWideTable('t_s50_01', 50, withAutoId: true);
        $this->createWideTable('temp_s50_latest', 50, withAutoId: false);
        $this->createWideTable('t_s19_01', 19, withAutoId: true);
        $this->createWideTable('temp_s19_latest', 19, withAutoId: false);
    }

    private function createWideTable(string $name, int $sensors, bool $withAutoId): void
    {
        Schema::create($name, function (Blueprint $table) use ($sensors, $withAutoId) {
            if ($withAutoId) {
                $table->bigIncrements('id');
                $table->string('id_logger', 15);
            } else {
                $table->string('id_logger', 15)->primary();
            }
            $table->dateTime('waktu');
            for ($i = 1; $i <= $sensors; $i++) {
                $table->float('sensor' . $i)->nullable();
            }
            if (!$withAutoId) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function test_ingestion_persists_all_50_sensors_for_t_s50_logger(): void
    {
        DB::table('t_logger')->insert([
            'id_logger'    => '99001',
            'nama_logger'  => 'Logger 50 Sensor',
            'tabel_main'   => 't_s50_01',
            'sensor_count' => 50,
        ]);

        $payload = ['id_alat' => '99001', 'waktu' => '2026-06-08 10:00:00'];
        for ($i = 1; $i <= 50; $i++) {
            $payload['sensor' . $i] = $i; // distinct, easy to assert
        }

        $response = $this->postJson('/api/datamasuk', $payload);

        $response->assertOk()
            ->assertJson([
                'success'    => true,
                'table_main' => 't_s50_01',
                'table_temp' => 'temp_s50_latest',
            ]);

        $row = DB::table('t_s50_01')->where('id_logger', '99001')->first();
        $this->assertNotNull($row, 'Row was not inserted into t_s50_01');
        $this->assertEquals(1, $row->sensor1);
        $this->assertEquals(19, $row->sensor19);
        $this->assertEquals(20, $row->sensor20, 'sensor20 must not be dropped');
        $this->assertEquals(50, $row->sensor50, 'sensor50 must not be dropped');

        $snap = DB::table('temp_s50_latest')->where('id_logger', '99001')->first();
        $this->assertNotNull($snap, 'Latest snapshot was not upserted into temp_s50_latest');
        $this->assertEquals(50, $snap->sensor50);
    }

    public function test_19_sensor_logger_still_routes_to_t_s19(): void
    {
        DB::table('t_logger')->insert([
            'id_logger'    => '18001',
            'nama_logger'  => 'Logger 19 Sensor',
            'tabel_main'   => 't_s19_01',
            'sensor_count' => 19,
        ]);

        $payload = ['id_alat' => '18001', 'waktu' => '2026-06-08 10:00:00'];
        for ($i = 1; $i <= 19; $i++) {
            $payload['sensor' . $i] = $i;
        }

        $this->postJson('/api/datamasuk', $payload)
            ->assertOk()
            ->assertJson(['success' => true, 'table_main' => 't_s19_01', 'table_temp' => 'temp_s19_latest']);

        $row = DB::table('t_s19_01')->where('id_logger', '18001')->first();
        $this->assertEquals(19, $row->sensor19);
    }
}
