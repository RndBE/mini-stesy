<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_logger', function (Blueprint $table) {
            if (!Schema::hasColumn('t_logger', 'instansi_id')) {
                $table->unsignedInteger('instansi_id')->nullable()->after('user_id');
                $table->index('instansi_id', 'idx_logger_instansi');
                $table->foreign('instansi_id', 'fk_logger_instansi')
                    ->references('id')
                    ->on('instansi')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('t_logger', 'user_id')) {
            DB::statement('UPDATE t_logger tl JOIN t_user tu ON tu.id_user = tl.user_id SET tl.instansi_id = tu.instansi_id');
        }

        if (Schema::hasColumn('t_logger', 'user_id')) {
            DB::statement('ALTER TABLE t_logger DROP FOREIGN KEY fk_logger_user');
            DB::statement('ALTER TABLE t_logger DROP INDEX idx_logger_user');
            Schema::table('t_logger', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('t_logger', function (Blueprint $table) {
            if (!Schema::hasColumn('t_logger', 'user_id')) {
                $table->unsignedInteger('user_id')->nullable()->after('id_logger');
                $table->index('user_id', 'idx_logger_user');
                $table->foreign('user_id', 'fk_logger_user')
                    ->references('id_user')
                    ->on('t_user')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });

        if (Schema::hasColumn('t_logger', 'instansi_id')) {
            Schema::table('t_logger', function (Blueprint $table) {
                $table->dropForeign('fk_logger_instansi');
                $table->dropIndex('idx_logger_instansi');
                $table->dropColumn('instansi_id');
            });
        }
    }
};
