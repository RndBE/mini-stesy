@extends('layouts.app')

@section('content')
    <div x-data="templateKategoriCrud()" class="space-y-3 mt-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <button type="button" @click="openCreateModal()"
                class="inline-flex items-center rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                + Tambah Template
            </button>
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
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Parameter</th>
                            <th class="px-4 py-3">Urutan</th>
                            <th class="px-4 py-3">Kolom Sensor Default</th>
                            <th class="px-4 py-3">Satuan</th>
                            <th class="px-4 py-3">Group</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $index => $item)
                            @php
                                $editPayload = [
                                    'id' => $item->id,
                                    'id_katlogger' => $item->id_katlogger,
                                    'list_parameter_id' => $item->list_parameter_id,
                                    'urutan' => $item->urutan,
                                    'kolom_sensor_default' => $item->kolom_sensor_default,
                                    'satuan_override' => $item->satuan_override,
                                    'parameter_group_id' => $item->parameter_group_id,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ $item->kategori?->nama_kategori ?? '-' }}
                                </td>
                                <td class="px-4 py-3">{{ $item->listParameter?->nama_parameter ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->urutan }}</td>
                                <td class="px-4 py-3">
                                    {{ $item->kolom_sensor_default ?: $item->listParameter?->default_kolom_sensor ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $item->satuan_override ?: $item->listParameter?->default_satuan ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $item->parameterGroup?->nama_group ?: $item->listParameter?->parameterGroup?->nama_group ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click='openEditModal(@json($editPayload))'
                                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Edit
                                        </button>
                                        <form action="{{ route('template-kategori-parameter.destroy', $item) }}"
                                            method="POST" onsubmit="return confirm('Hapus template parameter ini?')">
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
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada template
                                    kategori parameter.</td>
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
                        <h3 class="text-xl font-bold text-gray-900">Tambah Template Kategori Parameter</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-slate-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('template-kategori-parameter.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="form_mode" value="create">

                        <div class="px-8 pt-4 pb-3 space-y-3">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kategori Logger</label>
                                    <select name="id_katlogger" required
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Pilih Kategori -</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id_katlogger }}" @selected((string) old('id_katlogger') === (string) $kategori->id_katlogger)>
                                                {{ $kategori->nama_kategori }}{{ $kategori->kepanjangan ? ' - ' . $kategori->kepanjangan : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'create')
                                        @error('id_katlogger')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">List Parameter</label>
                                    <select name="list_parameter_id" required
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Pilih Parameter -</option>
                                        @foreach ($listParameters as $lp)
                                            <option value="{{ $lp->id }}" @selected((string) old('list_parameter_id') === (string) $lp->id)>
                                                {{ $lp->nama_parameter }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'create')
                                        @error('list_parameter_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Urutan</label>
                                    <input type="number" min="0" step="1" name="urutan"
                                        value="{{ old('form_mode') === 'create' ? old('urutan', 0) : 0 }}"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'create')
                                        @error('urutan')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kolom Sensor Default
                                        (opsional)</label>
                                    <input type="text" name="kolom_sensor_default"
                                        value="{{ old('form_mode') === 'create' ? old('kolom_sensor_default') : '' }}"
                                        placeholder="contoh: sensor1"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'create')
                                        @error('kolom_sensor_default')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Satuan Override
                                        (opsional)</label>
                                    <input type="text" name="satuan_override"
                                        value="{{ old('form_mode') === 'create' ? old('satuan_override') : '' }}"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'create')
                                        @error('satuan_override')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Group Parameter
                                        (opsional)</label>
                                    <select name="parameter_group_id"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Ikuti default list parameter -</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}" @selected((string) old('parameter_group_id') === (string) $group->id)>
                                                {{ $group->nama_group }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'create')
                                        @error('parameter_group_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>
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
                        <h3 class="text-xl font-bold text-gray-900">Edit Template Kategori Parameter</h3>
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

                        <div class="px-8 pt-4 pb-3 space-y-3">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kategori Logger</label>
                                    <select name="id_katlogger" x-model="editData.id_katlogger" required
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Pilih Kategori -</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id_katlogger }}">
                                                {{ $kategori->nama_kategori }}{{ $kategori->kepanjangan ? ' - ' . $kategori->kepanjangan : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'edit')
                                        @error('id_katlogger')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">List Parameter</label>
                                    <select name="list_parameter_id" x-model="editData.list_parameter_id" required
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Pilih Parameter</option>
                                        @foreach ($listParameters as $lp)
                                            <option value="{{ $lp->id }}">{{ $lp->nama_parameter }}</option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'edit')
                                        @error('list_parameter_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Urutan</label>
                                    <input type="number" min="0" step="1" name="urutan"
                                        x-model="editData.urutan"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'edit')
                                        @error('urutan')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kolom Sensor Default
                                        (opsional)</label>
                                    <input type="text" name="kolom_sensor_default"
                                        x-model="editData.kolom_sensor_default" placeholder="contoh: sensor1"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'edit')
                                        @error('kolom_sensor_default')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Satuan Override
                                        (opsional)</label>
                                    <input type="text" name="satuan_override" x-model="editData.satuan_override"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'edit')
                                        @error('satuan_override')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Group Parameter
                                        (opsional)</label>
                                    <select name="parameter_group_id" x-model="editData.parameter_group_id"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">- Ikuti default list parameter -</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->nama_group }}</option>
                                        @endforeach
                                    </select>
                                    @if (old('form_mode') === 'edit')
                                        @error('parameter_group_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>
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
        function templateKategoriCrud() {
            const hasErrors = @json($errors->any());
            const oldFormMode = @json(old('form_mode'));

            return {
                baseUrl: @json(url('template-kategori-parameter')),
                showCreateModal: hasErrors && oldFormMode === 'create',
                showEditModal: hasErrors && oldFormMode === 'edit',
                editData: {
                    id: @json(old('item_id')),
                    id_katlogger: @json(old('form_mode') === 'edit' ? (string) old('id_katlogger', '') : ''),
                    list_parameter_id: @json(old('form_mode') === 'edit' ? (string) old('list_parameter_id', '') : ''),
                    urutan: @json(old('form_mode') === 'edit' ? old('urutan', 0) : 0),
                    kolom_sensor_default: @json(old('form_mode') === 'edit' ? old('kolom_sensor_default') : ''),
                    satuan_override: @json(old('form_mode') === 'edit' ? old('satuan_override') : ''),
                    parameter_group_id: @json(old('form_mode') === 'edit' ? (string) old('parameter_group_id', '') : ''),
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
                        id_katlogger: row.id_katlogger ? String(row.id_katlogger) : '',
                        list_parameter_id: row.list_parameter_id ? String(row.list_parameter_id) : '',
                        urutan: row.urutan ?? 0,
                        kolom_sensor_default: row.kolom_sensor_default ?? '',
                        satuan_override: row.satuan_override ?? '',
                        parameter_group_id: row.parameter_group_id ? String(row.parameter_group_id) : '',
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
