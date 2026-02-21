@extends('layouts.app')

@section('content')
    <div x-data="listParameterCrud()" class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3 mt-3">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    + Tambah Parameter
                </button>
                <a href="{{ route('parameter-group.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Kelola Group Parameter
                </a>
            </div>
        </div>

        @if (session('success'))
            <div
                class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-900">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama Parameter</th>
                            <th class="px-4 py-3">Parameter Utama</th>
                            <th class="px-4 py-3">Default Satuan</th>
                            <th class="px-4 py-3">Default Kolom Sensor</th>
                            <th class="px-4 py-3">Default Group</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $index => $item)
                            @php
                                $editPayload = [
                                    'id' => $item->id,
                                    'nama_parameter' => $item->nama_parameter,
                                    'parameter_utama' => $item->parameter_utama,
                                    'default_satuan' => $item->default_satuan,
                                    'default_kolom_sensor' => $item->default_kolom_sensor,
                                    'default_parameter_group_id' => $item->default_parameter_group_id,
                                    'is_active' => (bool) $item->is_active,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->nama_parameter }}</td>
                                <td class="px-4 py-3">{{ $item->parameter_utama ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->default_satuan ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->default_kolom_sensor ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->parameterGroup?->nama_group ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click='openEditModal(@json($editPayload))'
                                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Edit
                                        </button>
                                        <form action="{{ route('list-parameter.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('Hapus parameter {{ $item->nama_parameter }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada list
                                    parameter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
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
                    class="w-full max-w-4xl rounded-lg bg-white shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 px-8 py-2">
                        <h3 class="text-xl font-bold text-gray-900">Tambah List Parameter</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('list-parameter.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="form_mode" value="create">

                        <div class="px-8 pt-4 pb-3 space-y-3">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Parameter</label>
                                    <input type="text" name="nama_parameter"
                                        value="{{ old('form_mode') === 'create' ? old('nama_parameter') : '' }}"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                    @if (old('form_mode') === 'create')
                                        @error('nama_parameter')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Parameter Utama</label>
                                    <input type="text" name="parameter_utama"
                                        value="{{ old('form_mode') === 'create' ? old('parameter_utama') : '' }}"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'create')
                                        @error('parameter_utama')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Default Satuan</label>
                                    <input type="text" name="default_satuan"
                                        value="{{ old('form_mode') === 'create' ? old('default_satuan') : '' }}"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'create')
                                        @error('default_satuan')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Default Kolom
                                        Sensor</label>
                                    <input type="text" name="default_kolom_sensor"
                                        value="{{ old('form_mode') === 'create' ? old('default_kolom_sensor') : '' }}"
                                        placeholder="contoh: sensor1"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'create')
                                        @error('default_kolom_sensor')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Default Group
                                        Parameter</label>
                                    <select name="default_parameter_group_id"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Pilih Group -</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}" @selected((string) old('default_parameter_group_id') === (string) $group->id)>
                                                {{ $group->nama_group }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'create')
                                        @error('default_parameter_group_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="px-8 pb-3 flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" id="is_active_create"
                                @checked(old('form_mode') === 'create' ? old('is_active', 1) : 1) class="rounded border-slate-300 text-indigo-600">
                            <label for="is_active_create" class="text-sm font-medium text-slate-700">Aktif</label>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-8 py-3 border-t border-gray-100 bg-white">
                            <button type="button" @click="closeCreateModal()"
                                class="h-10 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-10 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
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
                    class="w-full max-w-4xl rounded-lg bg-white shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 px-8 py-2">
                        <h3 class="text-xl font-bold text-gray-900">Edit List Parameter</h3>
                        <button type="button" @click="closeEditModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="POST" :action="updateAction()">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_mode" value="edit">
                        <input type="hidden" name="item_id" x-model="editData.id">
                        <input type="hidden" name="is_active" :value="editData.is_active ? 1 : 0">

                        <div class="px-8 pt-4 pb-3 space-y-3">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Parameter</label>
                                    <input type="text" name="nama_parameter" x-model="editData.nama_parameter"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                    @if (old('form_mode') === 'edit')
                                        @error('nama_parameter')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Parameter Utama</label>
                                    <input type="text" name="parameter_utama" x-model="editData.parameter_utama"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'edit')
                                        @error('parameter_utama')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Default Satuan</label>
                                    <input type="text" name="default_satuan" x-model="editData.default_satuan"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'edit')
                                        @error('default_satuan')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Default Kolom
                                        Sensor</label>
                                    <input type="text" name="default_kolom_sensor"
                                        x-model="editData.default_kolom_sensor" placeholder="contoh: sensor1"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'edit')
                                        @error('default_kolom_sensor')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Default Group
                                        Parameter</label>
                                    <select name="default_parameter_group_id"
                                        x-model="editData.default_parameter_group_id"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Pilih Group -</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->nama_group }}</option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'edit')
                                        @error('default_parameter_group_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="px-8 pb-3 flex items-center gap-2">
                            <input type="checkbox" id="is_active_edit" :checked="editData.is_active"
                                @change="editData.is_active = $event.target.checked"
                                class="rounded border-slate-300 text-indigo-600">
                            <label for="is_active_edit" class="text-sm font-medium text-slate-700">Aktif</label>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-8 py-3 border-t border-gray-100 bg-white">
                            <button type="button" @click="closeEditModal()"
                                class="h-10 px-6 rounded-lg border border-indigo-500 text-indigo-600 font-semibold hover:bg-indigo-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-10 px-6 rounded-lg bg-indigo-700 text-white font-semibold hover:bg-indigo-800">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function listParameterCrud() {
            const hasErrors = @json($errors->any());
            const oldFormMode = @json(old('form_mode'));

            return {
                baseUrl: @json(url('list-parameter')),
                showCreateModal: hasErrors && oldFormMode === 'create',
                showEditModal: hasErrors && oldFormMode === 'edit',
                editData: {
                    id: @json(old('item_id')),
                    nama_parameter: @json(old('form_mode') === 'edit' ? old('nama_parameter') : ''),
                    parameter_utama: @json(old('form_mode') === 'edit' ? old('parameter_utama') : ''),
                    default_satuan: @json(old('form_mode') === 'edit' ? old('default_satuan') : ''),
                    default_kolom_sensor: @json(old('form_mode') === 'edit' ? old('default_kolom_sensor') : ''),
                    default_parameter_group_id: @json(old('form_mode') === 'edit' ? (string) old('default_parameter_group_id', '') : ''),
                    is_active: @json(old('form_mode') === 'edit' ? (bool) old('is_active') : false),
                },
                openCreateModal() {
                    this.showCreateModal = true;
                },
                closeCreateModal() {
                    this.showCreateModal = false;
                },
                openEditModal(row) {
                    this.editData = {
                        id: row.id,
                        nama_parameter: row.nama_parameter ?? '',
                        parameter_utama: row.parameter_utama ?? '',
                        default_satuan: row.default_satuan ?? '',
                        default_kolom_sensor: row.default_kolom_sensor ?? '',
                        default_parameter_group_id: row.default_parameter_group_id ? String(row
                            .default_parameter_group_id) : '',
                        is_active: !!row.is_active,
                    };
                    this.showEditModal = true;
                },
                closeEditModal() {
                    this.showEditModal = false;
                },
                updateAction() {
                    return `${this.baseUrl}/${this.editData.id}`;
                },
            };
        }
    </script>
@endpush
