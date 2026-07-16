<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kategori_logger')) {
            return;
        }

        DB::table('kategori_logger')->updateOrInsert(
            ['nama_kategori' => 'APMS'],
            [
                'kepanjangan' => 'Automatic Peatland Monitoring System',
                'icon_app' => 'apms.svg',
                'view' => 1,
            ]
        );

        if (!Schema::hasTable('list_parameter') || !Schema::hasTable('template_kategori_parameter')) {
            return;
        }

        $now = now();
        $categoryId = DB::table('kategori_logger')
            ->where('nama_kategori', 'APMS')
            ->value('id_katlogger');
        $groups = Schema::hasTable('parameter_groups')
            ? DB::table('parameter_groups')->pluck('id', 'kode_group')->all()
            : [];
        $sumurGroupId = $groups['SUMUR'] ?? null;
        $loggerGroupId = $groups['LOGGER'] ?? null;

        $parameters = [
            ['name' => 'Muka Air Tanah', 'base' => 'muka_air_tanah', 'unit' => 'm', 'column' => 'sensor1', 'icon' => 'icons/awlr/elevasi_muka_air.svg', 'group' => $sumurGroupId],
            ['name' => 'pH Tanah', 'base' => 'ph_tanah', 'unit' => null, 'column' => 'sensor2', 'icon' => 'icons/apms/ph_tanah.svg', 'group' => $sumurGroupId],
            ['name' => 'Electrical Conductivity', 'base' => 'electrical_conductivity', 'unit' => 'uS/cm', 'column' => 'sensor3', 'icon' => 'icons/apms/electrical_conductivity.svg', 'group' => $sumurGroupId],
            ['name' => 'Kelembaban Tanah', 'base' => 'kelembaban_tanah', 'unit' => '%', 'column' => 'sensor4', 'icon' => 'icons/apms/soil_moisture.svg', 'group' => $sumurGroupId],
            ['name' => 'Temperature Tanah', 'base' => 'temperature_tanah', 'unit' => 'C', 'column' => 'sensor5', 'icon' => 'icons/apms/soil_temperature.svg', 'group' => $sumurGroupId],
            ['name' => 'Salinity', 'base' => 'salinity', 'unit' => 'PSU', 'column' => 'sensor7', 'icon' => 'icons/apms/soil_salinity.svg', 'group' => $sumurGroupId],
            ['name' => 'Curah Hujan', 'base' => 'hujan', 'unit' => 'mm', 'column' => 'sensor6', 'icon' => 'icons/apms/rainfall.svg', 'group' => $sumurGroupId],
            ['name' => 'Humidity Logger', 'base' => 'humidity_logger', 'unit' => '%', 'column' => 'sensor14', 'icon' => 'icons/beranda/humidity_online.svg', 'group' => $loggerGroupId],
            ['name' => 'Battery Logger', 'base' => 'battery_logger', 'unit' => 'Volt', 'column' => 'sensor15',('base') . '.svg';
            }

            $parameterIds[$parameter['base']] = DB::table('list_parameter')->insertGetId([
                'nama_parameter' => $parameter['name'],
                'parameter_utama' => $parameter['base'],
                'default_satuan' => $parameter['unit'],
                'default_kolom_sensor' => $parameter['column'],
                'icon_app' => $parameter['icon'],
                'default_parameter_group_id' => $parameter['group'],
               ('is_active') . true,
               ('created_at') . $now,
        ];

        $parameterIds = [];
        foreach ($parameters as $parameter) {
            $existing = DB::table('list_parameter')
                ->where('parameter_utama', $parameter['base'])
                ->first();

            if ($existing) {
                $parameterIds[$parameter['base']] = $existing->id;
                continue;
            }

            $parameterIds[$parameter['base']] = DB::table('list_parameter')->insertGetId([
                'nama_parameter' => $parameter['name'],
                'parameter_utama' => $parameter['base'],
                'default_satuan' => $parameter['unit'],
                'default_kolom_sensor' => $parameter['column'],
                'icon_app' => $parameter['icon'],
                'default_parameter_group_id' => $parameter['group'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $template = [
            ['base' => 'muka_air_tanah', 'order' => 1, 'column' => 'sensor1', 'unit' => 'm', 'group' => $sumurGroupId],
            ['base' => 'ph_tanah', 'order' => 2, 'column' => 'sensor2', 'unit' => null, 'group' => $sumurGroupId],
            ['base' => 'electrical_conductivity', 'order' => 3, 'column' => 'sensor3', 'unit' => 'uS/cm', 'group' => $sumurGroupId],
            ['base' => 'kelembaban_tanah', 'order' => 4, 'column' => 'sensor4', 'unit' => '%', 'group' => $sumurGroupId],
            ['base' => 'temperature_tanah', 'order' => 5, 'column' => 'sensor5', 'unit' => 'C', 'group' => $sumurGroupId],
            ['base' => 'salinity', 'order' => 6, 'column' => 'sensor7', 'unit' => 'PSU', 'group' => $sumurGroupId],
            ['base' => 'hujan', 'order' => 7, 'column' => 'sensor6', 'unit' => 'mm', 'group' => $sumurGroupId],
            ['base' => 'humidity_logger', 'order' => 8, 'column' => 'sensor14', 'unit' => '%', 'group' => $loggerGroupId],
            ['base' => 'battery_logger', 'order' => 9, 'column' => 'sensor15', 'unit' => 'Volt', 'group' => $loggerGroupId],
            ['base' => 'temperature_logger', 'order' => 10, 'column' => 'sensor16', 'unit' => 'C', 'group' => $loggerGroupId],
        ];

        foreach ($template as $row) {
            DB::table('template_kategori_parameter')->updateOrInsert(
                [
                    'id_katlogger' => $categoryId,
                    'list_parameter_id' => $parameterIds[$row['base']],
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
    }

    public function down(): void
    {
        if (!Schema::hasTable('kategori_logger')) {
            return;
        }

        $categoryId = DB::table('kategori_logger')
            ->where('nama_kategori', 'APMS')
            ->value('id_katlogger');

        if ($categoryId && Schema::hasTable('template_kategori_parameter')) {
            DB::table('template_kategori_parameter')
                ->where('id_katlogger', $categoryId)
                ->delete();
        }

        if (Schema::hasTable('list_parameter')) {
            DB::table('list_parameter')
                ->whereIn('parameter_utama', [
                    'ph_tanah',
                    'electrical_conductivity',
                    'kelembaban_tanah',
                    'temperature_tanah',
                ])
                ->delete();
        }

        DB::table('kategori_logger')->where('nama_kategori', 'APMS')->delete();
    }
};
