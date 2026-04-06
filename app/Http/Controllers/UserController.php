<?php

namespace App\Http\Controllers;

use App\Models\t_User;
use App\Models\Role;
use App\Models\Instansi;
use App\Models\t_Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        $query = t_User::query()
            ->with(['instansi', 'accessibleLoggers'])
            ->orderBy('nama');

        if ($currentUser && !$currentUser->isSuperAdmin()) {
            $query->where('instansi_id', $currentUser->instansi_id);
        }

        if ($currentUser && $currentUser->isInstansiAdmin()) {
            $query->whereIn('level_user', ['pegawai', 'user']);
        }

        $users = $query->get();

        $roles = Role::query()
            ->orderBy('role_name')
            ->get()
            ->filter(fn($role) => $this->canAssignRole($currentUser, (string) $role->role_name))
            ->values();

        $instansiQuery = Instansi::query()->orderBy('nama');
        if ($currentUser && !$currentUser->isSuperAdmin()) {
            $instansiQuery->where('id', $currentUser->instansi_id);
        }
        $instansi = $instansiQuery->get();

        $loggerOptions = t_Logger::query()
            ->forUser($currentUser)
            ->orderBy('nama_logger')
            ->get(['id_logger', 'nama_logger', 'instansi_id'])
            ->map(function ($logger) {
                return [
                    'id_logger' => (string) $logger->id_logger,
                    'nama_logger' => (string) $logger->nama_logger,
                    'instansi_id' => $logger->instansi_id !== null ? (int) $logger->instansi_id : null,
                ];
            })
            ->values();

        return view('users.index', [
            'title' => 'User',
            'users' => $users,
            'roles' => $roles,
            'instansi' => $instansi,
            'loggerOptions' => $loggerOptions,
            'currentUser' => $currentUser,
        ]);
    }

    public function create()
    {
        return redirect()->route('users.index');
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser || (!$currentUser->isSuperAdmin() && !$currentUser->isInstansiAdmin())) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:100',
            'username'      => 'required|string|max:30|unique:t_user,username',
            'password'      => 'required|string|min:6',
            'level_user'    => 'required|string|max:50',
            'instansi_id'   => 'nullable|integer|exists:instansi,id',
            'logger_access' => 'nullable|array',
            'logger_access.*' => ['string', 'max:15', Rule::exists('t_logger', 'id_logger')],
            'status'        => 'nullable|in:aktif,suspend,non-aktif',
            'suspend_reason'=> 'nullable|string|max:1000',
        ]);

        $validated = $validator->validate();

        $targetRole = $this->normalizeRoleName((string) $validated['level_user']);
        if (!Role::query()->where('role_name', $targetRole)->exists()) {
            throw ValidationException::withMessages([
                'level_user' => 'Role tidak valid.',
            ]);
        }
        if (!$this->canAssignRole($currentUser, $targetRole)) {
            abort(403);
        }

        $validated['level_user'] = $targetRole;

        if ($currentUser->isInstansiAdmin()) {
            $validated['instansi_id'] = $currentUser->instansi_id;
        }

        if ($validated['level_user'] !== 'superadmin' && empty($validated['instansi_id'])) {
            throw ValidationException::withMessages([
                'instansi_id' => 'Instansi wajib dipilih.',
            ]);
        }

        $user = t_User::create([
            'nama'       => $validated['nama'],
            'username'   => $validated['username'],
            'password'   => Hash::make($validated['password']),
            'level_user' => $validated['level_user'],
            'instansi_id'=> $validated['instansi_id'] ?? null,
            'status'     => 'aktif', // Default aktif saat buat user baru
        ]);

        $loggerIds = $request->input('logger_access', []);
        $this->syncLoggerAccessForUser($user, $loggerIds, $currentUser);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(t_User $user)
    {
        return redirect()->route('users.index');
    }

    public function show(t_User $user)
    {
        $currentUser = auth()->user();
        if (!$this->canManageUser($currentUser, $user)) {
            abort(403);
        }

        $user->loadMissing('accessibleLoggers:id_logger');

        return response()->json([
            'user' => $user,
            'logger_access_ids' => $user->accessibleLoggers
                ->pluck('id_logger')
                ->map(fn($id) => (string) $id)
                ->values(),
            'status'         => $user->status ?? 'aktif',
            'suspend_reason' => $user->suspend_reason ?? '',
        ]);
    }

    public function update(Request $request, t_User $user)
    {
        $currentUser = auth()->user();
        if (!$this->canManageUser($currentUser, $user)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:100',
            'username'      => 'required|string|max:30|unique:t_user,username,' . $user->id_user . ',id_user',
            'password'      => 'nullable|string|min:6',
            'level_user'    => 'required|string|max:50',
            'instansi_id'   => 'nullable|integer|exists:instansi,id',
            'logger_access' => 'nullable|array',
            'logger_access.*' => ['string', 'max:15', Rule::exists('t_logger', 'id_logger')],
            'status'        => 'nullable|in:aktif,suspend,non-aktif',
            'suspend_reason'=> 'nullable|string|max:1000',
        ]);

        $validated = $validator->validate();

        $targetRole = $this->normalizeRoleName((string) $validated['level_user']);
        if (!Role::query()->where('role_name', $targetRole)->exists()) {
            throw ValidationException::withMessages([
                'level_user' => 'Role tidak valid.',
            ]);
        }
        if (!$this->canAssignRole($currentUser, $targetRole)) {
            abort(403);
        }

        if ($currentUser->isInstansiAdmin()) {
            $validated['instansi_id'] = $currentUser->instansi_id;
        }

        if ($targetRole !== 'superadmin' && empty($validated['instansi_id'])) {
            throw ValidationException::withMessages([
                'instansi_id' => 'Instansi wajib dipilih.',
            ]);
        }

        $payload = [
            'nama'        => $validated['nama'],
            'username'    => $validated['username'],
            'level_user'  => $targetRole,
            'instansi_id' => $validated['instansi_id'] ?? null,
        ];

        // Hanya Superadmin yang boleh mengubah status & pesan suspend
        if ($currentUser->isSuperAdmin()) {
            $newStatus = $request->input('status', $user->status ?? 'aktif');
            $payload['status'] = $newStatus;
            if ($newStatus === 'suspend') {
                $payload['suspend_reason'] = $request->input('suspend_reason', '');
            } else {
                $payload['suspend_reason'] = null;
            }
        }

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        $loggerIds = $request->input('logger_access', []);
        $this->syncLoggerAccessForUser($user, $loggerIds, $currentUser);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui.',
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(t_User $user)
    {
        $currentUser = auth()->user();
        if (!$this->canManageUser($currentUser, $user)) {
            abort(403);
        }

        if ($currentUser && $currentUser->id_user === $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun yang sedang login tidak bisa dihapus.',
            ], 422);
        }

        $user->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.',
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    private function canManageUser(?t_User $actor, t_User $target): bool
    {
        if (!$actor) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        if (!$actor->isInstansiAdmin()) {
            return false;
        }

        if ((int) $target->instansi_id !== (int) $actor->instansi_id) {
            return false;
        }

        // Instansi admin hanya boleh kelola akun pegawai di instansinya.
        return $target->isPegawai();
    }

    private function canAssignRole(?t_User $actor, string $roleName): bool
    {
        if (!$actor) {
            return false;
        }

        $normalized = strtolower(trim($roleName));

        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->isInstansiAdmin()) {
            return in_array($normalized, ['pegawai', 'user'], true);
        }

        return false;
    }

    private function normalizeRoleName(string $roleName): string
    {
        $normalized = strtolower(trim($roleName));

        if ($normalized === 'admin') {
            return Role::query()->where('role_name', 'instansi_admin')->exists()
                ? 'instansi_admin'
                : 'admin';
        }

        if ($normalized === 'user') {
            return Role::query()->where('role_name', 'pegawai')->exists()
                ? 'pegawai'
                : 'user';
        }

        return $roleName;
    }

    /**
     * @param array<int, string>|null $loggerIds
     */
    private function syncLoggerAccessForUser(t_User $user, ?array $loggerIds, t_User $actor): void
    {
        if (!$user->isPegawai()) {
            $user->accessibleLoggers()->sync([]);
            return;
        }

        $loggerIds = collect($loggerIds ?? [])
            ->filter(fn($id) => is_string($id) || is_numeric($id))
            ->map(fn($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($loggerIds->isEmpty()) {
            throw ValidationException::withMessages([
                'logger_access' => 'Minimal pilih 1 logger untuk akun pegawai.',
            ]);
        }

        $allowedLoggers = t_Logger::query()
            ->forUser($actor)
            ->where('instansi_id', $user->instansi_id)
            ->whereIn('id_logger', $loggerIds)
            ->pluck('id_logger')
            ->map(fn($id) => (string) $id)
            ->values();

        if ($allowedLoggers->count() !== $loggerIds->count()) {
            throw ValidationException::withMessages([
                'logger_access' => 'Ada logger yang tidak valid atau di luar instansi user.',
            ]);
        }

        $user->accessibleLoggers()->sync($allowedLoggers->all());
    }
}
