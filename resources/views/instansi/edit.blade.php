@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Instansi</h1>
            <p class="text-sm text-slate-500">Perbarui data instansi.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('instansi.update', $instansi) }}" method="POST" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Instansi</label>
                    <input type="text" name="nama" value="{{ old('nama', $instansi->nama) }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea name="alamat" rows="3"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">{{ old('alamat', $instansi->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Telepon</label>
                    <input type="text" name="telp" value="{{ old('telp', $instansi->telp) }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                    @error('telp')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Latitude</label>
                        <input type="text" name="latitude" value="{{ old('latitude', $instansi->latitude) }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                        @error('latitude')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Longitude</label>
                        <input type="text" name="longitude" value="{{ old('longitude', $instansi->longitude) }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                        @error('longitude')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Zoom</label>
                        <input type="number" name="zoom" value="{{ old('zoom', $instansi->zoom) }}" min="1"
                            max="20" class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                        @error('zoom')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Logo (opsional)</label>
                        <input type="file" name="logo" accept="image/*"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                        @if (!empty($instansi->logo))
                            <img src="{{ asset('storage/' . ltrim($instansi->logo, '/')) }}" alt="Logo Instansi"
                                class="mt-2 h-12 rounded border border-slate-200 object-cover">
                        @endif
                        @error('logo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Logo Mobile (opsional)</label>
                        <input type="file" name="logo_mobile" accept="image/*"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                        @if (!empty($instansi->logo_mobile))
                            <img src="{{ asset('storage/' . ltrim($instansi->logo_mobile, '/')) }}"
                                alt="Logo Mobile Instansi"
                                class="mt-2 h-12 rounded border border-slate-200 object-cover">
                        @endif
                        @error('logo_mobile')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('instansi.index') }}"
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
