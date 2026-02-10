@extends('layouts.app')

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
                    <thead class="bg-slate-100 text-xs font-semibold uppercase text-slate-700">
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
                                            class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Edit
                                        </button>
                                        <button @click="deleteUser({{ $user->id_user }}, '{{ $user->nama }}')"
                                            class="inline-flex items-center rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Hapus
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
                                    <select x-model="createForm.level_user" required
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Pilih Role --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->role_name }}">{{ $role->role_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Instansi <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="createForm.instansi_id"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Pilih Instansi --</option>
                                        @foreach($instansi as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->nama }}</option>
                                        @endforeach
                                    </select>
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
                                        placeholder="Latitude"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Longitude <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="createForm.longitude" required
                                        placeholder="Longitude"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Zoom <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" x-model.number="createForm.zoom" required min="1" max="20"
                                        placeholder="Zoom level"
                                        class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">
                                        Logo <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="file" id="create_logo" @change="createForm.logo = $event.target.files[0]; previewCreateLogo()" accept="image/*" required
                                            class="hidden">
                                        <label for="create_logo" @click.prevent="document.getElementById('create_logo').click()"
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
                                        <label for="create_logo_mobile" @click.prevent="document.getElementById('create_logo_mobile').click()"
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
                                        <select x-model="editForm.level_user" required
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="">-- Pilih Role --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->role_name }}">{{ $role->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Instansi <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="editForm.instansi_id"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="">-- Pilih Instansi --</option>
                                            @foreach($instansi as $inst)
                                                <option value="{{ $inst->id }}">{{ $inst->nama }}</option>
                                            @endforeach
                                        </select>
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
                                            placeholder="Latitude"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Longitude <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="editForm.longitude" required
                                            placeholder="Longitude"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Zoom <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" x-model.number="editForm.zoom" required min="1" max="20"
                                            placeholder="Zoom level"
                                            class="w-full h-10 rounded-lg border border-slate-200 px-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                                            Logo (kosongkan jika tidak diubah)
                                        </label>
                                        <div class="relative">
                                            <input type="file" id="edit_logo" @change="editForm.logo = $event.target.files[0]; previewEditLogo()" accept="image/*"
                                                class="hidden">
                                            <label for="edit_logo" @click.prevent="document.getElementById('edit_logo').click()"
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
                                            <label for="edit_logo_mobile" @click.prevent="document.getElementById('edit_logo_mobile').click()"
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
                editLogoMobilePreview: '',

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
                },

                closeCreateModal() {
                    this.showCreateModal = false;
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
                    this.createSubmitting = true;
                    this.createError = '';

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

                        const response = await fetch('{{ route('users.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            if (data.errors) {
                                const firstError = Object.values(data.errors)[0];
                                this.createError = Array.isArray(firstError) ? firstError[0] : firstError;
                            } else {
                                this.createError = data.message || 'Terjadi kesalahan saat menyimpan data.';
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.createError = 'Terjadi kesalahan saat menghubungi server.';
                    } finally {
                        this.createSubmitting = false;
                    }
                },

                async openEditModal(userId) {
                    this.showEditModal = true;
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
                            this.editForm.instansi_id = data.user.instansi_id;
                            this.editForm.alamat = data.user.alamat;
                            this.editForm.telp = data.user.telp;
                            this.editForm.latitude = data.user.latitude;
                            this.editForm.longitude = data.user.longitude;
                            this.editForm.zoom = data.user.zoom;
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
                    this.editSubmitting = true;
                    this.editError = '';

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

                        const response = await fetch(`/users/${this.editForm.id}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            if (data.errors) {
                                const firstError = Object.values(data.errors)[0];
                                this.editError = Array.isArray(firstError) ? firstError[0] : firstError;
                            } else {
                                this.editError = data.message || 'Terjadi kesalahan saat menyimpan data.';
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.editError = 'Terjadi kesalahan saat menghubungi server.';
                    } finally {
                        this.editSubmitting = false;
                    }
                },

                async deleteUser(userId, userName) {
                    if (!confirm(`Hapus user "${userName}"?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/users/${userId}`, {
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
@endsection
