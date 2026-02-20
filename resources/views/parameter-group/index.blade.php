@extends('layouts.app')

@section('content')
    <div x-data="parameterGroupCrud()" class="space-y-3">
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('list-parameter.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                Kembali ke List Parameter
            </a>
            <button type="button" @click="openCreateModal()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                + Tambah Group Parameter
            </button>
        </div>

        @if (session('success'))
            <div
                class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="px-4 py-2 bg-rose-100 text-rose-700 rounded-lg text-sm font-semibold shadow-sm ring-1 ring-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 whitespace-nowrap">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-900">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama Group</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Urutan</th>
                            <th class="px-4 py-3">Digunakan</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $index => $item)
                            @php
                                $editPayload = [
                                    'id' => $item->id,
                                    'kode_group' => $item->kode_group,
                                    'nama_group' => $item->nama_group,
                                    'deskripsi' => $item->deskripsi,
                                    'sort_order' => $item->sort_order,
                                ];
                                $usageCount =
                                    (int) $item->list_parameters_count +
                                    (int) $item->template_parameters_count +
                                    (int) $item->sensor_parameters_count;
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->kode_group }}</td>
                                <td class="px-4 py-3">{{ $item->nama_group }}</td>
                                <td class="px-4 py-3 whitespace-normal max-w-lg">{{ $item->deskripsi ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $item->sort_order ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                        {{ $usageCount }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click='openEditModal(@json($editPayload))'
                                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Edit
                                        </button>
                                        <button type="button"
                                            @click='openDeleteModal(@json(['id' => $item->id, 'nama_group' => $item->nama_group]))'
                                            class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada group
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
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75"
                @click="closeCreateModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeCreateModal()">
                <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-4xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-8 py-3 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-gray-900">Tambah Group Parameter</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('parameter-group.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="form_mode" value="create">

                        <div class="px-8 pt-4 pb-3 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kode Group <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="kode_group"
                                        value="{{ old('form_mode') === 'create' ? old('kode_group') : '' }}"
                                        placeholder="contoh: SUMUR"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                    @if (old('form_mode') === 'create')
                                        @error('kode_group')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Group <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="nama_group"
                                        value="{{ old('form_mode') === 'create' ? old('nama_group') : '' }}"
                                        placeholder="contoh: Parameter Sumur"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                    @if (old('form_mode') === 'create')
                                        @error('nama_group')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Urutan</label>
                                    <input type="number" name="sort_order"
                                        value="{{ old('form_mode') === 'create' ? old('sort_order') : '' }}"
                                        min="0"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'create')
                                        @error('sort_order')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Deskripsi</label>
                                <textarea name="deskripsi" rows="4" placeholder="Opsional"
                                    class="w-full rounded-lg border border-gray-200 p-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('form_mode') === 'create' ? old('deskripsi') : '' }}</textarea>
                                @if (old('form_mode') === 'create')
                                    @error('deskripsi')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
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
            <div x-show="showEditModal" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75"
                @click="closeEditModal()"></div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" @click="closeEditModal()">
                <div x-show="showEditModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-4xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-8 py-3 border-b border-slate-200">
                        <h3 class="text-xl font-bold text-gray-900">Edit Group Parameter</h3>
                        <button type="button" @click="closeEditModal()" class="p-2 rounded-lg hover:bg-gray-100">
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kode Group <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="kode_group" x-model="editData.kode_group"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                    @if (old('form_mode') === 'edit')
                                        @error('kode_group')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Group <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="nama_group" x-model="editData.nama_group"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                    @if (old('form_mode') === 'edit')
                                        @error('nama_group')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Urutan</label>
                                    <input type="number" name="sort_order" x-model="editData.sort_order" min="0"
                                        class="w-full rounded-lg border border-gray-200 px-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if (old('form_mode') === 'edit')
                                        @error('sort_order')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Deskripsi</label>
                                <textarea name="deskripsi" rows="4" x-model="editData.deskripsi"
                                    class="w-full rounded-lg border border-gray-200 p-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                @if (old('form_mode') === 'edit')
                                    @error('deskripsi')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
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

        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeDeleteModal()">
            <div x-show="showDeleteModal" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75"
                @click="closeDeleteModal()"></div>

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
                            <h3 class="text-xl text-center font-bold text-blue-900">Hapus Group Parameter</h3>
                        </div>
                    </div>

                    <div class="px-6 py-3">
                        <p class="text-sm text-center text-slate-600">
                            Anda yakin ingin menghapus group
                            <span class="font-semibold text-slate-900" x-text="deleteData.nama_group"></span>?
                        </p>
                        <p class="mt-1 text-sm text-center text-red-600 font-medium">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div class="flex items-center justify-center gap-3 px-6 py-3">
                        <button type="button" @click="closeDeleteModal()"
                            class="px-10 py-2 rounded-lg border border-blue-300 text-blue-700 font-semibold hover:bg-blue-100 transition-colors">
                            Batal
                        </button>
                        <button type="button" @click="confirmDelete()"
                            class="px-10 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function parameterGroupCrud() {
            const hasErrors = @json($errors->any());
            const oldFormMode = @json(old('form_mode'));

            return {
                baseUrl: @json(url('parameter-group')),
                showCreateModal: hasErrors && oldFormMode === 'create',
                showEditModal: hasErrors && oldFormMode === 'edit',
                showDeleteModal: false,
                editData: {
                    id: @json(old('item_id')),
                    kode_group: @json(old('form_mode') === 'edit' ? old('kode_group') : ''),
                    nama_group: @json(old('form_mode') === 'edit' ? old('nama_group') : ''),
                    deskripsi: @json(old('form_mode') === 'edit' ? old('deskripsi') : ''),
                    sort_order: @json(old('form_mode') === 'edit' ? old('sort_order') : ''),
                },
                deleteData: {
                    id: null,
                    nama_group: '',
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
                        kode_group: row.kode_group ?? '',
                        nama_group: row.nama_group ?? '',
                        deskripsi: row.deskripsi ?? '',
                        sort_order: row.sort_order ?? '',
                    };
                    this.showEditModal = true;
                },
                closeEditModal() {
                    this.showEditModal = false;
                },
                openDeleteModal(row) {
                    this.deleteData = {
                        id: row.id,
                        nama_group: row.nama_group ?? '',
                    };
                    this.showDeleteModal = true;
                },
                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteData = {
                        id: null,
                        nama_group: '',
                    };
                },
                confirmDelete() {
                    if (!this.deleteData.id) {
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `${this.baseUrl}/${this.deleteData.id}`;

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
                },
                updateAction() {
                    return `${this.baseUrl}/${this.editData.id}`;
                },
            };
        }
    </script>
@endpush
