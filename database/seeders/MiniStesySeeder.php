<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MiniStesySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        DB::table('sub_user')->truncate();
        DB::table('t_user')->truncate();

        DB::table('kat_view')->truncate();
        DB::table('filter')->truncate();
        DB::table('kategori_logger')->truncate();

        DB::table('t_lokasi')->truncate();
        DB::table('list_das')->truncate();

        DB::table('notifikasi')->truncate();
        DB::table('tingkat_siaga_awlr')->truncate();
        DB::table('rumus_debit')->truncate();
        DB::table('set_sinkronisasi')->truncate();
        DB::table('t_perbaikan')->truncate();
        DB::table('t_riwayat')->truncate();

        DB::table('foto_pos')->truncate();
        DB::table('jiat_data')->truncate();
        DB::table('t_informasi')->truncate();
        DB::table('parameter_sensor')->truncate();
        DB::table('t_logger')->truncate();

        DB::table('klasifikasi_hujan')->truncate();

        if (Schema::hasTable('t_s19_01')) DB::table('t_s19_01')->truncate();
        if (Schema::hasTable('t_s16_01')) DB::table('t_s16_01')->truncate();
        if (Schema::hasTable('temp_s19_latest')) DB::table('temp_s19_latest')->truncate();
        if (Schema::hasTable('temp_s16_latest')) DB::table('temp_s16_latest')->truncate();
        if (Schema::hasTable('logger_storage_map')) DB::table('logger_storage_map')->truncate();
        if (Schema::hasTable('ts_table_pool')) DB::table('ts_table_pool')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('roles')->insert([
            ['id' => 1, 'role_name' => 'superadmin'],
            ['id' => 2, 'role_name' => 'admin'],
            ['id' => 3, 'role_name' => 'user'],
        ]);

        DB::table('permissions')->insert([
            ['id' => 1, 'permission_name' => 'view_beranda'],
            ['id' => 2, 'permission_name' => 'view_peta_lokasi'],
            ['id' => 3, 'permission_name' => 'logout'],
            ['id' => 4, 'permission_name' => 'manage_logger'],
            ['id' => 5, 'permission_name' => 'manage_user'],
        ]);

        $rp = [];
        foreach ([1, 2, 3, 4, 5] as $pid) $rp[] = ['role_id' => 1, 'permission_id' => $pid];
        foreach ([1, 2, 3, 4] as $pid) $rp[] = ['role_id' => 2, 'permission_id' => $pid];
        foreach ([1, 2, 3] as $pid) $rp[] = ['role_id' => 3, 'permission_id' => $pid];
        DB::table('role_permissions')->insert($rp);

        DB::table('t_user')->insert([
            [
                'id_user' => 1,
                'nama' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'level_user' => 'superadmin',
                'alamat' => 'Kantor Pusat',
                'telp' => '081234567890',
                'instansi' => 'Beacon Engineering',
                'latitude' => '-7.797068',
                'longitude' => '110.370529',
                'zoom' => 11,
                'logo' => 'logo.png',
                'logo_mobile' => 'logo_mobile.png',
            ],
            [
                'id_user' => 2,
                'nama' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'level_user' => 'admin',
                'alamat' => 'Kantor Cabang',
                'telp' => '081200000000',
                'instansi' => 'Instansi Contoh',
                'latitude' => '-7.780000',
                'longitude' => '110.360000',
                'zoom' => 11,
                'logo' => 'logo.png',
                'logo_mobile' => 'logo_mobile.png',
            ],
        ]);

        DB::table('sub_user')->insert([
            ['id' => 1, 'id_user' => 1, 'nama' => 'Operator 1', 'level' => 'operator', 'no' => '081111111111', 'status' => 1],
            ['id' => 2, 'id_user' => 2, 'nama' => 'Operator 2', 'level' => 'operator', 'no' => '082222222222', 'status' => 1],
        ]);

        DB::table('list_das')->insert([
            ['id' => 1, 'nama_das' => 'DAS Progo'],
            ['id' => 2, 'nama_das' => 'DAS Opak'],
            ['id' => 3, 'nama_das' => 'DAS Serang'],
        ]);

        DB::table('t_lokasi')->insert([
            ['idlokasi' => 1, 'nama_lokasi' => 'Pos Seturan', 'latitude' => '-7.760000', 'longitude' => '110.410000', 'alamat' => 'Seturan, Yogyakarta', 'das_id' => 2],
            ['idlokasi' => 2, 'nama_lokasi' => 'Pos Pogung', 'latitude' => '-7.770000', 'longitude' => '110.370000', 'alamat' => 'Pogung, Yogyakarta', 'das_id' => 2],
            ['idlokasi' => 3, 'nama_lokasi' => 'Pos Sinduadi', 'latitude' => '-7.750000', 'longitude' => '110.350000', 'alamat' => 'Sinduadi, Yogyakarta', 'das_id' => 2],
            ['idlokasi' => 4, 'nama_lokasi' => 'Pos Bantar', 'latitude' => '-7.820000', 'longitude' => '110.330000', 'alamat' => 'Bantar, Bantul', 'das_id' => 2],
        ]);

        DB::table('kategori_logger')->insert([
            ['id_katlogger' => 1, 'nama_kategori' => 'AWLR', 'controller' => 'awlr', 'tabel' => 't_awlr', 'kepanjangan' => 'Automatic Water Level Recorder', 'temp_data' => 'temp_s16_latest', 'icon_app' => 'awlr.png', 'view' => 1],
            ['id_katlogger' => 2, 'nama_kategori' => 'ARR', 'controller' => 'arr', 'tabel' => 't_arr', 'kepanjangan' => 'Automatic Rain Recorder', 'temp_data' => 'temp_s16_latest', 'icon_app' => 'arr.png', 'view' => 1],
        ]);

        DB::table('kat_view')->insert([
            ['id' => 1, 'kategori_id' => 1, 'user_id' => 1],
            ['id' => 2, 'kategori_id' => 2, 'user_id' => 1],
            ['id' => 3, 'kategori_id' => 1, 'user_id' => 2],
        ]);

        DB::table('filter')->insert([
            ['id' => 1, 'id_kategori' => 2, 'nama_filter' => 'Tidak Hujan', 'icon' => 'arr_on'],
            ['id' => 2, 'id_kategori' => 2, 'nama_filter' => 'Hujan Sangat Ringan', 'icon' => 'arr_hujan_sangat_ringan'],
            ['id' => 3, 'id_kategori' => 2, 'nama_filter' => 'Hujan Ringan', 'icon' => 'arr_hujan_ringan'],
            ['id' => 4, 'id_kategori' => 2, 'nama_filter' => 'Hujan Sedang', 'icon' => 'arr_hujan_sedang'],
            ['id' => 5, 'id_kategori' => 2, 'nama_filter' => 'Hujan Lebat', 'icon' => 'arr_hujan_lebat'],
            ['id' => 6, 'id_kategori' => 2, 'nama_filter' => 'Hujan Sangat Lebat', 'icon' => 'arr_hujan_sangat_lebat'],
            ['id' => 7, 'id_kategori' => 2, 'nama_filter' => 'Perbaikan', 'icon' => 'arr_perbaikan'],
            ['id' => 8, 'id_kategori' => 2, 'nama_filter' => 'Koneksi Terputus', 'icon' => 'arr_of'],
        ]);

        DB::table('klasifikasi_hujan')->insert([
            ['id_klasifikasi' => 1, 'waktuper' => '1jam', 'hijau' => 5, 'biru' => '10', 'biru_tua' => 20, 'kuning' => 50, 'oranye' => 80, 'merah' => 100],
        ]);

        DB::table('t_logger')->insert([
            ['id' => 1, 'id_logger' => '10001', 'user_id' => 1, 'nama_logger' => 'AWLR Seturan', 'tabel_main' => 't_s19_01', 'jeda_notif' => 10, 'idlokasi' => 1, 'id_katlogger' => 1, 'sensor_count' => 19],
            ['id' => 2, 'id_logger' => '10002', 'user_id' => 1, 'nama_logger' => 'ARR Pogung', 'tabel_main' => 't_s16_01', 'jeda_notif' => 10, 'idlokasi' => 2, 'id_katlogger' => 2, 'sensor_count' => 16],
            ['id' => 3, 'id_logger' => '10003', 'user_id' => 1, 'nama_logger' => 'AWLR Sinduadi', 'tabel_main' => 't_s16_01', 'jeda_notif' => 15, 'idlokasi' => 3, 'id_katlogger' => 1, 'sensor_count' => 16],
            ['id' => 4, 'id_logger' => '10004', 'user_id' => 2, 'nama_logger' => 'ARR Bantar', 'tabel_main' => 't_s19_01', 'jeda_notif' => 20, 'idlokasi' => 4, 'id_katlogger' => 2, 'sensor_count' => 19],
        ]);

        DB::table('parameter_sensor')->insert([
            ['id_param' => 1, 'logger_id' => '10001', 'nama_parameter' => 'TMA', 'kolom_sensor' => 'sensor14', 'satuan' => 'cm', 'tipe_graf' => 'line', 'icon_app' => 'water', 'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' => 2, 'logger_id' => '10002', 'nama_parameter' => 'Curah Hujan', 'kolom_sensor' => 'sensor12', 'satuan' => 'mm', 'tipe_graf' => 'bar', 'icon_app' => 'rain', 'debit_awlr' => '-', 'parameter_utama' => 'hujan'],
            ['id_param' => 3, 'logger_id' => '10003', 'nama_parameter' => 'TMA', 'kolom_sensor' => 'sensor14', 'satuan' => 'cm', 'tipe_graf' => 'line', 'icon_app' => 'water', 'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' => 4, 'logger_id' => '10004', 'nama_parameter' => 'Curah Hujan', 'kolom_sensor' => 'sensor12', 'satuan' => 'mm', 'tipe_graf' => 'bar', 'icon_app' => 'rain', 'debit_awlr' => '-', 'parameter_utama' => 'hujan'],

            ['id_param' => 5, 'logger_id' => '10001', 'nama_parameter' => 'humidity_logger', 'kolom_sensor' => 'sensor5', 'satuan' => '%', 'tipe_graf' => 'line', 'icon_app' => 'water_percent', 'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 6, 'logger_id' => '10001', 'nama_parameter' => 'battery_logger', 'kolom_sensor' => 'sensor4', 'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80', 'debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 7, 'logger_id' => '10001', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor10', 'satuan' => '°C', 'tipe_graf' => 'line', 'icon_app' => 'thermometer', 'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' => 8, 'logger_id' => '10001', 'nama_parameter' => 'muka_air_tanah', 'kolom_sensor' => 'sensor1', 'satuan' => 'm', 'tipe_graf' => 'line', 'icon_app' => 'waves', 'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],

            ['id_param' => 9, 'logger_id' => '10003', 'nama_parameter' => 'humidity_logger', 'kolom_sensor' => 'sensor6', 'satuan' => '%', 'tipe_graf' => 'line', 'icon_app' => 'water_percent', 'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 10, 'logger_id' => '10003', 'nama_parameter' => 'battery_logger', 'kolom_sensor' => 'sensor8', 'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80', 'debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 11, 'logger_id' => '10003', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor11', 'satuan' => '°C', 'tipe_graf' => 'line', 'icon_app' => 'thermometer', 'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' => 12, 'logger_id' => '10003', 'nama_parameter' => 'muka_air_tanah', 'kolom_sensor' => 'sensor1', 'satuan' => 'm', 'tipe_graf' => 'line', 'icon_app' => 'waves', 'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],

            ['id_param' => 13, 'logger_id' => '10002', 'nama_parameter' => 'humidity_logger', 'kolom_sensor' => 'sensor3', 'satuan' => '%', 'tipe_graf' => 'line', 'icon_app' => 'water_percent', 'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 14, 'logger_id' => '10002', 'nama_parameter' => 'battery_logger', 'kolom_sensor' => 'sensor2', 'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80', 'debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 15, 'logger_id' => '10002', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor15', 'satuan' => '°C', 'tipe_graf' => 'line', 'icon_app' => 'thermometer', 'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' => 16, 'logger_id' => '10002', 'nama_parameter' => 'muka_air_tanah', 'kolom_sensor' => 'sensor1', 'satuan' => 'm', 'tipe_graf' => 'line', 'icon_app' => 'waves', 'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],

            ['id_param' => 17, 'logger_id' => '10004', 'nama_parameter' => 'humidity_logger', 'kolom_sensor' => 'sensor7', 'satuan' => '%', 'tipe_graf' => 'line', 'icon_app' => 'water_percent', 'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 18, 'logger_id' => '10004', 'nama_parameter' => 'battery_logger', 'kolom_sensor' => 'sensor5', 'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80', 'debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 19, 'logger_id' => '10004', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor2', 'satuan' => '°C', 'tipe_graf' => 'line', 'icon_app' => 'thermometer', 'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' => 20, 'logger_id' => '10004', 'nama_parameter' => 'muka_air_tanah', 'kolom_sensor' => 'sensor1', 'satuan' => 'm', 'tipe_graf' => 'line', 'icon_app' => 'waves', 'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],
        ]);

        DB::table('t_informasi')->insert([
            [
                'id_inf' => 1,
                'logger_id' => '10001',
                'seri_logger' => 'Beacon Logger V1',
                'sensor' => 'Ultrasonic',
                'serial_number' => 'SN-10001',
                'elevasi' => '100',
                'nosell' => '-',
                'nama_pic' => 'PIC 1',
                'no_pic' => '081234000001',
                'tanggal_pemasangan' => '2026-01-01',
                'garansi' => '2027-01-01',
                'awal_kontrak' => '2025-01-01',
                'imei' => '123456789012345',
                'gps1' => '-',
                'gps2' => '-',
                'gps3' => '-',
                'ad' => '-',
                'kd' => '-',
                'mr' => '-',
                'wdt' => '-',
            ],
            [
                'id_inf' => 2,
                'logger_id' => '10002',
                'seri_logger' => 'Beacon Logger V1',
                'sensor' => 'Tipping Bucket',
                'serial_number' => 'SN-10002',
                'elevasi' => '95',
                'nosell' => '-',
                'nama_pic' => 'PIC 2',
                'no_pic' => '081234000002',
                'tanggal_pemasangan' => '2026-01-02',
                'garansi' => '2027-01-02',
                'awal_kontrak' => '2025-01-02',
                'imei' => '123456789012346',
                'gps1' => '-',
                'gps2' => '-',
                'gps3' => '-',
                'ad' => '-',
                'kd' => '-',
                'mr' => '-',
                'wdt' => '-',
            ],
            [
                'id_inf' => 3,
                'logger_id' => '10003',
                'seri_logger' => 'Beacon Logger V2',
                'sensor' => 'Pressure',
                'serial_number' => 'SN-10003',
                'elevasi' => '102',
                'nosell' => '-',
                'nama_pic' => 'PIC 3',
                'no_pic' => '081234000003',
                'tanggal_pemasangan' => '2026-01-03',
                'garansi' => '2027-01-03',
                'awal_kontrak' => '2025-01-03',
                'imei' => '123456789012347',
                'gps1' => '-',
                'gps2' => '-',
                'gps3' => '-',
                'ad' => '-',
                'kd' => '-',
                'mr' => '-',
                'wdt' => '-',
            ],
            [
                'id_inf' => 4,
                'logger_id' => '10004',
                'seri_logger' => 'Beacon Logger V2',
                'sensor' => 'Tipping Bucket',
                'serial_number' => 'SN-10004',
                'elevasi' => '90',
                'nosell' => '-',
                'nama_pic' => 'PIC 4',
                'no_pic' => '081234000004',
                'tanggal_pemasangan' => '2026-01-04',
                'garansi' => '2027-01-04',
                'awal_kontrak' => '2025-01-04',
                'imei' => '123456789012348',
                'gps1' => '-',
                'gps2' => '-',
                'gps3' => '-',
                'ad' => '-',
                'kd' => '-',
                'mr' => '-',
                'wdt' => '-',
            ],
        ]);

        DB::table('jiat_data')->insert([
            ['id' => 1, 'id_logger' => '10001', 'kedalaman_sumur' => 5.5, 'kedalaman_pompa' => 2.0, 'kedalaman_sensor' => 1.2],
            ['id' => 2, 'id_logger' => '10002', 'kedalaman_sumur' => 4.2, 'kedalaman_pompa' => 1.7, 'kedalaman_sensor' => 1.0],
            ['id' => 3, 'id_logger' => '10003', 'kedalaman_sumur' => 6.1, 'kedalaman_pompa' => 2.4, 'kedalaman_sensor' => 1.4],
            ['id' => 4, 'id_logger' => '10004', 'kedalaman_sumur' => 3.9, 'kedalaman_pompa' => 1.5, 'kedalaman_sensor' => 0.9],
        ]);

        DB::table('foto_pos')->insert([
            ['id' => 1, 'id_logger' => '10001', 'url_foto' => 'pos/10001.png', 'foto_utama' => 1],
            ['id' => 2, 'id_logger' => '10002', 'url_foto' => 'pos/10002.png', 'foto_utama' => 1],
            ['id' => 3, 'id_logger' => '10003', 'url_foto' => 'pos/10003.png', 'foto_utama' => 1],
            ['id' => 4, 'id_logger' => '10004', 'url_foto' => 'pos/10004.png', 'foto_utama' => 1],
        ]);

        DB::table('tingkat_siaga_awlr')->insert([
            ['id' => 1, 'id_logger' => '10001', 'id_status' => 1, 'nama' => 'Normal', 'nilai' => 100.0, 'status' => 1, 'warna' => 'hijau'],
            ['id' => 2, 'id_logger' => '10003', 'id_status' => 1, 'nama' => 'Normal', 'nilai' => 105.0, 'status' => 1, 'warna' => 'hijau'],
            ['id' => 3, 'id_logger' => '10001', 'id_status' => 2, 'nama' => 'Siaga', 'nilai' => 140.0, 'status' => 1, 'warna' => 'kuning'],
            ['id' => 4, 'id_logger' => '10003', 'id_status' => 2, 'nama' => 'Siaga', 'nilai' => 145.0, 'status' => 1, 'warna' => 'kuning'],
        ]);

        DB::table('notifikasi')->insert([
            ['id' => 1, 'id_logger' => '10001', 'id_tingkat_siaga' => 1, 'tma' => 95.5, 'datetime' => '2025-01-10 10:00:00'],
            ['id' => 2, 'id_logger' => '10003', 'id_tingkat_siaga' => 2, 'tma' => 101.2, 'datetime' => '2025-01-10 10:05:00'],
            ['id' => 3, 'id_logger' => '10001', 'id_tingkat_siaga' => 3, 'tma' => 142.3, 'datetime' => '2025-01-11 08:00:00'],
            ['id' => 4, 'id_logger' => '10003', 'id_tingkat_siaga' => 4, 'tma' => 146.1, 'datetime' => '2025-01-11 08:10:00'],
        ]);

        DB::table('rumus_debit')->insert([
            ['id' => 1, 'id_logger' => '10001', 'rumus' => 'Q = a*(H^b)'],
        ]);

        DB::table('set_sinkronisasi')->insert([
            ['id' => 1, 'idlogger' => '10001', 'tanggal' => '2025-01-01'],
        ]);

        DB::table('t_perbaikan')->insert([
            ['id_perbaikan' => 1, 'id_logger' => '10001', 'data_terakhir' => 'OK', 'tabel' => 't_awlr'],
        ]);

        DB::table('t_riwayat')->insert([
            ['id_riwayat' => 1, 'id_logger' => '10001', 'tanggal' => '2025-01-05', 'kendala' => 'Tidak ada', 'perbaikan' => 'Tidak ada', 'gambar' => '-', 'file' => '-'],
        ]);
        DB::table('ts_table_pool')->insert([
            ['table_name' => 't_s16_01', 'sensor_count' => 16, 'max_logger' => 5, 'is_active' => 1, 'created_at' => now()],
            ['table_name' => 't_s19_01', 'sensor_count' => 19, 'max_logger' => 5, 'is_active' => 1, 'created_at' => now()],
        ]);

        DB::table('logger_storage_map')->insert([
            ['id_logger' => '10001', 'table_name' => 't_s19_01', 'sensor_count' => 19, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10002', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10003', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10004', 'table_name' => 't_s19_01', 'sensor_count' => 19, 'active' => 1, 'created_at' => now()],
        ]);

        $now = now()->format('Y-m-d H:i:s');

        $s16 = [
            [
                'id_logger' => '10002',
                'waktu' => $now,
                'sensor1' => 1.2,
                'sensor2' => 2.2,
                'sensor3' => 3.2,
                'sensor4' => 4.2,
                'sensor5' => 5.2,
                'sensor6' => 6.2,
                'sensor7' => 7.2,
                'sensor8' => 8.2,
                'sensor9' => 9.2,
                'sensor10' => 10.2,
                'sensor11' => 11.2,
                'sensor12' => 12.2,
                'sensor13' => 13.2,
                'sensor14' => 14.2,
                'sensor15' => 15.2,
                'sensor16' => 16.2,
            ],
            [
                'id_logger' => '10003',
                'waktu' => $now,
                'sensor1' => 1.3,
                'sensor2' => 2.3,
                'sensor3' => 3.3,
                'sensor4' => 4.3,
                'sensor5' => 5.3,
                'sensor6' => 6.3,
                'sensor7' => 7.3,
                'sensor8' => 8.3,
                'sensor9' => 9.3,
                'sensor10' => 10.3,
                'sensor11' => 11.3,
                'sensor12' => 12.3,
                'sensor13' => 13.3,
                'sensor14' => 140.5,
                'sensor15' => 15.3,
                'sensor16' => 16.3,
            ],
        ];
        DB::table('t_s16_01')->insert($s16);

        $s19 = [
            [
                'id_logger' => '10001',
                'waktu' => $now,
                'sensor1' => 1.1,
                'sensor2' => 2.1,
                'sensor3' => 3.1,
                'sensor4' => 4.1,
                'sensor5' => 5.1,
                'sensor6' => 6.1,
                'sensor7' => 7.1,
                'sensor8' => 8.1,
                'sensor9' => 9.1,
                'sensor10' => 10.1,
                'sensor11' => 11.1,
                'sensor12' => 12.1,
                'sensor13' => 13.1,
                'sensor14' => 120.5,
                'sensor15' => 15.1,
                'sensor16' => 16.1,
                'sensor17' => 17.1,
                'sensor18' => 18.1,
                'sensor19' => 19.1,
            ],
            [
                'id_logger' => '10004',
                'waktu' => $now,
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
                'sensor14' => 14.4,
                'sensor15' => 15.4,
                'sensor16' => 16.4,
                'sensor17' => 17.4,
                'sensor18' => 18.4,
                'sensor19' => 19.4,
            ],
        ];
        DB::table('t_s19_01')->insert($s19);

        $temp16 = DB::table('temp_s16_latest')->whereIn('id_logger', ['10002', '10003'])->get()->toArray();
        $temp19 = DB::table('temp_s19_latest')->whereIn('id_logger', ['10001', '10004'])->get()->toArray();

        if (count($temp16) === 0) {
            DB::table('temp_s16_latest')->insert([
                [
                    'id_logger' => '10002',
                    'waktu' => $now,
                    'sensor1' => 1.2,
                    'sensor2' => 2.2,
                    'sensor3' => 3.2,
                    'sensor4' => 4.2,
                    'sensor5' => 5.2,
                    'sensor6' => 6.2,
                    'sensor7' => 7.2,
                    'sensor8' => 8.2,
                    'sensor9' => 9.2,
                    'sensor10' => 10.2,
                    'sensor11' => 11.2,
                    'sensor12' => 12.2,
                    'sensor13' => 13.2,
                    'sensor14' => 14.2,
                    'sensor15' => 15.2,
                    'sensor16' => 16.2,
                    'updated_at' => $now,
                ],
                [
                    'id_logger' => '10003',
                    'waktu' => $now,
                    'sensor1' => 1.3,
                    'sensor2' => 2.3,
                    'sensor3' => 3.3,
                    'sensor4' => 4.3,
                    'sensor5' => 5.3,
                    'sensor6' => 6.3,
                    'sensor7' => 7.3,
                    'sensor8' => 8.3,
                    'sensor9' => 9.3,
                    'sensor10' => 10.3,
                    'sensor11' => 11.3,
                    'sensor12' => 12.3,
                    'sensor13' => 13.3,
                    'sensor14' => 140.5,
                    'sensor15' => 15.3,
                    'sensor16' => 16.3,
                    'updated_at' => $now,
                ],
            ]);
        }

        if (count($temp19) === 0) {
            DB::table('temp_s19_latest')->insert([
                [
                    'id_logger' => '10001',
                    'waktu' => $now,
                    'sensor1' => 1.1,
                    'sensor2' => 2.1,
                    'sensor3' => 3.1,
                    'sensor4' => 4.1,
                    'sensor5' => 5.1,
                    'sensor6' => 6.1,
                    'sensor7' => 7.1,
                    'sensor8' => 8.1,
                    'sensor9' => 9.1,
                    'sensor10' => 10.1,
                    'sensor11' => 11.1,
                    'sensor12' => 12.1,
                    'sensor13' => 13.1,
                    'sensor14' => 120.5,
                    'sensor15' => 15.1,
                    'sensor16' => 16.1,
                    'sensor17' => 17.1,
                    'sensor18' => 18.1,
                    'sensor19' => 19.1,
                    'updated_at' => $now,
                ],
                [
                    'id_logger' => '10004',
                    'waktu' => $now,
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
                    'sensor14' => 14.4,
                    'sensor15' => 15.4,
                    'sensor16' => 16.4,
                    'sensor17' => 17.4,
                    'sensor18' => 18.4,
                    'sensor19' => 19.4,
                    'updated_at' => $now,
                ],
            ]);
        }

        $start = Carbon::create(2026, 1, 7, 0, 0, 0);
        $end = Carbon::create(2026, 2, 5, 23, 0, 0);

        if (Schema::hasTable('t_s19_01')) {
            DB::table('t_s19_01')->where('id_logger', '10001')->whereBetween('waktu', [$start, $end])->delete();
        }

        if (Schema::hasTable('t_s16_01')) {
            DB::table('t_s16_01')->where('id_logger', '10003')->whereBetween('waktu', [$start, $end])->delete();
        }

        $badDays = [];
        $rates = [0.62, 0.68, 0.72, 0.75, 0.78, 0.82, 0.85, 0.88];

        $d = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();
        $days = [];

        while ($d <= $lastDay) {
            $days[] = $d->format('Y-m-d');
            $d->addDay();
        }

        shuffle($days);
        $pick = min(10, count($days));
        for ($i = 0; $i < $pick; $i++) {
            $badDays[$days[$i]] = $rates[$i % count($rates)];
        }

        $this->seedS19Logger10001($start, $end, $badDays);
        $this->seedS16Logger10003($start, $end, $badDays);
    }

    private function seedS19Logger10001(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 10;
        $bulk = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dayKey = $current->format('Y-m-d');
            $keepRate = $badDays[$dayKey] ?? 1.0;

            if ($keepRate >= 1.0 || (mt_rand(1, 10000) / 10000) <= $keepRate) {
                $time = $current->format('Y-m-d H:i:s');

                $bulk[] = [
                    'id_logger' => '10001',
                    'waktu' => $time,
                    'sensor1' => mt_rand(90, 110) / 10,
                    'sensor2' => mt_rand(90, 110) / 10,
                    'sensor3' => mt_rand(90, 110) / 10,
                    'sensor4' => mt_rand(120, 130) / 10,
                    'sensor5' => mt_rand(70, 90) / 10,
                    'sensor6' => mt_rand(70, 90) / 10,
                    'sensor7' => mt_rand(70, 90) / 10,
                    'sensor8' => mt_rand(70, 90) / 10,
                    'sensor9' => mt_rand(70, 90) / 10,
                    'sensor10' => mt_rand(25, 32),
                    'sensor11' => mt_rand(70, 90) / 10,
                    'sensor12' => mt_rand(0, 5) / 10,
                    'sensor13' => mt_rand(70, 90) / 10,
                    'sensor14' => mt_rand(1100, 1500) / 10,
                    'sensor15' => mt_rand(70, 90) / 10,
                    'sensor16' => mt_rand(70, 90) / 10,
                    'sensor17' => mt_rand(70, 90) / 10,
                    'sensor18' => mt_rand(70, 90) / 10,
                    'sensor19' => mt_rand(70, 90) / 10,
                ];

                if (count($bulk) >= 500) {
                    DB::table('t_s19_01')->insert($bulk);
                    $bulk = [];
                }
            }

            $current->addMinutes($intervalMinutes);
        }

        if (!empty($bulk)) DB::table('t_s19_01')->insert($bulk);
    }

    private function seedS16Logger10003(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 15;
        $bulk = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dayKey = $current->format('Y-m-d');
            $keepRate = $badDays[$dayKey] ?? 1.0;

            if ($keepRate >= 1.0 || (mt_rand(1, 10000) / 10000) <= $keepRate) {
                $time = $current->format('Y-m-d H:i:s');

                $bulk[] = [
                    'id_logger' => '10003',
                    'waktu' => $time,
                    'sensor1' => mt_rand(1, 5) / 10,
                    'sensor2' => mt_rand(1, 5) / 10,
                    'sensor3' => mt_rand(1, 5) / 10,
                    'sensor4' => mt_rand(1, 5) / 10,
                    'sensor5' => mt_rand(60, 90) / 10,
                    'sensor6' => mt_rand(60, 90) / 10,
                    'sensor7' => mt_rand(60, 90) / 10,
                    'sensor8' => mt_rand(120, 130) / 10,
                    'sensor9' => mt_rand(60, 90) / 10,
                    'sensor10' => mt_rand(60, 90) / 10,
                    'sensor11' => mt_rand(25, 32),
                    'sensor12' => mt_rand(0, 3) / 10,
                    'sensor13' => mt_rand(60, 90) / 10,
                    'sensor14' => mt_rand(1300, 1700) / 10,
                    'sensor15' => mt_rand(60, 90) / 10,
                    'sensor16' => mt_rand(60, 90) / 10,
                ];

                if (count($bulk) >= 500) {
                    DB::table('t_s16_01')->insert($bulk);
                    $bulk = [];
                }
            }

            $current->addMinutes($intervalMinutes);
        }

        if (!empty($bulk)) DB::table('t_s16_01')->insert($bulk);
    }
}
