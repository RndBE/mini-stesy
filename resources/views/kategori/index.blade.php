@extends('layouts.app')

@section('content')
    <div x-data="kategoriCrud()" class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3 mt-2">

            <button type="button" @click="openCreateModal()"
                class="inline-flex items-center rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                + Tambah Kategori
            </button>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-900">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Kepanjangan</th>
                            <th class="px-4 py-3">Icon</th>
                            <th class="px-4 py-3">View</th>
                            <th class="px-4 py-3">Jumlah Logger</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kategoris as $index => $kategori)
                            @php
                                $editPayload = [
                                    'id' => $kategori->id_katlogger,
                                    'nama_kategori' => $kategori->nama_kategori,
                                    'kepanjangan' => $kategori->kepanjangan,
                                    'icon_app' => $kategori->icon_app,
                                    'view' => $kategori->view,
                                ];
                                $deletePayload = [
                                    'id' => $kategori->id_katlogger,
                                    'nama' => $kategori->nama_kategori,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $kategori->nama_kategori }}</td>
                                <td class="px-4 py-3">{{ $kategori->kepanjangan }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset('kategori/' . ltrim((string) $kategori->icon_app, '/')) }}"
                                            alt="{{ $kategori->nama_kategori }}"
                                            onerror="this.onerror=null;this.src='{{ asset('images/mini_stesy.png') }}';"
                                            class="h-30 w-30 rounded-md  object-contain bg-white">

                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $kategori->view }}</td>
                                <td class="px-4 py-3">{{ $kategori->logger_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click='openEditModal(@json($editPayload))'
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-950 hover:bg-slate-200 transition-colors"
                                            title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button" @click='openDeleteModal(@json($deletePayload))'
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
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada kategori
                                    logger.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-50" aria-labelledby="modal-title-create"
            role="dialog" aria-modal="true" @keydown.escape.window="closeCreateModal()">

            <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75" @click="closeCreateModal()">
            </div>

            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto"
                @click="closeCreateModal()">
                <div x-show="showCreateModal" x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="w-full max-w-3xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-8 py-2 border-b border-slate-200">
                        <h3 id="modal-title-create" class="text-xl font-bold text-gray-900">Tambah Kategori</h3>
                        <button type="button" @click="closeCreateModal()" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('kategori.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="form_mode" value="create">
                        <div class="px-8 pt-4 pb-3 space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Kategori (Kode)</label>
                                    <input type="text" name="nama_kategori"
                                        value="{{ old('form_mode') === 'create' ? old('nama_kategori') : '' }}"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @if (old('form_mode') === 'create')
                                        @error('nama_kategori')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kepanjangan</label>
                                    <input type="text" name="kepanjangan"
                                        value="{{ old('form_mode') === 'create' ? old('kepanjangan') : '' }}"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @if (old('form_mode') === 'create')
                                        @error('kepanjangan')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Icon App (Upload Gambar)</label>
                                    <input type="file" name="icon_app_file" accept="image/*"
                                        @change="onCreateIconFileChange($event)"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <p class="mt-1 text-xs text-slate-500">Pilih file gambar icon untuk kategori ini.</p>
                                    @if (old('form_mode') === 'create')
                                        @error('icon_app_file')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Preview Icon</label>
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                    <img :src="createIconPreviewUrl()" alt="Preview Icon"
                                        onerror="this.onerror=null;this.src='{{ asset('images/mini_stesy.png') }}';"
                                        class="h-12 w-12 object-contain">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">View</label>
                                <input type="number" min="0" step="1" name="view"
                                    value="{{ old('form_mode') === 'create' ? old('view', 1) : 1 }}"
                                    class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                @if (old('form_mode') === 'create')
                                    @error('view')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-8 py-4 border-t border-gray-100 bg-white">
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

        <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50" aria-labelledby="modal-title-edit"
            role="dialog" aria-modal="true" @keydown.escape.window="closeEditModal()">

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
                    class="w-full max-w-3xl bg-white rounded-lg shadow-xl overflow-hidden my-8" @click.stop>
                    <div class="flex items-center justify-between px-8 py-2 border-b border-slate-200">
                        <h3 id="modal-title-edit" class="text-xl font-bold text-gray-900">Edit Kategori</h3>
                        <button type="button" @click="closeEditModal()" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="POST" :action="updateAction()" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_mode" value="edit">
                        <input type="hidden" name="id_katlogger" x-model="editData.id">
                        <input type="hidden" name="current_icon_app" x-model="editData.icon_app">

                        <div class="px-8 pt-4 pb-3 space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Kategori (Kode)</label>
                                    <input type="text" name="nama_kategori" x-model="editData.nama_kategori"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @if (old('form_mode') === 'edit')
                                        @error('nama_kategori')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Kepanjangan</label>
                                    <input type="text" name="kepanjangan" x-model="editData.kepanjangan"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @if (old('form_mode') === 'edit')
                                        @error('kepanjangan')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Icon App (Upload Gambar)</label>
                                    <input type="file" name="icon_app_file" accept="image/*"
                                        @change="onEditIconFileChange($event)"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <p class="mt-1 text-xs text-slate-500">Pilih file gambar icon untuk kategori ini.</p>
                                    @if (old('form_mode') === 'edit')
                                        @error('icon_app_file')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Preview Icon</label>
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                    <img :src="editIconPreviewUrl()" alt="Preview Icon"
                                        onerror="this.onerror=null;this.src='{{ asset('images/mini_stesy.png') }}';"
                                        class="h-12 w-12 object-contain">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">View</label>
                                <input type="number" min="0" step="1" name="view" x-model="editData.view"
                                    class="w-full rounded-lg border border-gray-200 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                @if (old('form_mode') === 'edit')
                                    @error('view')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-8 py-4 border-t border-gray-100 bg-white">
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
                                <svg class="w-14 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <h3 class="text-xl text-center font-bold text-blue-900">Hapus Kategori</h3>
                        </div>
                    </div>

                    <div class="px-6 py-3">
                        <p class="text-sm text-center text-slate-600">
                            Anda yakin ingin menghapus kategori
                            <span class="font-semibold text-slate-900" x-text="deleteData.nama"></span>?
                        </p>
                        <p class="mt-1 text-sm text-center text-red-600 font-medium">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div class="flex items-center justify-center gap-3 px-6 py-3">
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
    </div>
@endsection

@push('scripts')
    <script>
        function kategoriCrud() {
            const hasErrors = @json($errors->any());
            const oldFormMode = @json(old('form_mode'));

            return {
                kategoriBaseUrl: @json(route('kategori.index')),
                kategoriIconBaseUrl: @json(asset('kategori')),
                showCreateModal: hasErrors && oldFormMode === 'create',
                showEditModal: hasErrors && oldFormMode === 'edit',
                showDeleteModal: false,
                createData: {
                    iconPreviewObjectUrl: '',
                },
                editData: {
                    id: @json(old('id_katlogger')),
                    nama_kategori: @json(old('form_mode') === 'edit' ? old('nama_kategori') : ''),
                    kepanjangan: @json(old('form_mode') === 'edit' ? old('kepanjangan') : ''),
                    icon_app: @json(old('form_mode') === 'edit' ? old('current_icon_app') : ''),
                    iconPreviewObjectUrl: '',
                    view: @json(old('form_mode') === 'edit' ? old('view', 1) : 1),
                },
                deleteData: {
                    id: null,
                    nama: '',
                },
                openCreateModal() {
                    this.showCreateModal = true;
                },
                closeCreateModal() {
                    this.showCreateModal = false;
                },
                createIconPreviewUrl() {
                    if (this.createData.iconPreviewObjectUrl) {
                        return this.createData.iconPreviewObjectUrl;
                    }

                    return '{{ asset('images/mini_stesy.png') }}';
                },
                onCreateIconFileChange(event) {
                    const [file] = event?.target?.files ?? [];
                    if (!file) {
                        this.createData.iconPreviewObjectUrl = '';
                        return;
                    }

                    this.createData.iconPreviewObjectUrl = URL.createObjectURL(file);
                },
                iconPreviewUrl(iconName) {
                    const value = String(iconName ?? '').trim().replace(/^\/+/, '');
                    if (!value) {
                        return '{{ asset('images/mini_stesy.png') }}';
                    }

                    return `${this.kategoriIconBaseUrl}/${value}`;
                },
                editIconPreviewUrl() {
                    if (this.editData.iconPreviewObjectUrl) {
                        return this.editData.iconPreviewObjectUrl;
                    }

                    return this.iconPreviewUrl(this.editData.icon_app);
                },
                onEditIconFileChange(event) {
                    const [file] = event?.target?.files ?? [];
                    if (!file) {
                        this.editData.iconPreviewObjectUrl = '';
                        return;
                    }

                    this.editData.iconPreviewObjectUrl = URL.createObjectURL(file);
                },
                openEditModal(row) {
                    this.editData = {
                        id: row.id,
                        nama_kategori: row.nama_kategori ?? '',
                        kepanjangan: row.kepanjangan ?? '',
                        icon_app: row.icon_app ?? '',
                        iconPreviewObjectUrl: '',
                        view: row.view ?? 1,
                    };
                    this.showEditModal = true;
                },
                closeEditModal() {
                    this.showEditModal = false;
                },
                updateAction() {
                    return `${this.kategoriBaseUrl}/${this.editData.id}`;
                },
                openDeleteModal(row) {
                    this.deleteData = {
                        id: row.id,
                        nama: row.nama ?? '',
                    };
                    this.showDeleteModal = true;
                },
                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteData = { id: null, nama: '' };
                },
                confirmDelete() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `${this.kategoriBaseUrl}/${this.deleteData.id}`;

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
                deleteAction() {
                    return `${this.kategoriBaseUrl}/${this.deleteData.id}`;
                },
            };
        }
    </script>
@endpush
