<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $adminRole = DB::table('roles')->where('role_name', 'admin')->first();
            $instansiAdminRole = DB::table('roles')->where('role_name', 'instansi_admin')->first();

            if ($adminRole && !$instansiAdminRole) {
                DB::table('roles')->where('id', $adminRole->id)->update(['role_name' => 'instansi_admin']);
            }

            DB::table('t_user')->where('level_user', 'admin')->update(['level_user' => 'instansi_admin']);

            $userRole = DB::table('roles')->where('role_name', 'user')->first();
            $pegawaiRole = DB::table('roles')->where('role_name', 'pegawai')->first();

            if ($userRole && !$pegawaiRole) {
                DB::table('roles')->where('id', $userRole->id)->update(['role_name' => 'pegawai']);
            }

            DB::table('t_user')->where('level_user', 'user')->update(['level_user' => 'pegawai']);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $instansiAdminRole = DB::table('roles')->where('role_name', 'instansi_admin')->first();
            $adminRole = DB::table('roles')->where('role_name', 'admin')->first();

            if ($instansiAdminRole && !$adminRole) {
                DB::table('roles')->where('id', $instansiAdminRole->id)->update(['role_name' => 'admin']);
            }

            DB::table('t_user')->where('level_user', 'instansi_admin')->update(['level_user' => 'admin']);

            $pegawaiRole = DB::table('roles')->where('role_name', 'pegawai')->first();
            $userRole = DB::table('roles')->where('role_name', 'user')->first();

            if ($pegawaiRole && !$userRole) {
                DB::table('roles')->where('id', $pegawaiRole->id)->update(['role_name' => 'user']);
            }

            DB::table('t_user')->where('level_user', 'pegawai')->update(['level_user' => 'user']);
        });
    }
};
