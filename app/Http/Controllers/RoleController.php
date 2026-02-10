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

        $permissions = Permission::query()->orderBy('permission_name')->get();

        return view('rbac.roles.index', [
            'title' => 'Roles',
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        $permissions = Permission::query()->orderBy('permission_name')->get();

        // Return JSON for AJAX or view for direct access
        if (request()->wantsJson()) {
            return response()->json(['permissions' => $permissions]);
        }

        return view('rbac.roles.index', [
            'title' => 'Roles',
            'roles' => Role::withCount('permissions')->orderBy('role_name')->get(),
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

        // Return JSON for AJAX
        if ($request->wantsJson() || $request->ajax()) {
            $role->load('permissions');
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil ditambahkan.',
                'role' => $role,
            ]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::query()->orderBy('permission_name')->get();
        $selected = $role->permissions->pluck('id')->all();

        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
            'selected' => $selected,
        ]);
    }

    public function edit(Role $role)
    {
        // Redirect to index for modal-based editing
        return redirect()->route('roles.index');
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

        // Return JSON for AJAX
        if ($request->wantsJson() || $request->ajax()) {
            $role->load('permissions');
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diperbarui.',
                'role' => $role,
            ]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();

        // Return JSON for AJAX
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus.',
            ]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
