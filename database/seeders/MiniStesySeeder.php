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
        if (Schema::hasTable('nonjiat_data')) DB::table('nonjiat_data')->truncate();
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
        if (Schema::hasTable('audit_logs')) DB::table('audit_logs')->truncate();

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

        if (Schema::hasTable('audit_logs')) {
            $seededAt = now();

            DB::table('audit_logs')->insert([
                [
                    'user_id' => 1,
                    'module' => 'Autentikasi',
                    'action_type' => 'LOGIN',
                    'activity' => 'Login berhasil',
                    'target' => 'Aplikasi Mini Stesy',
                    'status' => 'SUCCESS',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder Script',
                    'description' => 'Pengguna berhasil masuk ke aplikasi.',
                    'metadata' => json_encode([
                        'guard' => 'web',
                        'session_regenerated' => true,
                    ], JSON_UNESCAPED_UNICODE),
                    'occurred_at' => $seededAt->copy()->subMinutes(55),
                    'actor_name' => 'Super Admin',
                    'actor_username' => 'superadmin',
                    'actor_role' => 'superadmin',
                    'created_at' => $seededAt,
                    'updated_at' => $seededAt,
                ],
                [
                    'user_id' => 2,
                    'module' => 'Peta Lokasi',
                    'action_type' => 'VIEW',
                    'activity' => 'Membuka halaman peta lokasi',
                    'target' => 'Halaman Peta Lokasi',
                    'status' => 'SUCCESS',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder Script',
                    'description' => 'Pengguna meninjau daftar logger pada peta.',
                    'metadata' => json_encode([
                        'route' => 'peta.lokasi',
                        'filters' => ['kategori' => 'ALL', 'status' => 'ALL'],
                    ], JSON_UNESCAPED_UNICODE),
                    'occurred_at' => $seededAt->copy()->subMinutes(45),
                    'actor_name' => 'Instansi Admin',
                    'actor_username' => 'instansi_admin',
                    'actor_role' => 'instansi_admin',
                    'created_at' => $seededAt,
                    'updated_at' => $seededAt,
                ],
                [
                    'user_id' => 3,
                    'module' => 'Data Masuk',
                    'action_type' => 'EXPORT',
                    'activity' => 'Ekspor data sensor ke CSV',
                    'target' => 'Logger AWLR-001',
                    'status' => 'SUCCESS',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder Script',
                    'description' => 'Pengguna mengunduh hasil data sensor untuk kebutuhan analisis.',
                    'metadata' => json_encode([
                        'format' => 'csv',
                        'records' => 1440,
                        'date' => $seededAt->toDateString(),
                    ], JSON_UNESCAPED_UNICODE),
                    'occurred_at' => $seededAt->copy()->subMinutes(35),
                    'actor_name' => 'Pegawai Contoh',
                    'actor_username' => 'pegawai',
                    'actor_role' => 'pegawai',
                    'created_at' => $seededAt,
                    'updated_at' => $seededAt,
                ],
                [
                    'user_id' => 2,
                    'module' => 'Pengaturan Device',
                    'action_type' => 'UPDATE',
                    'activity' => 'Perubahan konfigurasi logger',
                    'target' => 'ID Logger 10366',
                    'status' => 'SUCCESS',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder Script',
                    'description' => 'Konfigurasi interval pengiriman data logger diperbarui.',
                    'metadata' => json_encode([
                        'field_changed' => 'interval_kirim',
                        'before' => '10 menit',
                        'after' => '5 menit',
                    ], JSON_UNESCAPED_UNICODE),
                    'occurred_at' => $seededAt->copy()->subMinutes(25),
                    'actor_name' => 'Instansi Admin',
                    'actor_username' => 'instansi_admin',
                    'actor_role' => 'instansi_admin',
                    'created_at' => $seededAt,
                    'updated_at' => $seededAt,
                ],
                [
                    'user_id' => 1,
                    'module' => 'API Integrasi',
                    'action_type' => 'SYNC',
                    'activity' => 'Sinkronisasi data ke endpoint eksternal',
                    'target' => 'Forward API',
                    'status' => 'FAILED',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder Script',
                    'description' => 'Sinkronisasi gagal karena timeout jaringan.',
                    'metadata' => json_encode([
                        'http_status' => 504,
                        'retry' => 2,
                    ], JSON_UNESCAPED_UNICODE),
                    'occurred_at' => $seededAt->copy()->subMinutes(15),
                    'actor_name' => 'Super Admin',
                    'actor_username' => 'superadmin',
                    'actor_role' => 'superadmin',
                    'created_at' => $seededAt,
                    'updated_at' => $seededAt,
                ],
            ]);
        }

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
            ['idlokasi' => 1, 'nama_lokasi' => 'Pos Seturan',    'latitude' => '-7.760000', 'longitude' => '110.410000', 'alamat' => 'Seturan, Yogyakarta',   'das_id' => 2],
            ['idlokasi' => 2, 'nama_lokasi' => 'Pos Pogung',     'latitude' => '-7.770000', 'longitude' => '110.370000', 'alamat' => 'Pogung, Yogyakarta',    'das_id' => 2],
            ['idlokasi' => 3, 'nama_lokasi' => 'Pos Sinduadi',   'latitude' => '-7.750000', 'longitude' => '110.350000', 'alamat' => 'Sinduadi, Yogyakarta',  'das_id' => 2],
            ['idlokasi' => 4, 'nama_lokasi' => 'Pos Bantar',     'latitude' => '-7.820000', 'longitude' => '110.330000', 'alamat' => 'Bantar, Bantul',        'das_id' => 2],
            ['idlokasi' => 5, 'nama_lokasi' => 'Pos Kranggan',   'latitude' => '-7.790000', 'longitude' => '110.420000', 'alamat' => 'Kranggan, Sleman',      'das_id' => 1],
            ['idlokasi' => 6, 'nama_lokasi' => 'Pos Nanggulan',  'latitude' => '-7.810000', 'longitude' => '110.290000', 'alamat' => 'Nanggulan, Kulonprogo', 'das_id' => 1],
            ['idlokasi' => 7, 'nama_lokasi' => 'Pos Kretek',     'latitude' => '-7.960000', 'longitude' => '110.360000', 'alamat' => 'Kretek, Bantul',        'das_id' => 3],
            ['idlokasi' => 8, 'nama_lokasi' => 'Pos Bangunjiwo', 'latitude' => '-7.840000', 'longitude' => '110.345000', 'alamat' => 'Bangunjiwo, Bantul',    'das_id' => 2],
        ]);

        DB::table('kategori_logger')->insert([
            ['id_katlogger' => 1, 'nama_kategori' => 'AWLR', 'kepanjangan' => 'Automatic Water Level Recorder',    'icon_app' => 'awlr.png', 'view' => 1],
            ['id_katlogger' => 2, 'nama_kategori' => 'ARR',  'kepanjangan' => 'Automatic Rain Recorder',            'icon_app' => 'arr.png',  'view' => 1],
            ['id_katlogger' => 3, 'nama_kategori' => 'AFMR', 'kepanjangan' => 'Automatic Flow Measurement Recorder','icon_app' => 'afmr.png', 'view' => 1],
            ['id_katlogger' => 4, 'nama_kategori' => 'AWR',  'kepanjangan' => 'Automatic Weather Recorder',         'icon_app' => 'awr.png',  'view' => 1],
            ['id_katlogger' => 5, 'nama_kategori' => 'AWQR', 'kepanjangan' => 'Automatic Water Quality Recorder',   'icon_app' => 'awqr.png', 'view' => 1],
            ['id_katlogger' => 6, 'nama_kategori' => 'APMS', 'kepanjangan' => 'Automatic Peatland Monitoring System','icon_app' => 'apms.svg', 'view' => 1],
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
                ['id_kategori' => $arrKategoriId, 'state_key' => 'tidak_hujan',      'state_label' => 'Tidak Hujan',       'min_value' => null,  'max_value' => 0.10,  'icon_path' => '/icons/arr/tidak_hujan.svg',       'color_hex' => '#22c55e', 'sort_order' => 1],
                ['id_kategori' => $arrKategoriId, 'state_key' => 'hujan_sangat_ringan','state_label' => 'Hujan Sangat Ringan','min_value' => 0.10, 'max_value' => 1.00,  'icon_path' => '/icons/arr/hujan_sangat_ringan.svg','color_hex' => '#7dd3fc', 'sort_order' => 2],
                ['id_kategori' => $arrKategoriId, 'state_key' => 'hujan_ringan',     'state_label' => 'Hujan Ringan',      'min_value' => 1.00,  'max_value' => 2.50,  'icon_path' => '/icons/arr/hujan_ringan.svg',      'color_hex' => '#3b82f6', 'sort_order' => 3],
                ['id_kategori' => $arrKategoriId, 'state_key' => 'hujan_sedang',     'state_label' => 'Hujan Sedang',      'min_value' => 2.50,  'max_value' => 7.60,  'icon_path' => '/icons/arr/hujan_sedang.svg',      'color_hex' => '#eab308', 'sort_order' => 4],
                ['id_kategori' => $arrKategoriId, 'state_key' => 'hujan_lebat',      'state_label' => 'Hujan Lebat',       'min_value' => 7.60,  'max_value' => 15.60, 'icon_path' => '/icons/arr/hujan_lebat.svg',       'color_hex' => '#f97316', 'sort_order' => 5],
                ['id_kategori' => $arrKategoriId, 'state_key' => 'hujan_sangat_lebat','state_label' => 'Hujan Sangat Lebat','min_value' => 15.60, 'max_value' => null,  'icon_path' => '/icons/arr/hujan_sangat_lebat.svg','color_hex' => '#ef4444', 'sort_order' => 6],
                ['id_kategori' => $arrKategoriId, 'state_key' => 'koneksi_terputus', 'state_label' => 'Koneksi Terputus',  'min_value' => null,  'max_value' => null,  'icon_path' => '/icons/arr/koneksi_terputus.svg',  'color_hex' => '#9ca3af', 'sort_order' => 7],
            ]);
        }

        // Thresholds AFMR (online/offline only)
        $afmrKategoriId = DB::table('kategori_logger')->where('nama_kategori', 'AFMR')->value('id_katlogger');
        if ($afmrKategoriId && Schema::hasTable('klasifikasi_threshold')) {
            DB::table('klasifikasi_threshold')->insert([
                ['id_kategori' => $afmrKategoriId, 'state_key' => 'online',           'state_label' => 'Koneksi Terhubung', 'min_value' => null, 'max_value' => null, 'icon_path' => '/icons/afmr/online.svg',          'color_hex' => '#22c55e', 'sort_order' => 1],
                ['id_kategori' => $afmrKategoriId, 'state_key' => 'koneksi_terputus', 'state_label' => 'Koneksi Terputus',  'min_value' => null, 'max_value' => null, 'icon_path' => '/icons/afmr/offline.svg',         'color_hex' => '#9ca3af', 'sort_order' => 2],
            ]);
        }

        // Thresholds AWR (curah hujan per jam, mirip ARR, versi weather)
        $awrKategoriId = DB::table('kategori_logger')->where('nama_kategori', 'AWR')->value('id_katlogger');
        if ($awrKategoriId && Schema::hasTable('klasifikasi_threshold')) {
            DB::table('klasifikasi_threshold')->insert([
                ['id_kategori' => $awrKategoriId, 'state_key' => 'tidak_hujan',        'state_label' => 'Tidak Hujan',        'min_value' => null,  'max_value' => 0.10,  'icon_path' => '/icons/awr/tidak_hujan.svg',        'color_hex' => '#22c55e', 'sort_order' => 1],
                ['id_kategori' => $awrKategoriId, 'state_key' => 'awr_sangat_ringan',  'state_label' => 'Hujan Sangat Ringan','min_value' => 0.10,  'max_value' => 1.00,  'icon_path' => '/icons/awr/sangat_ringan.svg',      'color_hex' => '#7dd3fc', 'sort_order' => 2],
                ['id_kategori' => $awrKategoriId, 'state_key' => 'awr_ringan',         'state_label' => 'Hujan Ringan',       'min_value' => 1.00,  'max_value' => 2.50,  'icon_path' => '/icons/awr/ringan.svg',             'color_hex' => '#3b82f6', 'sort_order' => 3],
                ['id_kategori' => $awrKategoriId, 'state_key' => 'awr_sedang',         'state_label' => 'Hujan Sedang',       'min_value' => 2.50,  'max_value' => 7.60,  'icon_path' => '/icons/awr/sedang.svg',             'color_hex' => '#eab308', 'sort_order' => 4],
                ['id_kategori' => $awrKategoriId, 'state_key' => 'awr_lebat',          'state_label' => 'Hujan Lebat',        'min_value' => 7.60,  'max_value' => 15.60, 'icon_path' => '/icons/awr/lebat.svg',              'color_hex' => '#f97316', 'sort_order' => 5],
                ['id_kategori' => $awrKategoriId, 'state_key' => 'awr_sangat_lebat',   'state_label' => 'Hujan Sangat Lebat', 'min_value' => 15.60, 'max_value' => null,  'icon_path' => '/icons/awr/sangat_lebat.svg',       'color_hex' => '#ef4444', 'sort_order' => 6],
                ['id_kategori' => $awrKategoriId, 'state_key' => 'perbaikan',          'state_label' => 'Perbaikan',          'min_value' => null,  'max_value' => null,  'icon_path' => '/icons/awr/perbaikan.svg',          'color_hex' => '#a78bfa', 'sort_order' => 7],
                ['id_kategori' => $awrKategoriId, 'state_key' => 'koneksi_terputus',   'state_label' => 'Koneksi Terputus',   'min_value' => null,  'max_value' => null,  'icon_path' => '/icons/awr/offline.svg',            'color_hex' => '#9ca3af', 'sort_order' => 8],
            ]);
        }

        // Thresholds AWQR (online/offline only)
        $awqrKategoriId = DB::table('kategori_logger')->where('nama_kategori', 'AWQR')->value('id_katlogger');
        if ($awqrKategoriId && Schema::hasTable('klasifikasi_threshold')) {
            DB::table('klasifikasi_threshold')->insert([
                ['id_kategori' => $awqrKategoriId, 'state_key' => 'online',           'state_label' => 'Koneksi Terhubung', 'min_value' => null, 'max_value' => null, 'icon_path' => '/icons/awgr/awlr_map_pins_on.svg', 'color_hex' => '#22c55e', 'sort_order' => 1],
                ['id_kategori' => $awqrKategoriId, 'state_key' => 'koneksi_terputus', 'state_label' => 'Koneksi Terputus',  'min_value' => null, 'max_value' => null, 'icon_path' => '/icons/awgr/awlr_map_pins_off.svg','color_hex' => '#9ca3af', 'sort_order' => 2],
            ]);
        }

        DB::table('t_logger')->insert([
            ['id' => 1, 'id_logger' => '10366', 'instansi_id' => $instansiBeaconId,  'nama_logger' => 'AWLR Seturan',     'tabel_main' => 't_s19_01', 'jeda_notif' => 1, 'idlokasi' => 1, 'id_katlogger' => 1, 'sensor_count' => 19],
            ['id' => 2, 'id_logger' => '10002', 'instansi_id' => $instansiBeaconId,  'nama_logger' => 'ARR Pogung',       'tabel_main' => 't_s16_01', 'jeda_notif' => 1, 'idlokasi' => 2, 'id_katlogger' => 2, 'sensor_count' => 16],
            ['id' => 3, 'id_logger' => '10003', 'instansi_id' => $instansiBeaconId,  'nama_logger' => 'AWLR Sinduadi',    'tabel_main' => 't_s16_01', 'jeda_notif' => 1, 'idlokasi' => 3, 'id_katlogger' => 1, 'sensor_count' => 16],
            ['id' => 4, 'id_logger' => '10004', 'instansi_id' => $instansiContohId,  'nama_logger' => 'ARR Bantar',       'tabel_main' => 't_s19_01', 'jeda_notif' => 1, 'idlokasi' => 4, 'id_katlogger' => 2, 'sensor_count' => 19],
            ['id' => 5, 'id_logger' => '10005', 'instansi_id' => $instansiBeaconId,  'nama_logger' => 'AFMR Kranggan',    'tabel_main' => 't_s16_01', 'jeda_notif' => 1, 'idlokasi' => 5, 'id_katlogger' => 3, 'sensor_count' => 16],
            ['id' => 6, 'id_logger' => '10006', 'instansi_id' => $instansiBeaconId,  'nama_logger' => 'AWR Nanggulan',    'tabel_main' => 't_s19_01', 'jeda_notif' => 1, 'idlokasi' => 6, 'id_katlogger' => 4, 'sensor_count' => 19],
            ['id' => 7, 'id_logger' => '10007', 'instansi_id' => $instansiContohId,  'nama_logger' => 'AWQR Kretek',      'tabel_main' => 't_s16_01', 'jeda_notif' => 1, 'idlokasi' => 7, 'id_katlogger' => 5, 'sensor_count' => 16],
            ['id' => 8, 'id_logger' => '10008', 'instansi_id' => $instansiContohId,  'nama_logger' => 'AWLR Non-JIAT Bangunjiwo', 'tabel_main' => 't_s16_01', 'jeda_notif' => 1, 'idlokasi' => 8, 'id_katlogger' => 1, 'sensor_count' => 16],
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
            // ── AWLR 10366 ──────────────────────────────────────────────────────
            ['id_param' =>  1, 'logger_id' => '10366', 'nama_parameter' => 'TMA',                'kolom_sensor' => 'sensor14', 'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'water',              'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' =>  5, 'logger_id' => '10366', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor5',  'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' =>  6, 'logger_id' => '10366', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor4',  'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' =>  7, 'logger_id' => '10366', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor10', 'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' =>  8, 'logger_id' => '10366', 'nama_parameter' => 'muka_air_tanah',     'kolom_sensor' => 'sensor1',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'waves',              'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],
            // ── ARR 10002 ───────────────────────────────────────────────────────
            ['id_param' =>  2, 'logger_id' => '10002', 'nama_parameter' => 'Curah Hujan',        'kolom_sensor' => 'sensor8',  'satuan' => 'mm',   'tipe_graf' => 'bar',  'icon_app' => 'rain',               'debit_awlr' => '-', 'parameter_utama' => 'hujan'],
            ['id_param' => 13, 'logger_id' => '10002', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor3',  'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 14, 'logger_id' => '10002', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor2',  'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 15, 'logger_id' => '10002', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor15', 'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' => 16, 'logger_id' => '10002', 'nama_parameter' => 'muka_air_tanah',     'kolom_sensor' => 'sensor1',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'waves',              'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],
            // ── AWLR 10003 ──────────────────────────────────────────────────────
            ['id_param' =>  3, 'logger_id' => '10003', 'nama_parameter' => 'TMA',                'kolom_sensor' => 'sensor14', 'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'water',              'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' =>  9, 'logger_id' => '10003', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor6',  'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 10, 'logger_id' => '10003', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor8',  'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 11, 'logger_id' => '10003', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor11', 'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' => 12, 'logger_id' => '10003', 'nama_parameter' => 'muka_air_tanah',     'kolom_sensor' => 'sensor1',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'waves',              'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],
            // ── ARR 10004 ───────────────────────────────────────────────────────
            ['id_param' =>  4, 'logger_id' => '10004', 'nama_parameter' => 'Curah Hujan',        'kolom_sensor' => 'sensor8',  'satuan' => 'mm',   'tipe_graf' => 'bar',  'icon_app' => 'rain',               'debit_awlr' => '-', 'parameter_utama' => 'hujan'],
            ['id_param' => 17, 'logger_id' => '10004', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor7',  'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 18, 'logger_id' => '10004', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor5',  'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 19, 'logger_id' => '10004', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor2',  'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            ['id_param' => 20, 'logger_id' => '10004', 'nama_parameter' => 'muka_air_tanah',     'kolom_sensor' => 'sensor1',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'waves',              'debit_awlr' => '-', 'parameter_utama' => 'muka_air_tanah'],
            // ── AFMR 10005 (t_s16_01) ───────────────────────────────────────────
            ['id_param' => 21, 'logger_id' => '10005', 'nama_parameter' => 'luas_penampang',     'kolom_sensor' => 'sensor1',  'satuan' => 'm²',   'tipe_graf' => 'line', 'icon_app' => 'area_chart',         'debit_awlr' => '-', 'parameter_utama' => 'luas_penampang'],
            ['id_param' => 22, 'logger_id' => '10005', 'nama_parameter' => 'debit',              'kolom_sensor' => 'sensor2',  'satuan' => 'm³/s', 'tipe_graf' => 'line', 'icon_app' => 'water',              'debit_awlr' => '-', 'parameter_utama' => 'debit'],
            ['id_param' => 23, 'logger_id' => '10005', 'nama_parameter' => 'flow_velocity',      'kolom_sensor' => 'sensor3',  'satuan' => 'm/s',  'tipe_graf' => 'line', 'icon_app' => 'speed',              'debit_awlr' => '-', 'parameter_utama' => 'flow_velocity'],
            ['id_param' => 24, 'logger_id' => '10005', 'nama_parameter' => 'elevasi_muka_air',   'kolom_sensor' => 'sensor4',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'waves',              'debit_awlr' => '-', 'parameter_utama' => 'elevasi_muka'],
            ['id_param' => 25, 'logger_id' => '10005', 'nama_parameter' => 'jarak_sensor',       'kolom_sensor' => 'sensor5',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'straighten',         'debit_awlr' => '-', 'parameter_utama' => 'jarak_sensor'],
            ['id_param' => 26, 'logger_id' => '10005', 'nama_parameter' => 'elevasi_sensor',     'kolom_sensor' => 'sensor6',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'height',             'debit_awlr' => '-', 'parameter_utama' => 'elevasi_sensor'],
            ['id_param' => 27, 'logger_id' => '10005', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor9',  'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 28, 'logger_id' => '10005', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor10', 'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 29, 'logger_id' => '10005', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor11', 'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            // ── AWR 10006 (t_s19_01) ────────────────────────────────────────────
            ['id_param' => 30, 'logger_id' => '10006', 'nama_parameter' => 'kecepatan_angin',    'kolom_sensor' => 'sensor1',  'satuan' => 'Km',   'tipe_graf' => 'line', 'icon_app' => 'air',                'debit_awlr' => '-', 'parameter_utama' => 'kecepatan_angin'],
            ['id_param' => 31, 'logger_id' => '10006', 'nama_parameter' => 'arah_angin',         'kolom_sensor' => 'sensor2',  'satuan' => '°',    'tipe_graf' => 'line', 'icon_app' => 'explore',            'debit_awlr' => '-', 'parameter_utama' => 'arah_angin'],
            ['id_param' => 32, 'logger_id' => '10006', 'nama_parameter' => 'temperatur_udara',   'kolom_sensor' => 'sensor3',  'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperatur_udara'],
            ['id_param' => 33, 'logger_id' => '10006', 'nama_parameter' => 'kelembaban_udara',   'kolom_sensor' => 'sensor4',  'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'kelembaban_udara'],
            ['id_param' => 34, 'logger_id' => '10006', 'nama_parameter' => 'tekanan_udara',      'kolom_sensor' => 'sensor5',  'satuan' => 'hPa',  'tipe_graf' => 'line', 'icon_app' => 'compress',           'debit_awlr' => '-', 'parameter_utama' => 'tekanan'],
            ['id_param' => 35, 'logger_id' => '10006', 'nama_parameter' => 'kecerahan',          'kolom_sensor' => 'sensor6',  'satuan' => 'K Lux','tipe_graf' => 'line', 'icon_app' => 'light_mode',         'debit_awlr' => '-', 'parameter_utama' => 'kecerahan'],
            ['id_param' => 36, 'logger_id' => '10006', 'nama_parameter' => 'arah_cahaya',        'kolom_sensor' => 'sensor7',  'satuan' => '°',    'tipe_graf' => 'line', 'icon_app' => 'explore',            'debit_awlr' => '-', 'parameter_utama' => 'arah_cahaya'],
            ['id_param' => 37, 'logger_id' => '10006', 'nama_parameter' => 'Curah Hujan',        'kolom_sensor' => 'sensor8',  'satuan' => 'mm',   'tipe_graf' => 'bar',  'icon_app' => 'rain',               'debit_awlr' => '-', 'parameter_utama' => 'hujan'],
            ['id_param' => 38, 'logger_id' => '10006', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor12', 'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 39, 'logger_id' => '10006', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor13', 'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 40, 'logger_id' => '10006', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor14', 'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            // ── AWLR Non-JIAT 10008 (t_s16_01) ─────────────────────────────────
            ['id_param' => 53, 'logger_id' => '10008', 'nama_parameter' => 'tma',                'kolom_sensor' => 'sensor1',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'water',              'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' => 54, 'logger_id' => '10008', 'nama_parameter' => 'debit',              'kolom_sensor' => 'sensor2',  'satuan' => 'm³/s', 'tipe_graf' => 'line', 'icon_app' => 'waves',              'debit_awlr' => '-', 'parameter_utama' => 'debit'],
            ['id_param' => 55, 'logger_id' => '10008', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor4',  'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 56, 'logger_id' => '10008', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor5',  'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 57, 'logger_id' => '10008', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor6',  'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
            // ── AWQR 10007 (t_s16_01) ───────────────────────────────────────────
            ['id_param' => 41, 'logger_id' => '10007', 'nama_parameter' => 'tma',                'kolom_sensor' => 'sensor1',  'satuan' => 'mdpl', 'tipe_graf' => 'line', 'icon_app' => 'water',              'debit_awlr' => '-', 'parameter_utama' => 'tma'],
            ['id_param' => 42, 'logger_id' => '10007', 'nama_parameter' => 'ph_air',             'kolom_sensor' => 'sensor2',  'satuan' => '',     'tipe_graf' => 'line', 'icon_app' => 'science',            'debit_awlr' => '-', 'parameter_utama' => 'ph'],
            ['id_param' => 43, 'logger_id' => '10007', 'nama_parameter' => 'suhu_air',           'kolom_sensor' => 'sensor3',  'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'suhu_air'],
            ['id_param' => 44, 'logger_id' => '10007', 'nama_parameter' => 'orp',                'kolom_sensor' => 'sensor4',  'satuan' => 'mV',   'tipe_graf' => 'line', 'icon_app' => 'electric_bolt',      'debit_awlr' => '-', 'parameter_utama' => 'orp'],
            ['id_param' => 45, 'logger_id' => '10007', 'nama_parameter' => 'conductivity',       'kolom_sensor' => 'sensor5',  'satuan' => 'µS/cm','tipe_graf' => 'line', 'icon_app' => 'waves',              'debit_awlr' => '-', 'parameter_utama' => 'conductivity'],
            ['id_param' => 46, 'logger_id' => '10007', 'nama_parameter' => 'salinity',           'kolom_sensor' => 'sensor6',  'satuan' => 'PSU',  'tipe_graf' => 'line', 'icon_app' => 'water_drop',         'debit_awlr' => '-', 'parameter_utama' => 'salinity'],
            ['id_param' => 47, 'logger_id' => '10007', 'nama_parameter' => 'tds',                'kolom_sensor' => 'sensor7',  'satuan' => 'mg/L', 'tipe_graf' => 'line', 'icon_app' => 'opacity',            'debit_awlr' => '-', 'parameter_utama' => 'tds'],
            ['id_param' => 48, 'logger_id' => '10007', 'nama_parameter' => 'turbidity',          'kolom_sensor' => 'sensor8',  'satuan' => 'NTU',  'tipe_graf' => 'line', 'icon_app' => 'blur_on',            'debit_awlr' => '-', 'parameter_utama' => 'turbidity'],
            ['id_param' => 49, 'logger_id' => '10007', 'nama_parameter' => 'tinggi_sensor',      'kolom_sensor' => 'sensor9',  'satuan' => 'm',    'tipe_graf' => 'line', 'icon_app' => 'height',             'debit_awlr' => '-', 'parameter_utama' => 'tinggi_sensor'],
            ['id_param' => 50, 'logger_id' => '10007', 'nama_parameter' => 'humidity_logger',    'kolom_sensor' => 'sensor12', 'satuan' => '%',    'tipe_graf' => 'line', 'icon_app' => 'water_percent',      'debit_awlr' => '-', 'parameter_utama' => 'humidity_logger'],
            ['id_param' => 51, 'logger_id' => '10007', 'nama_parameter' => 'battery_logger',     'kolom_sensor' => 'sensor13', 'satuan' => 'volt', 'tipe_graf' => 'line', 'icon_app' => 'battery_charging_80','debit_awlr' => '-', 'parameter_utama' => 'battery_logger'],
            ['id_param' => 52, 'logger_id' => '10007', 'nama_parameter' => 'temperature_logger', 'kolom_sensor' => 'sensor14', 'satuan' => '°C',   'tipe_graf' => 'line', 'icon_app' => 'thermometer',        'debit_awlr' => '-', 'parameter_utama' => 'temperature_logger'],
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
                'logger_id' => '10366',
                'seri_logger' => 'Beacon Logger V1',
                'sensor' => 'Ultrasonic',
                'serial_number' => 'SN-10366',
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
            ['id' => 1, 'id_logger' => '10366', 'kedalaman_sumur' => 5.5, 'kedalaman_pompa' => 2.0, 'kedalaman_sensor' => 1.2, 'has_pump' => true],
            ['id' => 2, 'id_logger' => '10002', 'kedalaman_sumur' => 4.2, 'kedalaman_pompa' => 1.7, 'kedalaman_sensor' => 1.0, 'has_pump' => true],
            ['id' => 3, 'id_logger' => '10003', 'kedalaman_sumur' => 6.1, 'kedalaman_pompa' => 2.4, 'kedalaman_sensor' => 1.4, 'has_pump' => true],
            ['id' => 4, 'id_logger' => '10004', 'kedalaman_sumur' => 3.9, 'kedalaman_pompa' => 1.5, 'kedalaman_sensor' => 0.9, 'has_pump' => true],
        ]);

        // Non-JIAT data untuk AWLR Bangunjiwo (10008)
        if (Schema::hasTable('nonjiat_data')) {
            DB::table('nonjiat_data')->insert([
                [
                    'id_logger'           => '10008',
                    'jenis_sensor'        => 'ultrasonic',
                    'jarak_sensor_ke_air' => 2.50,   // jarak sensor ke permukaan air (m)
                    'tinggi_sensor'       => 5.00,   // tinggi sensor dari dasar sungai (m)
                    'elevasi_max'         => 5.00,   // batas skala atas peil (m)
                    'elevasi_min'         => 0.00,   // batas skala bawah peil (m)
                ],
            ]);
        }

        // AFMR Non-Contact data untuk AFMR Kranggan (10005)
        if (Schema::hasTable('afmr_noncontact_data')) {
            DB::table('afmr_noncontact_data')->insert([
                [
                    'id_logger'           => '10005',
                    'tinggi_sensor'       => 6.00,   // tinggi sensor dari dasar sungai (m)
                    'jarak_sensor_ke_air' => 3.50,   // jarak sensor ke permukaan air (m)
                    'elevasi_max'         => 6.00,   // batas skala atas (m)
                    'elevasi_min'         => 0.00,   // batas skala bawah (m)
                    'catatan'             => 'Sensor radar non-contact, konfigurasi awal',
                ],
            ]);
        }

        DB::table('foto_pos')->insert([
            ['id' => 1, 'id_logger' => '10366', 'url_foto' => 'pos/10366.png', 'foto_utama' => 1],
            ['id' => 2, 'id_logger' => '10002', 'url_foto' => 'pos/10002.png', 'foto_utama' => 1],
            ['id' => 3, 'id_logger' => '10003', 'url_foto' => 'pos/10003.png', 'foto_utama' => 1],
            ['id' => 4, 'id_logger' => '10004', 'url_foto' => 'pos/10004.png', 'foto_utama' => 1],
            ['id' => 5, 'id_logger' => '10005', 'url_foto' => 'pos/10005.png', 'foto_utama' => 1],
            ['id' => 6, 'id_logger' => '10006', 'url_foto' => 'pos/10006.png', 'foto_utama' => 1],
            ['id' => 7, 'id_logger' => '10007', 'url_foto' => 'pos/10007.png', 'foto_utama' => 1],
            ['id' => 8, 'id_logger' => '10008', 'url_foto' => 'pos/10008.png', 'foto_utama' => 1],
        ]);

        DB::table('tingkat_siaga_awlr')->insert([
            ['id' => 1, 'id_logger' => '10366', 'id_status' => 1, 'nama' => 'Normal', 'nilai' => 100.0, 'status' => 1, 'warna' => 'hijau'],
            ['id' => 2, 'id_logger' => '10003', 'id_status' => 1, 'nama' => 'Normal', 'nilai' => 105.0, 'status' => 1, 'warna' => 'hijau'],
            ['id' => 3, 'id_logger' => '10366', 'id_status' => 2, 'nama' => 'Siaga', 'nilai' => 140.0, 'status' => 1, 'warna' => 'kuning'],
            ['id' => 4, 'id_logger' => '10003', 'id_status' => 2, 'nama' => 'Siaga', 'nilai' => 145.0, 'status' => 1, 'warna' => 'kuning'],
        ]);

        DB::table('notifikasi')->insert([
            ['id' => 1, 'id_logger' => '10366', 'id_tingkat_siaga' => 1, 'tma' => 95.5, 'datetime' => '2025-01-10 10:00:00'],
            ['id' => 2, 'id_logger' => '10003', 'id_tingkat_siaga' => 2, 'tma' => 101.2, 'datetime' => '2025-01-10 10:05:00'],
            ['id' => 3, 'id_logger' => '10366', 'id_tingkat_siaga' => 3, 'tma' => 142.3, 'datetime' => '2025-01-11 08:00:00'],
            ['id' => 4, 'id_logger' => '10003', 'id_tingkat_siaga' => 4, 'tma' => 146.1, 'datetime' => '2025-01-11 08:10:00'],
        ]);

        DB::table('rumus_debit')->insert([
            ['id' => 1, 'id_logger' => '10366', 'rumus' => 'Q = a*(H^b)'],
        ]);

        DB::table('set_sinkronisasi')->insert([
            ['id' => 1, 'idlogger' => '10366', 'tanggal' => '2025-01-01'],
        ]);

        DB::table('t_perbaikan')->insert([
            ['id_perbaikan' => 1, 'id_logger' => '10366', 'data_terakhir' => 'OK', 'tabel' => 't_awlr'],
        ]);

        DB::table('t_riwayat')->insert([
            ['id_riwayat' => 1, 'id_logger' => '10366', 'tanggal' => '2025-01-05', 'kendala' => 'Tidak ada', 'perbaikan' => 'Tidak ada', 'gambar' => '-', 'file' => '-'],
        ]);
        DB::table('ts_table_pool')->insert([
            ['table_name' => 't_s16_01', 'sensor_count' => 16, 'max_logger' => 5, 'is_active' => 1, 'created_at' => now()],
            ['table_name' => 't_s19_01', 'sensor_count' => 19, 'max_logger' => 5, 'is_active' => 1, 'created_at' => now()],
        ]);

        DB::table('logger_storage_map')->insert([
            ['id_logger' => '10366', 'table_name' => 't_s19_01', 'sensor_count' => 19, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10002', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10003', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10004', 'table_name' => 't_s19_01', 'sensor_count' => 19, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10005', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10006', 'table_name' => 't_s19_01', 'sensor_count' => 19, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10007', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => now()],
            ['id_logger' => '10008', 'table_name' => 't_s16_01', 'sensor_count' => 16, 'active' => 1, 'created_at' => now()],
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
                'id_logger' => '10366',
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
        $temp19 = DB::table('temp_s19_latest')->whereIn('id_logger', ['10366', '10004'])->get()->toArray();

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
                    'id_logger' => '10366',
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

        $start = Carbon::create(2026, 2, 1, 0, 0, 0);
        $end = Carbon::create(2026, 3, 6, 16, 0, 0);

        if (Schema::hasTable('t_s19_01')) {
            DB::table('t_s19_01')->where('id_logger', '10366')->whereBetween('waktu', [$start, $end])->delete();
            DB::table('t_s19_01')->where('id_logger', '10004')->whereBetween('waktu', [$start, $end])->delete();
            DB::table('t_s19_01')->where('id_logger', '10006')->whereBetween('waktu', [$start, $end])->delete(); // AWR
        }

        if (Schema::hasTable('t_s16_01')) {
            DB::table('t_s16_01')->where('id_logger', '10002')->whereBetween('waktu', [$start, $end])->delete();
            DB::table('t_s16_01')->where('id_logger', '10003')->whereBetween('waktu', [$start, $end])->delete();
            DB::table('t_s16_01')->where('id_logger', '10005')->whereBetween('waktu', [$start, $end])->delete(); // AFMR
            DB::table('t_s16_01')->where('id_logger', '10007')->whereBetween('waktu', [$start, $end])->delete(); // AWQR
            DB::table('t_s16_01')->where('id_logger', '10008')->whereBetween('waktu', [$start, $end])->delete(); // AWLR Non-JIAT
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

        $this->seedS19Logger10366($start, $end, $badDays);
        $this->seedS16Logger10003($start, $end, $badDays);
        $this->seedS16Logger10002($start, $end, $badDays);
        $this->seedS19Logger10004($start, $end, $badDays);
        $this->seedS16Logger10005($start, $end, $badDays); // AFMR
        $this->seedS19Logger10006($start, $end, $badDays); // AWR
        $this->seedS16Logger10007($start, $end, $badDays); // AWQR
        $this->seedS16Logger10008($start, $end, $badDays); // AWLR Non-JIAT
    }

    private function seedS19Logger10366(Carbon $start, Carbon $end, array $badDays): void
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
                    'id_logger' => '10366',
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

    /**
     * AFMR Kranggan — t_s16_01
     * sensor1=luas_penampang, sensor2=debit, sensor3=flow_velocity,
     * sensor4=elevasi_muka_air, sensor5=jarak_sensor, sensor6=elevasi_sensor,
     * sensor9=humidity, sensor10=battery, sensor11=temperature
     */
    private function seedS16Logger10005(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 1;
        $bulk = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dayKey = $current->format('Y-m-d');
            $keepRate = $badDays[$dayKey] ?? 1.0;

            if ($keepRate >= 1.0 || (mt_rand(1, 10000) / 10000) <= $keepRate) {
                $time = $current->format('Y-m-d H:i:s');

                // simulasi debit berfluktuasi harian (peak siang)
                $hour   = (int) $current->format('H');
                $factor = 1.0 + 0.3 * sin(M_PI * ($hour - 6) / 12);

                $bulk[] = [
                    'id_logger' => '10005',
                    'waktu'     => $time,
                    'sensor1'   => round(mt_rand(180, 220) / 1.0 * $factor, 2),  // luas penampang m²
                    'sensor2'   => round(mt_rand(150, 190) / 1.0 * $factor, 3),  // debit m³/s
                    'sensor3'   => round(mt_rand(70, 110) / 100, 3),              // flow velocity m/s
                    'sensor4'   => round(mt_rand(4800, 5050) / 1000, 3),          // elevasi muka air m
                    'sensor5'   => round(mt_rand(700, 800) / 100, 2),             // jarak sensor m
                    'sensor6'   => round(mt_rand(1200, 1300) / 100, 3),           // elevasi sensor m
                    'sensor7'   => mt_rand(0, 3) / 10,
                    'sensor8'   => mt_rand(0, 3) / 10,
                    'sensor9'   => mt_rand(55, 80),                               // humidity %
                    'sensor10'  => mt_rand(120, 130) / 10,                        // battery V
                    'sensor11'  => mt_rand(27, 35),                               // temperature °C
                    'sensor12'  => mt_rand(0, 3) / 10,
                    'sensor13'  => mt_rand(0, 3) / 10,
                    'sensor14'  => mt_rand(0, 3) / 10,
                    'sensor15'  => mt_rand(0, 3) / 10,
                    'sensor16'  => mt_rand(0, 3) / 10,
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

    /**
     * AWR Nanggulan — t_s19_01
     * sensor1=kecepatan_angin, sensor2=arah_angin, sensor3=temperatur_udara,
     * sensor4=kelembaban_udara, sensor5=tekanan_udara, sensor6=kecerahan,
     * sensor7=arah_cahaya, sensor8=curah_hujan,
     * sensor12=humidity, sensor13=battery, sensor14=temperature
     */
    private function seedS19Logger10006(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 1;
        $bulk = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dayKey = $current->format('Y-m-d');
            $keepRate = $badDays[$dayKey] ?? 1.0;

            if ($keepRate >= 1.0 || (mt_rand(1, 10000) / 10000) <= $keepRate) {
                $time   = $current->format('Y-m-d H:i:s');
                $hour   = (int) $current->format('H');
                $isPagi = $hour >= 6 && $hour < 18;

                // curah hujan hanya kemungkinan tertentu
                $isRain  = mt_rand(1, 10) <= 2;
                $hujan   = $isRain ? mt_rand(5, 250) / 100 : 0;

                $bulk[] = [
                    'id_logger' => '10006',
                    'waktu'     => $time,
                    'sensor1'   => round(mt_rand(30, 150) / 1000, 3),           // kecepatan angin Km
                    'sensor2'   => round(mt_rand(0, 36000) / 100, 1),           // arah angin °
                    'sensor3'   => round(mt_rand(250, 340) / 10, 1),            // temperatur udara °C
                    'sensor4'   => round(mt_rand(600, 980) / 10, 1),            // kelembaban udara %
                    'sensor5'   => round(mt_rand(10000, 10300) / 10, 1),        // tekanan hPa
                    'sensor6'   => $isPagi ? round(mt_rand(10, 1200) / 10, 1) : 0, // kecerahan K Lux
                    'sensor7'   => round(mt_rand(0, 36000) / 100, 1),           // arah cahaya °
                    'sensor8'   => $hujan,                                       // curah hujan mm
                    'sensor9'   => mt_rand(0, 5) / 10,
                    'sensor10'  => mt_rand(0, 5) / 10,
                    'sensor11'  => mt_rand(0, 5) / 10,
                    'sensor12'  => mt_rand(55, 85),                              // humidity logger %
                    'sensor13'  => mt_rand(120, 130) / 10,                      // battery V
                    'sensor14'  => mt_rand(27, 35),                             // temperature logger °C
                    'sensor15'  => mt_rand(0, 5) / 10,
                    'sensor16'  => mt_rand(0, 5) / 10,
                    'sensor17'  => mt_rand(0, 5) / 10,
                    'sensor18'  => mt_rand(0, 5) / 10,
                    'sensor19'  => mt_rand(0, 5) / 10,
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

    /**
     * AWQR Kretek — t_s16_01
     * sensor1=tma, sensor2=ph_air, sensor3=suhu_air, sensor4=orp,
     * sensor5=conductivity, sensor6=salinity, sensor7=tds, sensor8=turbidity,
     * sensor9=tinggi_sensor, sensor12=humidity, sensor13=battery, sensor14=temperature
     */
    private function seedS16Logger10007(Carbon $start, Carbon $end, array $badDays): void
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
                    'id_logger' => '10007',
                    'waktu'     => $time,
                    'sensor1'   => round(mt_rand(1400, 1600) / 100, 2),   // tma mdpl
                    'sensor2'   => round(mt_rand(65, 85) / 10, 2),         // pH air
                    'sensor3'   => round(mt_rand(230, 280) / 10, 1),       // suhu air °C
                    'sensor4'   => round(mt_rand(450, 560),0),              // ORP mV
                    'sensor5'   => round(mt_rand(50, 150) / 10, 2),        // conductivity µS/cm
                    'sensor6'   => round(mt_rand(1, 10) / 100, 3),         // salinity PSU
                    'sensor7'   => round(mt_rand(100, 200) / 1, 0),        // TDS mg/L
                    'sensor8'   => round(mt_rand(10, 80) / 10, 1),         // turbidity NTU
                    'sensor9'   => round(mt_rand(1300, 1500) / 100, 2),    // tinggi sensor m
                    'sensor10'  => mt_rand(0, 3) / 10,
                    'sensor11'  => mt_rand(0, 3) / 10,
                    'sensor12'  => mt_rand(55, 85),                         // humidity logger %
                    'sensor13'  => mt_rand(120, 130) / 10,                  // battery V
                    'sensor14'  => mt_rand(27, 35),                         // temperature logger °C
                    'sensor15'  => mt_rand(0, 3) / 10,
                    'sensor16'  => mt_rand(0, 3) / 10,
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

    /**
     * AWLR Non-JIAT Bangunjiwo — t_s16_01
     * sensor1=tma (m), sensor2=debit (m³/s),
     * sensor4=humidity, sensor5=battery, sensor6=temperature
     * nonjiat_data: jarak_sensor_ke_air=2.5, tinggi_sensor=5, elevasi_min=0, elevasi_max=5
     */
    private function seedS16Logger10008(Carbon $start, Carbon $end, array $badDays): void
    {
        $intervalMinutes = 1;
        $bulk  = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dayKey  = $current->format('Y-m-d');
            $keepRate = $badDays[$dayKey] ?? 1.0;

            if ($keepRate >= 1.0 || (mt_rand(1, 10000) / 10000) <= $keepRate) {
                $time = $current->format('Y-m-d H:i:s');

                // TMA: 0.5–4.5 m, naik siang hari, sedikit noise
                $hour   = (int) $current->format('H');
                $base   = 1.8 + 0.8 * sin(M_PI * ($hour - 6) / 12);
                $noise  = mt_rand(-20, 20) / 100;
                $tma    = max(0.1, round($base + $noise, 3));

                // Debit: proporsional ke TMA² (Q ≈ k × H^b), k=2.5, b=2
                $debit  = round(2.5 * pow($tma, 2), 3);

                $bulk[] = [
                    'id_logger' => '10008',
                    'waktu'     => $time,
                    'sensor1'   => $tma,                           // TMA (m)
                    'sensor2'   => $debit,                         // Debit (m³/s)
                    'sensor3'   => mt_rand(0, 2) / 10,
                    'sensor4'   => mt_rand(55, 85),                // humidity %
                    'sensor5'   => mt_rand(120, 130) / 10,         // battery V
                    'sensor6'   => mt_rand(27, 35),                // temperature °C
                    'sensor7'   => mt_rand(0, 2) / 10,
                    'sensor8'   => mt_rand(0, 2) / 10,
                    'sensor9'   => mt_rand(0, 2) / 10,
                    'sensor10'  => mt_rand(0, 2) / 10,
                    'sensor11'  => mt_rand(0, 2) / 10,
                    'sensor12'  => mt_rand(0, 2) / 10,
                    'sensor13'  => mt_rand(0, 2) / 10,
                    'sensor14'  => mt_rand(0, 2) / 10,
                    'sensor15'  => mt_rand(0, 2) / 10,
                    'sensor16'  => mt_rand(0, 2) / 10,
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
