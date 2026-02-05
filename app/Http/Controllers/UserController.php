<?php

namespace App\Http\Controllers;

use App\Models\t_User;
use App\Models\Role;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $query = t_User::query()->with('instansi')->orderBy('nama');
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->level_user !== 'superadmin') {
            $query->where('instansi_id', $currentUser->instansi_id);
        }
        $users = $query->get();

        return view('users.index', [
            'title' => 'User',
            'users' => $users,
        ]);
    }

    public function create()
    {
        $roles = Role::query()->orderBy('role_name')->get();
        $instansi = Instansi::query()->orderBy('nama')->get();

        return view('users.create', [
            'title' => 'Tambah User',
            'roles' => $roles,
            'instansi' => $instansi,
            'currentUser' => auth()->user(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:30|unique:t_user,username',
            'password' => 'required|string|min:6',
            'level_user' => 'required|string|exists:roles,role_name',
            'instansi_id' => 'nullable|integer|exists:instansi,id',
            'alamat' => 'required|string',
            'telp' => 'required|string|max:25',
            'latitude' => 'required|string|max:50',
            'longitude' => 'required|string|max:50',
            'zoom' => 'required|integer',
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo_mobile' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $currentUser = auth()->user();
        if ($currentUser && $currentUser->level_user !== 'superadmin') {
            if ($validated['level_user'] === 'superadmin') {
                abort(403);
            }
            $validated['instansi_id'] = $currentUser->instansi_id;
        }

        $instansiName = null;
        if (!empty($validated['instansi_id'])) {
            $instansi = Instansi::find($validated['instansi_id']);
            $instansiName = $instansi?->nama;
        }

        $logoPath = $request->file('logo')->store('logos', 'public');
        $logoMobilePath = $request->file('logo_mobile')->store('logos', 'public');

        t_User::create([
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'level_user' => $validated['level_user'],
            'alamat' => $validated['alamat'],
            'telp' => $validated['telp'],
            'instansi' => $instansiName,
            'instansi_id' => $validated['instansi_id'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'zoom' => $validated['zoom'],
            'logo' => $logoPath,
            'logo_mobile' => $logoMobilePath,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(t_User $user)
    {
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->level_user !== 'superadmin' && $user->instansi_id !== $currentUser->instansi_id) {
            abort(403);
        }
        $roles = Role::query()->orderBy('role_name')->get();
        $instansi = Instansi::query()->orderBy('nama')->get();

        return view('users.edit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles,
            'instansi' => $instansi,
            'currentUser' => $currentUser,
        ]);
    }

    public function update(Request $request, t_User $user)
    {
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->level_user !== 'superadmin' && $user->instansi_id !== $currentUser->instansi_id) {
            abort(403);
        }
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:30|unique:t_user,username,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6',
            'level_user' => 'required|string|exists:roles,role_name',
            'instansi_id' => 'nullable|integer|exists:instansi,id',
            'alamat' => 'required|string',
            'telp' => 'required|string|max:25',
            'latitude' => 'required|string|max:50',
            'longitude' => 'required|string|max:50',
            'zoom' => 'required|integer',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo_mobile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($currentUser && $currentUser->level_user !== 'superadmin') {
            if ($validated['level_user'] === 'superadmin') {
                abort(403);
            }
            $validated['instansi_id'] = $currentUser->instansi_id;
        }

        $instansiName = null;
        if (!empty($validated['instansi_id'])) {
            $instansi = Instansi::find($validated['instansi_id']);
            $instansiName = $instansi?->nama;
        }

        $payload = [
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'level_user' => $validated['level_user'],
            'alamat' => $validated['alamat'],
            'telp' => $validated['telp'],
            'instansi' => $instansiName,
            'instansi_id' => $validated['instansi_id'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'zoom' => $validated['zoom'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('logo')) {
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }
            $payload['logo'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('logo_mobile')) {
            if ($user->logo_mobile) {
                Storage::disk('public')->delete($user->logo_mobile);
            }
            $payload['logo_mobile'] = $request->file('logo_mobile')->store('logos', 'public');
        }

        $user->update($payload);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(t_User $user)
    {
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->level_user !== 'superadmin' && $user->instansi_id !== $currentUser->instansi_id) {
            abort(403);
        }
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
