@extends('layouts.app')

@section('content')
    @php
        $rolesPayload = $roles->pluck('role_name')->values();
        $instansiPayload = $instansi
            ->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'nama' => $item->nama,
                ];
            })
            ->values();
        $usersPayload = $users
            ->map(function ($u) {
                return [
                    'id_user'    => $u->id_user,
                    'nama'       => $u->nama,
                    'username'   => $u->username,
                    'level_user' => $u->level_user,
                    'instansi'   => $u->instansi?->nama ?? '-',
                    'status'     => $u->status ?? 'aktif',
                ];
            })
            ->values();
    @endphp
    <div x-data="userData()" class="space-y-3">
        <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
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
                    <input type="text" x-model="searchQuery" placeholder="Cari user..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <svg class="absolute right-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button @click="openCreateModal()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 whitespace-nowrap">
                    + Tambah User
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch; scroll-behavior: smooth;">
                <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-950">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Nama</th>
                            <th class="px-6 py-4">Username</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Instansi</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(u, i) in filteredUsers()" :key="u.id_user">
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-900" x-text="i + 1"></td>
                                <td class="px-6 py-4 font-semibold text-slate-900" x-text="u.nama"></td>
                                <td class="px-6 py-4" x-text="u.username"></td>
                                <td class="px-6 py-4" x-text="u.level_user"></td>
                                <td class="px-6 py-4" x-text="u.instansi || '-' "></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-green-100 text-green-800': (u.status || 'aktif') === 'aktif',
                                            'bg-yellow-100 text-yellow-800': u.status === 'suspend',
                                            'bg-red-100 text-red-800': u.status === 'non-aktif'
                                        }">
                                        <template x-if="(u.status || 'aktif') === 'aktif'">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Aktif
                                            </div>
                                        </template>
                                        <template x-if="u.status === 'suspend'">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                Suspend
                                            </div>
                                        </template>
                                        <template x-if="u.status === 'non-aktif'">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Non-Aktif
                                            </div>
                                        </template>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(u.id_user)"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-950 hover:bg-slate-200 transition-colors"
                                            title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteUser(u.id_user, u.nama)"
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
                        <tr x-show="filteredUsers().length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">Data tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="closeCreateModal()">
            <div x-show="showCreateModal"
                x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeCreateModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeCreateModal()">
                <div x-show="showCreateModal"
                    x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-5xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-4 sm:px-8 py-3 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-gray-900">Tambah User</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate()">
                        <div class="px-4 sm:px-8 pt-4 pb-3 space-y-3 max-h-[70vh] overflow-y-auto">
                            <div x-show="createError" x-cloak
                                class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"
                                x-text="createError"></div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama</label>
                                    <input type="text" x-model="createForm.nama"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Username</label>
                                    <input type="text" x-model="createForm.username"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Password</label>
                                    <input type="password" x-model="createForm.password"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Role</label>
<select class="hidden sm:block w-full rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        x-model="createForm.level_user"
                                        @change="handleRoleChange('create', createForm.level_user)">
                                        <option value="">-- Pilih Role --</option>
                                        <template x-for="r in roles" :key="'cr-sel-' + r">
                                            <option :value="r" x-text="r"></option>
                                        </template>
                                    </select>
<div class="sm:hidden relative" x-data="{ openRole: false }">
                                        <button type="button" @click="openRole = !openRole"
                                            class="w-full flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            :class="createForm.level_user ? 'text-gray-900' : 'text-gray-400'">
                                            <span x-text="createForm.level_user || '-- Pilih Role --'"></span>
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform"
                                                :class="openRole ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="openRole" x-cloak @click.outside="openRole = false"
                                            class="w-full mt-1 bg-white border border-gray-200 rounded-lg shadow max-h-44 overflow-y-auto">
                                            <div @click="createForm.level_user = ''; handleRoleChange('create', ''); openRole = false"
                                                class="px-4 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">-- Pilih Role --</div>
                                            <template x-for="r in roles" :key="'cr-dd-' + r">
                                                <div @click="createForm.level_user = r; handleRoleChange('create', r); openRole = false"
                                                    :class="createForm.level_user === r ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                    class="px-4 py-2 text-sm cursor-pointer" x-text="r"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Instansi</label>
                                <template x-if="isSuperAdminUser">
                                    <div>
