@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Kategori Logger</h1>
            <p class="text-sm text-slate-500">Perbarui data kategori logger.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('kategori.update', $kategori) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nama Kategori (Kode)</label>
                        <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                        @error('nama_kategori')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Kepanjangan</label>
                        <input type="text" name="kepanjangan" value="{{ old('kepanjangan', $kategori->kepanjangan) }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                        @error('kepanjangan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Icon App</label>
                        <input type="text" name="icon_app" value="{{ old('icon_app', $kategori->icon_app) }}"
                            class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                        @error('icon_app')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">View</label>
                    <input type="number" min="0" step="1" name="view" value="{{ old('view', $kategori->view) }}"
                        class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                    @error('view')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('kategori.index') }}"
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
