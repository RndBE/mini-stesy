@extends('layouts.app')

@section('content')
    @php
        $sensorOptions = collect(range(1, max(\App\Support\SensorFamily::FAMILIES)))->map(fn($number) => 'sensor' . $number);
    @endphp

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
                        <div class="relative mt-1">
                            <select name="default_kolom_sensor"
                                class="h-10 w-full appearance-none rounded-md border border-slate-300 bg-white px-3 pr-10 text-sm font-medium text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">- Pilih Sensor -</option>
                                @foreach ($sensorOptions as $sensor)
                                    <option value="{{ $sensor }}" @selected(old('default_kolom_sensor') === $sensor)>
                                        {{ 'Sensor ' . str_pad((string) substr($sensor, 6), 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        @error('default_kolom_sensor')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Icon</label>
                        <div class="relative mt-1">
                            <select name="icon_app"
                                class="h-10 w-full appearance-none rounded-md border border-slate-300 bg-white px-3 pr-10 text-sm font-medium text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">- Pilih Icon -</option>
                                @foreach ($iconOptions as $path => $label)
                                    <option value="{{ $path }}" @selected(old('icon_app') === $path)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        @error('icon_app')
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