<select class="hidden sm:block w-full rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            x-model="createForm.instansi_id"
                                            @change="syncSelectedLoggerAccess('create')">
                                            <option value="">-- Pilih Instansi --</option>
                                            <template x-for="inst in instansiList" :key="'ci-sel-' + inst.id">
                                                <option :value="String(inst.id)" x-text="inst.nama"></option>
                                            </template>
                                        </select>
<div class="sm:hidden relative" x-data="{ openInst: false }">
                                            <button type="button" @click="openInst = !openInst"
                                                class="w-full flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                :class="createForm.instansi_id ? 'text-gray-900' : 'text-gray-400'">
                                                <span x-text="instansiList.find(i => String(i.id) === createForm.instansi_id)?.nama || '-- Pilih Instansi --'" class="truncate"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openInst ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="openInst" x-cloak @click.outside="openInst = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-lg shadow max-h-44 overflow-y-auto">
                                                <div @click="createForm.instansi_id = ''; syncSelectedLoggerAccess('create'); openInst = false"
                                                    class="px-4 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">-- Pilih Instansi --</div>
                                                <template x-for="inst in instansiList" :key="'ci-dd-' + inst.id">
                                                    <div @click="createForm.instansi_id = String(inst.id); syncSelectedLoggerAccess('create'); openInst = false"
                                                        :class="createForm.instansi_id === String(inst.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-4 py-2 text-sm cursor-pointer truncate" x-text="inst.nama"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!isSuperAdminUser">
                                    <input type="text" :value="currentInstansiName"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 bg-slate-100" disabled>
                                </template>
                            </div>

                            <div x-show="shouldShowLoggerPicker(createForm.level_user)" x-cloak>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-semibold">Akses Logger / Pos</label>
                                    <span class="text-xs text-slate-500"
                                        x-text="`${createForm.logger_access.length} dipilih`"></span>
                                </div>
                                <p class="text-xs text-slate-500 mb-2" x-show="!isPegawaiRole(createForm.level_user)" x-cloak>
                                    Logger tambahan (opsional) lintas instansi untuk admin ini.</p>
                                <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-3">
                                    <template x-for="group in getLoggerGroups('create')" :key="'create-grp-' + group.instansi_id">
                                        <div>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 mb-1"
                                                x-show="isSuperAdminUser">
                                                <input type="checkbox" class="rounded border-slate-300"
                                                    :checked="isInstansiGroupAllChecked('create', group)"
                                                    @change="toggleInstansiGroup('create', group, $event.target.checked)">
                                                <span x-text="group.instansi_nama"></span>
                                            </label>
                                            <div class="space-y-2 pl-1">
                                                <template x-for="logger in group.loggers" :key="'create-log-' + logger.id_logger">
                                                    <label class="flex items-center gap-2 text-sm">
                                                        <input type="checkbox" class="rounded border-slate-300"
                                                            :checked="createForm.logger_access.includes(String(logger.id_logger))"
                                                            @change="toggleLoggerAccess('create', String(logger.id_logger), $event.target.checked)">
                                                        <span x-text="`${logger.nama_logger} (${logger.id_logger})`"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="getLoggerGroups('create').length === 0" class="text-xs text-slate-500">
                                        Logger belum tersedia.</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 px-4 sm:px-8 py-3 border-t border-gray-100 bg-white">
                            <button type="button" @click="closeCreateModal()"
                                class="h-10 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">Batal</button>
                            <button type="submit" :disabled="createSubmitting"
                                class="h-10 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800 disabled:opacity-50">
                                <span x-show="!createSubmitting">Simpan</span>
                                <span x-show="createSubmitting" x-cloak>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="closeEditModal()">
            <div x-show="showEditModal"
                x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeEditModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeEditModal()">
                <div x-show="showEditModal"
                    x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-5xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-4 sm:px-8 py-3 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-gray-900">Edit User</h3>
                        <button type="button" @click="closeEditModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitEdit()">
                        <div class="px-4 sm:px-8 pt-4 pb-3 space-y-3 max-h-[70vh] overflow-y-auto">
                            <div x-show="editError" x-cloak
                                class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"
                                x-text="editError"></div>
                            <div x-show="editLoading" x-cloak class="text-sm text-slate-500">Memuat data...</div>

                            <div x-show="!editLoading" x-cloak class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Nama</label>
                                        <input type="text" x-model="editForm.nama"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Username</label>
                                        <input type="text" x-model="editForm.username"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Password
                                            (opsional)</label>
                                        <input type="password" x-model="editForm.password"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">Role</label>
