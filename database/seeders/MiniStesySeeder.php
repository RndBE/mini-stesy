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
        if (Schema::hasTable('instansi')) DB::table('instansi')->truncate();

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
        if (Schema::hasTable('user_logger_access')) DB::table('user_logger_access')->truncate();
        if (Schema::hasTable('template_kategori_parameter')) DB::table('template_kategori_parameter')->truncate();
        if (Schema::hasTable('list_parameter')) DB::table('list_parameter')->truncate();
        if (Schema::hasTable('parameter_groups')) DB::table('parameter_groups')->truncate();
        DB::table('t_logger')->truncate();

        DB::table('klasifikasi_hujan')->truncate();
        if (Schema::hasTable('klasifikasi_threshold')) DB::table('klasifikasi_threshold')->truncate();

        if (Schema::hasTable('t_s19_01')) DB::table('t_s19_01')->truncate();
        if (Schema::hasTable('t_s16_01')) DB::table('t_s16_01')->truncate();
        if (Schema::hasTable('temp_s19_latest')) DB::table('temp_s19_latest')->truncate();
        if (Schema::hasTable('temp_s16_latest')) DB::table('temp_s16_latest')->truncate();
        if (Schema::hasTable('logger_storage_map')) DB::table('logger_storage_map')->truncate();
        if (Schema::hasTable('ts_table_pool')) DB::table('ts_table_pool')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('roles')->insert([
            ['id' => 1, 'role_name' => 'superadmin'],
            ['id' => 2, 'role_name' => 'instansi_admin'],
            ['id' => 3, 'role_name' => 'pegawai'],
        ]);

        $permissions = [
            ['id' => 1, 'permission_name' => 'view_dashboard'],
            ['id' => 2, 'permission_name' => 'view_beranda'],
            ['id' => 3, 'permission_name' => 'view_peta_lokasi'],
            ['id' => 4, 'permission_name' => 'view_realtime'],
            ['id' => 5, 'permission_name' => 'view_device'],
            ['id' => 6, 'permission_name' => 'manage_device'],
            ['id' => 7, 'permission_name' => 'view_data_perangkat'],
            ['id' => 8, 'permission_name' => 'manage_data_perangkat'],
            ['id' => 9, 'permission_name' => 'manage_instansi'],
            ['id' => 10, 'permission_name' => 'view_profile'],
            ['id' => 11, 'permission_name' => 'manage_profile'],
            ['id' => 12, 'permission_name' => 'logout'],
            ['id' => 13, 'permission_name' => 'manage_logger'],
            ['id' => 14, 'permission_name' => 'manage_user'],
            ['id' => 15, 'permission_name' => 'manage_rbac'],
        ];
        DB::table('permissions')->insert($permissions);

        $rp = [];
        $allPermissionIds = collect($permissions)->pluck('id')->all();
        foreach ($allPermissionIds as $pid) $rp[] = ['role_id' => 1, 'permission_id' => $pid];
        foreach ([1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 14] as $pid) $rp[] = ['role_id' => 2, 'permission_id' => $pid];
        foreach ([1, 2, 3, 4, 5, 7, 10, 11, 12] as $pid) $rp[] = ['role_id' => 3, 'permission_id' => $pid];
        DB::table('role_permissions')->insert($rp);

        $instansiBeaconId = null;
        $instansiContohId = null;
        if (Schema::hasTable('instansi')) {
            $instansiBeaconId = DB::table('instansi')->insertGetId([
                'nama' => 'Beacon Engineering',
                'alamat' => 'Kantor Pusat',
                'telp' => '081234567890',
                'latitude' => '-7.797068',
                'longitude' => '110.370529',
                'zoom' => 11,
                'logo' => 'logo_instansi/logo.png',
                'logo_mobile' => 'logo_instansi/logo_mobile.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $instansiContohId = DB::table('instansi')->insertGetId([
                'nama' => 'Instansi Contoh',
                'alamat' => 'Kantor Cabang',
                'telp' => '081200000000',
                'latitude' => '-7.780000',
                'longitude' => '110.360000',
                'zoom' => 11,
                'logo' => 'logo_instansi/logo.png',
                'logo_mobile' => 'logo_instansi/logo_mobile.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('t_user')->insert([
            [
                'id_user' => 1,
                'nama' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'level_user' => 'superadmin',
                'instansi_id' => $instansiBeaconId,
            ],
            [
                'id_user' => 2,
                'nama' => 'Instansi Admin',
                'username' => 'instansi_admin',
                'password' => Hash::make('password'),
                'level_user' => 'instansi_admin',
                'instansi_id' => $instansiContohId,
            ],
            [
                'id_user' => 3,
                'nama' => 'Pegawai Contoh',
                'username' => 'pegawai',
                'password' => Hash::make('password'),
                'level_user' => 'pegawai',
                'instansi_id' => $instansiContohId,
            ],
        ]);

        DB::table('sub_user')->insert([
            ['id' => 1, 'id_user' => 1, 'nama' => 'Operator 1', 'level' => 'operator', 'no' => '081111111111', 'status' => 1],
            ['id' => 2, 'id_user' => 2, 'nama' => 'Operator 2', 'level' => 'operator', 'no' => '082222222222', 'status' => 1],
            ['id' => 3, 'id_user' => 3, 'nama' => 'Operator 3', 'level' => 'operator', 'no' => '083333333333', 'status' => 1],
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
            ['id_katlogger' => 1, 'nama_kategori' => 'AWLR', 'kepanjangan' => 'Automatic Water Level Recorder', 'icon_app' => 'awlr.png', 'view' => 1],
            ['id_katlogger' => 2, 'nama_kategori' => 'ARR', 'kepanjangan' => 'Automatic Rain Recorder', 'icon_app' => 'arr.png', 'view' => 1],
        ]);

        if (Schema::hasTable('parameter_groups')) {
            DB::table('parameter_groups')->insert([
                ['id' => 1, 'kode_group' => 'LOGGER', 'nama_group' => 'Kesehatan Logger', 'deskripsi' => 'Parameter kesehatan/performa logger seperti baterai, suhu, dan kelembaban.', 'sort_order' => 1],
                ['id' => 2, 'kode_group' => 'ANGIN', 'nama_group' => 'Parameter Angin', 'deskripsi' => 'Parameter terkait angin seperti arah dan kecepatan.', 'sort_order' => 2],
                ['id' => 3, 'kode_group' => 'SUMUR', 'nama_group' => 'Parameter Sumur', 'deskripsi' => 'Parameter pengukuran utama sumur/air tanah.', 'sort_order' => 3],
            ]);
        }

        if (Schema::hasTable('list_parameter')) {
            DB::table('list_parameter')->insert([
                [
                    'id' => 1,
                    'nama_parameter' => 'muka_air_tanah',
                    'parameter_utama' => 'muka_air_tanah',
                    'default_satuan' => 'm',
                    'default_kolom_sensor' => 'sensor14',
                    'default_parameter_group_id' => 3,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'nama_parameter' => 'Curah Hujan',
                    'parameter_utama' => 'hujan',
                    'default_satuan' => 'mm',
                    'default_kolom_sensor' => 'sensor8',
                    'default_parameter_group_id' => 3,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'nama_parameter' => 'battery_logger',
                    'parameter_utama' => 'battery_logger',
                    'default_satuan' => 'Volt',
                    'default_kolom_sensor' => 'sensor6',
                    'default_parameter_group_id' => 1,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 4,
                    'nama_parameter' => 'humidity_logger',
                    'parameter_utama' => 'humidity_logger',
                    'default_satuan' => '%',
                    'default_kolom_sensor' => 'sensor4',
                    'default_parameter_group_id' => 1,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 5,
                    'nama_parameter' => 'temperature_logger',
                    'parameter_utama' => 'temperature_logger',
                    'default_satuan' => 'C',
                    'default_kolom_sensor' => 'sensor5',
                    'default_parameter_group_id' => 1,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

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

        if (Schema::hasTable('template_kategori_parameter')) {
            DB::table('template_kategori_parameter')->insert([
                [
                    'id' => 1,
                    'id_katlogger' => 1,
                    'list_parameter_id' => 1,
                    'urutan' => 1,
                    'kolom_sensor_default' => 'sensor14',
                    'satuan_override' => 'm',
                    'parameter_group_id' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'id_katlogger' => 1,
                    'list_parameter_id' => 3,
                    'urutan' => 2,
                    'kolom_sensor_default' => 'sensor6',
                    'satuan_override' => 'Volt',
                    'parameter_group_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'id_katlogger' => 1,
                    'list_parameter_id' => 4,
                    'urutan' => 3,
                    'kolom_sensor_default' => 'sensor4',
                    'satuan_override' => '%',
                    'parameter_group_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 4,
                    'id_katlogger' => 1,
                    'list_parameter_id' => 5,
                    'urutan' => 4,
                    'kolom_sensor_default' => 'sensor5',
                    'satuan_override' => 'C',
                    'parameter_group_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 5,
                    'id_katlogger' => 2,
                    'list_parameter_id' => 2,
                    'urutan' => 1,
                    'kolom_sensor_default' => 'sensor8',
                    'satuan_override' => 'mm',
                    'parameter_group_id' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 6,
                    'id_katlogger' => 2,
                    'list_parameter_id' => 3,
                    'urutan' => 2,
                    'kolom_sensor_default' => 'sensor6',
                    'satuan_override' => 'Volt',
                    'parameter_group_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 7,
                    'id_katlogger' => 2,
                    'list_parameter_id' => 4,
                    'urutan' => 3,
                    'kolom_sensor_default' => 'sensor4',
                    'satuan_override' => '%',
                    'parameter_group_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 8,
                    'id_katlogger' => 2,
                    'list_parameter_id' => 5,
                    'urutan' => 4,
                    'kolom_sensor_default' => 'sensor5',
                    'satuan_override' => 'C',
                    'parameter_group_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Dynamic rain classification thresholds for ARR kategori
        $arrKategoriId = DB::table('kategori_logger')->where('nama_kategori', 'ARR')->value('id_katlogger');

        if ($arrKategoriId && Schema::hasTable('klasifikasi_threshold')) {
            DB::table('klasifikasi_threshold')->insert([
                [
                    'id_kategori' => $arrKategoriId,
                    'state_key' => 'tidak_hujan',
                    'state_label' => 'Tidak Hujan',
                    'min_value' => null,
                    'max_value' => 0.10,
                    'icon_path' => '/icons/arr/tidak_hujan.svg',
                    'color_hex' => '#22c55e',
                    'sort_order' => 1,
                ],
                [
                    'id_kategori' => $arrKategoriId,
                    'state_key' => 'hujan_sangat_ringan',
                    'state_label' => 'Hujan Sangat Ringan',
                    'min_value' => 0.10,
                    'max_value' => 1.00,
                    'icon_path' => '/icons/arr/hujan_sangat_ringan.svg',
                    'color_hex' => '#7dd3fc',
                    'sort_order' => 2,
                ],
                [
                    'id_kategori' => $arrKategoriId,
                    'state_key' => 'hujan_ringan',
                    'state_label' => 'Hujan Ringan',
                    'min_value' => 1.00,
                    'max_value' => 2.50,
                    'icon_path' => '/icons/arr/hujan_ringan.svg',
                    'color_hex' => '#3b82f6',
                    'sort_order' => 3,
                ],
                [
                    'id_kategori' => $arrKategoriId,
                    'state_key' => 'hujan_sedang',
                    'state_label' => 'Hujan Sedang',
                    'min_value' => 2.50,
                    'max_value' => 7.60,
                    'icon_path' => '/icons/arr/hujan_sedang.svg',
                    'color_hex' => '#eab308',
                    'sort_order' => 4,
                ],
                [
                    'id_kategori' => $arrKategoriId,
                    'state_key' => 'hujan_lebat',
                    'state_label' => 'Hujan Lebat',
                    'min_value' => 7.60,
                    'max_value' => 15.60,
                    'icon_path' => '/icons/arr/hujan_lebat.svg',
                    'color_hex' => '#f97316',
                    'sort_order' => 5,
                ],
                [
                    'id_kategori' => $arrKategoriId,
                    'state_key' => 'hujan_sangat_lebat',
                    'state_label' => 'Hujan Sangat Lebat',
                    'min_value' => 15.60,
                    'max_value' => null,
                    'icon_path' => '/icons/arr/hujan_sangat_lebat.svg',
                    'color_hex' => '#ef4444',
                    'sort_order' => 6,
                ],
                [
                    'id_kategori' => $arrKategoriId,
                    'state_key' => 'koneksi_terputus',
                    'state_label' => 'Koneksi Terputus',
                    'min_value' => null,
                    'max_value' => null,
                    'icon_path' => '/icons/arr/koneksi_terputus.svg',
                    'color_hex' => '#9ca3af',
                    'sort_order' => 7,
                ],
            ]);
        }

        DB::table('t_logger')->insert([
            ['id' => 1, 'id_logger' => '10001', 'instansi_id' => $instansiBeaconId, 'nama_logger' => 'AWLR Seturan', 'tabel_main' => 't_s19_01', 'jeda_notif' => 1, 'idlokasi' => 1, 'id_katlogger' => 1, 'sensor_count' => 19],
            ['id' => 2, 'id_logger' => '10002', 'instansi_id' => $instansiBeaconId, 'nama_logger' => 'ARR Pogung', 'tabel_main' => 't_s16_01', 'jeda_notif' => 1, 'idlokasi' => 2, 'id_katlogger' => 2, 'sensor_count' => 16],
            ['id' => 3, 'id_logger' => '10003', 'instansi_id' => $instansiBeaconId, 'nama_logger' => 'AWLR Sinduadi', 'tabel_main' => 't_s16_01', 'jeda_notif' => 1, 'idlokasi' => 3, 'id_katlogger' => 1, 'sensor_count' => 16],
            ['id' => 4, 'id_logger' => '10004', 'instansi_id' => $instansiContohId, 'nama_logger' => 'ARR Bantar', 'tabel_main' => 't_s19_01', 'jeda_notif' => 1, 'idlokasi' => 4, 'id_katlogger' => 2, 'sensor_count' => 19],
        ]);

        if (Schema::hasTable('user_logger_access')) {
            DB::table('user_logger_access')->insert([
                ['user_id' => 3, 'logger_id' => '10004', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        DB::table('klasifikasi_hujan')->insert([
            ['id_klasifikasi' => 1,'logger_id'=> '10002', 'waktu' => 'perjam', 'debit_air' => 0, 'intensitas' => 'Tidak Hujan'],
            ['id_klasifikasi' => 2,'logger_id'=> '10002', 'waktu' => 'perjam', 'debit_air' => 0.1, 'intensitas' => 'Hujan Sangat Ringan'],
            ['id_klasifikasi' => 3,'logger_id'=> '10002', 'waktu' => 'perjam', 'debit_air' => 2.5, 'intensitas' => 'Hujan Ringan'],
            ['id_klasifikasi' => 4,'logger_id'=> '10002', 'waktu' => 'perjam', 'debit_air' => 7.6, 'intensitas' => 'Hujan Sedang'],
            ['id_klasifikasi' => 5,'logger_id'=> '10002', 'waktu' => 'perjam', 'debit_air' => 15.6, 'intensitas' => 'Hujan Lebat'],
            ['id_klasifikasi' => 6,'logger_id'=> '10002', 'waktu' => 'perjam', 'debit_air' => 30.1, 'intensitas' => 'Hujan Sangat Lebat'],
            ['id_klasifikasi' => 7,'logger_id'=> '10002', 'waktu' => 'perhari', 'debit_air' => 0, 'intensitas' => 'Tidak Hujan'],
            ['id_klasifikasi' => 8,'logger_id'=> '10002', 'waktu' => 'perhari', 'debit_air' => 2.5, 'intensitas' => 'Hujan Sangat Ringan'],
            ['id_klasifikasi' => 9,'logger_id'=> '10002', 'waktu' => 'perhari', 'debit_air' => 15.5, 'intensitas' => 'Hujan Ringan'],
            ['id_klasifikasi' => 10,'logger_id'=> '10002', 'waktu' => 'perhari', 'debit_air' => 60.5, 'intensitas' => 'Hujan Sedang'],
            ['id_klasifikasi' => 11,'logger_id'=> '10002', 'waktu' => 'perhari', 'debit_air' => 100.5, 'intensitas' => 'Hujan Lebat'],
            ['id_klasifikasi' => 12,'logger_id'=> '10002', 'waktu' => 'perhari', 'debit_air' => 250.5, 'intensitas' => 'Hujan Sangat Lebat'],
        ]);

        DB::table('parameter_sensor')->insert([
            ['id_param' => 1, 'logger_id' => '10001', 'nama_parameter' => 'TMA', 'kolom_sensor' => 'sensor14', 'satuan' => 'cm', 'tipe_graf' => 'line', 'icon_app' => 'water', 'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' => 2, 'logger_id' => '10002', 'nama_parameter' => 'Curah Hujan', 'kolom_sensor' => 'sensor8', 'satuan' => 'mm', 'tipe_graf' => 'bar', 'icon_app' => 'rain', 'debit_awlr' => '-', 'parameter_utama' => 'hujan'],
            ['id_param' => 3, 'logger_id' => '10003', 'nama_parameter' => 'TMA', 'kolom_sensor' => 'sensor14', 'satuan' => 'cm', 'tipe_graf' => 'line', 'icon_app' => 'water', 'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' => 4, 'logger_id' => '10004', 'nama_parameter' => 'Curah Hujan', 'kolom_sensor' => 'sensor8', 'satuan' => 'mm', 'tipe_graf' => 'bar', 'icon_app' => 'rain', 'debit_awlr' => '-', 'parameter_utama' => 'hujan'],

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

        if (Schema::hasTable('parameter_groups') && Schema::hasColumn('parameter_sensor', 'parameter_group_id')) {
            $loggerGroupId = DB::table('parameter_groups')->where('kode_group', 'LOGGER')->value('id');
            $anginGroupId = DB::table('parameter_groups')->where('kode_group', 'ANGIN')->value('id');
            $sumurGroupId = DB::table('parameter_groups')->where('kode_group', 'SUMUR')->value('id');

            if ($sumurGroupId) {
                DB::table('parameter_sensor')->update(['parameter_group_id' => $sumurGroupId]);
            }

            if ($loggerGroupId) {
                DB::table('parameter_sensor')
                    ->whereRaw('LOWER(COALESCE(nama_parameter, "")) LIKE ?', ['%battery%'])
                    ->orWhereRaw('LOWER(COALESCE(parameter_utama, "")) LIKE ?', ['%battery%'])
                    ->orWhereRaw('LOWER(COALESCE(nama_parameter, "")) LIKE ?', ['%humidity%'])
                    ->orWhereRaw('LOWER(COALESCE(parameter_utama, "")) LIKE ?', ['%humidity%'])
                    ->orWhereRaw('LOWER(COALESCE(nama_parameter, "")) LIKE ?', ['%temperature%'])
                    ->orWhereRaw('LOWER(COALESCE(parameter_utama, "")) LIKE ?', ['%temperature%'])
                    ->update(['parameter_group_id' => $loggerGroupId]);
            }

            if ($anginGroupId) {
                DB::table('parameter_sensor')
                    ->whereRaw('LOWER(COALESCE(nama_parameter, "")) LIKE ?', ['%angin%'])
                    ->orWhereRaw('LOWER(COALESCE(parameter_utama, "")) LIKE ?', ['%angin%'])
                    ->orWhereRaw('LOWER(COALESCE(nama_parameter, "")) LIKE ?', ['%wind%'])
                    ->orWhereRaw('LOWER(COALESCE(parameter_utama, "")) LIKE ?', ['%wind%'])
                    ->update(['parameter_group_id' => $anginGroupId]);
            }
        }

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
        $end = Carbon::create(2026, 2, 12, 23, 0, 0);

        if (Schema::hasTable('t_s19_01')) {
            DB::table('t_s19_01')->where('id_logger', '10001')->whereBetween('waktu', [$start, $end])->delete();
            DB::table('t_s19_01')->where('id_logger', '10004')->whereBetween('waktu', [$start, $end])->delete();
        }

        if (Schema::hasTable('t_s16_01')) {
            DB::table('t_s16_01')->where('id_logger', '10002')->whereBetween('waktu', [$start, $end])->delete();
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
        $this->seedS16Logger10002($start, $end, $badDays);
        $this->seedS19Logger10004($start, $end, $badDays);
    }

    private function seedS19Logger10001(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 1;
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
        $intervalMinutes = 1;
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

    private function seedS16Logger10002(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 1;
        $bulk = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dayKey = $current->format('Y-m-d');
            $keepRate = $badDays[$dayKey] ?? 1.0;

            if ($keepRate >= 1.0 || (mt_rand(1, 10000) / 10000) <= $keepRate) {
                $time = $current->format('Y-m-d H:i:s');
                $hour = (int) $current->format('H');

                // Rainfall pattern: higher chance during certain hours
                $rainChance = ($hour >= 13 && $hour <= 18) ? 0.4 : 0.15;
                $rainfall = (mt_rand(1, 100) / 100) <= $rainChance ? mt_rand(0, 150) / 10 : mt_rand(0, 5) / 100;

                $bulk[] = [
                    'id_logger' => '10002',
                    'waktu' => $time,
                    'sensor1' => mt_rand(10, 30) / 10,
                    'sensor2' => mt_rand(120, 130) / 10,
                    'sensor3' => mt_rand(65, 85) / 10,
                    'sensor4' => mt_rand(1, 5) / 10,
                    'sensor5' => mt_rand(1, 5) / 10,
                    'sensor6' => mt_rand(1, 5) / 10,
                    'sensor7' => mt_rand(1, 5) / 10,
                    'sensor8' => $rainfall,
                    'sensor9' => mt_rand(1, 5) / 10,
                    'sensor10' => mt_rand(1, 5) / 10,
                    'sensor11' => mt_rand(1, 5) / 10,
                    'sensor12' => mt_rand(1, 5) / 10,
                    'sensor13' => mt_rand(1, 5) / 10,
                    'sensor14' => mt_rand(1, 5) / 10,
                    'sensor15' => mt_rand(26, 31),
                    'sensor16' => mt_rand(1, 5) / 10,
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

    private function seedS19Logger10004(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 1;
        $bulk = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dayKey = $current->format('Y-m-d');
            $keepRate = $badDays[$dayKey] ?? 1.0;

            if ($keepRate >= 1.0 || (mt_rand(1, 10000) / 10000) <= $keepRate) {
                $time = $current->format('Y-m-d H:i:s');
                $hour = (int) $current->format('H');

                // Rainfall pattern: higher chance during night and afternoon
                $rainChance = ($hour >= 14 && $hour <= 19) || ($hour >= 0 && $hour <= 3) ? 0.35 : 0.12;
                $rainfall = (mt_rand(1, 100) / 100) <= $rainChance ? mt_rand(0, 180) / 10 : mt_rand(0, 8) / 100;

                $bulk[] = [
                    'id_logger' => '10004',
                    'waktu' => $time,
                    'sensor1' => mt_rand(15, 35) / 10,
                    'sensor2' => mt_rand(25, 30),
                    'sensor3' => mt_rand(1, 5) / 10,
                    'sensor4' => mt_rand(1, 5) / 10,
                    'sensor5' => mt_rand(120, 128) / 10,
                    'sensor6' => mt_rand(1, 5) / 10,
                    'sensor7' => mt_rand(70, 82) / 10,
                    'sensor8' => $rainfall,
                    'sensor9' => mt_rand(1, 5) / 10,
                    'sensor10' => mt_rand(1, 5) / 10,
                    'sensor11' => mt_rand(1, 5) / 10,
                    'sensor12' => mt_rand(1, 5) / 10,
                    'sensor13' => mt_rand(1, 5) / 10,
                    'sensor14' => mt_rand(1, 5) / 10,
                    'sensor15' => mt_rand(1, 5) / 10,
                    'sensor16' => mt_rand(1, 5) / 10,
                    'sensor17' => mt_rand(1, 5) / 10,
                    'sensor18' => mt_rand(1, 5) / 10,
                    'sensor19' => mt_rand(1, 5) / 10,
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
}
