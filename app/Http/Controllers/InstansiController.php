<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class InstansiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if ($this->canManageAll($user)) {
            $query = Instansi::query();
        } elseif ($this->canManageOwn($user)) {
            $query = Instansi::query()->where('id', $user->instansi_id);
        } else {
            abort(403);
        }

        $instansi = $query
            ->withCount('users')
            ->withCount([
                'loggers as controllable_loggers_count' => function ($query) {
                    $query->whereHas('jiat', fn ($jiat) => $jiat->where('has_pump', true));
                },
            ])
            ->orderBy('nama')
            ->get();

        return view('instansi.index', [
            'title' => 'Instansi',
            'instansi' => $instansi,
            'canManageAllInstansi' => $this->canManageAll($user),
        ]);
    }

    public function create()
    {
        if (!$this->canManageAll(auth()->user())) {
            abort(403);
        }

        return view('instansi.create', [
            'title' => 'Tambah Instansi',
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->canManageAll(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:instansi,nama',
            'judul_mobile' => 'nullable|string|max:120',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:25',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'zoom' => 'nullable|integer|min:1|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo_mobile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'control_pin' => ['nullable', 'string', 'min:4', 'max:12', 'regex:/^[0-9]+$/'],
        ]);

        $this->applyControlPinForStore($validated);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logo_instansi', 'public');
        }

        if ($request->hasFile('logo_mobile')) {
            $validated['logo_mobile'] = $request->file('logo_mobile')->store('logo_instansi', 'public');
        }

        Instansi::create($validated);

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function edit(Instansi $instansi)
    {
        $this->authorizeInstansi($instansi);

        return view('instansi.edit', [
            'title' => 'Edit Instansi',
            'instansi' => $instansi,
        ]);
    }

    public function update(Request $request, Instansi $instansi)
    {
        $this->authorizeInstansi($instansi);

        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:instansi,nama,' . $instansi->id,
            'judul_mobile' => 'nullable|string|max:120',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:25',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'zoom' => 'nullable|integer|min:1|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo_mobile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'control_pin' => ['nullable', 'string', 'min:4', 'max:12', 'regex:/^[0-9]+$/'],
            'clear_control_pin' => 'nullable|boolean',
        ]);

        $this->applyControlPinForUpdate($request, $validated);

        if ($request->hasFile('logo')) {
            if (!empty($instansi->logo)) {
                Storage::disk('public')->delete($instansi->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logo_instansi', 'public');
        }

        if ($request->hasFile('logo_mobile')) {
            if (!empty($instansi->logo_mobile)) {
                Storage::disk('public')->delete($instansi->logo_mobile);
            }
            $validated['logo_mobile'] = $request->file('logo_mobile')->store('logo_instansi', 'public');
        }

        $instansi->update($validated);

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        if (!$this->canManageAll(auth()->user())) {
            abort(403);
        }

        if (!empty($instansi->logo)) {
            Storage::disk('public')->delete($instansi->logo);
        }

        if (!empty($instansi->logo_mobile)) {
            Storage::disk('public')->delete($instansi->logo_mobile);
        }

        $instansi->delete();

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil dihapus.');
    }

    private function canManageAll($user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermission('manage_instansi');
    }

    private function canManageOwn($user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isInstansiAdmin() && !empty($user->instansi_id);
    }

    private function authorizeInstansi(Instansi $instansi): void
    {
        $user = auth()->user();

        if ($this->canManageAll($user)) {
            return;
        }

        if ($this->canManageOwn($user) && (int) $instansi->id === (int) $user->instansi_id) {
            return;
        }

        abort(403);
    }

    private function applyControlPinForStore(array &$validated): void
    {
        $pin = $validated['control_pin'] ?? null;
        unset($validated['control_pin']);

        if (filled($pin)) {
            $validated['control_pin_hash'] = Hash::make((string) $pin);
            $validated['control_pin_enabled'] = true;
            $validated['control_pin_updated_at'] = now();
        } else {
            $validated['control_pin_hash'] = null;
            $validated['control_pin_enabled'] = false;
            $validated['control_pin_updated_at'] = null;
        }
    }

    private function applyControlPinForUpdate(Request $request, array &$validated): void
    {
        $pin = $validated['control_pin'] ?? null;
        unset($validated['control_pin'], $validated['clear_control_pin']);

        if (filled($pin)) {
            $validated['control_pin_hash'] = Hash::make((string) $pin);
            $validated['control_pin_enabled'] = true;
            $validated['control_pin_updated_at'] = now();

            return;
        }

        if ($request->boolean('clear_control_pin')) {
            $validated['control_pin_hash'] = null;
            $validated['control_pin_enabled'] = false;
            $validated['control_pin_updated_at'] = null;
        }
    }
}
