<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('module', 100)->index();
            $table->string('action_type', 40)->index();
            $table->string('activity', 255);
            $table->string('target', 255)->nullable();
            $table->string('status', 20)->default('SUCCESS')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->string('actor_name', 100)->nullable();
            $table->string('actor_username', 30)->nullable();
            $table->string('actor_role', 20)->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_audit_logs_user')
                ->references('id_user')
                ->on('t_user')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign('fk_audit_logs_user');
        });

        Schema::dropIfExists('audit_logs');
    }
};
