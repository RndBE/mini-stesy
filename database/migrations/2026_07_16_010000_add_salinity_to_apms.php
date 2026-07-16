<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('kategori_logger')
            || !Schema::hasTable('list_parameter')
            || !Schema::hasTable('template_kategori_parameter')
        ) {
            return;
        }

        $categoryId = DB::table('kategori_logger')
            ->whereRaw('UPPER(nama_kategori) = ?', ['APMS'])
            ->value('id_katlogger');

        if (!$categoryId) {
            return;
        }

        $now = now();
        $sumurGroupId = Schema::hasTable('parameter_groups')
            ? DB::table('parameter_groups')->where('kode_group', 'SUMUR')->value('id')
            : null;
        $loggerGroupId = Schema::hasTable('parameter_groups')
            ? DB::table('parameter_groups')->where('kode_group', 'LOGGER')->value('id')
            : null;

        $salinityId = DB::table('list_parameter')
            ->where('parameter_utama', 'salinity')
            ->value('id');

        if (!$salinityId) {
            $payload = [
                'nama_parameter' => 'Salinity',
                'parameter_utama' => 'salinity',
                'default_satuan' => 'PSU',
                'default_kolom_sensor' => 'sensor7',
                'default_parameter_group_id' => $sumurGroupId,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('list_parameter', 'icon_app')) {
                $payload['icon_app'] = 'icons/awgr/salinity.svg';
            }

            $salinityId = DB::table('list_parameter')->insertGetId($payload);
        }

        $templateRows = [
            'salinity' => ['order' => 6, 'column' => 'sensor7', 'unit' => 'PSU', 'group' => $sumurGroupId],
            'hujan' => ['order' => 7, 'column' => 'sensor6', 'unit' => 'mm', 'group' => $sumurGroupId],
            'humidity_logger' => ['order' => 8, 'column' => 'sensor14', 'unit' => '%', 'group' => $loggerGroupId],
            'battery_logger' => ['order' => 9, 'column' => 'sensor15', 'unit' => 'Volt', 'group' => $loggerGroupId],
            'temperature_logger' => ['order' => 10, 'column' => 'sensor16', 'unit' => 'C', 'group' => $loggerGroupId],
        ];

        foreach ($templateRows as $base => $row) {
            $listParameterId = $base === 'salinity'
                ? $salinityId
                : DB::table('list_parameter')->where('parameter_utama', $base)->value('id');

            if (!$listParameterId) {
                continue;
            }

            DB::table('template_kategori_parameter')->updateOrInsert(
                [
                    'id_katlogger' => $categoryId,
                    'list_parameter_id' => $listParameterId,
                ],
                [
                    'urutan' => $row['order'],
                    'kolom_sensor_default' => $row['column'],
                    'satuan_override' => $row['unit'],
                    'parameter_group_id' => $row['group'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (
            Schema::hasTable('t_logger')
            && Schema::hasTable('parameter_sensor')
            && DB::table('t_logger')->where('id_logger', '30081')->exists()
        ) {
            DB::table('parameter_sensor')->updateOrInsert(
                [
                    'logger_id' => '30081',
                    'parameter_utama' => 'salinity',
                ],
                [
                    'nama_parameter' => 'Salinity',
                    'kolom_sensor' => 'sensor7',
                    'satuan' => 'PSU',
                    'tipe_graf' => 'line',
                    'icon_app' => 'icons/awgr/salinity.svg',
                    'debit_awlr' => '-',
                    'parameter_group_id' => $sumurGroupId,
                ]
            );
        }
    }

    public function down(): void
    {
        if (
            !Schema::hasTable('kategori_logger')
            || !Schema::hasTable('list_parameter')
            || !Schema::hasTable('template_kategori_parameter')
        ) {
            return;
        }

        $categoryId = DB::table('kategori_logger')
            ->whereRaw('UPPER(nama_kategori) = ?', ['APMS'])
            ->value('id_katlogger');
        $salinityId = DB::table('list_parameter')
            ->where('parameter_utama', 'salinity')
            ->value('id');

        if ($categoryId && $salinityId) {
            DB::table('template_kategori_parameter')
                ->where('id_katlogger', $categoryId)
                ->where('list_parameter_id', $salinityId)
                ->delete();
        }
    }
};
