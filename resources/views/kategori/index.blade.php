@extends('layouts.app')

@section('content')
    <div x-data="kategoriCrud()" class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Kategori Logger</h1>
                <p class="text-sm text-slate-500">Kelola kategori perangkat logger.</p>
            </div>
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

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead class="bg-neutral-200 text-xs font-semibold uppercase text-neutral-900">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Kepanjangan</th>
                            <th class="px-4 py-3">Controller</th>
                            <th class="px-4 py-3">Tabel</th>
                            <th class="px-4 py-3">Temp Data</th>
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
                                    'controller' => $kategori->controller,
                                    'tabel' => $kategori->tabel,
                                    'temp_data' => $kategori->temp_data,
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
                                <td class="px-4 py-3">{{ $kategori->controller }}</td>
                                <td class="px-4 py-3">{{ $kategori->tabel }}</td>
                                <td class="px-4 py-3">{{ $kategori->temp_data }}</td>
                                <td class="px-4 py-3">{{ $kategori->icon_app }}</td>
                                <td class="px-4 py-3">{{ $kategori->view }}</td>
                                <td class="px-4 py-3">{{ $kategori->logger_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                            @click='openEditModal(@json($editPayload))'
                                            class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Edit
                                        </button>
                                        <button type="button"
                                            @click='openDeleteModal(@json($deletePayload))'
                                            class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada kategori
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
                                <input type="text" name="nama_kategori" value="{{ old('form_mode') === 'create' ? old('nama_kategori') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('nama_kategori')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Kepanjangan</label>
                                <input type="text" name="kepanjangan" value="{{ old('form_mode') === 'create' ? old('kepanjangan') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('kepanjangan')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Controller</label>
                                <input type="text" name="controller" value="{{ old('form_mode') === 'create' ? old('controller') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('controller')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tabel</label>
                                <input type="text" name="tabel" value="{{ old('form_mode') === 'create' ? old('tabel') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('tabel')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Temp Data</label>
                                <input type="text" name="temp_data" value="{{ old('form_mode') === 'create' ? old('temp_data') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('temp_data')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Icon App</label>
                                <input type="text" name="icon_app" value="{{ old('form_mode') === 'create' ? old('icon_app') : '' }}"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'create')
                                    @error('icon_app')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
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
                    <form method="POST" :action="updateAction()" class="space-y-5 p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_mode" value="edit">
                        <input type="hidden" name="id_katlogger" x-model="editData.id">

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
                                <label class="block text-sm font-medium text-slate-700">Controller</label>
                                <input type="text" name="controller" x-model="editData.controller"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'edit')
                                    @error('controller')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tabel</label>
                                <input type="text" name="tabel" x-model="editData.tabel"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'edit')
                                    @error('tabel')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Temp Data</label>
                                <input type="text" name="temp_data" x-model="editData.temp_data"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'edit')
                                    @error('temp_data')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Icon App</label>
                                <input type="text" name="icon_app" x-model="editData.icon_app"
                                    class="mt-1 w-full rounded-md border-slate-300 p-2 text-sm shadow-sm" required>
                                @if (old('form_mode') === 'edit')
                                    @error('icon_app')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
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
                        <p>Anda yakin ingin menghapus kategori <span class="font-semibold" x-text="deleteData.nama"></span>?</p>
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
                kategoriBaseUrl: @json(url('kategori')),
                showCreateModal: hasErrors && oldFormMode === 'create',
                showEditModal: hasErrors && oldFormMode === 'edit',
                showDeleteModal: false,
                editData: {
                    id: @json(old('id_katlogger')),
                    nama_kategori: @json(old('form_mode') === 'edit' ? old('nama_kategori') : ''),
                    kepanjangan: @json(old('form_mode') === 'edit' ? old('kepanjangan') : ''),
                    controller: @json(old('form_mode') === 'edit' ? old('controller') : ''),
                    tabel: @json(old('form_mode') === 'edit' ? old('tabel') : ''),
                    temp_data: @json(old('form_mode') === 'edit' ? old('temp_data') : ''),
                    icon_app: @json(old('form_mode') === 'edit' ? old('icon_app') : ''),
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
                openEditModal(row) {
                    this.editData = {
                        id: row.id,
                        nama_kategori: row.nama_kategori ?? '',
                        kepanjangan: row.kepanjangan ?? '',
                        controller: row.controller ?? '',
                        tabel: row.tabel ?? '',
                        temp_data: row.temp_data ?? '',
                        icon_app: row.icon_app ?? '',
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
