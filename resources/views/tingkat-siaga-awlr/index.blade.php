@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="tingkatSiagaAwlrTable" x-init="init(@js($rows), @js(csrf_token()))">
        <div class="flex flex-col items-start justify-end gap-3 sm:flex-row sm:items-center">
            <div class="w-full sm:w-80">
                <div class="relative w-full">
                    <input type="text" x-model="searchQuery" placeholder="Cari perangkat..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <svg class="absolute right-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap text-left text-sm text-slate-600">
                    <thead class="bg-neutral-200 text-sm font-bold uppercase text-neutral-950">
                        <tr>
                            <th class="px-6 py-4 text-center">No</th>
                            <th class="px-6 py-4 text-left">ID Logger</th>
                            <th class="px-6 py-4 text-left">Nama Pos</th>
                            <th class="px-6 py-4 text-left">Status Notifikasi</th>
                            <th class="px-6 py-4 text-left">Level Siaga</th>
                            <th class="px-6 py-4 text-center">Jeda Notifikasi</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(row, idx) in filteredRows()" :key="row.id_logger">
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-center" x-text="idx + 1"></td>
                                <td class="px-6 py-4 font-medium text-slate-900" x-text="row.id_logger"></td>
                                <td class="px-6 py-4 text-slate-900" x-text="row.nama_lokasi || row.nama_pos"></td>
                                <td class="px-6 py-4">
                                    <template x-if="row.status_notifikasi_bool">
                                        <span
                                            class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            Aktif
                                        </span>
                                    </template>
                                    <template x-if="!row.status_notifikasi_bool">
                                        <span
                                            class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                            Tidak Aktif
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4">
                                    <template x-if="row.levels && row.levels.length > 0">
                                        <div class="space-y-1">
                                            <template x-for="(level, levelIdx) in row.levels"
                                                :key="`${row.id_logger}-${levelIdx}`">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="h-3 w-3 rounded-full"
                                                        :style="`background-color: ${toHexColor(level.warna)}`"></span>
                                                    <span class="min-w-16" x-text="level.nama"></span>
                                                    <span class="font-semibold" x-text="`${level.nilai} m`"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!row.levels || row.levels.length === 0">
                                        <span class="text-slate-400">-</span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center text-slate-900" x-text="row.jeda_notif"></td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" @click="openEditModal(row)"
                                        class="rounded-lg bg-blue-100 p-2 transition-colors hover:bg-blue-200"
                                        title="Edit notifikasi">
                                        <img src="{{ asset('icons/edit_icon.svg') }}"
                                            class="h-5 w-5 transition duration-200 ease-out filter hover:invert hover:sepia hover:saturate-[500%] hover:hue-rotate-[190deg] hover:brightness-90">
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <template x-if="filteredRows().length === 0">
                <div class="p-6 text-center text-slate-500">Data tidak ditemukan</div>
            </template>
        </div>

        <div x-show="showEditModal" x-cloak style="display: none;" class="fixed inset-0 z-50" role="dialog"
            aria-modal="true">
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 transition-opacity" @click="closeEditModal()"></div>

            <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
                @click="closeEditModal()">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full h-[100dvh] max-h-[100dvh] rounded-none sm:h-auto sm:max-h-[92vh] sm:max-w-3xl sm:rounded-2xl overflow-hidden bg-slate-100 text-left shadow-xl flex flex-col"
                    @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-4 sm:px-6 sm:py-5">
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-900">Edit Pos</h3>
                            <p class="text-sm text-slate-500" x-text="`${editForm.nama_pos} - ${editForm.id_logger}`"></p>
                        </div>
                        <button type="button" @click="closeEditModal()"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto">
                        <div class="space-y-4 px-4 py-4 sm:px-6 sm:py-5">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-medium text-slate-700">Status Notifikasi</span>
                                <button type="button" @click="toggleStatus()"
                                    class="inline-flex h-7 w-14 items-center rounded-full border transition"
                                    :class="editForm.status_notifikasi ? 'border-emerald-300 bg-emerald-500' :
                                        'border-slate-300 bg-slate-300'">
                                    <span class="h-6 w-6 rounded-full bg-white transition-transform"
                                        :class="editForm.status_notifikasi ? 'translate-x-7' : 'translate-x-0'"></span>
                                </button>
                                <span class="text-sm font-semibold"
                                    :class="editForm.status_notifikasi ? 'text-emerald-600' : 'text-slate-500'"
                                    x-text="editForm.status_notifikasi ? 'ON' : 'OFF'"></span>
                            </div>
                            <template x-if="editForm.status_notifikasi">
                                <div class="space-y-4">
                                    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                                        <div
                                            class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900">
                                            Level Alert
                                        </div>

                                        <div class="p-3 sm:p-4">
                                            <div class="hidden sm:grid grid-cols-12 gap-4 pb-3 text-sm font-bold text-slate-900">
                                                <div class="col-span-4">Nama Alert</div>
                                                <div class="col-span-4">Nilai</div>
                                                <div class="col-span-3">Warna</div>
                                                <div class="col-span-1 text-center">Aksi</div>
                                            </div>

                                            <div class="space-y-3">
                                                <template x-for="(level, index) in editForm.levels" :key="`level-${index}`">
                                                    <div>
                                                        <div class="sm:hidden rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-3">
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                                                    x-text="`Level ${index + 1}`"></span>
                                                                <button type="button" x-show="index === 0" @click="addLevel()"
                                                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-900 text-white shadow-sm hover:bg-indigo-800"
                                                                    title="Tambah level">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2" d="M12 4v16m8-8H4" />
                                                                    </svg>
                                                                </button>
                                                                <button type="button" x-show="index !== 0"
                                                                    @click="removeLevel(index)"
                                                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-600 text-white shadow-sm hover:bg-rose-700"
                                                                    title="Hapus level">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-2h6l1 2" />
                                                                    </svg>
                                                                </button>
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-xs font-semibold text-slate-600">Nama Alert</label>
                                                                <input type="text" x-model="level.nama"
                                                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                                    placeholder="Nama alert">
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-xs font-semibold text-slate-600">Nilai</label>
                                                                <div
                                                                    class="flex h-11 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                                                                    <input type="number" step="0.01" x-model="level.nilai"
                                                                        class="h-full w-full border-0 px-3 text-sm text-slate-900 focus:outline-none"
                                                                        placeholder="0.00">
                                                                    <div
                                                                        class="flex h-full w-14 items-center justify-center border-l border-slate-200 text-sm text-slate-700">
                                                                        m
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-xs font-semibold text-slate-600">Warna</label>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="relative shrink-0">
                                                                        <div
                                                                            class="flex h-11 w-12 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm">
                                                                            <div class="h-6 w-6 rounded-full"
                                                                                :style="`background-color:${toHexColor(level.warna)}`">
                                                                            </div>
                                                                        </div>
                                                                        <input type="color" x-model="level.warna"
                                                                            class="absolute inset-0 h-11 w-12 cursor-pointer opacity-0">
                                                                    </div>
                                                                    <input type="text" x-model="level.warna"
                                                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm uppercase text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                                        placeholder="#HEX">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="hidden sm:grid grid-cols-12 gap-4 items-center">
                                                            <div class="col-span-4">
                                                                <input type="text" x-model="level.nama"
                                                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                                    placeholder="Nama alert">
                                                            </div>

                                                            <div class="col-span-4">
                                                                <div
                                                                    class="flex h-12 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                                                                    <input type="number" step="0.01" x-model="level.nilai"
                                                                        class="h-full w-full border-0 px-4 text-sm text-slate-900 focus:outline-none"
                                                                        placeholder="0.00">
                                                                    <div
                                                                        class="flex h-full w-16 items-center justify-center border-l border-slate-200 text-sm text-slate-700">
                                                                        m
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-span-3">
                                                                <div class="flex items-center gap-3">
                                                                    <div class="relative">
                                                                        <div
                                                                            class="flex h-12 w-14 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm">
                                                                            <div class="h-7 w-7 rounded-full"
                                                                                :style="`background-color:${toHexColor(level.warna)}`">
                                                                            </div>
                                                                        </div>
                                                                        <input type="color" x-model="level.warna"
                                                                            class="absolute inset-0 h-12 w-14 cursor-pointer opacity-0">
                                                                    </div>

                                                                    <input type="text" x-model="level.warna"
                                                                        class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm uppercase text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                                        placeholder="#HEX">
                                                                </div>
                                                            </div>

                                                            <div class="col-span-1 flex justify-center">
                                                                <button type="button" x-show="index === 0" @click="addLevel()"
                                                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-900 text-white shadow-sm hover:bg-indigo-800"
                                                                    title="Tambah level">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2" d="M12 4v16m8-8H4" />
                                                                    </svg>
                                                                </button>

                                                                <button type="button" x-show="index !== 0"
                                                                    @click="removeLevel(index)"
                                                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-600 text-white shadow-sm hover:bg-rose-700"
                                                                    title="Hapus level">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-2h6l1 2" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-900">Jeda Notifikasi</label>
                                        <div class="flex w-full overflow-hidden rounded-xl border border-slate-50 bg-white">
                                            <input type="number" min="1" max="1440"
                                                x-model.number="editForm.jeda_notif"
                                                class="h-11 w-full flex-1 bg-white px-4 text-sm text-slate-900 outline-none focus:ring-0"
                                                placeholder="1" />
                                            <div
                                                class="flex items-center gap-2 border-l border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700">
                                                Menit
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </template>

                            <template x-if="editError">
                                <div class="rounded border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"
                                    x-text="editError"></div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-white px-4 py-4 sm:px-6">
                        <button type="button" @click="closeEditModal()"
                            class="h-11 sm:h-auto flex-1 sm:flex-none rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="button" @click="submitEdit()" :disabled="savingEdit"
                            class="h-11 sm:h-auto flex-1 sm:flex-none rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                            <span x-show="!savingEdit">Simpan</span>
                            <span x-show="savingEdit" x-cloak>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tingkatSiagaAwlrTable', () => ({
                allRows: [],
                searchQuery: '',
                csrfToken: '',
                showEditModal: false,
                savingEdit: false,
                editError: '',
                editForm: {
                    id_logger: '',
                    nama_pos: '',
                    status_notifikasi: false,
                    jeda_notif: 1,
                    levels: []
                },

                init(rows = [], csrfToken = '') {
                    this.allRows = Array.isArray(rows) ? rows : []
                    this.csrfToken = csrfToken || ''
                },

                toHexColor(color) {
                    const c = String(color || '').trim().toLowerCase()
                    if (c.startsWith('#') && /^#[0-9a-f]{6}$/i.test(c)) return c.toUpperCase()
                    if (c === 'hijau' || c === 'green') return '#22C55E'
                    if (c === 'kuning' || c === 'yellow') return '#FACC15'
                    if (c === 'oranye' || c === 'orange') return '#F97316'
                    if (c === 'merah' || c === 'red') return '#EF4444'
                    return '#94A3B8'
                },

                filteredRows() {
                    const q = (this.searchQuery || '').trim();
                    if (!q) return this.allRows;
                    const fuse = new Fuse(this.allRows, {
                        threshold: 0.35,
                        keys: ['nama_pos', 'nama_lokasi']
                    });
                    const fuzzyResults = fuse.search(q).map(r => r.item);
                    const ql = q.toLowerCase();
                    const exactResults = this.allRows.filter(row =>
                        row.id_logger && String(row.id_logger).toLowerCase().includes(ql)
                    );

                    const seen = new Set();
                    return [...fuzzyResults, ...exactResults].filter(row => {
                        if (seen.has(row.id_logger)) return false;
                        seen.add(row.id_logger);
                        return true;
                    });
                },

                openEditModal(row) {
                    this.editError = ''
                    this.editForm = {
                        id_logger: row.id_logger,
                        nama_pos: row.nama_pos || row.nama_lokasi || '',
                        status_notifikasi: !!row.status_notifikasi_bool,
                        jeda_notif: Number(row.jeda_notif_value || 1),
                        levels: (row.levels || []).map(level => ({
                            nama: level.nama || '',
                            nilai: level.nilai || '',
                            warna: this.toHexColor(level.warna),
                        })),
                    }

                    if (this.editForm.status_notifikasi && this.editForm.levels.length === 0) {
                        this.editForm.levels = this.defaultLevels()
                    }

                    this.showEditModal = true
                },

                closeEditModal() {
                    this.showEditModal = false
                    this.editError = ''
                },

                toggleStatus() {
                    this.editForm.status_notifikasi = !this.editForm.status_notifikasi
                    if (this.editForm.status_notifikasi && this.editForm.levels.length === 0) {
                        this.editForm.levels = this.defaultLevels()
                    }
                },

                defaultLevels() {
                    return [{
                            nama: 'Waspada',
                            nilai: '',
                            warna: '#FACC15'
                        },
                        {
                            nama: 'Siaga',
                            nilai: '',
                            warna: '#F97316'
                        },
                        {
                            nama: 'Awas',
                            nilai: '',
                            warna: '#EF4444'
                        },
                    ]
                },

                addLevel() {
                    this.editForm.levels.push({
                        nama: '',
                        nilai: '',
                        warna: '#EF4444'
                    })
                },

                removeLevel(index) {
                    this.editForm.levels.splice(index, 1)
                },

                buildPayload() {
                    const statusActive = !!this.editForm.status_notifikasi
                    const levels = statusActive ?
                        (this.editForm.levels || []).map(level => ({
                            nama: String(level.nama || '').trim(),
                            nilai: Number(level.nilai),
                            warna: this.toHexColor(level.warna),
                        })) : []

                    return {
                        status_notifikasi: statusActive ? 1 : 0,
                        jeda_notif: statusActive ? Number(this.editForm.jeda_notif || 1) : null,
                        levels: levels,
                    }
                },

                validatePayload(payload) {
                    if (!payload.status_notifikasi) return null
                    if (!Number.isFinite(payload.jeda_notif) || payload.jeda_notif < 1)
                        return 'Jeda notifikasi harus lebih dari 0 menit.'
                    if (!payload.levels.length)
                        return 'Minimal ada satu level alert ketika status notifikasi aktif.'
                    for (const level of payload.levels) {
                        if (!level.nama) return 'Nama alert wajib diisi.'
                        if (!Number.isFinite(level.nilai)) return 'Nilai alert harus berupa angka.'
                        if (!/^#[0-9A-F]{6}$/i.test(level.warna)) return 'Warna alert harus format HEX.'
                    }
                    return null
                },

                replaceRow(updatedRow) {
                    const idx = this.allRows.findIndex(r => r.id_logger === updatedRow.id_logger)
                    if (idx >= 0) this.allRows[idx] = updatedRow
                },

                async submitEdit() {
                    if (this.savingEdit) return
                    this.editError = ''

                    const payload = this.buildPayload()
                    const clientError = this.validatePayload(payload)
                    if (clientError) {
                        this.editError = clientError
                        return
                    }

                    this.savingEdit = true
                    try {
                        const url = `{{ url('/tingkat-siaga-awlr') }}/${this.editForm.id_logger}`
                        const response = await fetch(url, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify(payload),
                        })

                        const result = await response.json()
                        if (!response.ok || !result.success) {
                            this.editError = result.message || 'Gagal menyimpan data.'
                            if (result.errors) {
                                const firstError = Object.values(result.errors)[0]
                                if (Array.isArray(firstError) && firstError.length > 0) this
                                    .editError = firstError[0]
                            }
                            return
                        }

                        this.replaceRow(result.row)
                        this.closeEditModal()
                    } catch (e) {
                        this.editError = 'Terjadi kesalahan saat menghubungi server.'
                    } finally {
                        this.savingEdit = false
                    }
                },
            }))
        })
    </script>
@endpush
