<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()
            ->withCount('roles')
            ->orderBy('permission_name')
            ->get();

        return view('rbac.permissions.index', [
            'title' => 'Permissions',
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        return view('rbac.permissions.create', [
            'title' => 'Tambah Permission',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'permission_name' => 'required|string|max:100|unique:permissions,permission_name',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    public function edit(Permission $permission)
    {
        return view('rbac.permissions.edit', [
            'title' => 'Edit Permission',
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'permission_name' => 'required|string|max:100|unique:permissions,permission_name,' . $permission->id,
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
}
