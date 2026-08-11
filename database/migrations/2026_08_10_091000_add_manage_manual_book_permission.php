<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_permissions')) {
            return;
        }

        $exists = DB::table('permissions')
            ->where('permission_name', 'manage_manual_book')
            ->exists();

        if ($exists) {
            return;
        }

        $id = DB::table('permissions')->insertGetId([
            'permission_name' => 'manage_manual_book',
        ]);

        // Default hanya superadmin. Role lain bisa diberi akses lewat halaman RBAC Role.
        $roleIds = DB::table('roles')
            ->where('role_name', 'superadmin')
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $id,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $id = DB::table('permissions')
            ->where('permission_name', 'manage_manual_book')
            ->value('id');

        if (!$id) {
            return;
        }

        if (Schema::hasTable('role_permissions')) {
            DB::table('role_permissions')->where('permission_id', $id)->delete();
        }

        DB::table('permissions')->where('id', $id)->delete();
    }
};
