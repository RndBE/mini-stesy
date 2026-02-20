<?php

namespace App\Http\Controllers;

use App\Models\Kategori_logger;
use App\Models\ListParameter;
use App\Models\ParameterGroup;
use App\Models\TemplateKategoriParameter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateKategoriParameterController extends Controller
{
    public function index()
    {
        $items = TemplateKategoriParameter::query()
            ->with(['kategori', 'listParameter', 'parameterGroup'])
            ->orderBy('id_katlogger')
            ->orderBy('urutan')
            ->get();

        return view('template-kategori-parameter.index', [
            'title' => 'Template Kategori Parameter',
            'items' => $items,
            'kategoris' => Kategori_logger::query()
                ->orderBy('nama_kategori')
                ->get(),
            'listParameters' => ListParameter::query()
                ->orderBy('nama_parameter')
                ->get(),
            'groups' => ParameterGroup::query()
                ->orderBy('sort_order')
                ->orderBy('nama_group')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('template-kategori-parameter.create', [
            'title' => 'Tambah Template Kategori Parameter',
            'kategoris' => Kategori_logger::query()->orderBy('nama_kategori')->get(),
            'listParameters' => ListParameter::query()->orderBy('nama_parameter')->get(),
            'groups' => ParameterGroup::query()->orderBy('sort_order')->orderBy('nama_group')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_katlogger' => 'required|integer|exists:kategori_logger,id_katlogger',
            'list_parameter_id' => [
                'required',
                'integer',
                'exists:list_parameter,id',
                Rule::unique('template_kategori_parameter', 'list_parameter_id')
                    ->where(fn($q) => $q->where('id_katlogger', $request->integer('id_katlogger'))),
            ],
            'urutan' => 'nullable|integer|min:0',
            'kolom_sensor_default' => 'nullable|string|max:20',
            'satuan_override' => 'nullable|string|max:20',
            'parameter_group_id' => 'nullable|integer|exists:parameter_groups,id',
        ]);

        $validated['kolom_sensor_default'] = isset($validated['kolom_sensor_default'])
            ? trim((string) $validated['kolom_sensor_default'])
            : null;
        $validated['satuan_override'] = isset($validated['satuan_override'])
            ? trim((string) $validated['satuan_override'])
            : null;
        $validated['urutan'] = (int) ($validated['urutan'] ?? 0);

        TemplateKategoriParameter::create($validated);

        return redirect()->route('template-kategori-parameter.index')
            ->with('success', 'Template kategori parameter berhasil ditambahkan.');
    }

    public function edit(TemplateKategoriParameter $template_kategori_parameter)
    {
        return view('template-kategori-parameter.edit', [
            'title' => 'Edit Template Kategori Parameter',
            'item' => $template_kategori_parameter,
            'kategoris' => Kategori_logger::query()->orderBy('nama_kategori')->get(),
            'listParameters' => ListParameter::query()->orderBy('nama_parameter')->get(),
            'groups' => ParameterGroup::query()->orderBy('sort_order')->orderBy('nama_group')->get(),
        ]);
    }

    public function update(Request $request, TemplateKategoriParameter $template_kategori_parameter)
    {
        $validated = $request->validate([
            'id_katlogger' => 'required|integer|exists:kategori_logger,id_katlogger',
            'list_parameter_id' => [
                'required',
                'integer',
                'exists:list_parameter,id',
                Rule::unique('template_kategori_parameter', 'list_parameter_id')
                    ->ignore($template_kategori_parameter->id)
                    ->where(fn($q) => $q->where('id_katlogger', $request->integer('id_katlogger'))),
            ],
            'urutan' => 'nullable|integer|min:0',
            'kolom_sensor_default' => 'nullable|string|max:20',
            'satuan_override' => 'nullable|string|max:20',
            'parameter_group_id' => 'nullable|integer|exists:parameter_groups,id',
        ]);

        $validated['kolom_sensor_default'] = isset($validated['kolom_sensor_default'])
            ? trim((string) $validated['kolom_sensor_default'])
            : null;
        $validated['satuan_override'] = isset($validated['satuan_override'])
            ? trim((string) $validated['satuan_override'])
            : null;
        $validated['urutan'] = (int) ($validated['urutan'] ?? 0);

        $template_kategori_parameter->update($validated);

        return redirect()->route('template-kategori-parameter.index')
            ->with('success', 'Template kategori parameter berhasil diperbarui.');
    }

    public function destroy(TemplateKategoriParameter $template_kategori_parameter)
    {
        $template_kategori_parameter->delete();

        return redirect()->route('template-kategori-parameter.index')
            ->with('success', 'Template kategori parameter berhasil dihapus.');
    }
}
