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

        $permission = Permission::create($validated);

        // Return JSON for AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil ditambahkan.',
                'permission' => $permission,
            ]);
        }

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    public function show(Permission $permission)
    {
        return response()->json([
            'permission' => $permission,
        ]);
    }

    public function edit(Permission $permission)
    {
        // Redirect to index for modal-based editing
        return redirect()->route('permissions.index');
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'permission_name' => 'required|string|max:100|unique:permissions,permission_name,' . $permission->id,
        ]);

        $permission->update($validated);

        // Return JSON for AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil diperbarui.',
                'permission' => $permission,
            ]);
        }

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        // Return JSON for AJAX
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dihapus.',
            ]);
        }

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
}
