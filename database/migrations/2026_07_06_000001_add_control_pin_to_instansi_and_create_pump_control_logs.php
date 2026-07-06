<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('instansi')) {
            Schema::table('instansi', function (Blueprint $table) {
                if (!Schema::hasColumn('instansi', 'control_pin_hash')) {
                    $table->string('control_pin_hash')->nullable();
                }
                if (!Schema::hasColumn('instansi', 'control_pin_enabled')) {
                    $table->boolean('control_pin_enabled')->default(false);
                }
                if (!Schema::hasColumn('instansi', 'control_pin_updated_at')) {
                    $table->timestamp('control_pin_updated_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('pump_control_logs')) {
            Schema::create('pump_control_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->unsignedInteger('instansi_id')->nullable()->index();
                $table->string('id_logger', 15)->index();
                $table->string('action', 10)->index();
                $table->string('status', 30)->index();
                $table->text('message')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('location_permission_status', 30)->nullable();
                $table->timestamp('requested_at')->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pump_control_logs');

        if (Schema::hasTable('instansi')) {
            Schema::table('instansi', function (Blueprint $table) {
                foreach (['control_pin_hash', 'control_pin_enabled', 'control_pin_updated_at'] as $column) {
                    if (Schema::hasColumn('instansi', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
