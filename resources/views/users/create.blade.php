@extends('layouts.app')

@section('content')
    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah User</h1>
            <p class="text-sm text-slate-500">Buat user baru dan set role.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" name="nama" value="{{ old('nama') }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('nama')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('username')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Password</label>
                        <input type="password" name="password"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role</label>
                        <select name="level_user" class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                            <option value="" disabled selected>Pilih role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->role_name }}" @selected(old('level_user') === $role->role_name)>
                                    {{ $role->role_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('level_user')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Instansi</label>
                        @if (($currentUser->level_user ?? null) === 'superadmin')
                            <select name="instansi_id" class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                                <option value="">-</option>
                                @foreach ($instansi as $row)
                                    <option value="{{ $row->id }}" @selected(old('instansi_id') == $row->id)>
                                        {{ $row->nama }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" value="{{ $currentUser?->instansi?->nama ?? '-' }}" disabled
                                class="mt-1 w-full rounded-md border-slate-200 bg-slate-100 shadow-sm p-2 text-sm">
                            <input type="hidden" name="instansi_id" value="{{ $currentUser?->instansi_id }}">
                        @endif
                        @error('instansi_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Telepon</label>
                        <input type="text" name="telp" value="{{ old('telp') }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('telp')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea name="alamat" rows="3"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Latitude</label>
                        <input type="text" name="latitude" value="{{ old('latitude', '-7.800000') }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('latitude')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Longitude</label>
                        <input type="text" name="longitude" value="{{ old('longitude', '110.360000') }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('longitude')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Zoom</label>
                        <input type="number" name="zoom" value="{{ old('zoom', 11) }}"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('zoom')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Logo</label>
                        <input type="file" name="logo" accept="image/*"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('logo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Logo Mobile</label>
                        <input type="file" name="logo_mobile" accept="image/*"
                            class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                        @error('logo_mobile')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('users.index') }}"
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