<select class="hidden sm:block w-full rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            x-model="editForm.level_user"
                                            @change="handleRoleChange('edit', editForm.level_user)">
                                            <option value="">-- Pilih Role --</option>
                                            <template x-for="r in roles" :key="'er-sel-' + r">
                                                <option :value="r" x-text="r"></option>
                                            </template>
                                        </select>
<div class="sm:hidden relative" x-data="{ openRoleE: false }">
                                            <button type="button" @click="openRoleE = !openRoleE"
                                                class="w-full flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                :class="editForm.level_user ? 'text-gray-900' : 'text-gray-400'">
                                                <span x-text="editForm.level_user || '-- Pilih Role --'"></span>
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openRoleE ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="openRoleE" x-cloak @click.outside="openRoleE = false"
                                                class="w-full mt-1 bg-white border border-gray-200 rounded-lg shadow max-h-44 overflow-y-auto">
                                                <div @click="editForm.level_user = ''; handleRoleChange('edit', ''); openRoleE = false"
                                                    class="px-4 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">-- Pilih Role --</div>
                                                <template x-for="r in roles" :key="'er-dd-' + r">
                                                    <div @click="editForm.level_user = r; handleRoleChange('edit', r); openRoleE = false"
                                                        :class="editForm.level_user === r ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                        class="px-4 py-2 text-sm cursor-pointer" x-text="r"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Instansi</label>
                                    <template x-if="isSuperAdminUser">
                                        <div>
<select class="hidden sm:block w-full rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                x-model="editForm.instansi_id"
                                                @change="syncSelectedLoggerAccess('edit')">
                                                <option value="">-- Pilih Instansi --</option>
                                                <template x-for="inst in instansiList" :key="'ei-sel-' + inst.id">
                                                    <option :value="String(inst.id)" x-text="inst.nama"></option>
                                                </template>
                                            </select>
