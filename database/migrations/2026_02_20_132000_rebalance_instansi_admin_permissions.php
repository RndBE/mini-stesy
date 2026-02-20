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

        $instansiAdminRoleIds = DB::table('roles')
            ->whereIn('role_name', ['instansi_admin', 'admin'])
            ->pluck('id')
            ->all();

        $pegawaiRoleIds = DB::table('roles')
            ->whereIn('role_name', ['pegawai', 'user'])
            ->pluck('id')
            ->all();

        $restrictedPermissions = DB::table('permissions')
            ->whereIn('permission_name', ['manage_instansi', 'manage_rbac'])
            ->pluck('id')
            ->all();

        if (!empty($instansiAdminRoleIds) && !empty($restrictedPermissions)) {
            DB::table('role_permissions')
                ->whereIn('role_id', $instansiAdminRoleIds)
                ->whereIn('permission_id', $restrictedPermissions)
                ->delete();
        }

        if (!empty($pegawaiRoleIds) && !empty($restrictedPermissions)) {
            DB::table('role_permissions')
                ->whereIn('role_id', $pegawaiRoleIds)
                ->whereIn('permission_id', $restrictedPermissions)
                ->delete();
        }

        $superadminRoleIds = DB::table('roles')
            ->where('role_name', 'superadmin')
            ->pluck('id')
            ->all();

        if (!empty($superadminRoleIds)) {
            $allPermissionIds = DB::table('permissions')->pluck('id')->all();

            foreach ($superadminRoleIds as $roleId) {
                foreach ($allPermissionIds as $permissionId) {
                    $exists = DB::table('role_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $permissionId)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_permissions')) {
            return;
        }

        $instansiAdminRoleIds = DB::table('roles')
            ->whereIn('role_name', ['instansi_admin', 'admin'])
            ->pluck('id')
            ->all();

        $permIds = DB::table('permissions')
            ->whereIn('permission_name', ['manage_instansi', 'manage_rbac'])
            ->pluck('id')
            ->all();

        foreach ($instansiAdminRoleIds as $roleId) {
            foreach ($permIds as $permId) {
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
    }
};
