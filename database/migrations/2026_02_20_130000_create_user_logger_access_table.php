<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_logger_access')) {
            return;
        }

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15)->collation('utf8_general_ci');
            $table->timestamps();

            $table->unique(['user_id', 'logger_id'], 'uq_user_logger_access');
            $table->index('user_id', 'idx_user_logger_access_user');
            $table->index('logger_id', 'idx_user_logger_access_logger');

            $table->foreign('user_id', 'fk_user_logger_access_user')
                ->references('id_user')
                ->on('t_user')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('logger_id', 'fk_user_logger_access_logger')
                ->references('id_logger')
                ->on('t_logger')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_logger_access')) {
            return;
        }

        Schema::table('user_logger_access', function (Blueprint $table) {
            $table->dropForeign('fk_user_logger_access_user');
            $table->dropForeign('fk_user_logger_access_logger');
            $table->dropUnique('uq_user_logger_access');
            $table->dropIndex('idx_user_logger_access_user');
            $table->dropIndex('idx_user_logger_access_logger');
        });

        Schema::dropIfExists('user_logger_access');
    }
};