<div class="sm:hidden relative" x-data="{ openInstE: false }">
                                                <button type="button" @click="openInstE = !openInstE"
                                                    class="w-full flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                    :class="editForm.instansi_id ? 'text-gray-900' : 'text-gray-400'">
                                                    <span x-text="instansiList.find(i => String(i.id) === editForm.instansi_id)?.nama || '-- Pilih Instansi --'" class="truncate"></span>
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openInstE ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <div x-show="openInstE" x-cloak @click.outside="openInstE = false"
                                                    class="w-full mt-1 bg-white border border-gray-200 rounded-lg shadow max-h-44 overflow-y-auto">
                                                    <div @click="editForm.instansi_id = ''; syncSelectedLoggerAccess('edit'); openInstE = false"
                                                        class="px-4 py-2 text-sm text-gray-400 hover:bg-slate-50 cursor-pointer">-- Pilih Instansi --</div>
                                                    <template x-for="inst in instansiList" :key="'ei-dd-' + inst.id">
                                                        <div @click="editForm.instansi_id = String(inst.id); syncSelectedLoggerAccess('edit'); openInstE = false"
                                                            :class="editForm.instansi_id === String(inst.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-900 hover:bg-slate-50'"
                                                            class="px-4 py-2 text-sm cursor-pointer truncate" x-text="inst.nama"></div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!isSuperAdminUser">
                                        <input type="text" :value="currentInstansiName"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2 bg-slate-100"
                                            disabled>
                                    </template>
                                </div>

                                <div x-show="shouldShowLoggerPicker(editForm.level_user)" x-cloak>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold">Akses Logger / Pos</label>
                                        <span class="text-xs text-slate-500"
                                            x-text="`${editForm.logger_access.length} dipilih`"></span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-2" x-show="!isPegawaiRole(editForm.level_user)" x-cloak>
                                        Logger tambahan (opsional) lintas instansi untuk admin ini.</p>
                                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-3">
                                        <template x-for="group in getLoggerGroups('edit')" :key="'edit-grp-' + group.instansi_id">
                                            <div>
                                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 mb-1"
                                                    x-show="isSuperAdminUser">
                                                    <input type="checkbox" class="rounded border-slate-300"
                                                        :checked="isInstansiGroupAllChecked('edit', group)"
                                                        @change="toggleInstansiGroup('edit', group, $event.target.checked)">
                                                    <span x-text="group.instansi_nama"></span>
                                                </label>
                                                <div class="space-y-2 pl-1">
                                                    <template x-for="logger in group.loggers" :key="'edit-log-' + logger.id_logger">
                                                        <label class="flex items-center gap-2 text-sm">
                                                            <input type="checkbox" class="rounded border-slate-300"
                                                                :checked="editForm.logger_access.includes(String(logger.id_logger))"
                                                                @change="toggleLoggerAccess('edit', String(logger.id_logger), $event.target.checked)">
                                                            <span x-text="`${logger.nama_logger} (${logger.id_logger})`"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="getLoggerGroups('edit').length === 0" class="text-xs text-slate-500">
                                            Logger belum tersedia.</div>
                                    </div>
                                </div>
<template x-if="isSuperAdminUser">
                                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50 space-y-3">
                                        <label class="block text-sm font-semibold text-gray-900">Status Akun</label>
                                        <div class="flex gap-2">
                                            <button type="button"
                                                @click="editForm.status = 'aktif'"
                                                :class="editForm.status === 'aktif' ? 'bg-green-600 text-white ring-2 ring-green-400' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                                                class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all flex items-center justify-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Aktif
                                            </button>
                                            <button type="button"
                                                @click="editForm.status = 'suspend'"
                                                :class="editForm.status === 'suspend' ? 'bg-yellow-500 text-white ring-2 ring-yellow-300' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                                                class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all flex items-center justify-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                Suspend
                                            </button>
                                            <button type="button"
                                                @click="editForm.status = 'non-aktif'"
                                                :class="editForm.status === 'non-aktif' ? 'bg-red-600 text-white ring-2 ring-red-400' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                                                class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all flex items-center justify-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Non-Aktif
                                            </button>
                                        </div>
