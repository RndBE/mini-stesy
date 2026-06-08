<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the 50-sensor storage family (t_s50), parallel to the existing 16/19
 * families created in 2026_01_26_072601. Strictly additive: it creates new
 * tables, registers a pool entry, and (on MySQL) adds the sync trigger and
 * rebuilds v_temp_latest with a third 50-column branch.
 *
 * Trigger/view are MySQL-only (guarded), matching the precedent of other
 * migrations, so the portable table creation still runs on sqlite (tests).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('temp_s50_latest')) {
            Schema::create('temp_s50_latest', function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';

                $table->string('id_logger', 15)->primary();
                $table->dateTime('waktu');

                for ($i = 1; $i <= 50; $i++) {
                    $table->float('sensor' . $i)->nullable();
                }

                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
                $table->index('waktu', 'idx_temp50_waktu');

                $table->foreign('id_logger', 'fk_temp50_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('t_s50_01')) {
            Schema::create('t_s50_01', function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';

                $table->bigIncrements('id');
                $table->string('id_logger', 15);
                $table->dateTime('waktu');

                for ($i = 1; $i <= 50; $i++) {
                    $table->float('sensor' . $i);
                }

                $table->index(['id_logger', 'waktu'], 'idx_s50_logger_time');
                $table->index('waktu', 'idx_s50_time');

                $table->foreign('id_logger', 'fk_s50_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        if (Schema::hasTable('ts_table_pool')) {
            DB::table('ts_table_pool')->updateOrInsert(
                ['table_name' => 't_s50_01'],
                ['sensor_count' => 50, 'max_logger' => 5, 'is_active' => 1, 'created_at' => now()]
            );
        }

        // Trigger + view are MySQL-specific (ON DUPLICATE KEY UPDATE / CREATE VIEW).
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $cols    = $this->sensorCols(1, 50);                                  // sensor1, ..., sensor50
        $newCols = $this->sensorCols(1, 50, 'NEW.');                          // NEW.sensor1, ...
        $updates = implode(', ', array_map(fn ($i) => "sensor{$i}=VALUES(sensor{$i})", range(1, 50)));

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_t_s50_01_to_temp;
            CREATE TRIGGER trg_t_s50_01_to_temp
            AFTER INSERT ON t_s50_01
            FOR EACH ROW
            INSERT INTO temp_s50_latest (id_logger, waktu, {$cols})
            VALUES (NEW.id_logger, NEW.waktu, {$newCols})
            ON DUPLICATE KEY UPDATE waktu=VALUES(waktu), {$updates};
        ");

        DB::unprepared($this->viewSql(true));
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_t_s50_01_to_temp;");
            // Restore the original two-branch view (16 + 19).
            DB::unprepared($this->viewSql(false));
        }

        if (Schema::hasTable('ts_table_pool')) {
            DB::table('ts_table_pool')->where('table_name', 't_s50_01')->delete();
        }

        Schema::dropIfExists('t_s50_01');
        Schema::dropIfExists('temp_s50_latest');
    }

    /** Build a "sensor1, sensor2, ..." list, optionally prefixed (e.g. "t." or "NEW."). */
    private function sensorCols(int $from, int $to, string $prefix = ''): string
    {
        return implode(', ', array_map(fn ($i) => $prefix . 'sensor' . $i, range($from, $to)));
    }

    /** Build a NULL-padded "NULL AS sensorX, ..." list for a branch narrower than 50. */
    private function nullPad(int $from, int $to): string
    {
        if ($from > $to) {
            return '';
        }
        return ', ' . implode(', ', array_map(fn ($i) => "NULL AS sensor{$i}", range($from, $to)));
    }

    /**
     * Rebuild v_temp_latest. When $withS50 is true it has three branches
     * (16/19/50, all exposing 50 columns); otherwise the original two (16/19,
     * exposing 19 columns) so down() restores the prior shape exactly.
     */
    private function viewSql(bool $withS50): string
    {
        $maxCol = $withS50 ? 50 : 19;

        $branch = function (string $tempTable, int $width) use ($maxCol) {
            $sensorSelect = $this->sensorCols(1, $width, 't.');
            $sensorSelect .= $this->nullPad($width + 1, $maxCol);

            return "
                SELECT
                  l.id_logger,
                  l.nama_logger,
                  lk.nama_lokasi AS lokasi_logger,
                  l.sensor_count,
                  t.waktu,
                  {$sensorSelect}
                FROM t_logger l
                LEFT JOIN t_lokasi lk ON lk.idlokasi = l.idlokasi
                JOIN {$tempTable} t ON t.id_logger = l.id_logger
            ";
        };

        $branches = [
            $branch('temp_s16_latest', 16),
            $branch('temp_s19_latest', 19),
        ];

        if ($withS50) {
            $branches[] = $branch('temp_s50_latest', 50);
        }

        return "DROP VIEW IF EXISTS v_temp_latest;\n"
            . "CREATE VIEW v_temp_latest AS\n"
            . implode("\nUNION ALL\n", $branches) . ';';
    }
};
