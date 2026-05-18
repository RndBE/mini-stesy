<?php

namespace App\Http\Controllers;

use App\Models\ListParameter;
use App\Models\ParameterGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'iconOptions' => $this->iconOptions(),
        ]);
    }

    public function create()
    {
        return view('list-parameter.create', [
            'title' => 'Tambah List Parameter',
            'groups' => ParameterGroup::query()->orderBy('sort_order')->orderBy('nama_group')->get(),
            'iconOptions' => $this->iconOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:100|unique:list_parameter,nama_parameter',
            'parameter_utama' => 'nullable|string|max:50',
            'default_satuan' => 'nullable|string|max:20',
            'default_kolom_sensor' => ['nullable', Rule::in($this->sensorColumnOptions())],
            'icon_app' => ['nullable', Rule::in(array_keys($this->iconOptions()))],
            'default_parameter_group_id' => 'nullable|integer|exists:parameter_groups,id',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['nama_parameter'] = $this->normalizeDisplayName($validated['nama_parameter']);

        $validated['parameter_utama'] = $this->normalizeBaseKey($validated['parameter_utama'] ?? null);

        $validated['default_satuan'] = isset($validated['default_satuan']) ? trim((string) $validated['default_satuan']) : null;
        $validated['default_kolom_sensor'] = isset($validated['default_kolom_sensor']) ? trim((string) $validated['default_kolom_sensor']) : null;
        $validated['icon_app'] = isset($validated['icon_app']) ? trim((string) $validated['icon_app']) : null;
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
            'iconOptions' => $this->iconOptions(),
        ]);
    }

    public function update(Request $request, ListParameter $list_parameter)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:100|unique:list_parameter,nama_parameter,' . $list_parameter->id,
            'parameter_utama' => 'nullable|string|max:50',
            'default_satuan' => 'nullable|string|max:20',
            'default_kolom_sensor' => ['nullable', Rule::in($this->sensorColumnOptions())],
            'icon_app' => ['nullable', Rule::in(array_keys($this->iconOptions()))],
            'default_parameter_group_id' => 'nullable|integer|exists:parameter_groups,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['nama_parameter'] = $this->normalizeDisplayName($validated['nama_parameter']);
    
        $validated['parameter_utama'] = $this->normalizeBaseKey($validated['parameter_utama'] ?? null);

        $validated['default_satuan'] = isset($validated['default_satuan']) ? trim((string) $validated['default_satuan']) : null;
        $validated['default_kolom_sensor'] = isset($validated['default_kolom_sensor']) ? trim((string) $validated['default_kolom_sensor']) : null;
        $validated['icon_app'] = isset($validated['icon_app']) ? trim((string) $validated['icon_app']) : null;
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

    private function normalizeDisplayName(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value));
    }

    private function normalizeBaseKey(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/', ' ', $value);
        if (!str_contains($value, '_')) {
            $value = str_replace(' ', '_', $value);
        }

        return strtolower($value);
    }

    private function sensorColumnOptions(): array
    {
        return array_map(fn($number) => 'sensor' . $number, range(1, 19));
    }

    private function iconOptions(): array
    {
        return [
            'icons/awlr/elevasi_muka_air.svg' => 'AWLR - Elevasi Muka Air',
            'icons/awlr/debit.svg' => 'AWLR - Debit',
            'icons/arr/tidak_hujan.svg' => 'ARR - Curah Hujan',
            'icons/afmr/luas_penampang_air.svg' => 'AFMR - Luas Penampang',
            'icons/afmr/flow_velocity.svg' => 'AFMR - Flow Velocity',
            'icons/afmr/jarak_sensor.svg' => 'AFMR - Jarak Sensor',
            'icons/afmr/tinggi_sensor.svg' => 'AFMR - Tinggi/Elevasi Sensor',
            'icons/afmr/curah_hujan.svg' => 'AFMR - Curah Hujan',
            'icons/afmr/totalizer_debit.svg' => 'AFMR Contact - Totalizer',
            'icons/afmr/sensor_prv.svg' => 'AFMR Contact - Pressure',
            'icons/afmr/fault_wm.svg' => 'AFMR Contact - Fault',
            'icons/awr/kecepatan_angin.svg' => 'AWR - Kecepatan Angin',
            'icons/awr/arah_angin.svg' => 'AWR - Arah Angin',
            'icons/awr/kecerahan.svg' => 'AWR - Kecerahan',
            'icons/awr/arah.svg' => 'AWR - Arah Cahaya',
            'icons/beranda/temper_online.svg' => 'AWR/Logger - Temperatur',
            'icons/awr/tekanan_udara.svg' => 'AWR - Tekanan Udara',
            'icons/beranda/humidity_online.svg' => 'Logger - Humidity',
            'icons/beranda/battery_online.svg' => 'Logger - Battery',
            'icons/awgr/elevasi_muka_air.svg' => 'AWQR - Tinggi Muka Air',
            'icons/awgr/ph_air.svg' => 'AWQR - PH Air',
            'icons/awgr/suhu_air.svg' => 'AWQR - Suhu Air',
            'icons/awgr/orp.svg' => 'AWQR - ORP',
            'icons/awgr/conductivity.svg' => 'AWQR - Conductivity',
            'icons/awgr/salinity.svg' => 'AWQR - Salinity',
            'icons/awgr/total_dissolved_solids.svg' => 'AWQR - TDS',
            'icons/awgr/turbidity.svg' => 'AWQR - Turbidity',
            'icons/awgr/tinggi_sensor.svg' => 'AWQR - Tinggi Sensor',
        ];
    }
}
