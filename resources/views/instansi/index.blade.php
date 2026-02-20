@extends('layouts.app')

@section('content')
    @php
        $isManageAllInstansi = $canManageAllInstansi ?? false;
        $ownInstansi = $instansi->first();
        $ownInstansiPayload = $ownInstansi
            ? [
                'id' => $ownInstansi->id,
                'nama' => $ownInstansi->nama,
                'alamat' => $ownInstansi->alamat,
                'telp' => $ownInstansi->telp,
                'latitude' => $ownInstansi->latitude,
                'longitude' => $ownInstansi->longitude,
                'zoom' => $ownInstansi->zoom,
                'logo' => $ownInstansi->logo,
                'logo_mobile' => $ownInstansi->logo_mobile,
            ]
            : null;
    @endphp
    <div x-data="instansiData()" class="space-y-3">
        <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-start">
            @if (session('success'))
                <div
                    class="px-4 py-2 me-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                    {{ session('success') }}
                </div>
            @else
                <div></div>
            @endif

            @if ($isManageAllInstansi)
                <div class="w-full sm:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 ">
                    <div class="relative w-full sm:w-64">
                        <input type="text" x-model="searchQuery" placeholder="Cari instansi..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <svg class="absolute right-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button @click="openCreateModal()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 whitespace-nowrap">
                        + Tambah Instansi
                    </button>
                </div>
            @endif
        </div>

        @if ($isManageAllInstansi)
            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                        <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-950">
                            <tr>
                                <th scope="col" class="px-6 py-4">No</th>
                                <th scope="col" class="px-6 py-4">Nama</th>
                                <th scope="col" class="px-6 py-4">Alamat</th>
                                <th scope="col" class="px-6 py-4">Telepon</th>
                                <th scope="col" class="px-6 py-4">Latitude</th>
                                <th scope="col" class="px-6 py-4">Longitude</th>
                                <th scope="col" class="px-6 py-4">Zoom</th>
                                <th scope="col" class="px-6 py-4">Jumlah User</th>
                                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <template x-for="(row, index) in filteredInstansi()" :key="row.id">
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900" x-text="index + 1">
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900" x-text="row.nama">
                                    </td>
                                    <td class="px-6 py-4 whitespace-normal max-w-md" x-text="row.alamat || '-'"></td>
                                    <td class="whitespace-nowrap px-6 py-4" x-text="row.telp || '-'"></td>
                                    <td class="whitespace-nowrap px-6 py-4" x-text="row.latitude || '-'"></td>
                                    <td class="whitespace-nowrap px-6 py-4" x-text="row.longitude || '-'"></td>
                                    <td class="whitespace-nowrap px-6 py-4" x-text="row.zoom || '-'"></td>
                                    <td class="whitespace-nowrap px-6 py-4" x-text="row.users_count"></td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openEditModal(row)"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-950 hover:bg-slate-200 transition-colors"
                                                title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="openDeleteModal(row.id, row.nama)"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-100 text-slate-950 hover:bg-red-200 transition-colors"
                                                title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tbody class="divide-y divide-slate-200 bg-white" x-show="filteredInstansi().length === 0">
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-sm text-slate-500">
                                    Belum ada data instansi.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @if ($ownInstansi)
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Instansi Saya</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $ownInstansi->nama }}</h2>
                        </div>
                        <button @click='openEditModal(@json($ownInstansiPayload))'
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                            Edit Instansi
                        </button>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">Alamat</p>
                            <p class="mt-1 text-sm text-slate-800">{{ $ownInstansi->alamat ?: '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">Telepon</p>
                            <p class="mt-1 text-sm text-slate-800">{{ $ownInstansi->telp ?: '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">Koordinat</p>
                            <p class="mt-1 text-sm text-slate-800">
                                {{ $ownInstansi->latitude ?: '-' }}, {{ $ownInstansi->longitude ?: '-' }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">Zoom Peta</p>
                            <p class="mt-1 text-sm text-slate-800">{{ $ownInstansi->zoom ?: '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">Jumlah User</p>
                            <p class="mt-1 text-sm text-slate-800">{{ $ownInstansi->users_count }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">Logo</p>
                            <div class="mt-2 flex items-center gap-3">
                                @if ($ownInstansi->logo)
                                    <img src="{{ asset('storage/' . ltrim($ownInstansi->logo, '/')) }}"
                                        alt="Logo Instansi"
                                        class="h-12 w-auto rounded border border-slate-200 object-contain">
                                @else
                                    <p class="text-sm text-slate-800">-</p>
                                @endif
                                @if ($ownInstansi->logo_mobile)
                                    <img src="{{ asset('storage/' . ltrim($ownInstansi->logo_mobile, '/')) }}"
                                        alt="Logo Mobile Instansi"
                                        class="h-12 w-auto rounded border border-slate-200 object-contain">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                    Data instansi belum tersedia untuk akun ini.
                </div>
            @endif
        @endif

        @if ($isManageAllInstansi)
            {{-- Create Modal --}}
            <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-50" aria-labelledby="modal-title"
                role="dialog" aria-modal="true" @keydown.escape.window="closeCreateModal()">

                <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75" @click="closeCreateModal()">
                </div>

                <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto"
                    @click="closeCreateModal()">
                    <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="ease-in-out duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                        class="w-full max-w-5xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                        <div class="flex items-center justify-between px-8 py-2 border-b border-slate-200">
                            <h3 id="modal-title" class="text-xl font-bold text-gray-900">Tambah Instansi</h3>
                            <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-gray-100">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form action="{{ route('instansi.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="px-8 pt-4 pb-3 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Instansi <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" name="nama" value="{{ old('nama') }}" required
                                            placeholder="Masukkan Nama Instansi"
                                            class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('nama')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Telepon</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                    <path
                                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.86.32 1.7.57 2.5a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.58-1.09a2 2 0 0 1 2.11-.45c.8.25 1.64.45 2.5.57A2 2 0 0 1 22 16.92z"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                            <input type="text" name="telp" value="{{ old('telp') }}"
                                                placeholder="+62 81234567890"
                                                class="w-full rounded-lg border border-gray-200 pl-12 pr-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        @error('telp')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Latitude</label>
                                        <input type="text" name="latitude" value="{{ old('latitude') }}"
                                            placeholder="-7.800000"
                                            class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('latitude')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Longitude</label>
                                        <input type="text" name="longitude" value="{{ old('longitude') }}"
                                            placeholder="110.360000"
                                            class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('longitude')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Zoom</label>
                                        <input type="number" name="zoom" value="{{ old('zoom', 11) }}"
                                            min="1" max="20"
                                            class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('zoom')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Logo</label>
                                        <input type="file" name="logo" accept="image/*"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('logo')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Logo Mobile</label>
                                        <input type="file" name="logo_mobile" accept="image/*"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('logo_mobile')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Alamat <span
                                            class="text-red-500">*</span></label>
                                    <textarea name="alamat" rows="5" placeholder="Masukkan alamat"
                                        class="w-full rounded-lg border border-gray-200 p-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('alamat') }}</textarea>
                                    @error('alamat')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-gray-100 bg-white">
                                <button type="button" @click="closeCreateModal()"
                                    class="h-12 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="h-12 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif


        {{-- Delete Confirmation Modal --}}
        @if ($isManageAllInstansi)
            <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
                @keydown.escape.window="closeDeleteModal()">

                <div x-show="showDeleteModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75" @click="closeDeleteModal()">
                </div>

                <div class="fixed inset-0 flex items-center justify-center p-4" @click="closeDeleteModal()">
                    <div x-show="showDeleteModal" x-transition:enter="ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="ease-in-out duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                        class="w-full max-w-md bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>

                        <div class="px-6 py-5">
                            <div class="flex flex-col items-center gap-2">
                                <div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center">
                                    <svg class="w-14 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <h3 class="text-xl text-center font-bold text-blue-900">Hapus Instansi</h3>
                            </div>
                        </div>

                        <div class="px-6 py-3">
                            <p class="text-sm text-center text-slate-600">
                                Anda yakin ingin menghapus instansi
                                <span class="font-semibold text-slate-900" x-text="deleteData.name"></span>?
                            </p>
                            <p class="mt-1 text-sm text-center text-red-600 font-medium">
                                Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>

                        <div class="flex items-center justify-center gap-3 px-6 py-3">
                            <button type="button" @click="closeDeleteModal()"
                                class="px-12 py-2 rounded-lg border border-blue-300 text-blue-700 font-semibold hover:bg-blue-100 transition-colors">
                                Batal
                            </button>
                            <button type="button" @click="confirmDelete()"
                                class="px-12 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">
                                Hapus
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        @endif

        {{-- Edit Modal --}}
        <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50" aria-labelledby="modal-title" role="dialog"
            aria-modal="true" @keydown.escape.window="closeEditModal()">

            <div x-show="showEditModal" x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75" @click="closeEditModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeEditModal()">
                <div x-show="showEditModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-5xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>

                    <div class="flex items-center justify-between px-8 py-2 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-gray-900">Edit Instansi</h3>
                        <button @click="closeEditModal()" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form :action="`{{ url('instansi') }}/${editData.id}`" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="px-8 pb-3 pt-4 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Instansi <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="nama" x-model="editData.nama" required
                                        placeholder="Masukkan Nama Instansi"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('nama')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Telepon</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.86.32 1.7.57 2.5a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.58-1.09a2 2 0 0 1 2.11-.45c.8.25 1.64.45 2.5.57A2 2 0 0 1 22 16.92z"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <input type="text" name="telp" x-model="editData.telp"
                                            placeholder="+62 81234567890"
                                            class="w-full rounded-lg border border-gray-200 pl-12 pr-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    @error('telp')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Latitude</label>
                                    <input type="text" name="latitude" x-model="editData.latitude"
                                        placeholder="-7.800000"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('latitude')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Longitude</label>
                                    <input type="text" name="longitude" x-model="editData.longitude"
                                        placeholder="110.360000"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('longitude')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Zoom</label>
                                    <input type="number" name="zoom" x-model="editData.zoom" min="1"
                                        max="20"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('zoom')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Logo (opsional)</label>
                                    <input type="file" name="logo" accept="image/*"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <template x-if="editData.logo">
                                        <img :src="resolveLogoUrl(editData.logo)" alt="Logo Instansi"
                                            class="mt-2 rounded border border-slate-200 object-cover">
                                    </template>
                                    @error('logo')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Logo Mobile
                                        (opsional)</label>
                                    <input type="file" name="logo_mobile" accept="image/*"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <template x-if="editData.logo_mobile">
                                        <img :src="resolveLogoUrl(editData.logo_mobile)" alt="Logo Mobile Instansi"
                                            class="mt-2 rounded border border-slate-200 object-cover">
                                    </template>
                                    @error('logo_mobile')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Alamat <span
                                        class="text-red-500">*</span></label>
                                <textarea name="alamat" rows="5" x-model="editData.alamat" placeholder="Masukkan alamat"
                                    class="w-full rounded-lg border border-gray-200 p-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                @error('alamat')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-gray-100 bg-white">
                            <button type="button" @click="closeEditModal()"
                                class="h-12 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-12 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function instansiData() {
            return {
                showCreateModal: false,
                showEditModal: false,
                showDeleteModal: false,
                searchQuery: '',
                allInstansi: @json($instansi),
                editData: {
                    id: null,
                    nama: '',
                    alamat: '',
                    telp: '',
                    latitude: '',
                    longitude: '',
                    zoom: '',
                    logo: '',
                    logo_mobile: ''
                },
                deleteData: {
                    id: null,
                    name: ''
                },

                filteredInstansi() {
                    if (!this.searchQuery.trim()) {
                        return this.allInstansi;
                    }

                    const query = this.searchQuery.toLowerCase();
                    return this.allInstansi.filter(item => {
                        return (
                            (item.nama && item.nama.toLowerCase().includes(query)) ||
                            (item.alamat && item.alamat.toLowerCase().includes(query)) ||
                            (item.telp && item.telp.toLowerCase().includes(query)) ||
                            (item.latitude && String(item.latitude).toLowerCase().includes(query)) ||
                            (item.longitude && String(item.longitude).toLowerCase().includes(query))
                        );
                    });
                },

                resolveLogoUrl(path) {
                    if (!path) return '';
                    if (String(path).startsWith('http://') || String(path).startsWith('https://')) {
                        return path;
                    }

                    const normalized = String(path).replace(/^[/\\]+/, '');
                    return `/storage/${normalized}`;
                },

                openCreateModal() {
                    this.showCreateModal = true;
                },

                closeCreateModal() {
                    this.showCreateModal = false;
                },

                openEditModal(instansi) {
                    this.editData = {
                        id: instansi.id,
                        nama: instansi.nama,
                        alamat: instansi.alamat || '',
                        telp: instansi.telp || '',
                        latitude: instansi.latitude || '',
                        longitude: instansi.longitude || '',
                        zoom: instansi.zoom || '',
                        logo: instansi.logo || '',
                        logo_mobile: instansi.logo_mobile || ''
                    };
                    this.showEditModal = true;
                },

                closeEditModal() {
                    this.showEditModal = false;
                    this.editData = {
                        id: null,
                        nama: '',
                        alamat: '',
                        telp: '',
                        latitude: '',
                        longitude: '',
                        zoom: '',
                        logo: '',
                        logo_mobile: ''
                    };
                },

                openDeleteModal(id, name) {
                    this.deleteData = {
                        id,
                        name
                    };
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteData = {
                        id: null,
                        name: ''
                    };
                },

                confirmDelete() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/instansi/${this.deleteData.id}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        // Auto-open create modal if there are validation errors for create
        @if ($errors->any() && old('_method') !== 'PUT')
            document.addEventListener('alpine:init', () => {
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('open-create-modal'));
                }, 100);
            });

            document.addEventListener('open-create-modal', () => {
                const alpineEl = document.querySelector('[x-data]');
                if (alpineEl && alpineEl.__x) {
                    alpineEl.__x.$data.openCreateModal();
                }
            });
        @endif

        // Auto-open edit modal if there are validation errors for edit
        @if ($errors->any() && old('_method') === 'PUT')
            document.addEventListener('alpine:init', () => {
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('open-edit-modal'));
                }, 100);
            });

            document.addEventListener('open-edit-modal', () => {
                const alpineEl = document.querySelector('[x-data]');
                if (alpineEl && alpineEl.__x) {
                    alpineEl.__x.$data.editData = {
                        id: {{ old('id', 'null') }},
                        nama: '{{ old('nama', '') }}',
                        alamat: '{{ old('alamat', '') }}',
                        telp: '{{ old('telp', '') }}',
                        latitude: '{{ old('latitude', '') }}',
                        longitude: '{{ old('longitude', '') }}',
                        zoom: '{{ old('zoom', '') }}',
                        logo: '',
                        logo_mobile: ''
                    };
                    alpineEl.__x.$data.openEditModal(alpineEl.__x.$data.editData);
                }
            });
        @endif
    </script>
@endsection
