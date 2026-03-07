<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nonjiat_data', function (Blueprint $table) {
            $table->decimal('elevasi_max', 10, 4)->nullable()->comment('Batas atas skala peil / tinggi (m)')->after('tinggi_sensor');
            $table->decimal('elevasi_min', 10, 4)->nullable()->comment('Batas bawah skala peil / dasar (m)')->after('elevasi_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nonjiat_data', function (Blueprint $table) {
            $table->dropColumn(['elevasi_max', 'elevasi_min']);
        });
    }
};
