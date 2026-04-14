<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jiat_data', function (Blueprint $table) {
            if (!Schema::hasColumn('jiat_data', 'has_pump')) {
                $table->boolean('has_pump')->default(false)->after('kedalaman_sensor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jiat_data', function (Blueprint $table) {
            if (Schema::hasColumn('jiat_data', 'has_pump')) {
                $table->dropColumn('has_pump');
            }
        });
    }
};
