@extends('layouts.app')

@section('content')
    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Role</h1>
            <p class="text-sm text-slate-500">Perbarui role dan permission.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Role</label>
                    <input type="text" name="role_name" value="{{ old('role_name', $role->role_name) }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                    @error('role_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Permission</label>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    class="rounded border-slate-300"
                                    @checked(in_array($permission->id, old('permissions', $selected)))>
                                <span>{{ $permission->permission_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('roles.index') }}"
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
