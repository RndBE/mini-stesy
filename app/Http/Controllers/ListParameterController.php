<?php

namespace App\Http\Controllers;

use App\Models\ListParameter;
use App\Models\ParameterGroup;
use Illuminate\Http\Request;

class ListParameterController extends Controller
{
    public function index()
    {
        $items = ListParameter::query()
            ->with('parameterGroup')
            ->orderBy('nama_parameter')
            ->get();

        return view('list-parameter.index', [
            'title' => 'List Parameter',
            'items' => $items,
            'groups' => ParameterGroup::query()
                ->orderBy('sort_order')
                ->orderBy('nama_group')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('list-parameter.create', [
            'title' => 'Tambah List Parameter',
            'groups' => ParameterGroup::query()->orderBy('sort_order')->orderBy('nama_group')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:100|unique:list_parameter,nama_parameter',
            'parameter_utama' => 'nullable|string|max:50',
            'default_satuan' => 'nullable|string|max:20',
            'default_kolom_sensor' => 'nullable|string|max:20',
            'default_parameter_group_id' => 'nullable|integer|exists:parameter_groups,id',
            'is_active' => 'nullable|boolean',
        ]);
        $nama = trim((string) $validated['nama_parameter']);
        $nama = preg_replace('/\s+/', ' ', $nama);

        if (!str_contains($nama, '_')) {
            $nama = str_replace(' ', '_', $nama);
        }

        $validated['nama_parameter'] = strtolower($nama);

        $utama = isset($validated['parameter_utama']) ? trim((string) $validated['parameter_utama']) : null;
        if ($utama !== null && $utama !== '') {
            $utama = preg_replace('/\s+/', ' ', $utama);
            if (!str_contains($utama, '_')) {
                $utama = str_replace(' ', '_', $utama);
            }
            $validated['parameter_utama'] = strtolower($utama);
        } else {
            $validated['parameter_utama'] = null;
        }

        // $validated['nama_parameter'] = trim((string) $validated['nama_parameter']);
        // $validated['parameter_utama'] = isset($validated['parameter_utama']) ? trim((string) $validated['parameter_utama']) : null;
        $validated['default_satuan'] = isset($validated['default_satuan']) ? trim((string) $validated['default_satuan']) : null;
        $validated['default_kolom_sensor'] = isset($validated['default_kolom_sensor']) ? trim((string) $validated['default_kolom_sensor']) : null;
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        ListParameter::create($validated);

        return redirect()->route('list-parameter.index')
            ->with('success', 'List parameter berhasil ditambahkan.');
    }

    public function edit(ListParameter $list_parameter)
    {
        return view('list-parameter.edit', [
            'title' => 'Edit List Parameter',
            'item' => $list_parameter,
            'groups' => ParameterGroup::query()->orderBy('sort_order')->orderBy('nama_group')->get(),
        ]);
    }

    public function update(Request $request, ListParameter $list_parameter)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:100|unique:list_parameter,nama_parameter,' . $list_parameter->id,
            'parameter_utama' => 'nullable|string|max:50',
            'default_satuan' => 'nullable|string|max:20',
            'default_kolom_sensor' => 'nullable|string|max:20',
            'default_parameter_group_id' => 'nullable|integer|exists:parameter_groups,id',
            'is_active' => 'nullable|boolean',
        ]);

        $nama = trim((string) $validated['nama_parameter']);
        $nama = preg_replace('/\s+/', ' ', $nama);

        if (!str_contains($nama, '_')) {
            $nama = str_replace(' ', '_', $nama);
        }

        $validated['nama_parameter'] = strtolower($nama);
    
        $utama = isset($validated['parameter_utama']) ? trim((string) $validated['parameter_utama']) : null;
        if ($utama !== null && $utama !== '') {
            $utama = preg_replace('/\s+/', ' ', $utama);
            if (!str_contains($utama, '_')) {
                $utama = str_replace(' ', '_', $utama);
            }
            $validated['parameter_utama'] = strtolower($utama);
        } else {
            $validated['parameter_utama'] = null;
        }

        // $validated['nama_parameter'] = trim((string) $validated['nama_parameter']);
        // $validated['parameter_utama'] = isset($validated['parameter_utama']) ? trim((string) $validated['parameter_utama']) : null;
        $validated['default_satuan'] = isset($validated['default_satuan']) ? trim((string) $validated['default_satuan']) : null;
        $validated['default_kolom_sensor'] = isset($validated['default_kolom_sensor']) ? trim((string) $validated['default_kolom_sensor']) : null;
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $list_parameter->update($validated);

        return redirect()->route('list-parameter.index')
            ->with('success', 'List parameter berhasil diperbarui.');
    }

    public function destroy(ListParameter $list_parameter)
    {
        $list_parameter->delete();

        return redirect()->route('list-parameter.index')
            ->with('success', 'List parameter berhasil dihapus.');
    }
}
