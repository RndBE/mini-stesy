@extends('layouts.app')

@section('content')
    <div x-data="roleData()" class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Role</h1>
                <p class="text-sm text-slate-500">Kelola role dan akses permission.</p>
            </div>

            <div class="flex items-center gap-3">
                @if (session('success'))
                    <div
                        class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <button @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    + Tambah Role
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-slate-100 text-xs font-semibold uppercase text-slate-700">
                        <tr>
                            <th scope="col" class="px-6 py-4">No</th>
                            <th scope="col" class="px-6 py-4">Nama Role</th>
                            <th scope="col" class="px-6 py-4">Jumlah Permission</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($roles as $index => $role)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $role->role_name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $role->permissions_count }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal({{ $role->id }})"
                                            class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Edit
                                        </button>
                                        <button @click="deleteRole({{ $role->id }}, '{{ $role->role_name }}')"
                                            class="inline-flex items-center rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">
                                    Belum ada data role.
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
                    class="w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden my-8" @click.stop>

                    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-slate-900">Tambah Role</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate()" id="createRoleForm">
                        <div class="px-8 py-6 space-y-6">

                            <div x-show="createError" x-cloak class="p-4 bg-red-50 border border-red-200 rounded-xl">
                                <p class="text-sm font-semibold text-red-800" x-text="createError"></p>
                            </div>

                            <div>
                                <label for="create_role_name" class="block text-sm font-semibold text-slate-900 mb-2">
                                    Nama Role <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="create_role_name" x-model="createForm.role_name" required
                                    placeholder="Masukkan Nama Role"
                                    class="w-full h-14 rounded-xl border border-slate-200 px-4 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900 mb-3">Permission</label>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach ($permissions as $permission)
                                        <label
                                            class="flex items-center gap-3 border border-slate-200 rounded-xl px-4 py-3 hover:bg-slate-50 cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span
                                                class="text-sm font-medium text-slate-800">{{ $permission->permission_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-slate-200 bg-white">
                            <button type="button" @click="closeCreateModal()"
                                class="h-12 px-6 rounded-xl border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="createSubmitting"
                                class="h-12 px-6 rounded-xl bg-indigo-700 text-white font-semibold hover:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed">
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
                    class="w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden my-8" @click.stop>

                    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-slate-900">Edit Role</h3>
                        <button type="button" @click="closeEditModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitEdit()" id="editRoleForm">
                        <div class="px-8 py-6 space-y-6">

                            <div x-show="editError" x-cloak class="p-4 bg-red-50 border border-red-200 rounded-xl">
                                <p class="text-sm font-semibold text-red-800" x-text="editError"></p>
                            </div>

                            <div x-show="editLoading" x-cloak class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600">
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Memuat data...</p>
                            </div>

                            <div x-show="!editLoading" x-cloak class="space-y-6">
                                <div>
                                    <label for="edit_role_name" class="block text-sm font-semibold text-slate-900 mb-2">
                                        Nama Role <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="edit_role_name" x-model="editForm.role_name" required
                                        placeholder="Masukkan Nama Role"
                                        class="w-full h-14 rounded-xl border border-slate-200 px-4 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-3">Permission</label>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <template x-for="permission in editPermissions" :key="permission.id">
                                            <label
                                                class="flex items-center gap-3 border border-slate-200 rounded-xl px-4 py-3 hover:bg-slate-50 cursor-pointer">
                                                <input type="checkbox" :value="permission.id"
                                                    x-model="editForm.permissions"
                                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span
                                                    class="text-sm font-medium text-slate-800"
                                                    x-text="permission.permission_name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-slate-200 bg-white">
                            <button type="button" @click="closeEditModal()"
                                class="h-12 px-6 rounded-xl border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="editSubmitting || editLoading"
                                class="h-12 px-6 rounded-xl bg-indigo-700 text-white font-semibold hover:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed">
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
        function roleData() {
            return {
                showCreateModal: false,
                showEditModal: false,
                createForm: {
                    role_name: '',
                    permissions: []
                },
                editForm: {
                    id: null,
                    role_name: '',
                    permissions: []
                },
                editPermissions: [],
                createError: '',
                editError: '',
                createSubmitting: false,
                editSubmitting: false,
                editLoading: false,

                openCreateModal() {
                    this.createForm = {
                        role_name: '',
                        permissions: []
                    };
                    this.createError = '';
                    // Uncheck all checkboxes
                    document.querySelectorAll('#createRoleForm input[type="checkbox"]').forEach(cb => cb.checked = false);
                    this.showCreateModal = true;
                },

                closeCreateModal() {
                    this.showCreateModal = false;
                    this.createForm = {
                        role_name: '',
                        permissions: []
                    };
                    this.createError = '';
                },

                async submitCreate() {
                    this.createSubmitting = true;
                    this.createError = '';

                    // Collect checked permissions
                    const checkedPermissions = Array.from(
                        document.querySelectorAll('#createRoleForm input[name="permissions[]"]:checked')
                    ).map(cb => parseInt(cb.value));

                    try {
                        const response = await fetch('{{ route('roles.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                role_name: this.createForm.role_name,
                                permissions: checkedPermissions
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            // Handle validation errors
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

                async openEditModal(roleId) {
                    this.showEditModal = true;
                    this.editLoading = true;
                    this.editError = '';
                    this.editForm = {
                        id: roleId,
                        role_name: '',
                        permissions: []
                    };

                    try {
                        const response = await fetch(`/roles/${roleId}`, {
                            headers: {
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.editForm.role_name = data.role.role_name;
                            this.editForm.permissions = data.selected || [];
                            this.editPermissions = data.permissions || [];
                        } else {
                            this.editError = 'Gagal memuat data role.';
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
                        role_name: '',
                        permissions: []
                    };
                    this.editError = '';
                },

                async submitEdit() {
                    this.editSubmitting = true;
                    this.editError = '';

                    try {
                        const response = await fetch(`/roles/${this.editForm.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                role_name: this.editForm.role_name,
                                permissions: this.editForm.permissions
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

                async deleteRole(roleId, roleName) {
                    if (!confirm(`Hapus role "${roleName}"?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/roles/${roleId}`, {
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
                            alert(data.message || 'Gagal menghapus role.');
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
