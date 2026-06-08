<?php

namespace Tests\Feature;

use App\Http\Controllers\DeviceController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

/**
 * When an operator picks 50 sensors, the device allocation engine must route
 * the logger to the t_s50 family and provision a 50-column shard — not collapse
 * it to the legacy 16/19 binary. Existing 16/19 allocation must be unchanged.
 */
class DeviceController50FamilyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ts_table_pool', 't_logger', 't_s16_01', 't_s19_01', 't_s50_01'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->string('tabel_main')->nullable();
            $table->unsignedTinyInteger('sensor_count')->nullable();
        });

        Schema::create('ts_table_pool', function (Blueprint $table) {
            $table->string('table_name', 64)->primary();
            $table->unsignedTinyInteger('sensor_count');
            $table->unsignedTinyInteger('max_logger')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
        });
    }

    private function allocate(int $sensorCount): string
    {
        $controller = app(DeviceController::class);
        $method = new ReflectionMethod($controller, 'allocateMainTableForSensorCount');
        $method->setAccessible(true);

        return $method->invoke($controller, $sensorCount, null, null);
    }

    public function test_allocates_t_s50_shard_with_50_columns_for_50_sensors(): void
    {
        $table = $this->allocate(50);

        $this->assertSame('t_s50_01', $table);
        $this->assertTrue(Schema::hasTable('t_s50_01'));
        $this->assertTrue(Schema::hasColumn('t_s50_01', 'sensor50'), 't_s50_01 must have sensor50 column');
        $this->assertSame(
            50,
            (int) DB::table('ts_table_pool')->where('table_name', 't_s50_01')->value('sensor_count'),
            'pool entry must record sensor_count=50'
        );
    }

    public function test_legacy_16_and_19_allocation_unchanged(): void
    {
        $this->assertSame('t_s16_01', $this->allocate(16));
        $this->assertTrue(Schema::hasColumn('t_s16_01', 'sensor16'));
        $this->assertFalse(Schema::hasColumn('t_s16_01', 'sensor17'), '16-family must not have sensor17');

        $this->assertSame('t_s19_01', $this->allocate(19));
        $this->assertTrue(Schema::hasColumn('t_s19_01', 'sensor19'));
        $this->assertFalse(Schema::hasColumn('t_s19_01', 'sensor20'), '19-family must not have sensor20');
    }
}
