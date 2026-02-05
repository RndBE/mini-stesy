<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('role_name')
            ->get();

        return view('rbac.roles.index', [
            'title' => 'Roles',
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = Permission::query()->orderBy('permission_name')->get();

        return view('rbac.roles.create', [
            'title' => 'Tambah Role',
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:50|unique:roles,role_name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create([
            'role_name' => $validated['role_name'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::query()->orderBy('permission_name')->get();
        $selected = $role->permissions()->pluck('permissions.id')->all();

        return view('rbac.roles.edit', [
            'title' => 'Edit Role',
            'role' => $role,
            'permissions' => $permissions,
            'selected' => $selected,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:50|unique:roles,role_name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role->update([
            'role_name' => $validated['role_name'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
