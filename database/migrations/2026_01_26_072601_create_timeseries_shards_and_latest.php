<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ts_table_pool', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->string('table_name', 64)->primary();
            $table->unsignedTinyInteger('sensor_count');
            $table->unsignedTinyInteger('max_logger')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('sensor_count', 'idx_pool_sensor');
        });

        Schema::create('logger_storage_map', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->string('id_logger', 15)->primary();
            $table->string('table_name', 64);
            $table->unsignedTinyInteger('sensor_count');
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('table_name', 'idx_map_table');
            $table->index('sensor_count', 'idx_map_sensor');

            $table->foreign('id_logger', 'fk_map_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('table_name', 'fk_map_table')->references('table_name')->on('ts_table_pool')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('temp_s16_latest', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->string('id_logger', 15)->primary();
            $table->dateTime('waktu');

            for ($i = 1; $i <= 16; $i++) {
                $table->float('sensor' . $i)->nullable();
            }

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('waktu', 'idx_temp16_waktu');

            $table->foreign('id_logger', 'fk_temp16_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('temp_s19_latest', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->string('id_logger', 15)->primary();
            $table->dateTime('waktu');

            for ($i = 1; $i <= 19; $i++) {
                $table->float('sensor' . $i)->nullable();
            }

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('waktu', 'idx_temp19_waktu');

            $table->foreign('id_logger', 'fk_temp19_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('t_s16_01', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->bigIncrements('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');

            for ($i = 1; $i <= 16; $i++) {
                $table->float('sensor' . $i);
            }

            $table->index(['id_logger', 'waktu'], 'idx_s16_logger_time');
            $table->index('waktu', 'idx_s16_time');

            $table->foreign('id_logger', 'fk_s16_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('t_s19_01', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->bigIncrements('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');

            for ($i = 1; $i <= 19; $i++) {
                $table->float('sensor' . $i);
            }

            $table->index(['id_logger', 'waktu'], 'idx_s19_logger_time');
            $table->index('waktu', 'idx_s19_time');

            $table->foreign('id_logger', 'fk_s19_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->restrictOnDelete();
        });

        DB::table('ts_table_pool')->insert([
            ['table_name' => 't_s16_01', 'sensor_count' => 16, 'max_logger' => 5, 'is_active' => 1, 'created_at' => now()],
            ['table_name' => 't_s19_01', 'sensor_count' => 19, 'max_logger' => 5, 'is_active' => 1, 'created_at' => now()],
        ]);

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_t_s16_01_to_temp;
            CREATE TRIGGER trg_t_s16_01_to_temp
            AFTER INSERT ON t_s16_01
            FOR EACH ROW
            INSERT INTO temp_s16_latest (
              id_logger, waktu,
              sensor1, sensor2, sensor3, sensor4, sensor5, sensor6, sensor7, sensor8,
              sensor9, sensor10, sensor11, sensor12, sensor13, sensor14, sensor15, sensor16
            ) VALUES (
              NEW.id_logger, NEW.waktu,
              NEW.sensor1, NEW.sensor2, NEW.sensor3, NEW.sensor4, NEW.sensor5, NEW.sensor6, NEW.sensor7, NEW.sensor8,
              NEW.sensor9, NEW.sensor10, NEW.sensor11, NEW.sensor12, NEW.sensor13, NEW.sensor14, NEW.sensor15, NEW.sensor16
            )
            ON DUPLICATE KEY UPDATE
              waktu=VALUES(waktu),
              sensor1=VALUES(sensor1),
              sensor2=VALUES(sensor2),
              sensor3=VALUES(sensor3),
              sensor4=VALUES(sensor4),
              sensor5=VALUES(sensor5),
              sensor6=VALUES(sensor6),
              sensor7=VALUES(sensor7),
              sensor8=VALUES(sensor8),
              sensor9=VALUES(sensor9),
              sensor10=VALUES(sensor10),
              sensor11=VALUES(sensor11),
              sensor12=VALUES(sensor12),
              sensor13=VALUES(sensor13),
              sensor14=VALUES(sensor14),
              sensor15=VALUES(sensor15),
              sensor16=VALUES(sensor16);
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_t_s19_01_to_temp;
            CREATE TRIGGER trg_t_s19_01_to_temp
            AFTER INSERT ON t_s19_01
            FOR EACH ROW
            INSERT INTO temp_s19_latest (
              id_logger, waktu,
              sensor1, sensor2, sensor3, sensor4, sensor5, sensor6, sensor7, sensor8,
              sensor9, sensor10, sensor11, sensor12, sensor13, sensor14, sensor15, sensor16,
              sensor17, sensor18, sensor19
            ) VALUES (
              NEW.id_logger, NEW.waktu,
              NEW.sensor1, NEW.sensor2, NEW.sensor3, NEW.sensor4, NEW.sensor5, NEW.sensor6, NEW.sensor7, NEW.sensor8,
              NEW.sensor9, NEW.sensor10, NEW.sensor11, NEW.sensor12, NEW.sensor13, NEW.sensor14, NEW.sensor15, NEW.sensor16,
              NEW.sensor17, NEW.sensor18, NEW.sensor19
            )
            ON DUPLICATE KEY UPDATE
              waktu=VALUES(waktu),
              sensor1=VALUES(sensor1),
              sensor2=VALUES(sensor2),
              sensor3=VALUES(sensor3),
              sensor4=VALUES(sensor4),
              sensor5=VALUES(sensor5),
              sensor6=VALUES(sensor6),
              sensor7=VALUES(sensor7),
              sensor8=VALUES(sensor8),
              sensor9=VALUES(sensor9),
              sensor10=VALUES(sensor10),
              sensor11=VALUES(sensor11),
              sensor12=VALUES(sensor12),
              sensor13=VALUES(sensor13),
              sensor14=VALUES(sensor14),
              sensor15=VALUES(sensor15),
              sensor16=VALUES(sensor16),
              sensor17=VALUES(sensor17),
              sensor18=VALUES(sensor18),
              sensor19=VALUES(sensor19);
        ");

        DB::unprepared("
            DROP VIEW IF EXISTS v_temp_latest;
            CREATE VIEW v_temp_latest AS
            SELECT
              l.id_logger,
              l.nama_logger,
              lk.nama_lokasi AS lokasi_logger,
              l.sensor_count,
              t.waktu,
              t.sensor1, t.sensor2, t.sensor3, t.sensor4, t.sensor5,
              t.sensor6, t.sensor7, t.sensor8, t.sensor9, t.sensor10,
              t.sensor11, t.sensor12, t.sensor13, t.sensor14, t.sensor15,
              t.sensor16,
              NULL AS sensor17,
              NULL AS sensor18,
              NULL AS sensor19
            FROM t_logger l
            LEFT JOIN t_lokasi lk ON lk.idlokasi = l.idlokasi
            JOIN temp_s16_latest t ON t.id_logger = l.id_logger

            UNION ALL

            SELECT
              l.id_logger,
              l.nama_logger,
              lk.nama_lokasi AS lokasi_logger,
              l.sensor_count,
              t.waktu,
              t.sensor1, t.sensor2, t.sensor3, t.sensor4, t.sensor5,
              t.sensor6, t.sensor7, t.sensor8, t.sensor9, t.sensor10,
              t.sensor11, t.sensor12, t.sensor13, t.sensor14, t.sensor15,
              t.sensor16,
              t.sensor17,
              t.sensor18,
              t.sensor19
            FROM t_logger l
            LEFT JOIN t_lokasi lk ON lk.idlokasi = l.idlokasi
            JOIN temp_s19_latest t ON t.id_logger = l.id_logger;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP VIEW IF EXISTS v_temp_latest;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_t_s19_01_to_temp;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_t_s16_01_to_temp;");

        Schema::dropIfExists('t_s19_01');
        Schema::dropIfExists('t_s16_01');
        Schema::dropIfExists('temp_s19_latest');
        Schema::dropIfExists('temp_s16_latest');
        Schema::dropIfExists('logger_storage_map');
        Schema::dropIfExists('ts_table_pool');
    }
};
