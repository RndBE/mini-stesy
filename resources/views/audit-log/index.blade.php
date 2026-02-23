@extends('layouts.app')

@section('content')
    <div x-data="auditLogPage(@js($auditLogs), @js($modules))" x-init="init()" class="space-y-4">
        <div x-show="logs.length === 0"
            class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm ring-1 ring-slate-100">
            Belum ada data audit log di database.
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Cari Aktivitas</label>
                        <input type="text" x-model="search" placeholder="Cari ID, modul, aksi, target, user..."
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Modul</label>
                        <select x-model="filterModule"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="ALL">Semua Modul</option>
                            <template x-for="moduleName in modules" :key="moduleName">
                                <option :value="moduleName" x-text="moduleName"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Status</label>
                        <select x-model="filterStatus"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="ALL">Semua Status</option>
                            <option value="SUCCESS">SUCCESS</option>
                            <option value="FAILED">FAILED</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Menampilkan <span class="font-semibold text-slate-700" x-text="filteredLogs().length"></span>
                        aktivitas.
                    </div>
                    <button type="button" @click="resetFilters()"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                        Reset Filter
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-100 text-xs font-semibold uppercase text-slate-700">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Modul</th>
                            <th class="px-4 py-3">Aktivitas</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="log in filteredLogs()" :key="log.id">
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="px-4 py-3 text-xs text-slate-700" x-text="formatDate(log.occurred_at)"></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-md border border-slate-300 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700"
                                        x-text="log.module"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900" x-text="log.activity"></div>
                                    <div class="text-xs text-slate-500" x-text="log.target"></div>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <div class="font-semibold text-slate-900" x-text="log.actor.name"></div>
                                    <div class="text-slate-500" x-text="'@' + log.actor.username"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-semibold"
                                        :class="statusClass(log.status)" x-text="log.status"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="openDetailModal(log.id)"
                                        class="rounded-md border border-indigo-300 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">
                                        Lihat
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredLogs().length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                Tidak ada aktivitas yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-cloak x-show="showDetailModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeDetailModal()">
            <div x-show="showDetailModal" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75" @click="closeDetailModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeDetailModal()">
                <div x-show="showDetailModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-5xl max-h-[90vh] rounded-lg bg-white shadow-xl overflow-hidden my-8 flex flex-col"
                    @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-xl font-bold text-gray-900">Detail Aktivitas Audit</h3>
                        <button type="button" @click="closeDetailModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <template x-if="selectedItem()">
                        <div class="px-6 py-5 space-y-4 text-sm overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="text-xs font-semibold uppercase text-slate-500">ID Audit</div>
                                    <div class="mt-1 font-semibold text-slate-900" x-text="selectedItem().id"></div>
                                </div>
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="text-xs font-semibold uppercase text-slate-500">Waktu</div>
                                    <div class="mt-1 text-slate-800" x-text="formatDate(selectedItem().occurred_at)"></div>
                                </div>
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="text-xs font-semibold uppercase text-slate-500">Status</div>
                                    <span class="mt-1 inline-flex rounded-md px-2 py-1 text-xs font-semibold"
                                        :class="statusClass(selectedItem().status)" x-text="selectedItem().status"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="text-xs font-semibold uppercase text-slate-500">Modul</div>
                                    <div class="mt-1 text-slate-800" x-text="selectedItem().module"></div>
                                </div>
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="text-xs font-semibold uppercase text-slate-500">Jenis Aksi</div>
                                    <span class="mt-1 inline-flex rounded-md px-2 py-1 text-xs font-semibold"
                                        :class="actionClass(selectedItem().action_type)"
                                        x-text="selectedItem().action_type"></span>
                                </div>
                            </div>

                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="text-xs font-semibold uppercase text-slate-500">Aktivitas</div>
                                <div class="mt-1 font-semibold text-slate-900 break-words" x-text="selectedItem().activity"></div>
                                <div class="mt-1 text-xs text-slate-500 break-words" x-text="selectedItem().description"></div>
                            </div>

                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="text-xs font-semibold uppercase text-slate-500">Target</div>
                                <div class="mt-1 text-slate-800 break-all" x-text="selectedItem().target"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="text-xs font-semibold uppercase text-slate-500">Aktor</div>
                                    <div class="mt-1 text-slate-900" x-text="selectedItem().actor.name"></div>
                                    <div class="text-xs text-slate-500 break-all" x-text="'@' + selectedItem().actor.username"></div>
                                    <div class="text-xs text-slate-500" x-text="'Role: ' + selectedItem().actor.role"></div>
                                </div>
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="text-xs font-semibold uppercase text-slate-500">IP Address</div>
                                    <div class="mt-1 rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700"
                                        x-text="selectedItem().ip_address"></div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="text-xs font-semibold uppercase text-slate-500">User Agent</div>
                                <div class="mt-1 max-h-24 overflow-auto rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700 break-all"
                                    x-text="selectedItem().user_agent"></div>
                            </div>

                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="text-xs font-semibold uppercase text-slate-500">Metadata</div>
                                <pre
                                    class="mt-1 max-h-56 overflow-auto rounded-md bg-slate-900 px-3 py-2 text-xs text-slate-100"
                                    x-text="prettyMetadata(selectedItem())"></pre>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedItem()" class="px-6 py-8 text-center text-sm text-slate-500 overflow-y-auto">
                        Data detail aktivitas tidak ditemukan.
                    </div>

                    <div class="border-t border-slate-100 px-6 py-3 flex justify-end bg-white">
                        <button type="button" @click="closeDetailModal()"
                            class="h-10 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function auditLogPage(initialLogs, initialModules) {
            return {
                logs: Array.isArray(initialLogs) ? initialLogs : [],
                modules: Array.isArray(initialModules) ? initialModules : [],
                search: '',
                filterModule: 'ALL',
                filterStatus: 'ALL',
                selectedId: null,
                showDetailModal: false,

                init() {},

                filteredLogs() {
                    const keyword = String(this.search || '').toLowerCase().trim();

                    return this.logs.filter((log) => {
                        const matchesKeyword = !keyword || [
                            log.id,
                            log.module,
                            log.activity,
                            log.target,
                            log.status,
                            log.actor?.name,
                            log.actor?.username,
                        ].join(' ').toLowerCase().includes(keyword);

                        const matchesModule = this.filterModule === 'ALL' || log.module === this.filterModule;
                        const matchesStatus = this.filterStatus === 'ALL' || log.status === this.filterStatus;

                        return matchesKeyword && matchesModule && matchesStatus;
                    }).sort((a, b) => new Date(b.occurred_at) - new Date(a.occurred_at));
                },

                openDetailModal(id) {
                    this.selectedId = id;
                    this.showDetailModal = true;
                },

                closeDetailModal() {
                    this.showDetailModal = false;
                },

                selectedItem() {
                    return this.logs.find((row) => row.id === this.selectedId) || null;
                },

                formatDate(value) {
                    if (!value) return '-';
                    const dt = new Date(value.replace(' ', 'T'));
                    if (Number.isNaN(dt.getTime())) return value;
                    return dt.toLocaleString('id-ID', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    });
                },

                statusClass(status) {
                    const value = String(status || '').toUpperCase();
                    if (value === 'SUCCESS') {
                        return 'bg-emerald-100 text-emerald-700';
                    }
                    if (value === 'FAILED') {
                        return 'bg-rose-100 text-rose-700';
                    }
                    return 'bg-slate-100 text-slate-700';
                },

                actionClass(action) {
                    const value = String(action || '').toUpperCase();
                    if (['CREATE', 'LOGIN'].includes(value)) {
                        return 'bg-blue-100 text-blue-700';
                    }
                    if (value === 'UPDATE') {
                        return 'bg-amber-100 text-amber-700';
                    }
                    if (value === 'EXPORT') {
                        return 'bg-emerald-100 text-emerald-700';
                    }
                    if (value === 'DELETE') {
                        return 'bg-rose-100 text-rose-700';
                    }
                    if (value === 'LOGOUT') {
                        return 'bg-slate-200 text-slate-700';
                    }
                    return 'bg-slate-100 text-slate-700';
                },

                prettyMetadata(item) {
                    if (!item || !item.metadata) {
                        return '{}';
                    }
                    return JSON.stringify(item.metadata, null, 2);
                },

                resetFilters() {
                    this.search = '';
                    this.filterModule = 'ALL';
                    this.filterStatus = 'ALL';
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
