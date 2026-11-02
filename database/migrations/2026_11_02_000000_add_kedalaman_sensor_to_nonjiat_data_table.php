<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nonjiat_data')) {
            return;
        }

        Schema::table('nonjiat_data', function (Blueprint $table) {
            if (!Schema::hasColumn('nonjiat_data', 'kedalaman_sensor')) {
                $table->double('kedalaman_sensor')
                    ->nullable()
                    ->comment('Sensor transduser: kedalaman pasang sensor dari puncak (elevasi_max), satuan meter')
                    ->after('tinggi_sensor');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nonjiat_data')) {
            return;
        }

        Schema::table('nonjiat_data', function (Blueprint $table) {
            if (Schema::hasColumn('nonjiat_data', 'kedalaman_sensor')) {
                $table->dropColumn('kedalaman_sensor');
            }
        });
    }
};
