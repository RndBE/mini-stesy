<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parameter_sensor', function (Blueprint $table) {
            $table->string('parameter_group', 20)->default('pengukuran');
            $table->index('parameter_group', 'idx_param_group');
        });

        DB::table('parameter_sensor')
            ->whereRaw('LOWER(COALESCE(nama_parameter, "")) LIKE ?', ['%battery%'])
            ->orWhereRaw('LOWER(COALESCE(parameter_utama, "")) LIKE ?', ['%battery%'])
            ->update(['parameter_group' => 'baterai']);
    }

    public function down(): void
    {
        Schema::table('parameter_sensor', function (Blueprint $table) {
            $table->dropIndex('idx_param_group');
            $table->dropColumn('parameter_group');
        });
    }
};