<div x-show="editForm.status === 'suspend'" x-cloak>
                                            <label class="block text-xs font-semibold text-yellow-700 mb-1">Pesan untuk User (wajib diisi):</label>
                                            <textarea x-model="editForm.suspend_reason" rows="3"
                                                placeholder="Contoh: Akun Anda di-suspend karena belum menyelesaikan administrasi bulan ini. Hubungi admin untuk informasi lebih lanjut."
                                                class="w-full rounded-lg border border-yellow-300 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none"></textarea>
                                        </div>
                                        <p class="text-xs text-slate-500">Hanya Superadmin yang dapat mengubah status akun.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 px-4 sm:px-8 py-3 border-t border-gray-100 bg-white">
                            <button type="button" @click="closeEditModal()"
                                class="h-10 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">Batal</button>
                            <button type="submit" :disabled="editSubmitting || editLoading"
                                class="h-10 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800 disabled:opacity-50">
                                <span x-show="!editSubmitting">Update</span>
                                <span x-show="editSubmitting" x-cloak>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="closeDeleteModal()">
            <div x-show="showDeleteModal"
                x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeDeleteModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4" @click="closeDeleteModal()">
                <div x-show="showDeleteModal"
                    x-transition:enter="ease-in-out duration-300"
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
                            <h3 class="text-xl text-center font-bold text-blue-900">Hapus User</h3>
                        </div>
                    </div>
                    <div class="px-6 py-3">
                        <p class="text-sm text-center text-slate-600">Yakin hapus user
                            <span class="font-semibold text-slate-900"
                                x-text="deleteData.name"></span>?
                            </p>
                        <p class="mt-1 text-sm text-center text-red-600 font-medium">Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div class="flex items-center justify-center gap-3 px-6 py-3">
                        <button type="button" @click="closeDeleteModal()"
                            class="px-10 py-2 rounded-lg border border-blue-300 text-blue-700 font-semibold hover:bg-blue-100 transition-colors">Batal</button>
                        <button type="button" @click="confirmDelete()"
                            class="px-10 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function userData() {
            const roles = @json($rolesPayload);
            const instansiList = @json($instansiPayload);
            const loggerOptions = @json($loggerOptions);
            const users = @json($usersPayload);

            const currentInstansiId = @json($currentUser?->instansi_id ? (string) $currentUser->instansi_id : '');
            const currentInstansiName = @json($currentUser?->instansi?->nama ?? '-');
            const isSuperAdminUser = @json((bool) ($currentUser && $currentUser->isSuperAdmin()));

            return {
                roles,
                instansiList,
                loggerOptions,
                users,
                currentInstansiId,
                currentInstansiName,
                isSuperAdminUser,
                searchQuery: '',
                showCreateModal: false,
                showEditModal: false,
                showDeleteModal: false,
                createSubmitting: false,
                editSubmitting: false,
                editLoading: false,
                createError: '',
                editError: '',
                createForm: {
                    nama: '',
                    username: '',
                    password: '',
                    level_user: '',
                    instansi_id: '',
                    logger_access: [],
                },
                editForm: {
                    id: null,
                    nama: '',
                    username: '',
                    password: '',
                    level_user: '',
                    instansi_id: '',
                    logger_access: [],
                    status: 'aktif',
                    suspend_reason: '',
                },
                deleteData: {
                    id: null,
                    name: '',
                },

                filteredUsers() {
                    const q = (this.searchQuery || '').toLowerCase().trim();
                    if (!q) return this.users;
                    const fuse = new Fuse(this.users, {
                        threshold: 0.35,
                        keys: ['nama', 'instansi']
                    });
                    const fuzzyResults = fuse.search(this.searchQuery.trim()).map(r => r.item);
                    const exactResults = this.users.filter(u =>
                        (u.username || '').toLowerCase().includes(q) ||
                        (u.level_user || '').toLowerCase().includes(q)
                    );
                    const seen = new Set();
                    return [...fuzzyResults, ...exactResults].filter(u => {
                        if (seen.has(u.id)) return false;
                        seen.add(u.id);
                        return true;
                    });
                },

                normalizeRole(role) {
                    return String(role || '').trim().toLowerCase();
                },

                isPegawaiRole(role) {
                    const normalized = this.normalizeRole(role);
                    return normalized === 'pegawai' || normalized === 'user';
                },

                isInstansiAdminRole(role) {
                    const normalized = this.normalizeRole(role);
                    return normalized === 'instansi_admin' || normalized === 'admin';
                },

                shouldShowLoggerPicker(role) {
                    if (this.isPegawaiRole(role)) return true;
                    // Superadmin boleh memberi logger tambahan lintas instansi
                    // ke seorang instansi_admin.
                    return this.isSuperAdminUser && this.isInstansiAdminRole(role);
                },

                getForm(formType) {
                    return formType === 'edit' ? this.editForm : this.createForm;
                },

                getFormInstansiId(formType) {
                    if (!this.isSuperAdminUser) {
                        return String(this.currentInstansiId || '');
                    }

                    const form = this.getForm(formType);
                    return String(form.instansi_id || '');
                },

                getLoggerOptions(formType) {
                    // Superadmin memilih dari semua instansi; user lain hanya
                    // dari instansi (terpilih) miliknya.
                    if (this.isSuperAdminUser) {
                        return this.loggerOptions;
                    }

                    const instansiId = this.getFormInstansiId(formType);
                    if (!instansiId) return [];

                    return this.loggerOptions.filter((l) => String(l.instansi_id) === instansiId);
                },

                getLoggerGroups(formType) {
                    const groups = {};
                    this.getLoggerOptions(formType).forEach((l) => {
                        const key = String(l.instansi_id);
                        if (!groups[key]) {
                            const inst = this.instansiList.find((i) => String(i.id) === key);
                            groups[key] = {
                                instansi_id: key,
                                instansi_nama: inst ? inst.nama : 'Tanpa Instansi',
                                loggers: [],
                            };
                        }
                        groups[key].loggers.push(l);
                    });
                    return Object.values(groups);
                },

                isInstansiGroupAllChecked(formType, group) {
                    const form = this.getForm(formType);
                    return group.loggers.length > 0
                        && group.loggers.every((l) => form.logger_access.includes(String(l.id_logger)));
                },

                toggleInstansiGroup(formType, group, checked) {
                    group.loggers.forEach((l) =>
                        this.toggleLoggerAccess(formType, String(l.id_logger), checked));
                },

                toggleLoggerAccess(formType, loggerId, checked) {
                    const form = this.getForm(formType);
                    const id = String(loggerId);

                    if (checked) {
                        if (!form.logger_access.includes(id)) {
                            form.logger_access.push(id);
                        }
                        return;
                    }

                    form.logger_access = form.logger_access.filter((x) => x !== id);
                },

                syncSelectedLoggerAccess(formType) {
                    const form = this.getForm(formType);
                    const allowed = this.getLoggerOptions(formType).map((l) => String(l.id_logger));
                    form.logger_access = (form.logger_access || []).filter((x) => allowed.includes(String(x)));
                },

                handleRoleChange(formType, roleName) {
                    const form = this.getForm(formType);
                    form.level_user = roleName;

                    if (!this.shouldShowLoggerPicker(form.level_user)) {
                        form.logger_access = [];
                        return;
                    }

                    this.syncSelectedLoggerAccess(formType);
                },

                openCreateModal() {
                    this.createError = '';
                    this.createForm = {
                        nama: '',
                        username: '',
                        password: '',
                        level_user: '',
                        instansi_id: this.isSuperAdminUser ? '' : String(this.currentInstansiId || ''),
                        logger_access: [],
                    };
                    this.showCreateModal = true;
                },

                closeCreateModal() {
                    this.showCreateModal = false;
                    this.createError = '';
                },

                async submitCreate() {
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

                    const instansiId = this.getFormInstansiId('create');
                    if (!instansiId && this.normalizeRole(this.createForm.level_user) !== 'superadmin') {
                        this.createError = 'Instansi harus dipilih.';
                        return;
                    }

                    if (this.isPegawaiRole(this.createForm.level_user) && this.createForm.logger_access.length === 0) {
                        this.createError = 'Minimal pilih 1 logger untuk akun pegawai.';
                        return;
                    }

                    this.createSubmitting = true;
                    this.createError = '';

                    try {
                        const formData = new FormData();
                        formData.append('nama', this.createForm.nama);
                        formData.append('username', this.createForm.username);
                        formData.append('password', this.createForm.password);
                        formData.append('level_user', this.createForm.level_user);
                        formData.append('instansi_id', instansiId);
                        this.createForm.logger_access.forEach((id) => formData.append('logger_access[]', id));
                        formData.append('_token', '{{ csrf_token() }}');

                        const response = await fetch('{{ route('users.store') }}', {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json'
                            },
                            body: formData,
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            window.location.reload();
                            return;
                        }

                        this.createError = data.message || 'Gagal menyimpan user.';
                        if (data.errors) {
                            const firstField = Object.keys(data.errors)[0];
                            if (firstField && data.errors[firstField]?.[0]) {
                                this.createError = data.errors[firstField][0];
                            }
                        }
                    } catch (e) {
                        this.createError = 'Gagal menghubungi server.';
                    } finally {
                        this.createSubmitting = false;
                    }
                },

                async openEditModal(userId) {
                    this.showEditModal = true;
                    this.editLoading = true;
                    this.editError = '';
                    this.editForm = {
                        id: userId,
                        nama: '',
                        username: '',
                        password: '',
                        level_user: '',
                        instansi_id: this.isSuperAdminUser ? '' : String(this.currentInstansiId || ''),
                        logger_access: [],
                    };

                    try {
                        const response = await fetch(`/users/${userId}`, {
                            headers: {
                                Accept: 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            this.editError = data.message || 'Gagal memuat data user.';
                            return;
                        }

                        this.editForm.nama = data.user.nama || '';
                        this.editForm.username = data.user.username || '';
                        this.editForm.level_user = data.user.level_user || '';
                        this.editForm.instansi_id = data.user.instansi_id ? String(data.user.instansi_id) : '';
                        this.editForm.logger_access = Array.isArray(data.logger_access_ids) ?
                            data.logger_access_ids.map((x) => String(x)) : [];
                        this.editForm.status = data.status || 'aktif';
                        this.editForm.suspend_reason = data.suspend_reason || '';

                        this.syncSelectedLoggerAccess('edit');
                    } catch (e) {
                        this.editError = 'Gagal menghubungi server.';
                    } finally {
                        this.editLoading = false;
                    }
                },

                closeEditModal() {
                    this.showEditModal = false;
                    this.editError = '';
                    this.editLoading = false;
                },

                async submitEdit() {
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

                    const instansiId = this.getFormInstansiId('edit');
                    if (!instansiId && this.normalizeRole(this.editForm.level_user) !== 'superadmin') {
                        this.editError = 'Instansi harus dipilih.';
                        return;
                    }

                    if (this.isPegawaiRole(this.editForm.level_user) && this.editForm.logger_access.length === 0) {
                        this.editError = 'Minimal pilih 1 logger untuk akun pegawai.';
                        return;
                    }

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
                        formData.append('instansi_id', instansiId);
                        this.editForm.logger_access.forEach((id) => formData.append('logger_access[]', id));
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('_method', 'PUT');
                        formData.append('status', this.editForm.status || 'aktif');
                        if (this.editForm.status === 'suspend') {
                            formData.append('suspend_reason', this.editForm.suspend_reason || '');
                        }

                        const response = await fetch(`/users/${this.editForm.id}`, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json'
                            },
                            body: formData,
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            window.location.reload();
                            return;
                        }

                        this.editError = data.message || 'Gagal menyimpan perubahan.';
                        if (data.errors) {
                            const firstField = Object.keys(data.errors)[0];
                            if (firstField && data.errors[firstField]?.[0]) {
                                this.editError = data.errors[firstField][0];
                            }
                        }
                    } catch (e) {
                        this.editError = 'Gagal menghubungi server.';
                    } finally {
                        this.editSubmitting = false;
                    }
                },

                deleteUser(id, name) {
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

                async confirmDelete() {
                    try {
                        const response = await fetch(`/users/${this.deleteData.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                Accept: 'application/json',
                            },
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            window.location.reload();
                            return;
                        }

                        alert(data.message || 'Gagal menghapus user.');
                    } catch (e) {
                        alert('Gagal menghubungi server.');
                    } finally {
                        this.closeDeleteModal();
                    }
                },
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
