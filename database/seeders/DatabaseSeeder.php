<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->upsert([
            ['id' => 1, 'role_name' => 'superadmin'],
            ['id' => 2, 'role_name' => 'admin'],
            ['id' => 3, 'role_name' => 'operator'],
            ['id' => 4, 'role_name' => 'viewer'],
        ], ['id'], ['role_name']);

        DB::table('permissions')->upsert([
            ['id' => 1, 'permission_name' => 'dashboard.view'],
            ['id' => 2, 'permission_name' => 'logger.view'],
            ['id' => 3, 'permission_name' => 'logger.create'],
            ['id' => 4, 'permission_name' => 'logger.update'],
            ['id' => 5, 'permission_name' => 'logger.delete'],
            ['id' => 6, 'permission_name' => 'timeseries.view'],
            ['id' => 7, 'permission_name' => 'timeseries.insert'],
            ['id' => 8, 'permission_name' => 'user.manage'],
            ['id' => 9, 'permission_name' => 'settings.manage'],
            ['id' => 10, 'permission_name' => 'notif.view'],
        ], ['id'], ['permission_name']);

        $allPermIds = DB::table('permissions')->pluck('id')->all();

        $rp = [];
        foreach ($allPermIds as $pid) {
            $rp[] = ['role_id' => 1, 'permission_id' => $pid];
        }

        foreach ([1,2,3,4,6,7,9,10] as $pid) {
            $rp[] = ['role_id' => 2, 'permission_id' => $pid];
        }

        foreach ([1,2,6,7,10] as $pid) {
            $rp[] = ['role_id' => 3, 'permission_id' => $pid];
        }

        foreach ([1,2,6,10] as $pid) {
            $rp[] = ['role_id' => 4, 'permission_id' => $pid];
        }

        DB::table('role_permissions')->upsert($rp, ['role_id','permission_id'], []);

        DB::table('kategori_logger')->upsert([
            [
                'id_katlogger' => 1,
                'nama_kategori' => 'AWLR',
                'controller' => 'awlr',
                'tabel' => 't_s16',
                'kepanjangan' => 'Automatic Water Level Recorder',
                'temp_data' => 'temp_s16_latest',
                'icon_app' => 'awlr.svg',
                'view' => 1,
            ],
            [
                'id_katlogger' => 2,
                'nama_kategori' => 'ARR',
                'controller' => 'arr',
                'tabel' => 't_s19',
                'kepanjangan' => 'Automatic Rain Recorder',
                'temp_data' => 'temp_s19_latest',
                'icon_app' => 'arr.svg',
                'view' => 1,
            ],
            [
                'id_katlogger' => 3,
                'nama_kategori' => 'APLR',
                'controller' => 'aplr',
                'tabel' => 't_s19',
                'kepanjangan' => 'Automatic Piezometer Logger Recorder',
                'temp_data' => 'temp_s19_latest',
                'icon_app' => 'aplr.svg',
                'view' => 1,
            ],
        ], ['id_katlogger'], ['nama_kategori','controller','tabel','kepanjangan','temp_data','icon_app','view']);

        DB::table('klasifikasi_hujan')->upsert([
            ['id_klasifikasi' => 1, 'waktuper' => '1jam', 'hijau' => 0, 'biru' => '0.5', 'biru_tua' => 5, 'kuning' => 10, 'oranye' => 20, 'merah' => 50],
            ['id_klasifikasi' => 2, 'waktuper' => '3jam', 'hijau' => 0, 'biru' => '1', 'biru_tua' => 10, 'kuning' => 20, 'oranye' => 35, 'merah' => 70],
            ['id_klasifikasi' => 3, 'waktuper' => '6jam', 'hijau' => 0, 'biru' => '2', 'biru_tua' => 15, 'kuning' => 30, 'oranye' => 50, 'merah' => 100],
            ['id_klasifikasi' => 4, 'waktuper' => '24jam', 'hijau' => 0, 'biru' => '5', 'biru_tua' => 20, 'kuning' => 50, 'oranye' => 100, 'merah' => 150],
        ], ['id_klasifikasi'], ['waktuper','hijau','biru','biru_tua','kuning','oranye','merah']);

        DB::table('t_user')->upsert([
            [
                'id_user' => 1,
                'nama' => 'Admin',
                'username' => 'admin',
                'password' => md5('admin123'),
                'level_user' => 'superadmin',
                'alamat' => '-',
                'telp' => '0000',
                'instansi' => 'Default',
                'latitude' => '0',
                'longitude' => '0',
                'zoom' => 10,
                'logo' => 'logo.png',
                'logo_mobile' => 'logo_mobile.png',
            ],
        ], ['id_user'], ['nama','username','password','level_user','alamat','telp','instansi','latitude','longitude','zoom','logo','logo_mobile']);

        DB::table('t_lokasi')->upsert([
            [
                'idlokasi' => 1,
                'nama_lokasi' => 'Lokasi 1',
                'latitude' => '-7.797068',
                'longitude' => '110.370529',
                'alamat' => 'Alamat Lokasi 1',
                'das' => 'DAS 1',
            ],
            [
                'idlokasi' => 2,
                'nama_lokasi' => 'Lokasi 2',
                'latitude' => '-6.175392',
                'longitude' => '106.827153',
                'alamat' => 'Alamat Lokasi 2',
                'das' => 'DAS 2',
            ],
        ], ['idlokasi'], ['nama_lokasi','latitude','longitude','alamat','das']);

        DB::table('t_logger')->upsert([
            [
                'id' => 1,
                'id_logger' => 'LGR001',
                'user_id' => 1,
                'nama_logger' => 'Logger 16 Sensor',
                'lokasi_logger' => 'Lokasi 1',
                'kategori_log' => 'AWLR',
                'tabel_main' => 't_s16_01',
                'jeda_notif' => 10,
                'idlokasi' => 1,
                'id_katlogger' => 1,
                'sensor_count' => 16,
            ],
            [
                'id' => 2,
                'id_logger' => 'LGR002',
                'user_id' => 1,
                'nama_logger' => 'Logger 19 Sensor',
                'lokasi_logger' => 'Lokasi 2',
                'kategori_log' => 'ARR',
                'tabel_main' => 't_s19_01',
                'jeda_notif' => 10,
                'idlokasi' => 2,
                'id_katlogger' => 2,
                'sensor_count' => 19,
            ],
        ], ['id'], ['id_logger','user_id','nama_logger','lokasi_logger','kategori_log','tabel_main','jeda_notif','idlokasi','id_katlogger','sensor_count']);

        $now = now();

        DB::table('ts_table_pool')->upsert([
            ['table_name' => 't_s16_01', 'sensor_count' => 16, 'max_logger' => 5, 'is_active' => 1, 'created_at' => $now],
            ['table_name' => 't_s19_01', 'sensor_count' => 19, 'max_logger' => 5, 'is_active' => 1, 'created_at' => $now],
        ], ['table_name'], ['sensor_count','max_logger','is_active','created_at']);

        DB::table('logger_storage_map')->upsert([
            ['id_logger' => 'LGR001', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => $now],
            ['id_logger' => 'LGR002', 'table_name' => 't_s19_01', 'sensor_count' => 19, 'active' => 1, 'created_at' => $now],
        ], ['id_logger'], ['table_name','sensor_count','active','created_at']);

        $waktu = $now->format('Y-m-d H:i:s');

        DB::table('t_s16_01')->insert([
            'id_logger' => 'LGR001',
            'waktu' => $waktu,
            'sensor1' => 1, 'sensor2' => 2, 'sensor3' => 3, 'sensor4' => 4,
            'sensor5' => 5, 'sensor6' => 6, 'sensor7' => 7, 'sensor8' => 8,
            'sensor9' => 9, 'sensor10' => 10, 'sensor11' => 11, 'sensor12' => 12,
            'sensor13' => 13, 'sensor14' => 14, 'sensor15' => 15, 'sensor16' => 16,
        ]);

        DB::table('t_s19_01')->insert([
            'id_logger' => 'LGR002',
            'waktu' => $waktu,
            'sensor1' => 1, 'sensor2' => 2, 'sensor3' => 3, 'sensor4' => 4,
            'sensor5' => 5, 'sensor6' => 6, 'sensor7' => 7, 'sensor8' => 8,
            'sensor9' => 9, 'sensor10' => 10, 'sensor11' => 11, 'sensor12' => 12,
            'sensor13' => 13, 'sensor14' => 14, 'sensor15' => 15, 'sensor16' => 16,
            'sensor17' => 17, 'sensor18' => 18, 'sensor19' => 19,
        ]);
    }
}
