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
            if (!Schema::hasColumn('nonjiat_data', 'jenis_pemasangan')) {
                $table->string('jenis_pemasangan', 20)
                    ->default('sungai')
                    ->comment('Pemasangan AWLR non-JIAT: sungai atau v_notch')
                    ->after('jenis_sensor');
            }

            if (!Schema::hasColumn('nonjiat_data', 'elevasi_apex')) {
                $table->double('elevasi_apex')
                    ->nullable()
                    ->comment('Pemasangan v_notch: elevasi apex (dasar V), datum peil')
                    ->after('elevasi_min');
            }

            if (!Schema::hasColumn('nonjiat_data', 'kedalaman_notch')) {
                $table->double('kedalaman_notch')
                    ->nullable()
                    ->comment('Pemasangan v_notch: kedalaman apex ke crest dalam meter')
                    ->after('elevasi_apex');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nonjiat_data')) {
            return;
        }

        Schema::table('nonjiat_data', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['jenis_pemasangan', 'elevasi_apex', 'kedalaman_notch'],
                fn ($column) => Schema::hasColumn('nonjiat_data', $column)
            ));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
