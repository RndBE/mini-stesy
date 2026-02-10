@extends('layouts.app')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .user-map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            z-index: 1;
        }
    </style>
@endpush

@section('content')
    <div x-data="userData()" class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">User</h1>
                <p class="text-sm text-slate-500">Kelola data user dan role.</p>
            </div>

            <div class="flex items-center gap-3">
                @if (session('success'))
                    <div class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <button @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    + Tambah User
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-950">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">Nama</th>
                            <th scope="col" class="px-6 py-4">Username</th>
                            <th scope="col" class="px-6 py-4">Role</th>
                            <th scope="col" class="px-6 py-4">Instansi</th>
                            <th scope="col" class="px-6 py-4">Telepon</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($users as $index => $user)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $user->nama }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $user->username }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $user->level_user }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $user->instansi?->nama ?? $user->instansi ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $user->telp }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal({{ $user->id_user }})"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-950 hover:bg-slate-200 transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteUser({{ $user->id_user }}, '{{ $user->nama }}')"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-100 text-slate-950 hover:bg-red-200 transition-colors" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">
                                    Belum ada data user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create Modal --}}
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeCreateModal()">

            <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeCreateModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
                <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden my-8" @click.stop>

                    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-slate-900">Tambah User</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate()" id="createUserForm">
                        <div class="px-8 py-6 space-y-6 max-h-[70vh] overflow-y-auto">

                            <div x-show="createError" x-cloak class="p-4 bg-red-50 border border-red-200 rounded-xl">
                                <p class="text-sm font-semibold text-red-800" x-text="createError"></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Nama <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="createForm.nama" required
                                        placeholder="Nama lengkap"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Username <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="createForm.username" required
                                        placeholder="Username"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" x-model="createForm.password" required
                                        placeholder="Password"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Role <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative" @click.outside="closeCreateRoleDropdown()">
                                        <button type="button" @click="createRoleDropdownOpen = !createRoleDropdownOpen"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 py-2 text-left text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-between bg-white hover:bg-slate-50 transition-colors">
                                            <span x-text="createForm.level_user || '-- Pilih Role --'" class="flex-1"></span>
                                            <svg class="h-4 w-4 text-slate-500 transition-transform flex-shrink-0" :class="createRoleDropdownOpen ? 'rotate-180' : ''"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M6 9l6 6 6-6" />
                                            </svg>
                                        </button>
                                        <div x-show="createRoleDropdownOpen" x-cloak x-transition
                                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                                            @foreach($roles as $role)
                                                <button type="button" @click="createForm.level_user = '{{ $role->role_name }}'; createRoleDropdownOpen = false"
                                                    class="w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 first:rounded-t-lg last:rounded-b-lg"
                                                    :class="createForm.level_user === '{{ $role->role_name }}' ? 'bg-indigo-50 text-indigo-700 font-semibold' : ''">
                                                    {{ $role->role_name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Instansi <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative" @click.outside="closeCreateInstansiDropdown()">
                                        <button type="button" @click="createInstansiDropdownOpen = !createInstansiDropdownOpen"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 py-2 text-left text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-between bg-white hover:bg-slate-50 transition-colors">
                                            <span x-text="getInstansiLabel(createForm.instansi_id, 'create')" class="flex-1"></span>
                                            <svg class="h-4 w-4 text-slate-500 transition-transform flex-shrink-0" :class="createInstansiDropdownOpen ? 'rotate-180' : ''"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M6 9l6 6 6-6" />
                                            </svg>
                                        </button>
                                        <div x-show="createInstansiDropdownOpen" x-cloak x-transition
                                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                                            <button type="button" @click="createForm.instansi_id = ''; createInstansiDropdownOpen = false"
                                                class="w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 first:rounded-t-lg"
                                                :class="!createForm.instansi_id ? 'bg-indigo-50 text-indigo-700 font-semibold' : ''">
                                                -- Pilih Instansi --
                                            </button>
                                            @foreach($instansi as $inst)
                                                <button type="button" @click="createForm.instansi_id = '{{ $inst->id }}'; createInstansiDropdownOpen = false"
                                                    class="w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 last:rounded-b-lg"
                                                    :class="String(createForm.instansi_id) === '{{ $inst->id }}' ? 'bg-indigo-50 text-indigo-700 font-semibold' : ''">
                                                    {{ $inst->nama }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Telepon <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="createForm.telp" required
                                        placeholder="Nomor telepon"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900 mb-2">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <textarea x-model="createForm.alamat" required rows="2"
                                    placeholder="Alamat lengkap"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Latitude <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="createForm.latitude" required
                                        @input="updateCreateMapFromInputs()"
                                        placeholder="Latitude"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Longitude <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="createForm.longitude" required
                                        @input="updateCreateMapFromInputs()"
                                        placeholder="Longitude"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Zoom <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" x-model.number="createForm.zoom" required min="1" max="20"
                                        @input="updateCreateMapFromInputs()"
                                        placeholder="Zoom level"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <!-- Interactive Map -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 mb-2">
                                    Pilih Lokasi di Peta
                                </label>
                                <div id="createMap" class="user-map border border-slate-200"></div>
                                <p class="text-xs text-slate-500 mt-2">Klik pada peta untuk memilih koordinat, atau ketik koordinat secara manual.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Logo <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="file" id="create_logo" @change="createForm.logo = $event.target.files[0]; previewCreateLogo()" accept="image/*" required
                                            class="hidden">
                                        <label for="create_logo"
                                            @dragover.prevent="createLogoDragover = true"
                                            @dragleave.prevent="createLogoDragover = false"
                                            @drop.prevent="handleCreateLogoDrop"
                                            :class="createLogoDragover ? 'bg-indigo-50 border-indigo-400' : 'bg-slate-50 border-slate-200'"
                                            class="flex items-center justify-center w-full px-6 py-12 border-2 border-dashed rounded-lg cursor-pointer transition-colors">
                                            <div x-show="!createLogoPreview" class="text-center">
                                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <p class="mt-2 text-sm text-slate-600">Seret file ke area ini atau klik untuk memilih file.</p>
                                            </div>
                                            <img x-show="createLogoPreview" :src="createLogoPreview" class="max-h-32 max-w-full" alt="Preview">
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Logo Mobile <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="file" id="create_logo_mobile" @change="createForm.logo_mobile = $event.target.files[0]; previewCreateLogoMobile()" accept="image/*" required
                                            class="hidden">
                                        <label for="create_logo_mobile"
                                            @dragover.prevent="createLogoMobileDragover = true"
                                            @dragleave.prevent="createLogoMobileDragover = false"
                                            @drop.prevent="handleCreateLogoMobileDrop"
                                            :class="createLogoMobileDragover ? 'bg-indigo-50 border-indigo-400' : 'bg-slate-50 border-slate-200'"
                                            class="flex items-center justify-center w-full px-6 py-12 border-2 border-dashed rounded-lg cursor-pointer transition-colors">
                                            <div x-show="!createLogoMobilePreview" class="text-center">
                                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <p class="mt-2 text-sm text-slate-600">Seret file ke area ini atau klik untuk memilih file.</p>
                                            </div>
                                            <img x-show="createLogoMobilePreview" :src="createLogoMobilePreview" class="max-h-32 max-w-full" alt="Preview">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-slate-200 bg-white">
                            <button type="button" @click="closeCreateModal()"
                                class="h-10 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="createSubmitting"
                                class="h-10 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!createSubmitting">Simpan</span>
                                <span x-show="createSubmitting" x-cloak>Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeDeleteModal()">

            <div x-show="showDeleteModal" x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeDeleteModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div x-show="showDeleteModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden" @click.stop>

                    <div class="px-6 py-5 border-b border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Hapus</h3>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <p class="text-sm text-slate-600">
                            Apakah Anda yakin ingin menghapus user
                            <span class="font-semibold text-slate-900" x-text="deleteData.name"></span>?
                        </p>
                        <p class="mt-2 text-sm text-red-600 font-medium">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-200">
                        <button type="button" @click="closeDeleteModal()"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="button" @click="confirmDelete()"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeEditModal()">

            <div x-show="showEditModal" x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeEditModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
                <div x-show="showEditModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden my-8" @click.stop>

                    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-slate-900">Edit User</h3>
                        <button type="button" @click="closeEditModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitEdit()" id="editUserForm">
                        <div class="px-8 py-6 space-y-6 max-h-[70vh] overflow-y-auto">

                            <div x-show="editError" x-cloak class="p-4 bg-red-50 border border-red-200 rounded-xl">
                                <p class="text-sm font-semibold text-red-800" x-text="editError"></p>
                            </div>

                            <div x-show="editLoading" x-cloak class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600">
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Memuat data...</p>
                            </div>

                            <div x-show="!editLoading" x-cloak class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Nama <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="editForm.nama" required
                                            placeholder="Nama lengkap"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Username <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="editForm.username" required
                                            placeholder="Username"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Password (kosongkan jika tidak diubah)
                                        </label>
                                        <input type="password" x-model="editForm.password"
                                            placeholder="Password baru (opsional)"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Role <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative" @click.outside="closeEditRoleDropdown()">
                                            <button type="button" @click="editRoleDropdownOpen = !editRoleDropdownOpen"
                                                class="w-full h-10 rounded-lg border border-slate-200 px-3 py-2 text-left text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-between bg-white hover:bg-slate-50 transition-colors">
                                                <span x-text="editForm.level_user || '-- Pilih Role --'" class="flex-1"></span>
                                                <svg class="h-4 w-4 text-slate-500 transition-transform flex-shrink-0" :class="editRoleDropdownOpen ? 'rotate-180' : ''"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M6 9l6 6 6-6" />
                                                </svg>
                                            </button>
                                            <div x-show="editRoleDropdownOpen" x-cloak x-transition
                                                class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                                                @foreach($roles as $role)
                                                    <button type="button" @click="editForm.level_user = '{{ $role->role_name }}'; editRoleDropdownOpen = false"
                                                        class="w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 first:rounded-t-lg last:rounded-b-lg"
                                                        :class="editForm.level_user === '{{ $role->role_name }}' ? 'bg-indigo-50 text-indigo-700 font-semibold' : ''">
                                                        {{ $role->role_name }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Instansi <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative" @click.outside="closeEditInstansiDropdown()">
                                            <button type="button" @click="editInstansiDropdownOpen = !editInstansiDropdownOpen"
                                                class="w-full h-10 rounded-lg border border-slate-200 px-3 py-2 text-left text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-between bg-white hover:bg-slate-50 transition-colors">
                                                <span x-text="getInstansiLabel(editForm.instansi_id, 'edit')" class="flex-1"></span>
                                                <svg class="h-4 w-4 text-slate-500 transition-transform flex-shrink-0" :class="editInstansiDropdownOpen ? 'rotate-180' : ''"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M6 9l6 6 6-6" />
                                                </svg>
                                            </button>
                                            <div x-show="editInstansiDropdownOpen" x-cloak x-transition
                                                class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                                                <button type="button" @click="editForm.instansi_id = ''; editInstansiDropdownOpen = false"
                                                    class="w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 first:rounded-t-lg"
                                                    :class="!editForm.instansi_id ? 'bg-indigo-50 text-indigo-700 font-semibold' : ''">
                                                    -- Pilih Instansi --
                                                </button>
                                                @foreach($instansi as $inst)
                                                    <button type="button" @click="editForm.instansi_id = '{{ $inst->id }}'; editInstansiDropdownOpen = false"
                                                        class="w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 last:rounded-b-lg"
                                                        :class="String(editForm.instansi_id) === '{{ $inst->id }}' ? 'bg-indigo-50 text-indigo-700 font-semibold' : ''">
                                                        {{ $inst->nama }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Telepon <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="editForm.telp" required
                                            placeholder="Nomor telepon"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Alamat <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="editForm.alamat" required rows="2"
                                        placeholder="Alamat lengkap"
                                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Latitude <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="editForm.latitude" required
                                            @input="updateEditMapFromInputs()"
                                            placeholder="Latitude"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Longitude <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="editForm.longitude" required
                                            @input="updateEditMapFromInputs()"
                                            placeholder="Longitude"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Zoom <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" x-model.number="editForm.zoom" required min="1" max="20"
                                            @input="updateEditMapFromInputs()"
                                            placeholder="Zoom level"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <!-- Interactive Map -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Pilih Lokasi di Peta
                                    </label>
                                    <div id="editMap" class="user-map border border-slate-200"></div>
                                    <p class="text-xs text-slate-500 mt-2">Klik pada peta untuk memilih koordinat, atau ketik koordinat secara manual.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Logo (kosongkan jika tidak diubah)
                                        </label>
                                        <div class="relative">
                                            <input type="file" id="edit_logo" @change="editForm.logo = $event.target.files[0]; previewEditLogo()" accept="image/*"
                                                class="hidden">
                                            <label for="edit_logo"
                                                @dragover.prevent="editLogoDragover = true"
                                                @dragleave.prevent="editLogoDragover = false"
                                                @drop.prevent="handleEditLogoDrop"
                                                :class="editLogoDragover ? 'bg-indigo-50 border-indigo-400' : 'bg-slate-50 border-slate-200'"
                                                class="flex items-center justify-center w-full px-6 py-12 border-2 border-dashed rounded-lg cursor-pointer transition-colors">
                                                <div x-show="!editLogoPreview" class="text-center">
                                                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    <p class="mt-2 text-sm text-slate-600">Seret file ke area ini atau klik untuk memilih file.</p>
                                                </div>
                                                <img x-show="editLogoPreview" :src="editLogoPreview" class="max-h-32 max-w-full" alt="Preview">
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Logo Mobile (kosongkan jika tidak diubah)
                                        </label>
                                        <div class="relative">
                                            <input type="file" id="edit_logo_mobile" @change="editForm.logo_mobile = $event.target.files[0]; previewEditLogoMobile()" accept="image/*"
                                                class="hidden">
                                            <label for="edit_logo_mobile"
                                                @dragover.prevent="editLogoMobileDragover = true"
                                                @dragleave.prevent="editLogoMobileDragover = false"
                                                @drop.prevent="handleEditLogoMobileDrop"
                                                :class="editLogoMobileDragover ? 'bg-indigo-50 border-indigo-400' : 'bg-slate-50 border-slate-200'"
                                                class="flex items-center justify-center w-full px-6 py-12 border-2 border-dashed rounded-lg cursor-pointer transition-colors">
                                                <div x-show="!editLogoMobilePreview" class="text-center">
                                                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    <p class="mt-2 text-sm text-slate-600">Seret file ke area ini atau klik untuk memilih file.</p>
                                                </div>
                                                <img x-show="editLogoMobilePreview" :src="editLogoMobilePreview" class="max-h-32 max-w-full" alt="Preview">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-slate-200 bg-white">
                            <button type="button" @click="closeEditModal()"
                                class="h-10 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="editSubmitting || editLoading"
                                class="h-10 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!editSubmitting">Update</span>
                                <span x-show="editSubmitting" x-cloak>Menyimpan...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        function userData() {
            return {
                showCreateModal: false,
                showEditModal: false,
                showDeleteModal: false,
                createRoleDropdownOpen: false,
                createInstansiDropdownOpen: false,
                editRoleDropdownOpen: false,
                editInstansiDropdownOpen: false,
                createForm: {
                    nama: '',
                    username: '',
                    password: '',
                    level_user: '',
                    instansi_id: '',
                    alamat: '',
                    telp: '',
                    latitude: '',
                    longitude: '',
                    zoom: 13,
                    logo: null,
                    logo_mobile: null,
                },
                editForm: {
                    id: null,
                    nama: '',
                    username: '',
                    password: '',
                    level_user: '',
                    instansi_id: '',
                    alamat: '',
                    telp: '',
                    latitude: '',
                    longitude: '',
                    zoom: 13,
                    logo: null,
                    logo_mobile: null,
                },
                createError: '',
                editError: '',
                createSubmitting: false,
                editSubmitting: false,
                editLoading: false,
                createLogoDragover: false,
                createLogoMobileDragover: false,
                editLogoDragover: false,
                editLogoMobileDragover: false,
                createLogoPreview: '',
                createLogoMobilePreview: '',
                editLogoPreview: '',
                editLogoMobile: null,
                editLogoMobilePreview: null,
                deleteData: {
                    id: null,
                    name: ''
                },
                // Map instances
                createMap: null,
                createMarker: null,
                editMap: null,
                editMarker: null,

                getInstansiLabel(id, form) {
                    if (!id) return '-- Pilih Instansi --';
                    const instansiList = {!! json_encode($instansi->map(fn($i) => ['id' => $i->id, 'nama' => $i->nama])) !!};
                    const instansi = instansiList.find(i => i.id == id);
                    return instansi ? instansi.nama : '-- Pilih Instansi --';
                },

                logFormData(label) {
                    console.log(`=== ${label} ===`);
                    console.log('Create Form:', this.createForm);
                    console.log('Edit Form:', this.editForm);
                },

                closeCreateRoleDropdown() {
                    this.createRoleDropdownOpen = false;
                },

                closeCreateInstansiDropdown() {
                    this.createInstansiDropdownOpen = false;
                },

                closeEditRoleDropdown() {
                    this.editRoleDropdownOpen = false;
                },

                closeEditInstansiDropdown() {
                    this.editInstansiDropdownOpen = false;
                },

                handleCreateLogoDrop(e) {
                    this.createLogoDragover = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.createForm.logo = files[0];
                        this.previewCreateLogo();
                    }
                },

                handleCreateLogoMobileDrop(e) {
                    this.createLogoMobileDragover = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.createForm.logo_mobile = files[0];
                        this.previewCreateLogoMobile();
                    }
                },

                handleEditLogoDrop(e) {
                    this.editLogoDragover = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.editForm.logo = files[0];
                        this.previewEditLogo();
                    }
                },

                handleEditLogoMobileDrop(e) {
                    this.editLogoMobileDragover = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.editForm.logo_mobile = files[0];
                        this.previewEditLogoMobile();
                    }
                },

                previewCreateLogo() {
                    if (this.createForm.logo) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.createLogoPreview = e.target.result;
                        };
                        reader.readAsDataURL(this.createForm.logo);
                    }
                },

                previewCreateLogoMobile() {
                    if (this.createForm.logo_mobile) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.createLogoMobilePreview = e.target.result;
                        };
                        reader.readAsDataURL(this.createForm.logo_mobile);
                    }
                },

                previewEditLogo() {
                    if (this.editForm.logo) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.editLogoPreview = e.target.result;
                        };
                        reader.readAsDataURL(this.editForm.logo);
                    }
                },

                previewEditLogoMobile() {
                    if (this.editForm.logo_mobile) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.editLogoMobilePreview = e.target.result;
                        };
                        reader.readAsDataURL(this.editForm.logo_mobile);
                    }
                },

                openCreateModal() {
                    this.createRoleDropdownOpen = false;
                    this.createInstansiDropdownOpen = false;
                    this.createForm = {
                        nama: '',
                        username: '',
                        password: '',
                        level_user: '',
                        instansi_id: '',
                        alamat: '',
                        telp: '',
                        latitude: '',
                        longitude: '',
                        zoom: 13,
                        logo: null,
                        logo_mobile: null,
                    };
                    this.createLogoPreview = '';
                    this.createLogoMobilePreview = '';
                    this.createError = '';
                    this.showCreateModal = true;
                    this.initCreateMap();
                },

                closeCreateModal() {
                    this.showCreateModal = false;
                    this.destroyCreateMap();
                    this.createRoleDropdownOpen = false;
                    this.createInstansiDropdownOpen = false;
                    this.createForm = {
                        nama: '',
                        username: '',
                        password: '',
                        level_user: '',
                        instansi_id: '',
                        alamat: '',
                        telp: '',
                        latitude: '',
                        longitude: '',
                        zoom: 13,
                        logo: null,
                        logo_mobile: null,
                    };
                    this.createLogoPreview = '';
                    this.createLogoMobilePreview = '';
                    this.createError = '';
                },

                async submitCreate() {
                    // Validasi field yang diperlukan
                    if (!this.createForm.nama.trim()) {
                        this.createError = 'Nama harus diisi.';
                        return;
                    }
                    if (!this.createForm.username.trim()) {
                        this.createError = 'Username harus diisi.';
                        return;
                    }
                    if (!this.createForm.password.trim()) {
                        this.createError = 'Password harus diisi.';
                        return;
                    }
                    if (!this.createForm.level_user) {
                        this.createError = 'Role harus dipilih.';
                        return;
                    }
                    if (!this.createForm.instansi_id) {
                        this.createError = 'Instansi harus dipilih.';
                        return;
                    }
                    if (!this.createForm.telp.trim()) {
                        this.createError = 'Telepon harus diisi.';
                        return;
                    }
                    if (!this.createForm.alamat.trim()) {
                        this.createError = 'Alamat harus diisi.';
                        return;
                    }
                    if (!this.createForm.latitude.trim()) {
                        this.createError = 'Latitude harus diisi.';
                        return;
                    }
                    if (!this.createForm.longitude.trim()) {
                        this.createError = 'Longitude harus diisi.';
                        return;
                    }
                    if (!this.createForm.logo) {
                        this.createError = 'Logo harus diunggah.';
                        return;
                    }
                    if (!this.createForm.logo_mobile) {
                        this.createError = 'Logo Mobile harus diunggah.';
                        return;
                    }

                    this.createSubmitting = true;
                    this.createError = '';
                    this.logFormData('SUBMIT CREATE');

                    try {
                        const formData = new FormData();
                        formData.append('nama', this.createForm.nama);
                        formData.append('username', this.createForm.username);
                        formData.append('password', this.createForm.password);
                        formData.append('level_user', this.createForm.level_user);
                        formData.append('instansi_id', this.createForm.instansi_id);
                        formData.append('alamat', this.createForm.alamat);
                        formData.append('telp', this.createForm.telp);
                        formData.append('latitude', this.createForm.latitude);
                        formData.append('longitude', this.createForm.longitude);
                        formData.append('zoom', this.createForm.zoom);
                        if (this.createForm.logo) {
                            formData.append('logo', this.createForm.logo);
                        }
                        if (this.createForm.logo_mobile) {
                            formData.append('logo_mobile', this.createForm.logo_mobile);
                        }
                        formData.append('_token', '{{ csrf_token() }}');

                        console.log('Sending request to:', '{{ route('users.store') }}');
                        const response = await fetch('{{ route('users.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();
                        console.log('Response Status:', response.status);
                        console.log('Response Data:', data);

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            if (data.errors) {
                                const errorMessages = [];
                                for (const [field, messages] of Object.entries(data.errors)) {
                                    const message = Array.isArray(messages) ? messages[0] : messages;
                                    errorMessages.push(`${field}: ${message}`);
                                }
                                this.createError = errorMessages.join(' | ');
                            } else {
                                this.createError = data.message || 'Terjadi kesalahan saat menyimpan data.';
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.createError = 'Terjadi kesalahan saat menghubungi server: ' + error.message;
                    } finally {
                        this.createSubmitting = false;
                    }
                },

                async openEditModal(userId) {
                    this.showEditModal = true;
                    this.editRoleDropdownOpen = false;
                    this.editInstansiDropdownOpen = false;
                    this.editLoading = true;
                    this.editError = '';
                    this.editLogoPreview = '';
                    this.editLogoMobilePreview = '';
                    this.editForm = {
                        id: userId,
                        nama: '',
                        username: '',
                        password: '',
                        level_user: '',
                        instansi_id: '',
                        alamat: '',
                        telp: '',
                        latitude: '',
                        longitude: '',
                        zoom: 13,
                        logo: null,
                        logo_mobile: null,
                    };

                    try {
                        const response = await fetch(`/users/${userId}`, {
                            headers: {
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.editForm.nama = data.user.nama;
                            this.editForm.username = data.user.username;
                            this.editForm.level_user = data.user.level_user;
                            this.editForm.instansi_id = data.user.instansi_id ? String(data.user.instansi_id) : '';
                            this.editForm.alamat = data.user.alamat;
                            this.editForm.telp = data.user.telp;
                            this.editForm.latitude = data.user.latitude;
                            this.editForm.longitude = data.user.longitude;
                            this.editForm.zoom = data.user.zoom;

                            // Delay map initialization to ensure modal is fully rendered
                            setTimeout(() => {
                                this.initEditMap();
                            }, 300);
                        } else {
                            this.editError = 'Gagal memuat data user.';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.editError = 'Terjadi kesalahan saat memuat data.';
                    } finally {
                        this.editLoading = false;
                    }
                },

                closeEditModal() {
                    this.showEditModal = false;
                    this.destroyEditMap();
                    this.editRoleDropdownOpen = false;
                    this.editInstansiDropdownOpen = false;
                    this.editForm = {
                        id: null,
                        nama: '',
                        username: '',
                        password: '',
                        level_user: '',
                        instansi_id: '',
                        alamat: '',
                        telp: '',
                        latitude: '',
                        longitude: '',
                        zoom: 13,
                        logo: null,
                        logo_mobile: null,
                    };
                    this.editLogoPreview = '';
                    this.editLogoMobilePreview = '';
                    this.editError = '';
                },

                async submitEdit() {
                    // Validasi field yang diperlukan
                    if (!this.editForm.nama.trim()) {
                        this.editError = 'Nama harus diisi.';
                        return;
                    }
                    if (!this.editForm.username.trim()) {
                        this.editError = 'Username harus diisi.';
                        return;
                    }
                    if (!this.editForm.level_user) {
                        this.editError = 'Role harus dipilih.';
                        return;
                    }
                    if (!this.editForm.instansi_id) {
                        this.editError = 'Instansi harus dipilih.';
                        return;
                    }
                    if (!this.editForm.telp.trim()) {
                        this.editError = 'Telepon harus diisi.';
                        return;
                    }
                    if (!this.editForm.alamat.trim()) {
                        this.editError = 'Alamat harus diisi.';
                        return;
                    }
                    if (!this.editForm.latitude.trim()) {
                        this.editError = 'Latitude harus diisi.';
                        return;
                    }
                    if (!this.editForm.longitude.trim()) {
                        this.editError = 'Longitude harus diisi.';
                        return;
                    }

                    this.editSubmitting = true;
                    this.editError = '';
                    this.logFormData('SUBMIT EDIT');

                    try {
                        const formData = new FormData();
                        formData.append('nama', this.editForm.nama);
                        formData.append('username', this.editForm.username);
                        if (this.editForm.password) {
                            formData.append('password', this.editForm.password);
                        }
                        formData.append('level_user', this.editForm.level_user);
                        formData.append('instansi_id', this.editForm.instansi_id);
                        formData.append('alamat', this.editForm.alamat);
                        formData.append('telp', this.editForm.telp);
                        formData.append('latitude', this.editForm.latitude);
                        formData.append('longitude', this.editForm.longitude);
                        formData.append('zoom', this.editForm.zoom);
                        if (this.editForm.logo) {
                            formData.append('logo', this.editForm.logo);
                        }
                        if (this.editForm.logo_mobile) {
                            formData.append('logo_mobile', this.editForm.logo_mobile);
                        }
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('_method', 'PUT');

                        console.log('Sending request to:', `/users/${this.editForm.id}`);
                        const response = await fetch(`/users/${this.editForm.id}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();
                        console.log('Response Status:', response.status);
                        console.log('Response Data:', data);

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            if (data.errors) {
                                const errorMessages = [];
                                for (const [field, messages] of Object.entries(data.errors)) {
                                    const message = Array.isArray(messages) ? messages[0] : messages;
                                    errorMessages.push(`${field}: ${message}`);
                                }
                                this.editError = errorMessages.join(' | ');
                            } else {
                                this.editError = data.message || 'Terjadi kesalahan saat menyimpan data.';
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.editError = 'Terjadi kesalahan saat menghubungi server: ' + error.message;
                    } finally {
                        this.editSubmitting = false;
                    }
                },

                // Map Methods
                initCreateMap() {
                    this.$nextTick(() => {
                        if (this.createMap) {
                            this.createMap.remove();
                        }

                        const defaultLat = this.createForm.latitude || -6.200000;
                        const defaultLng = this.createForm.longitude || 106.816666;
                        const defaultZoom = this.createForm.zoom || 13;

                        this.createMap = L.map('createMap').setView([defaultLat, defaultLng], defaultZoom);

                        L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                            maxZoom: 20,
                            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                        }).addTo(this.createMap);

                        this.createMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(this.createMap);

                        // Map click event
                        this.createMap.on('click', (e) => {
                            this.createForm.latitude = e.latlng.lat.toFixed(6);
                            this.createForm.longitude = e.latlng.lng.toFixed(6);
                            this.createMarker.setLatLng(e.latlng);
                        });

                        // Marker drag event
                        this.createMarker.on('dragend', (e) => {
                            const position = e.target.getLatLng();
                            this.createForm.latitude = position.lat.toFixed(6);
                            this.createForm.longitude = position.lng.toFixed(6);
                        });

                        // Zoom event
                        this.createMap.on('zoomend', () => {
                            this.createForm.zoom = this.createMap.getZoom();
                        });
                    });
                },

                updateCreateMapFromInputs() {
                    if (!this.createMap || !this.createMarker) return;

                    const lat = parseFloat(this.createForm.latitude);
                    const lng = parseFloat(this.createForm.longitude);
                    const zoom = parseInt(this.createForm.zoom);

                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        this.createMarker.setLatLng([lat, lng]);
                        this.createMap.setView([lat, lng], !isNaN(zoom) ? zoom : this.createMap.getZoom());
                    }
                },

                destroyCreateMap() {
                    if (this.createMap) {
                        this.createMap.remove();
                        this.createMap = null;
                        this.createMarker = null;
                    }
                },

                initEditMap() {
                    this.$nextTick(() => {
                        if (this.editMap) {
                            this.editMap.remove();
                        }

                        const defaultLat = this.editForm.latitude || -6.200000;
                        const defaultLng = this.editForm.longitude || 106.816666;
                        const defaultZoom = this.editForm.zoom || 13;

                        this.editMap = L.map('editMap').setView([defaultLat, defaultLng], defaultZoom);

                        L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                            maxZoom: 20,
                            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                        }).addTo(this.editMap);

                        this.editMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(this.editMap);

                        // Map click event
                        this.editMap.on('click', (e) => {
                            this.editForm.latitude = e.latlng.lat.toFixed(6);
                            this.editForm.longitude = e.latlng.lng.toFixed(6);
                            this.editMarker.setLatLng(e.latlng);
                        });

                        // Marker drag event
                        this.editMarker.on('dragend', (e) => {
                            const position = e.target.getLatLng();
                            this.editForm.latitude = position.lat.toFixed(6);
                            this.editForm.longitude = position.lng.toFixed(6);
                        });

                        // Zoom event
                        this.editMap.on('zoomend', () => {
                            this.editForm.zoom = this.editMap.getZoom();
                        });
                    });
                },

                updateEditMapFromInputs() {
                    if (!this.editMap || !this.editMarker) return;

                    const lat = parseFloat(this.editForm.latitude);
                    const lng = parseFloat(this.editForm.longitude);
                    const zoom = parseInt(this.editForm.zoom);

                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        this.editMarker.setLatLng([lat, lng]);
                        this.editMap.setView([lat, lng], !isNaN(zoom) ? zoom : this.editMap.getZoom());
                    }
                },

                destroyEditMap() {
                    if (this.editMap) {
                        this.editMap.remove();
                        this.editMap = null;
                        this.editMarker = null;
                    }
                },

                async deleteUser(userId, userName) {
                    this.deleteData = { id: userId, name: userName };
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteData = { id: null, name: '' };
                },

                async confirmDelete() {
                    try {
                        const response = await fetch(`/users/${this.deleteData.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal menghapus user.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus data.');
                    } finally {
                        this.closeDeleteModal();
                    }
                }
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
