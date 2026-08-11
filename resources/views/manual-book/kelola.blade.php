@extends('layouts.app')

@php
    use App\Models\ManualBook;

    // Data tiap dokumen untuk mengisi modal saat tombol Ubah ditekan.
    $booksData = $books->mapWithKeys(fn($book) => [$book->id => [
        'id' => $book->id,
        'judul' => $book->judul,
        'deskripsi' => (string) $book->deskripsi,
        'visibility' => $book->visibility,
        'is_active' => (bool) $book->is_active,
        'targets' => $book->targets->pluck('target_id')->map(fn($value) => (string) $value)->values()->all(),
        'fileName' => $book->file_name,
        'fileMeta' => strtoupper($book->fileExtension()) . ' · ' . $book->fileSizeLabel(),
    ]])->all();

    // Terisi hanya jika request sebelumnya gagal validasi, dipakai untuk
    // membuka ulang modal beserta isian terakhir user.
    $oldInput = old('form_mode') ? [
        'mode' => old('form_mode') === 'edit' ? 'edit' : 'create',
        'id' => old('manual_book_id'),
        'judul' => (string) old('judul', ''),
        'deskripsi' => (string) old('deskripsi', ''),
        'visibility' => old('visibility', ManualBook::VISIBILITY_ALL),
        'is_active' => (bool) old('is_active'),
        'targets' => collect(old('targets', []))->map(fn($value) => (string) $value)->values()->all(),
    ] : null;
@endphp

