<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAwlrSinduadiTimurSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('t_logger') || !Schema::hasTable('t_lokasi')) {
            return;
        }

        $seedLoggerId = '10009';
        $seedTimestamp = '2026-01-09 06:00:00';

        $sourceLogger = DB::table('t_logger')->where('id_logger', '10003')->first();
        $instansiId = $sourceLogger->instansi_id ?? null;
        if (!$instansiId && Schema::hasTable('instansi')) {
            $instansiId = DB::table('instansi')->where('nama', 'Beacon Engineering')->value('id');
        }

        $locationId = DB::table('t_lokasi')->where('nama_lokasi', 'Pos Sinduadi Timur')->value('idlokasi');
        if (!$locationId) {
            $locationId = ((int) DB::table('t_lokasi')->max('idlokasi')) + 1;
            DB::table('t_lokasi')->insert([
                'idlokasi' => $locationId,
                'nama_lokasi' => 'Pos Sinduadi Timur',
                'latitude' => '-7.748500',
                'longitude' => '110.352500',
                'alamat' => 'Sinduadi Timur, Yogyakarta',
                'das_id' => 2,
            ]);
        }

        $existingLoggerPk = DB::table('t_logger')->where('id_logger', $seedLoggerId)->value('id');
        $loggerPayload = [
            'id_logger' => $seedLoggerId,
            'instansi_id' => $instansiId,
            'nama_logger' => 'AWLR Sinduadi Timur',
            'tabel_main' => 't_s16_01',
            'jeda_notif' => 1,
            'idlokasi' => $locationId,
            'id_katlogger' => 1,
            'sensor_count' => 16,
        ];

        if ($existingLoggerPk) {
            DB::table('t_logger')->where('id', $existingLoggerPk)->update($loggerPayload);
        } else {
            DB::table('t_logger')->insert(array_merge([
                'id' => ((int) DB::table('t_logger')->max('id')) + 1,
            ], $loggerPayload));
        }

        $parameterRows = [
            [
                'nama_parameter' => 'TMA',
                'kolom_sensor' => 'sensor14',
                'satuan' => 'm',
                'tipe_graf' => 'line',
                'icon_app' => 'water',
                'debit_awlr' => '-',
                'parameter_utama' => 'tma',
            ],
            [
                'nama_parameter' => 'humidity_logger',
                'kolom_sensor' => 'sensor6',
                'satuan' => '%',
                'tipe_graf' => 'line',
                'icon_app' => 'water_percent',
                'debit_awlr' => '-',
                'parameter_utama' => 'humidity_logger',
            ],
            [
                'nama_parameter' => 'battery_logger',
                'kolom_sensor' => 'sensor8',
                'satuan' => 'volt',
                'tipe_graf' => 'line',
                'icon_app' => 'battery_charging_80',
                'debit_awlr' => '-',
                'parameter_utama' => 'battery_logger',
            ],
            [
                'nama_parameter' => 'temperature_logger',
                'kolom_sensor' => 'sensor11',
                'satuan' => '°C',
                'tipe_graf' => 'line',
                'icon_app' => 'thermometer',
                'debit_awlr' => '-',
                'parameter_utama' => 'temperature_logger',
            ],
            [
                'nama_parameter' => 'muka_air_tanah',
                'kolom_sensor' => 'sensor1',
                'satuan' => 'm',
                'tipe_graf' => 'line',
                'icon_app' => 'waves',
                'debit_awlr' => '-',
                'parameter_utama' => 'muka_air_tanah',
            ],
        ];

        $nextParamId = ((int) DB::table('parameter_sensor')->max('id_param')) + 1;
        foreach ($parameterRows as $row) {
            $existingParamId = DB::table('parameter_sensor')
                ->where('logger_id', $seedLoggerId)
                ->where('parameter_utama', $row['parameter_utama'])
                ->value('id_param');

            $payload = array_merge([
                'logger_id' => $seedLoggerId,
            ], $row);

            if ($existingParamId) {
                DB::table('parameter_sensor')->where('id_param', $existingParamId)->update($payload);
            } else {
                DB::table('parameter_sensor')->insert(array_merge([
                    'id_param' => $nextParamId++,
                ], $payload));
            }
        }

        if (Schema::hasTable('parameter_groups') && Schema::hasColumn('parameter_sensor', 'parameter_group_id')) {
            $loggerGroupId = DB::table('parameter_groups')->where('kode_group', 'LOGGER')->value('id');
            $sumurGroupId = DB::table('parameter_groups')->where('kode_group', 'SUMUR')->value('id');

            if ($loggerGroupId) {
                DB::table('parameter_sensor')
                    ->where('logger_id', $seedLoggerId)
                    ->whereIn('parameter_utama', ['humidity_logger', 'battery_logger', 'temperature_logger'])
                    ->update(['parameter_group_id' => $loggerGroupId]);
            }

            if ($sumurGroupId) {
                DB::table('parameter_sensor')
                    ->where('logger_id', $seedLoggerId)
                    ->whereIn('parameter_utama', ['tma', 'muka_air_tanah'])
                    ->update(['parameter_group_id' => $sumurGroupId]);
            }
        }

        $informasiPayload = [
            'logger_id' => $seedLoggerId,
            'seri_logger' => 'Beacon Logger V2',
            'sensor' => 'Pressure',
            'serial_number' => 'SN-10009',
            'elevasi' => '103',
            'nosell' => '-',
            'nama_pic' => 'PIC 9',
            'no_pic' => '081234000009',
            'tanggal_pemasangan' => '2026-01-09',
            'garansi' => '2027-01-09',
            'awal_kontrak' => '2025-01-09',
            'imei' => '123456789012349',
            'gps1' => '-',
            'gps2' => '-',
            'gps3' => '-',
            'ad' => '-',
            'kd' => '-',
            'mr' => '-',
            'wdt' => '-',
        ];

        $existingInformasiId = DB::table('t_informasi')->where('logger_id', $seedLoggerId)->value('id_inf');
        if ($existingInformasiId) {
            DB::table('t_informasi')->where('id_inf', $existingInformasiId)->update($informasiPayload);
        } else {
            DB::table('t_informasi')->insert(array_merge([
                'id_inf' => ((int) DB::table('t_informasi')->max('id_inf')) + 1,
            ], $informasiPayload));
        }

        $jiatPayload = [
            'id_logger' => $seedLoggerId,
            'kedalaman_sumur' => 6.3,
            'kedalaman_pompa' => 2.5,
            'kedalaman_sensor' => 1.5,
            'has_pump' => true,
        ];

        $existingJiatId = DB::table('jiat_data')->where('id_logger', $seedLoggerId)->value('id');
        if ($existingJiatId) {
            DB::table('jiat_data')->where('id', $existingJiatId)->update($jiatPayload);
        } else {
            DB::table('jiat_data')->insert(array_merge([
                'id' => ((int) DB::table('jiat_data')->max('id')) + 1,
            ], $jiatPayload));
        }

        $existingFotoId = DB::table('foto_pos')
            ->where('id_logger', $seedLoggerId)
            ->where('foto_utama', 1)
            ->value('id');
        $fotoPayload = [
            'id_logger' => $seedLoggerId,
            'url_foto' => 'pos/10003.png',
            'foto_utama' => 1,
        ];

        if ($existingFotoId) {
            DB::table('foto_pos')->where('id', $existingFotoId)->update($fotoPayload);
        } else {
            DB::table('foto_pos')->insert(array_merge([
                'id' => ((int) DB::table('foto_pos')->max('id')) + 1,
            ], $fotoPayload));
        }

        $siagaMap = [];
        foreach ([
            ['id_status' => 1, 'nama' => 'Normal', 'nilai' => 106.0, 'status' => 1, 'warna' => 'hijau'],
            ['id_status' => 2, 'nama' => 'Siaga', 'nilai' => 146.0, 'status' => 1, 'warna' => 'kuning'],
        ] as $row) {
            $existingSiagaId = DB::table('tingkat_siaga_awlr')
                ->where('id_logger', $seedLoggerId)
                ->where('id_status', $row['id_status'])
                ->value('id');

            $payload = array_merge(['id_logger' => $seedLoggerId], $row);

            if ($existingSiagaId) {
                DB::table('tingkat_siaga_awlr')->where('id', $existingSiagaId)->update($payload);
                $siagaMap[$row['id_status']] = $existingSiagaId;
            } else {
                $newId = ((int) DB::table('tingkat_siaga_awlr')->max('id')) + 1;
                DB::table('tingkat_siaga_awlr')->insert(array_merge(['id' => $newId], $payload));
                $siagaMap[$row['id_status']] = $newId;
            }
        }

        foreach ([
            ['datetime' => '2025-01-12 09:00:00', 'tma' => 102.4, 'id_status' => 1],
            ['datetime' => '2025-01-12 09:15:00', 'tma' => 147.2, 'id_status' => 2],
        ] as $row) {
            $existingNotifId = DB::table('notifikasi')
                ->where('id_logger', $seedLoggerId)
                ->where('datetime', $row['datetime'])
                ->value('id');

            $payload = [
                'id_logger' => $seedLoggerId,
                'id_tingkat_siaga' => $siagaMap[$row['id_status']] ?? null,
                'tma' => $row['tma'],
                'datetime' => $row['datetime'],
            ];

            if ($existingNotifId) {
                DB::table('notifikasi')->where('id', $existingNotifId)->update($payload);
            } else {
                DB::table('notifikasi')->insert(array_merge([
                    'id' => ((int) DB::table('notifikasi')->max('id')) + 1,
                ], $payload));
            }
        }

        if (DB::table('ts_table_pool')->where('table_name', 't_s16_01')->exists()) {
            DB::table('ts_table_pool')
                ->where('table_name', 't_s16_01')
                ->where('max_logger', '<', 6)
                ->update(['max_logger' => 6]);
        } else {
            DB::table('ts_table_pool')->insert([
                'table_name' => 't_s16_01',
                'sensor_count' => 16,
                'max_logger' => 6,
                'is_active' => 1,
                'created_at' => now(),
            ]);
        }

        DB::table('logger_storage_map')->updateOrInsert(
            ['id_logger' => $seedLoggerId],
            [
                'table_name' => 't_s16_01',
                'sensor_count' => 16,
                'active' => 1,
                'created_at' => now(),
            ]
        );

        DB::table('t_s16_01')->updateOrInsert(
            ['id_logger' => $seedLoggerId, 'waktu' => $seedTimestamp],
            [
                'sensor1' => 1.4,
                'sensor2' => 2.4,
                'sensor3' => 3.4,
                'sensor4' => 4.4,
                'sensor5' => 5.4,
                'sensor6' => 6.4,
                'sensor7' => 7.4,
                'sensor8' => 8.4,
                'sensor9' => 9.4,
                'sensor10' => 10.4,
                'sensor11' => 11.4,
                'sensor12' => 12.4,
                'sensor13' => 13.4,
                'sensor14' => 142.8,
                'sensor15' => 15.4,
                'sensor16' => 16.4,
            ]
        );
    }
}
