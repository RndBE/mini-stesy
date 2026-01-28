<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('role_name', 50)->unique();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('permission_name', 100)->unique();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('permission_id');

            $table->unique(['role_id', 'permission_id'], 'uq_role_perm');
            $table->index('role_id', 'idx_rp_role');
            $table->index('permission_id', 'idx_rp_perm');

            $table->foreign('role_id', 'fk_rp_role')->references('id')->on('roles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('permission_id', 'fk_rp_perm')->references('id')->on('permissions')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id_user');
            $table->string('nama', 100);
            $table->string('username', 30)->unique('uq_user_username');
            $table->string('password', 255);
            $table->string('level_user', 20);
            $table->text('alamat');
            $table->string('telp', 25);
            $table->string('instansi', 50)->nullable();
            $table->string('latitude', 50);
            $table->string('longitude', 50);
            $table->integer('zoom');
            $table->string('logo', 50);
            $table->string('logo_mobile', 225);
            $table->rememberToken();
        });

        Schema::create('sub_user', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->unsignedInteger('id_user');
            $table->string('nama', 225);
            $table->string('level', 15);
            $table->string('no', 225);
            $table->integer('status');

            $table->index('id_user', 'idx_subuser_user');
            $table->foreign('id_user', 'fk_subuser_user')->references('id_user')->on('t_user')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('list_das', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('nama_das', 225)->unique('uq_das');
        });

        Schema::create('t_lokasi', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('idlokasi');
            $table->string('nama_lokasi', 40)->unique('uq_lokasi_nama');
            $table->string('latitude', 20);
            $table->string('longitude', 20);
            $table->text('alamat');
            // $table->string('das', 225);
            $table->unsignedInteger('das_id');

            $table->foreign('das_id', 'fk_lokasi_das')->references('id')->on('list_das')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id_katlogger');
            $table->string('nama_kategori', 191)->unique('uq_kategori_nama');
            $table->string('controller', 20);
            $table->string('tabel', 20)->unique('uq_kategori_tabel');
            $table->text('kepanjangan');
            $table->string('temp_data', 20);
            $table->string('icon_app', 25);
            $table->integer('view');
        });

        Schema::create('kat_view', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->unsignedInteger('kategori_id');
            $table->unsignedInteger('user_id');

            $table->unique(['kategori_id', 'user_id'], 'uq_katview');
            $table->index('kategori_id', 'idx_katview_kat');
            $table->index('user_id', 'idx_katview_user');

            $table->foreign('kategori_id', 'fk_katview_kat')->references('id_katlogger')->on('kategori_logger')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('user_id', 'fk_katview_user')->references('id_user')->on('t_user')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('filter', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->unsignedInteger('id_kategori');
            $table->string('nama_filter', 225);
            $table->string('icon', 225);

            $table->index('id_kategori', 'idx_filter_kategori');
            $table->foreign('id_kategori', 'fk_filter_kategori')->references('id_katlogger')->on('kategori_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('klasifikasi_hujan', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id_klasifikasi');
            $table->string('waktuper', 10);
            $table->integer('hijau');
            $table->string('biru', 5);
            $table->integer('biru_tua');
            $table->integer('kuning');
            $table->integer('oranye');
            $table->integer('merah');
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 15)->unique('uq_logger_idlogger');
            $table->unsignedInteger('user_id');
            $table->string('nama_logger', 255);

            $table->string('tabel_main', 10);
            $table->integer('jeda_notif');
            $table->unsignedInteger('idlokasi')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedTinyInteger('sensor_count');

            $table->index('user_id', 'idx_logger_user');
            $table->index('idlokasi', 'idx_logger_lokasi');
            $table->index('id_katlogger', 'idx_logger_kat');
            $table->index('sensor_count', 'idx_logger_sensorcount');

            $table->foreign('user_id', 'fk_logger_user')->references('id_user')->on('t_user')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('idlokasi', 'fk_logger_lokasi')->references('idlokasi')->on('t_lokasi')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('id_katlogger', 'fk_logger_kat')->references('id_katlogger')->on('kategori_logger')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id_param');
            $table->string('logger_id', 15);
            $table->string('nama_parameter', 25);
            $table->string('kolom_sensor', 20);
            $table->string('satuan', 15);
            $table->string('tipe_graf', 20);
            $table->string('icon_app', 20);
            $table->string('debit_awlr', 15);
            $table->string('parameter_utama', 20);

            $table->index('logger_id', 'idx_param_logger');
            $table->index('kolom_sensor', 'idx_param_kolom');

            $table->foreign('logger_id', 'fk_param_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('t_informasi', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id_inf');
            $table->string('logger_id', 10);
            $table->text('seri_logger');
            $table->text('sensor');
            $table->string('serial_number', 25);
            $table->string('elevasi', 10);
            $table->string('nosell', 15);
            $table->string('nama_pic', 100);
            $table->string('no_pic', 100);
            $table->string('tanggal_pemasangan', 10);
            $table->string('garansi', 10);
            $table->string('awal_kontrak', 10);
            $table->string('imei', 20);
            $table->string('gps1', 30);
            $table->string('gps2', 30);
            $table->string('gps3', 30);
            $table->string('ad', 10);
            $table->string('kd', 10);
            $table->string('mr', 10);
            $table->string('wdt', 30);

            $table->index('logger_id', 'idx_info_logger');
            $table->foreign('logger_id', 'fk_info_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('jiat_data', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 25);
            $table->float('kedalaman_sumur');
            $table->float('kedalaman_pompa');
            $table->float('kedalaman_sensor');

            $table->index('id_logger', 'idx_jiat_logger');
            $table->foreign('id_logger', 'fk_jiat_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('foto_pos', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 20);
            $table->text('url_foto');
            $table->integer('foto_utama');

            $table->index('id_logger', 'idx_fotopos_logger');
            $table->foreign('id_logger', 'fk_fotopos_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('tingkat_siaga_awlr', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 15);
            $table->integer('id_status');
            $table->string('nama', 50);
            $table->float('nilai');
            $table->integer('status');
            $table->string('warna', 15);

            $table->index('id_logger', 'idx_tingkat_logger');
            $table->index('id_status', 'idx_tingkat_status');

            $table->foreign('id_logger', 'fk_tingkat_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 15);
            $table->unsignedInteger('id_tingkat_siaga');
            $table->double('tma');
            $table->string('datetime', 30);

            $table->index('id_logger', 'idx_notif_logger');
            $table->index('id_tingkat_siaga', 'idx_notif_tingkat');

            $table->foreign('id_logger', 'fk_notif_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_tingkat_siaga', 'fk_notif_tingkat')->references('id')->on('tingkat_siaga_awlr')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('rumus_debit', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 11);
            $table->text('rumus');

            $table->index('id_logger', 'idx_rumus_logger');
            $table->foreign('id_logger', 'fk_rumus_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('set_sinkronisasi', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('idlogger', 15);
            $table->string('tanggal', 15);

            $table->index('idlogger', 'idx_sink_logger');
            $table->foreign('idlogger', 'fk_sink_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('t_perbaikan', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id_perbaikan');
            $table->string('id_logger', 15);
            $table->text('data_terakhir');
            $table->string('tabel', 15);

            $table->index('id_logger', 'idx_perbaikan_logger');
            $table->foreign('id_logger', 'fk_perbaikan_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('t_riwayat', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id_riwayat');
            $table->string('id_logger', 20);
            $table->date('tanggal');
            $table->text('kendala');
            $table->text('perbaikan');
            $table->text('gambar');
            $table->string('file', 225);

            $table->index('id_logger', 'idx_riwayat_logger');
            $table->index('tanggal', 'idx_riwayat_tanggal');

            $table->foreign('id_logger', 'fk_riwayat_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Schema::create('ci_sessions', function (Blueprint $table) {
        //     $table->charset = 'utf8';
        //     $table->collation = 'utf8_general_ci';

        //     $table->string('id', 128);
        //     $table->string('ip_address', 45);
        //     $table->unsignedInteger('timestamp')->default(0);
        //     $table->binary('data');

        //     $table->index('timestamp', 'ci_sessions_timestamp');
        // });
    }

    public function down(): void
    {
        // Schema::dropIfExists('ci_sessions');
        Schema::dropIfExists('t_riwayat');
        Schema::dropIfExists('t_perbaikan');
        Schema::dropIfExists('set_sinkronisasi');
        Schema::dropIfExists('rumus_debit');
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('tingkat_siaga_awlr');
        Schema::dropIfExists('foto_pos');
        Schema::dropIfExists('jiat_data');
        Schema::dropIfExists('t_informasi');
        Schema::dropIfExists('parameter_sensor');
        Schema::dropIfExists('t_logger');
        Schema::dropIfExists('klasifikasi_hujan');
        Schema::dropIfExists('filter');
        Schema::dropIfExists('kat_view');
        Schema::dropIfExists('kategori_logger');

        Schema::dropIfExists('t_lokasi');
        Schema::dropIfExists('list_das');

        Schema::dropIfExists('sub_user');
        Schema::dropIfExists('t_user');

        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
