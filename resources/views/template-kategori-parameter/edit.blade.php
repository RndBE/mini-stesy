@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Template Kategori Parameter</h1>
            <p class="text-sm text-slate-500">Perbarui konfigurasi template parameter kategori.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('template-kategori-parameter.update', $item) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Kategori Logger</label>
                        <select name="id_katlogger" required class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                            <option value="">- Pilih Kategori -</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->id_katlogger }}"
                                    @selected((string) old('id_katlogger', $item->id_katlogger) === (string) $kategori->id_katlogger)>
                                    {{ $kategori->nama_kategori }}{{ $kategori->kepanjangan ? ' - ' . $kategori->kepanjangan : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_katlogger')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">List Parameter</label>
                        <select name="list_parameter_id" required class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                            <option value="">- Pilih Parameter -</option>
                            @foreach ($listParameters as $lp)
                                <option value="{{ $lp->id }}"
                                    @selected((string) old('list_parameter_id', $item->list_parameter_id) === (string) $lp->id)>
                                    {{ $lp->nama_parameter }}
                                </option>
                            @endforeach
                        </select>
                        @error('list_parameter_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Urutan</label>
                        <input type="number" min="0" step="1" name="urutan" value="{{ old('urutan', $item->urutan) }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                        @error('urutan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Kolom Sensor Default (opsional)</label>
                        <input type="text" name="kolom_sensor_default"
                            value="{{ old('kolom_sensor_default', $item->kolom_sensor_default) }}" placeholder="contoh: sensor1"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                        @error('kolom_sensor_default')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Satuan Override (opsional)</label>
                        <input type="text" name="satuan_override"
                            value="{{ old('satuan_override', $item->satuan_override) }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                        @error('satuan_override')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Group Parameter (opsional)</label>
                        <select name="parameter_group_id" class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                            <option value="">- Ikuti default list parameter -</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}"
                                    @selected((string) old('parameter_group_id', $item->parameter_group_id) === (string) $group->id)>
                                    {{ $group->nama_group }}
                                </option>
                            @endforeach
                        </select>
                        @error('parameter_group_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('template-kategori-parameter.index') }}"
                        class="inline-flex items-center rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Kembali
                    </a>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
