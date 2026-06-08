<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The t_s50 migration's portable parts (table creation + pool registration)
 * must run cleanly and be idempotent. Trigger/view are MySQL-only and skipped
 * here (sqlite), so we exercise everything else end-to-end.
 */
class TS50MigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['t_s50_01', 'temp_s50_latest', 'ts_table_pool', 't_logger'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
        });

        Schema::create('ts_table_pool', function (Blueprint $table) {
            $table->string('table_name', 64)->primary();
            $table->unsignedTinyInteger('sensor_count');
            $table->unsignedTinyInteger('max_logger')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_migration_provisions_50_family_and_is_idempotent(): void
    {
        $migration = require base_path('database/migrations/2026_06_08_120000_create_t_s50_family.php');

        $migration->up();

        $this->assertTrue(Schema::hasTable('t_s50_01'));
        $this->assertTrue(Schema::hasColumn('t_s50_01', 'sensor50'));
        $this->assertFalse(Schema::hasColumn('t_s50_01', 'sensor51'));

        $this->assertTrue(Schema::hasTable('temp_s50_latest'));
        $this->assertTrue(Schema::hasColumn('temp_s50_latest', 'sensor50'));

        $this->assertSame(
            50,
            (int) DB::table('ts_table_pool')->where('table_name', 't_s50_01')->value('sensor_count')
        );

        // Idempotent: a second run must not throw or duplicate the pool row.
        $migration->up();
        $this->assertSame(1, (int) DB::table('ts_table_pool')->where('table_name', 't_s50_01')->count());
    }
}
