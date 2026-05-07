<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ListParameterSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('list_parameter')) {
            return;
        }

        $now = now();
        $hasIconColumn = Schema::hasColumn('list_parameter', 'icon_app');
        $groups = Schema::hasTable('parameter_groups')
            ? DB::table('parameter_groups')->pluck('id', 'kode_group')->all()
            : [];

        $groupId = fn(string $kode) => $groups[$kode] ?? null;

        $parameters = [
            ['name' => 'TMA', 'base' => 'tma', 'unit' => 'm', 'column' => 'sensor14', 'group' => 'SUMUR', 'icon' => 'icons/awlr/elevasi_muka_air.svg'],
            ['name' => 'Muka Air Tanah', 'base' => 'muka_air_tanah', 'unit' => 'm', 'column' => 'sensor1', 'group' => 'SUMUR', 'icon' => 'icons/awlr/elevasi_muka_air.svg'],
            ['name' => 'Debit', 'base' => 'debit', 'unit' => 'm3/s', 'column' => 'sensor2', 'group' => 'SUMUR', 'icon' => 'icons/awlr/debit.svg'],
            ['name' => 'Curah Hujan', 'base' => 'hujan', 'unit' => 'mm', 'column' => 'sensor8', 'group' => 'SUMUR', 'icon' => 'icons/afmr/curah_hujan.svg'],

            ['name' => 'Luas Penampang Basah', 'base' => 'luas_penampang', 'unit' => 'm2', 'column' => 'sensor1', 'group' => 'SUMUR', 'icon' => 'icons/afmr/luas_penampang_air.svg'],
            ['name' => 'Flow Velocity', 'base' => 'flow_velocity', 'unit' => 'm/s', 'column' => 'sensor3', 'group' => 'SUMUR', 'icon' => 'icons/afmr/flow_velocity.svg'],
            ['name' => 'Elevasi Muka Air', 'base' => 'elevasi_muka_air', 'unit' => 'm', 'column' => 'sensor4', 'group' => 'SUMUR', 'icon' => 'icons/awlr/elevasi_muka_air.svg'],
            ['name' => 'Elevasi Sensor', 'base' => 'elevasi_sensor', 'unit' => 'm', 'column' => 'sensor6', 'group' => 'SUMUR', 'icon' => 'icons/afmr/tinggi_sensor.svg'],
            ['name' => 'Jarak Sensor', 'base' => 'jarak_sensor', 'unit' => 'm', 'column' => 'sensor5', 'group' => 'SUMUR', 'icon' => 'icons/afmr/jarak_sensor.svg'],

            ['name' => 'Kecepatan Angin', 'base' => 'kecepatan_angin', 'unit' => 'Km/h', 'column' => 'sensor1', 'group' => 'ANGIN', 'icon' => 'icons/awr/kecepatan_angin.svg'],
            ['name' => 'Arah Angin', 'base' => 'arah_angin', 'unit' => 'deg', 'column' => 'sensor2', 'group' => 'ANGIN', 'icon' => 'icons/awr/arah_angin.svg'],
            ['name' => 'Kecerahan', 'base' => 'kecerahan', 'unit' => 'K Lux', 'column' => 'sensor6', 'group' => 'SUMUR', 'icon' => 'icons/awr/kecerahan.svg'],
            ['name' => 'Arah Cahaya', 'base' => 'arah_cahaya', 'unit' => 'deg', 'column' => 'sensor7', 'group' => 'SUMUR', 'icon' => 'icons/awr/arah.svg'],
            ['name' => 'Temperatur Udara', 'base' => 'temperatur', 'unit' => 'C', 'column' => 'sensor3', 'group' => 'SUMUR', 'icon' => 'icons/beranda/temper_online.svg'],
            ['name' => 'Tekanan Udara', 'base' => 'tekanan_udara', 'unit' => 'hPa', 'column' => 'sensor5', 'group' => 'SUMUR', 'icon' => 'icons/awr/tekanan_udara.svg'],
            ['name' => 'Kelembaban Udara', 'base' => 'kelembaban', 'unit' => '%', 'column' => 'sensor4', 'group' => 'SUMUR', 'icon' => 'icons/beranda/humidity_online.svg'],

            ['name' => 'PH Air', 'base' => 'ph_air', 'unit' => null, 'column' => 'sensor2', 'group' => 'SUMUR', 'icon' => 'icons/awgr/ph_air.svg'],
            ['name' => 'Suhu Air', 'base' => 'suhu_air', 'unit' => 'C', 'column' => 'sensor3', 'group' => 'SUMUR', 'icon' => 'icons/awgr/suhu_air.svg'],
            ['name' => 'ORP', 'base' => 'orp', 'unit' => 'mV', 'column' => 'sensor4', 'group' => 'SUMUR', 'icon' => 'icons/awgr/orp.svg'],
            ['name' => 'Conductivity', 'base' => 'conductivity', 'unit' => 'uS/cm', 'column' => 'sensor5', 'group' => 'SUMUR', 'icon' => 'icons/awgr/conductivity.svg'],
            ['name' => 'Salinity', 'base' => 'salinity', 'unit' => 'PSU', 'column' => 'sensor6', 'group' => 'SUMUR', 'icon' => 'icons/awgr/salinity.svg'],
            ['name' => 'TDS', 'base' => 'tds', 'unit' => 'mg/L', 'column' => 'sensor7', 'group' => 'SUMUR', 'icon' => 'icons/awgr/total_dissolved_solids.svg'],
            ['name' => 'Turbidity', 'base' => 'turbidity', 'unit' => 'NTU', 'column' => 'sensor8', 'group' => 'SUMUR', 'icon' => 'icons/awgr/turbidity.svg'],
            ['name' => 'Tinggi Sensor', 'base' => 'tinggi_sensor', 'unit' => 'm', 'column' => 'sensor9', 'group' => 'SUMUR', 'icon' => 'icons/awgr/tinggi_sensor.svg'],

            ['name' => 'Humidity Logger', 'base' => 'humidity_logger', 'unit' => '%', 'column' => 'sensor4', 'group' => 'LOGGER', 'icon' => 'icons/beranda/humidity_online.svg'],
            ['name' => 'Battery Logger', 'base' => 'battery_logger', 'unit' => 'Volt', 'column' => 'sensor6', 'group' => 'LOGGER', 'icon' => 'icons/beranda/battery_online.svg'],
            ['name' => 'Temperature Logger', 'base' => 'temperature_logger', 'unit' => 'C', 'column' => 'sensor5', 'group' => 'LOGGER', 'icon' => 'icons/beranda/temper_online.svg'],
        ];

        foreach ($parameters as $parameter) {
            $payload = [
                'nama_parameter' => $parameter['name'],
                'parameter_utama' => $parameter['base'],
                'default_satuan' => $parameter['unit'],
                'default_kolom_sensor' => $parameter['column'],
                'default_parameter_group_id' => $groupId($parameter['group']),
                'is_active' => true,
                'updated_at' => $now,
            ] + ['created_at' => $now];

            if ($hasIconColumn) {
                $payload['icon_app'] = $parameter['icon'];
            }

            $this->upsertListParameter($payload);
        }

        if (!Schema::hasTable('template_kategori_parameter')) {
            return;
        }

        $listParameterIds = DB::table('list_parameter')
            ->whereIn('parameter_utama', collect($parameters)->pluck('base')->all())
            ->pluck('id', 'parameter_utama')
            ->all();

        if (
            Schema::hasTable('parameter_sensor')
            && Schema::hasColumn('parameter_sensor', 'parameter_utama')
            && Schema::hasColumn('parameter_sensor', 'icon_app')
            && $hasIconColumn
        ) {
            DB::table('list_parameter')
                ->whereIn('parameter_utama', collect($parameters)->pluck('base')->all())
                ->whereNotNull('icon_app')
                ->select(['parameter_utama', 'icon_app'])
                ->orderBy('parameter_utama')
                ->get()
                ->each(function ($parameter) {
                    DB::table('parameter_sensor')
                        ->where('parameter_utama', $parameter->parameter_utama)
                        ->update(['icon_app' => $parameter->icon_app]);
                });
        }

        $categoryIds = Schema::hasTable('kategori_logger')
            ? DB::table('kategori_logger')->pluck('id_katlogger', 'nama_kategori')->all()
            : [];

        $templates = [
            'AWLR' => [
                ['base' => 'tma', 'order' => 1, 'column' => 'sensor14', 'unit' => 'm', 'group' => 'SUMUR'],
                ['base' => 'muka_air_tanah', 'order' => 2, 'column' => 'sensor1', 'unit' => 'm', 'group' => 'SUMUR'],
                ['base' => 'debit', 'order' => 3, 'column' => 'sensor15', 'unit' => 'm3/s', 'group' => 'SUMUR'],
                ['base' => 'humidity_logger', 'order' => 4, 'column' => 'sensor4', 'unit' => '%', 'group' => 'LOGGER'],
                ['base' => 'battery_logger', 'order' => 5, 'column' => 'sensor6', 'unit' => 'Volt', 'group' => 'LOGGER'],
                ['base' => 'temperature_logger', 'order' => 6, 'column' => 'sensor5', 'unit' => 'C', 'group' => 'LOGGER'],
            ],
            'ARR' => [
                ['base' => 'hujan', 'order' => 1, 'column' => 'sensor8', 'unit' => 'mm', 'group' => 'SUMUR'],
                ['base' => 'humidity_logger', 'order' => 2, 'column' => 'sensor4', 'unit' => '%', 'group' => 'LOGGER'],
                ['base' => 'battery_logger', 'order' => 3, 'column' => 'sensor6', 'unit' => 'Volt', 'group' => 'LOGGER'],
                ['base' => 'temperature_logger', 'order' => 4, 'column' => 'sensor5', 'unit' => 'C', 'group' => 'LOGGER'],
            ],
            'AFMR' => [
                ['base' => 'luas_penampang', 'order' => 1, 'column' => 'sensor1', 'unit' => 'm2', 'group' => 'SUMUR'],
                ['base' => 'debit', 'order' => 2, 'column' => 'sensor2', 'unit' => 'm3/s', 'group' => 'SUMUR'],
                ['base' => 'flow_velocity', 'order' => 3, 'column' => 'sensor3', 'unit' => 'm/s', 'group' => 'SUMUR'],
                ['base' => 'elevasi_muka_air', 'order' => 4, 'column' => 'sensor4', 'unit' => 'm', 'group' => 'SUMUR'],
                ['base' => 'jarak_sensor', 'order' => 5, 'column' => 'sensor5', 'unit' => 'm', 'group' => 'SUMUR'],
                ['base' => 'elevasi_sensor', 'order' => 6, 'column' => 'sensor6', 'unit' => 'm', 'group' => 'SUMUR'],
                ['base' => 'hujan', 'order' => 7, 'column' => 'sensor7', 'unit' => 'mm', 'group' => 'SUMUR'],
                ['base' => 'humidity_logger', 'order' => 8, 'column' => 'sensor9', 'unit' => '%', 'group' => 'LOGGER'],
                ['base' => 'battery_logger', 'order' => 9, 'column' => 'sensor10', 'unit' => 'Volt', 'group' => 'LOGGER'],
                ['base' => 'temperature_logger', 'order' => 10, 'column' => 'sensor11', 'unit' => 'C', 'group' => 'LOGGER'],
            ],
            'AWR' => [
                ['base' => 'kecepatan_angin', 'order' => 1, 'column' => 'sensor1', 'unit' => 'Km/h', 'group' => 'ANGIN'],
                ['base' => 'arah_angin', 'order' => 2, 'column' => 'sensor2', 'unit' => 'deg', 'group' => 'ANGIN'],
                ['base' => 'temperatur', 'order' => 3, 'column' => 'sensor3', 'unit' => 'C', 'group' => 'SUMUR'],
                ['base' => 'kelembaban', 'order' => 4, 'column' => 'sensor4', 'unit' => '%', 'group' => 'SUMUR'],
                ['base' => 'tekanan_udara', 'order' => 5, 'column' => 'sensor5', 'unit' => 'hPa', 'group' => 'SUMUR'],
                ['base' => 'kecerahan', 'order' => 6, 'column' => 'sensor6', 'unit' => 'K Lux', 'group' => 'SUMUR'],
                ['base' => 'arah_cahaya', 'order' => 7, 'column' => 'sensor7', 'unit' => 'deg', 'group' => 'SUMUR'],
                ['base' => 'hujan', 'order' => 8, 'column' => 'sensor8', 'unit' => 'mm', 'group' => 'SUMUR'],
                ['base' => 'humidity_logger', 'order' => 9, 'column' => 'sensor9', 'unit' => '%', 'group' => 'LOGGER'],
                ['base' => 'battery_logger', 'order' => 10, 'column' => 'sensor10', 'unit' => 'Volt', 'group' => 'LOGGER'],
                ['base' => 'temperature_logger', 'order' => 11, 'column' => 'sensor11', 'unit' => 'C', 'group' => 'LOGGER'],
            ],
            'AWQR' => [
                ['base' => 'tma', 'order' => 1, 'column' => 'sensor1', 'unit' => 'mdpl', 'group' => 'SUMUR'],
                ['base' => 'ph_air', 'order' => 2, 'column' => 'sensor2', 'unit' => null, 'group' => 'SUMUR'],
                ['base' => 'suhu_air', 'order' => 3, 'column' => 'sensor3', 'unit' => 'C', 'group' => 'SUMUR'],
                ['base' => 'orp', 'order' => 4, 'column' => 'sensor4', 'unit' => 'mV', 'group' => 'SUMUR'],
                ['base' => 'conductivity', 'order' => 5, 'column' => 'sensor5', 'unit' => 'uS/cm', 'group' => 'SUMUR'],
                ['base' => 'salinity', 'order' => 6, 'column' => 'sensor6', 'unit' => 'PSU', 'group' => 'SUMUR'],
                ['base' => 'tds', 'order' => 7, 'column' => 'sensor7', 'unit' => 'mg/L', 'group' => 'SUMUR'],
                ['base' => 'turbidity', 'order' => 8, 'column' => 'sensor8', 'unit' => 'NTU', 'group' => 'SUMUR'],
                ['base' => 'tinggi_sensor', 'order' => 9, 'column' => 'sensor9', 'unit' => 'm', 'group' => 'SUMUR'],
                ['base' => 'humidity_logger', 'order' => 10, 'column' => 'sensor12', 'unit' => '%', 'group' => 'LOGGER'],
                ['base' => 'battery_logger', 'order' => 11, 'column' => 'sensor13', 'unit' => 'Volt', 'group' => 'LOGGER'],
                ['base' => 'temperature_logger', 'order' => 12, 'column' => 'sensor14', 'unit' => 'C', 'group' => 'LOGGER'],
            ],
        ];

        foreach ($templates as $category => $rows) {
            $categoryId = $categoryIds[$category] ?? null;
            if (!$categoryId) {
                continue;
            }

            foreach ($rows as $row) {
                $listParameterId = $listParameterIds[$row['base']] ?? null;
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
                        'parameter_group_id' => $groupId($row['group']),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    private function upsertListParameter(array $data): void
    {
        $existingByBase = DB::table('list_parameter')
            ->where('parameter_utama', $data['parameter_utama'])
            ->first();

        $existingByName = DB::table('list_parameter')
            ->where('nama_parameter', $data['nama_parameter'])
            ->first();

        if ($existingByBase) {
            if ($existingByName && (int) $existingByName->id !== (int) $existingByBase->id) {
                unset($data['nama_parameter']);
            }

            unset($data['created_at']);

            DB::table('list_parameter')
                ->where('id', $existingByBase->id)
                ->update($data);

            return;
        }

        if ($existingByName) {
            unset($data['created_at']);

            DB::table('list_parameter')
                ->where('id', $existingByName->id)
                ->update($data);

            return;
        }

        DB::table('list_parameter')->insert($data);
    }
}
