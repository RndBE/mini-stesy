@extends('layouts.app')
@section('content')
    <div x-data="permissionData()" class="space-y-3">
        <div class="flex items-center justify-between mt-3">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between ">
                @if (session('success'))
                    <div
                        class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                        {{ session('success') }}
                    </div>
                @else
                    <div></div>
                @endif
                <div class="w-full sm:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <input type="text" x-model="searchQuery" placeholder="Cari permission..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <svg class="absolute right-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button @click="openCreateModal()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 whitespace-nowrap">
                        + Tambah Permission
                    </button>
                </div>
            </div>
        </div>
        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-950">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">Nama Permission</th>
                            <th scope="col" class="px-6 py-4">Jumlah Role</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(permission, index) in filteredPermissions()" :key="permission.id">
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900" x-text="index + 1"></td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900"
                                    x-text="permission.permission_name"></td>
                                <td class="whitespace-nowrap px-6 py-4" x-text="permission.roles_count"></td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(permission.id)"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-950 hover:bg-slate-200 transition-colors"
                                            title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deletePermission(permission.id, permission.permission_name)"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-100 text-slate-950 hover:bg-red-200 transition-colors"
                                            title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tbody class="divide-y divide-slate-200 bg-white" x-show="filteredPermissions().length === 0">
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">
                                Belum ada data permission.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeCreateModal()">
            <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeCreateModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeCreateModal()">
                <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-4xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-8 py-2 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-gray-900">Tambah Permission</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitCreate()" id="createPermissionForm">
                        <div class="px-8 pt-0 pb-3 space-y-3">
                            <div x-show="createError" x-cloak class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm font-semibold text-red-800" x-text="createError"></p>
                            </div>
                            <div>
                                <label for="create_permission_name"
                                    class="block text-sm font-semibold text-slate-900 mb-2">
                                    Nama Permission <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="create_permission_name" x-model="createForm.permission_name"
                                    required placeholder="Masukkan Nama Permission"
                                    class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-8 py-3 border-t border-gray-100 bg-white">
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
                                <svg class="w-14 h-14 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <h3 class="text-xl text-center font-bold text-blue-900">Hapus RBAC Permission</h3>
                        </div>
                    </div>
                    <div class="px-6 py-3">
                        <p class="text-sm text-center text-slate-600">
                            Apakah Anda yakin ingin menghapus permission
                            <span class="font-semibold text-slate-900" x-text="deleteData.name"></span>?
                        </p>
                        <p class="mt-2 text-sm text-center text-red-600 font-medium">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4">
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
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeEditModal()">
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
                    class="w-full max-w-4xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-8 py-3 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-gray-900">Edit Permission</h3>
                        <button type="button" @click="closeEditModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitEdit()" id="editPermissionForm">
                        <div class="px-8 pt-1 pb-3 space-y-3">
                            <div x-show="editError" x-cloak class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm font-semibold text-red-800" x-text="editError"></p>
                            </div>
                            <div x-show="editLoading" x-cloak class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600">
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Memuat data...</p>
                            </div>
                            <div x-show="!editLoading" x-cloak>
                                <label for="edit_permission_name" class="block text-sm font-semibold text-slate-900 mb-2">
                                    Nama Permission <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="edit_permission_name" x-model="editForm.permission_name"
                                    required placeholder="Masukkan Nama Permission"
                                    class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-8 py-3 border-t border-gray-100 bg-white">
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
        function permissionData() {
            return {
                showCreateModal: false,
                showEditModal: false,
                showDeleteModal: false,
                searchQuery: '',
                allPermissions: @json($permissions),
                createForm: {
                    permission_name: '',
                },
                editForm: {
                    id: null,
                    permission_name: '',
                },
                createError: '',
                editError: '',
                createSubmitting: false,
                editSubmitting: false,
                editLoading: false,
                deleteData: {
                    id: null,
                    name: ''
                },
                filteredPermissions() {
                    if (!this.searchQuery.trim()) {
                        return this.allPermissions;
                    }
                    const query = this.searchQuery.toLowerCase();
                    return this.allPermissions.filter(permission => {
                        return permission.permission_name && permission.permission_name.toLowerCase().includes(
                            query);
                    });
                },
                openCreateModal() {
                    this.createForm = {
                        permission_name: '',
                    };
                    this.createError = '';
                    this.showCreateModal = true;
                },
                closeCreateModal() {
                    this.showCreateModal = false;
                    this.createForm = {
                        permission_name: '',
                    };
                    this.createError = '';
                },
                async submitCreate() {
                    this.createSubmitting = true;
                    this.createError = '';
                    try {
                        const response = await fetch('{{ route('permissions.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                permission_name: this.createForm.permission_name,
                            })
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
                async openEditModal(permissionId) {
                    this.showEditModal = true;
                    this.editLoading = true;
                    this.editError = '';
                    this.editForm = {
                        id: permissionId,
                        permission_name: '',
                    };
                    try {
                        const response = await fetch(`/permissions/${permissionId}`, {
                            headers: {
                                'Accept': 'application/json',
                            }
                        });
                        const data = await response.json();
                        if (response.ok) {
                            this.editForm.permission_name = data.permission.permission_name;
                        } else {
                            this.editError = 'Gagal memuat data permission.';
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
                        permission_name: '',
                    };
                    this.editError = '';
                },
                async submitEdit() {
                    this.editSubmitting = true;
                    this.editError = '';
                    try {
                        const response = await fetch(`/permissions/${this.editForm.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                permission_name: this.editForm.permission_name,
                            })
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
                async deletePermission(permissionId, permissionName) {
                    this.deleteData = {
                        id: permissionId,
                        name: permissionName
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
                async confirmDelete() {
                    try {
                        const response = await fetch(`/permissions/${this.deleteData.id}`, {
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
                            alert(data.message || 'Gagal menghapus permission.');
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
@endsection
