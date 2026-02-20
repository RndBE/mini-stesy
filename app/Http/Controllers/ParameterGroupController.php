<?php

namespace App\Http\Controllers;

use App\Models\ParameterGroup;
use Illuminate\Http\Request;

class ParameterGroupController extends Controller
{
    public function index()
    {
        $items = ParameterGroup::query()
            ->withCount(['listParameters', 'templateParameters', 'sensorParameters'])
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('nama_group')
            ->get();

        return view('parameter-group.index', [
            'title' => 'Group Parameter',
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'kode_group' => strtoupper(trim((string) $request->input('kode_group'))),
            'nama_group' => trim((string) $request->input('nama_group')),
            'deskripsi' => $request->filled('deskripsi') ? trim((string) $request->input('deskripsi')) : null,
        ]);

        $validated = $request->validate([
            'kode_group' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:parameter_groups,kode_group'],
            'nama_group' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        ParameterGroup::create($validated);

        return redirect()->route('parameter-group.index')
            ->with('success', 'Group parameter berhasil ditambahkan.');
    }

    public function update(Request $request, ParameterGroup $parameter_group)
    {
        $request->merge([
            'kode_group' => strtoupper(trim((string) $request->input('kode_group'))),
            'nama_group' => trim((string) $request->input('nama_group')),
            'deskripsi' => $request->filled('deskripsi') ? trim((string) $request->input('deskripsi')) : null,
        ]);

        $validated = $request->validate([
            'kode_group' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9_-]+$/',
                'unique:parameter_groups,kode_group,' . $parameter_group->id,
            ],
            'nama_group' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $parameter_group->update($validated);

        return redirect()->route('parameter-group.index')
            ->with('success', 'Group parameter berhasil diperbarui.');
    }

    public function destroy(ParameterGroup $parameter_group)
    {
        $parameter_group->loadCount(['listParameters', 'templateParameters', 'sensorParameters']);
        $totalUsage = (int) $parameter_group->list_parameters_count
            + (int) $parameter_group->template_parameters_count
            + (int) $parameter_group->sensor_parameters_count;

        if ($totalUsage > 0) {
            return redirect()->route('parameter-group.index')
                ->with('error', 'Group parameter tidak bisa dihapus karena masih digunakan.');
        }

        $parameter_group->delete();

        return redirect()->route('parameter-group.index')
            ->with('success', 'Group parameter berhasil dihapus.');
    }
}
