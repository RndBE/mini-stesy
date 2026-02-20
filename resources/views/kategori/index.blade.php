@extends('layouts.app')

@section('content')
    <div x-data="kategoriCrud()" class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3 mt-2">

            <button type="button" @click="openCreateModal()"
                class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
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
                                            class="h-8 w-8 rounded-md border border-slate-200 object-contain bg-white">
                                        <span class="text-xs text-slate-500">{{ $kategori->icon_app }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $kategori->view }}</td>
                                <td class="px-4 py-3">{{ $kategori->logger_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click='openEditModal(@json($editPayload))'
                                            class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Edit
                                        </button>
                                        <button type="button" @click='openDeleteModal(@json($deletePayload))'
                                            class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Hapus
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

        <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeCreateModal()">
            <div class="fixed inset-0 bg-black/50" @click="closeCreateModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl" @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-900">Tambah Kategori</h3>
                        <button type="button" @click="closeCreateModal()"
                            class="rounded-md p-1 text-slate-500 hover:bg-slate-100">X</button>
                    </div>
                    <form action="{{ route('kategori.store') }}" method="POST" class="space-y-5 p-6">
                        @csrf
                        <input type="hidden" name="form_mode" value="create">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Nama Kategori (Kode)</label>
                                <input type="text" name="nama_kategori"
                                    value="{{ old('form_mode') === 'create' ? old('nama_kategori') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('nama_kategori')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Kepanjangan</label>
                                <input type="text" name="kepanjangan"
                                    value="{{ old('form_mode') === 'create' ? old('kepanjangan') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('kepanjangan')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Icon App (Nama File)</label>
                                <input type="text" name="icon_app" x-model="createData.icon_app"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                <p class="mt-1 text-xs text-slate-500">Isi nama file gambar di folder
                                    <code>public/kategori</code>, contoh: <code>awlr.png</code>
                                </p>
                                @if (old('form_mode') === 'create')
                                    @error('icon_app')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Preview Icon</label>
                            <div class="mt-1 flex h-16 w-16 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                <img :src="iconPreviewUrl(createData.icon_app)" alt="Preview Icon"
                                    onerror="this.onerror=null;this.src='{{ asset('images/mini_stesy.png') }}';"
                                    class="h-12 w-12 object-contain">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">View</label>
                            <input type="number" min="0" step="1" name="view"
                                value="{{ old('form_mode') === 'create' ? old('view', 1) : 1 }}"
                                class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                            @if (old('form_mode') === 'create')
                                @error('view')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="closeCreateModal()"
                                class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeEditModal()">
            <div class="fixed inset-0 bg-black/50" @click="closeEditModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl" @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-900">Edit Kategori</h3>
                        <button type="button" @click="closeEditModal()"
                            class="rounded-md p-1 text-slate-500 hover:bg-slate-100">X</button>
                    </div>
                    <form method="POST" :action="updateAction()" enctype="multipart/form-data" class="space-y-5 p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_mode" value="edit">
                        <input type="hidden" name="id_katlogger" x-model="editData.id">
                        <input type="hidden" name="current_icon_app" x-model="editData.icon_app">

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Nama Kategori (Kode)</label>
                                <input type="text" name="nama_kategori" x-model="editData.nama_kategori"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'edit')
                                    @error('nama_kategori')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Kepanjangan</label>
                                <input type="text" name="kepanjangan" x-model="editData.kepanjangan"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'edit')
                                    @error('kepanjangan')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Icon App (Upload Gambar)</label>
                                <input type="file" name="icon_app_file" accept="image/*"
                                    @change="onEditIconFileChange($event)"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                <p class="mt-1 text-xs text-slate-500">Pilih file gambar icon untuk kategori ini.</p>
                                @if (old('form_mode') === 'edit')
                                    @error('icon_app_file')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Preview Icon</label>
                            <div class="mt-1 flex h-16 w-16 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                <img :src="editIconPreviewUrl()" alt="Preview Icon"
                                    onerror="this.onerror=null;this.src='{{ asset('images/mini_stesy.png') }}';"
                                    class="h-12 w-12 object-contain">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">View</label>
                            <input type="number" min="0" step="1" name="view" x-model="editData.view"
                                class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                            @if (old('form_mode') === 'edit')
                                @error('view')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="closeEditModal()"
                                class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
            @keydown.escape.window="closeDeleteModal()">
            <div class="fixed inset-0 bg-black/50" @click="closeDeleteModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-md rounded-xl bg-white shadow-xl" @click.stop>
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-900">Hapus Kategori</h3>
                    </div>
                    <div class="space-y-3 px-6 py-5 text-sm text-slate-700">
                        <p>Anda yakin ingin menghapus kategori <span class="font-semibold"
                                x-text="deleteData.nama"></span>?</p>
                        <p class="text-rose-600">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <form method="POST" :action="deleteAction()" class="flex justify-end gap-3 px-6 pb-5">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="closeDeleteModal()"
                            class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                            Hapus
                        </button>
                    </form>
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
                    icon_app: @json(old('form_mode') === 'create' ? old('icon_app') : ''),
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
                },
                deleteAction() {
                    return `${this.kategoriBaseUrl}/${this.deleteData.id}`;
                },
            };
        }
    </script>
@endpush
