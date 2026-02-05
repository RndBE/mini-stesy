@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Permission</h1>
            <p class="text-sm text-slate-500">Buat permission baru.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('permissions.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Permission</label>
                    <input type="text" name="permission_name" value="{{ old('permission_name') }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                    @error('permission_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('permissions.index') }}"
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
