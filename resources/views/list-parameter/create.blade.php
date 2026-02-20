@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah List Parameter</h1>
            <p class="text-sm text-slate-500">Tambahkan master parameter baru.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('list-parameter.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nama Parameter</label>
                        <input type="text" name="nama_parameter" value="{{ old('nama_parameter') }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                        @error('nama_parameter')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Parameter Utama</label>
                        <input type="text" name="parameter_utama" value="{{ old('parameter_utama') }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                        @error('parameter_utama')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Default Satuan</label>
                        <input type="text" name="default_satuan" value="{{ old('default_satuan') }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                        @error('default_satuan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Default Kolom Sensor</label>
                        <input type="text" name="default_kolom_sensor" value="{{ old('default_kolom_sensor') }}"
                            placeholder="contoh: sensor1"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                        @error('default_kolom_sensor')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Default Group Parameter</label>
                        <select name="default_parameter_group_id"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm">
                            <option value="">- Pilih Group -</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected((string) old('default_parameter_group_id') === (string) $group->id)>
                                    {{ $group->nama_group }}
                                </option>
                            @endforeach
                        </select>
                        @error('default_parameter_group_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', 1))
                        class="rounded border-slate-300 text-indigo-600">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Aktif</label>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('list-parameter.index') }}"
                        class="inline-flex items-center rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Kembali
                    </a>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
