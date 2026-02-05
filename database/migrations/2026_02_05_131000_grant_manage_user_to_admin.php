<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_permissions')) {
            return;
        }

        $roleId = DB::table('roles')->where('role_name', 'admin')->value('id');
        $permId = DB::table('permissions')->where('permission_name', 'manage_user')->value('id');

        if ($roleId && $permId) {
            $exists = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permId)
                ->exists();

            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_permissions')) {
            return;
        }

        $roleId = DB::table('roles')->where('role_name', 'admin')->value('id');
        $permId = DB::table('permissions')->where('permission_name', 'manage_user')->value('id');

        if ($roleId && $permId) {
            DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permId)
                ->delete();
        }
    }
};