@section('content')
<style>[x-cloak]{display:none!important}</style>
<div class="space-y-6" x-data="{
        books: @js($booksData),
        oldInput: @js($oldInput),
        baseUrl: @js(route('manual-book.kelola')),
        open: false,
        mode: 'create',
        form: {},
        fileDipilih: null,
        maksKb: @js(ManualBook::maxUploadKb()),
        maksLabel: @js(ManualBook::maxUploadLabel()),
        galatFile: null,
        hapusId: null,
        hapusJudul: '',

        init() {
            this.form = this.formKosong();

            if (!this.oldInput) return;

            // Validasi gagal: buka lagi modal dengan isian terakhir.
            this.mode = this.oldInput.mode;
            const dasar = this.mode === 'edit' ? (this.books[this.oldInput.id] ?? {}) : {};
            this.form = { ...this.formKosong(), ...dasar, ...this.oldInput };
            this.open = true;
        },

        formKosong() {
            return {
                id: null,
                judul: '',
                deskripsi: '',
                visibility: @js(ManualBook::VISIBILITY_ALL),
                is_active: true,
                targets: [],
                fileName: null,
                fileMeta: null,
            };
        },

        bukaTambah() {
            this.mode = 'create';
            this.form = this.formKosong();
            this.fileDipilih = null;
            this.galatFile = null;
            this.open = true;
        },

        bukaUbah(id) {
            this.mode = 'edit';
            this.form = { ...this.formKosong(), ...(this.books[id] ?? {}) };
            this.fileDipilih = null;
            this.galatFile = null;
            this.open = true;
        },

        /**
         * Tolak file kelebihan ukuran sebelum terkirim. Perlu di sisi klien
         * karena kalau melewati post_max_size PHP, ValidatePostSize melempar
         * error sebelum session dimulai sehingga pesan validasi tidak bisa
         * ditampilkan di form.
         */
        pilihFile(event) {
            const file = event.target.files[0] ?? null;

            if (file && file.size > this.maksKb * 1024) {
                this.galatFile = 'Ukuran file maksimal ' + this.maksLabel
                    + '. File yang dipilih ' + this.labelUkuran(file.size) + '.';
                event.target.value = '';
                this.fileDipilih = null;
                return;
            }

            this.galatFile = null;
            this.fileDipilih = file;
        },

        /** Label ukuran file yang baru dipilih di dropzone. */
        labelUkuran(bytes) {
            if (!bytes) return '';

            return bytes >= 1048576
                ? (bytes / 1048576).toFixed(2) + ' MB'
                : Math.round(bytes / 1024) + ' KB';
        },

        get actionUrl() {
            return this.mode === 'edit' ? this.baseUrl + '/' + this.form.id : this.baseUrl;
        },
    }">

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Tabel dokumen. Judul halaman sudah ditampilkan topbar, jadi head card
         ini sekaligus jadi tempat aksi utamanya. --}}
    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
            <div class="min-w-0">
                <h3 class="text-lg font-semibold text-slate-800">Daftar Manual Book</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $books->count() }} dokumen terdaftar.</p>
            </div>

            {{-- Warna & bentuk mengikuti tombol utama halaman lain:
                 tambah = bg-blue-900, sekunder = border slate, rounded-lg. --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('manual-book.index') }}"
                    class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>

                <button type="button" @click="bukaTambah()"
                    class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-blue-900 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-800">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">No</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Judul</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">File</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Tampil Ke</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Diunggah</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($books as $book)
                        <tr class="transition hover:bg-slate-50">
                            {{-- Nomor urut baris, bukan nilai dari database --}}
                            <td class="px-4 py-3 text-slate-500">{{ $loop->iteration }}</td>

                            <td class="max-w-[240px] px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $book->judul }}</p>
                                @if ($book->deskripsi)
                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $book->deskripsi }}</p>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold uppercase text-slate-700">{{ $book->fileExtension() }}</span>
                                <span class="ml-1 text-xs text-slate-500">{{ $book->fileSizeLabel() }}</span>
                            </td>

                            <td class="max-w-[220px] px-4 py-3">
                                <p class="font-medium text-slate-700">{{ $book->visibilityLabel() }}</p>
                                @if ($book->targets->isNotEmpty())
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $book->targets
                                            ->map(fn($target) => $targetLabels[$target->target_type . ':' . $target->target_id] ?? $target->target_id)
                                            ->join(', ') }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if ($book->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-semibold text-slate-600">Nonaktif</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">
                                <p>{{ $book->uploader?->nama ?? 'Sistem' }}</p>
                                <p class="text-xs text-slate-400">{{ $book->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') ?? '-' }}</p>
                            </td>

                            {{-- Chip ikon mengikuti pola tabel lain (instansi, device):
                                 w-9 h-9 rounded-lg, emerald=lihat, slate=ubah, red=hapus. --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('manual-book.preview', $book->id) }}" target="_blank" rel="noopener"
                                        title="Lihat dokumen"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-slate-950 transition-colors hover:bg-emerald-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <button type="button" @click="bukaUbah({{ $book->id }})" title="Ubah"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-950 transition-colors hover:bg-slate-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button type="button" title="Hapus"
                                        @click="hapusId = {{ $book->id }}; hapusJudul = @js($book->judul)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-slate-950 transition-colors hover:bg-red-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                Belum ada manual book yang diunggah.
                                <button type="button" @click="bukaTambah()" class="font-semibold text-blue-600 hover:underline">Tambah sekarang</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ Modal Tambah / Ubah ============ --}}
    {{-- role="dialog" wajib: layout menaikkan [role="dialog"].fixed ke z-index 500
         supaya modal tidak tertutup panel chatbot yang ada di z-[260]. --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true"
        @keydown.escape.window="open = false">
        {{-- Overlay & transisi mengikuti pola modal instansi/users/device --}}
        <div x-show="open" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500/75" @click="open = false"></div>

        {{-- Wrapper juga menutup modal: dia menumpuk di atas overlay, jadi klik
             luar panel mengenai wrapper ini, bukan overlay. --}}
        <div class="fixed inset-0 flex items-start justify-center overflow-y-auto p-4 sm:items-center"
            @click="open = false">
            <div x-show="open" x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="w-full max-w-4xl rounded-2xl bg-white shadow-xl" @click.stop>
                {{-- items-center: judul tinggal satu baris, tanpa subjudul --}}
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4">
                    <h3 class="min-w-0 truncate text-lg font-semibold text-slate-800"
                        x-text="mode === 'edit' ? 'Ubah Manual Book' : 'Tambah Manual Book'"></h3>

                    <button type="button" @click="open = false"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="actionUrl" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Mode ubah dikirim sebagai PUT lewat method spoofing Laravel. --}}
                    <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
                    <input type="hidden" name="form_mode" :value="mode">
                    <input type="hidden" name="manual_book_id" :value="form.id ?? ''">

                    <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
                        @if ($errors->any())
                            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4">
                                <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-xs text-red-600">Catatan: file yang tadi dipilih perlu dipilih ulang.</p>
                            </div>
                        @endif

                        @include('manual-book.partials.form-fields')
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                        <button type="button" @click="open = false"
                            class="h-10 rounded-lg border border-slate-200 px-6 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">Batal</button>
                        {{-- Submit modal di halaman lain memakai bg-indigo-700 --}}
                        <button type="submit"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-indigo-700 px-6 text-sm font-semibold text-white transition-colors hover:bg-indigo-800">
                            <span x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Manual Book'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============ Modal Konfirmasi Hapus ============ --}}
    <div x-show="hapusId !== null" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true"
        @keydown.escape.window="hapusId = null">
        <div x-show="hapusId !== null" x-transition:enter="ease-in-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500/75" @click="hapusId = null"></div>

        <div class="fixed inset-0 flex items-center justify-center p-4" @click="hapusId = null">
            <div x-show="hapusId !== null" x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.stop>
                <h3 class="text-lg font-semibold text-slate-800">Hapus Manual Book</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Dokumen <span class="font-semibold text-slate-700" x-text="hapusJudul"></span> beserta filenya akan dihapus permanen. Lanjutkan?
                </p>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="hapusId = null"
                        class="rounded-lg border border-slate-200 px-6 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">Batal</button>

                    <form method="POST" :action="baseUrl + '/' + hapusId">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="rounded-lg bg-red-600 px-6 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
