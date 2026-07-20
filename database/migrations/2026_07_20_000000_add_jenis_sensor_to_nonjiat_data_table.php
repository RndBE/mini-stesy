<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nonjiat_data', function (Blueprint $table) {
            $table->string('jenis_sensor', 20)
                ->default('ultrasonic')
                ->comment('Jenis sensor AWLR non-JIAT: ultrasonic atau radar')
                ->after('id_logger');
        });
    }

    public function down(): void
    {
        Schema::table('nonjiat_data', function (Blueprint $table) {
            $table->dropColumn('jenis_sensor');
        });
    }
};
