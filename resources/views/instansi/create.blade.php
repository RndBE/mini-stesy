@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Instansi</h1>
            <p class="text-sm text-slate-500">Tambahkan data instansi baru.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('instansi.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Instansi</label>
                    <input type="text" name="nama" value="{{ old('nama') }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm" required>
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea name="alamat" rows="3"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Telepon</label>
                    <input type="text" name="telp" value="{{ old('telp') }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm p-2 text-sm">
                    @error('telp')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('instansi.index') }}"
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
