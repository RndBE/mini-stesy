<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\ManualBook;
use App\Models\ManualBookTarget;
use App\Models\Role;
use App\Models\t_User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManualBookController extends Controller
{
    /** Folder di disk `local` (storage/app/private), sengaja di luar public. */
    private const STORAGE_DIR = 'manual-book';

    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

    /**
     * Content-Type saat file disajikan dipaksa dari daftar ini, bukan dari MIME
     * kiriman pengunggah, supaya file tidak pernah tersaji sebagai HTML/script.
     */
    private const MIME_MAP = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /** Halaman baca untuk semua user yang login. */
    public function index()
    {
        $user = auth()->user();

        $books = ManualBook::query()
            ->visibleFor($user)
            ->where('is_active', true)
            ->orderBy('judul')
            ->get();

        return view('manual-book.index', [
            'title' => 'Manual Book',
            'books' => $books,
            'canManage' => (bool) $user?->hasPermission('manage_manual_book'),
        ]);
    }

    /** Buka file di browser. Hanya PDF yang inline, sisanya dipaksa jadi unduhan. */
    public function preview(int $manualBook)
    {
        $book = $this->findAccessible($manualBook);

        return $this->streamFile($book, $book->isPdf() ? 'inline' : 'attachment');
    }

    public function download(int $manualBook)
    {
        return $this->streamFile($this->findAccessible($manualBook), 'attachment');
    }

    /** Halaman kelola — daftar semua dokumen + form tambah. */
    public function kelola()
    {
        $options = $this->targetOptions();

        return view('manual-book.kelola', array_merge($options, [
            'title' => 'Kelola Manual Book',
            'books' => ManualBook::with(['uploader', 'targets'])
                ->orderBy('judul')
                ->get(),
            'targetLabels' => $this->targetLabelMap($options),
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validateBook($request, true);
        $targets = $this->resolveTargets($data['visibility'], $request->input('targets', []));
        $stored = $this->storeFile($request->file('file'));

        DB::transaction(function () use ($request, $data, $targets, $stored) {
            $book = ManualBook::create([
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'file_path' => $stored['path'],
                'file_name' => $stored['name'],
                'file_mime' => $stored['mime'],
                'file_size' => $stored['size'],
                'visibility' => $data['visibility'],
                'is_active' => $request->boolean('is_active'),
                'uploaded_by' => auth()->id(),
            ]);

            $this->syncTargets($book, $data['visibility'], $targets);
        });

        return redirect()->route('manual-book.kelola')
            ->with('success', 'Manual book berhasil ditambahkan.');
    }

    public function update(Request $request, int $manualBook)
    {
        $book = ManualBook::findOrFail($manualBook);

        $data = $this->validateBook($request, false);
        $targets = $this->resolveTargets($data['visibility'], $request->input('targets', []));
        $stored = $request->hasFile('file') ? $this->storeFile($request->file('file')) : null;
        $oldPath = $book->file_path;

        DB::transaction(function () use ($request, $book, $data, $targets, $stored) {
            $book->fill([
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'visibility' => $data['visibility'],
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($stored) {
                $book->fill([
                    'file_path' => $stored['path'],
                    'file_name' => $stored['name'],
                    'file_mime' => $stored['mime'],
                    'file_size' => $stored['size'],
                ]);
            }

            $book->save();

            $this->syncTargets($book, $data['visibility'], $targets);
        });

        // File lama baru dibuang setelah data baru benar-benar tersimpan.
        if ($stored && $oldPath && $oldPath !== $stored['path']) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('manual-book.kelola')
            ->with('success', 'Manual book berhasil diperbarui.');
    }

    public function destroy(int $manualBook)
    {
        $book = ManualBook::findOrFail($manualBook);
        $path = $book->file_path;

        // Baris manual_book_targets ikut terhapus lewat cascade foreign key.
        $book->delete();

        if ($path) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('manual-book.kelola')
            ->with('success', 'Manual book berhasil dihapus.');
    }

    /**
     * Ambil dokumen sekaligus jadi pemeriksaan hak akses: user biasa hanya bisa
     * membuka dokumen aktif yang ditargetkan ke dirinya.
     */
    private function findAccessible(int $id): ManualBook
    {
        $user = auth()->user();

        if ($user && $user->hasPermission('manage_manual_book')) {
            return ManualBook::findOrFail($id);
        }

        return ManualBook::query()
            ->visibleFor($user)
            ->where('is_active', true)
            ->findOrFail($id);
    }

    private function streamFile(ManualBook $book, string $disposition)
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($book->file_path)) {
            abort(404, 'File manual book tidak ditemukan.');
        }

        return $disk->response($book->file_path, $book->file_name, [
            'Content-Type' => self::MIME_MAP[$book->fileExtension()] ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ], $disposition);
    }

    private function validateBook(Request $request, bool $fileRequired): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file' => [
                $fileRequired ? 'required' : 'nullable',
                'file',
                'max:' . ManualBook::maxUploadKb(),
                'extensions:' . implode(',', self::ALLOWED_EXTENSIONS),
            ],
            'visibility' => ['required', Rule::in(ManualBook::VISIBILITIES)],
            'targets' => ['array'],
            'targets.*' => ['string', 'max:100'],
        ], [
            'file.required' => 'File manual book wajib diunggah.',
            'file.max' => 'Ukuran file maksimal ' . ManualBook::maxUploadLabel() . '.',
            'file.extensions' => 'Format file harus salah satu dari: ' . implode(', ', self::ALLOWED_EXTENSIONS) . '.',
        ]);
    }

    /**
     * Saring nilai target agar hanya menyimpan user/role/instansi yang benar-benar
     * ada, dan pastikan mode selain "semua user" punya minimal satu tujuan.
     *
     * @return list<string>
     */
    private function resolveTargets(string $visibility, array $raw): array
    {
        if ($visibility === ManualBook::VISIBILITY_ALL) {
            return [];
        }

        $values = array_values(array_unique(array_filter(
            array_map(fn($value) => trim((string) $value), $raw),
            fn($value) => $value !== ''
        )));

        $valid = match ($visibility) {
            ManualBook::VISIBILITY_SELECTED => t_User::whereIn('id_user', $values)->pluck('id_user'),
            ManualBook::VISIBILITY_ROLE => Role::whereIn('role_name', $values)->pluck('role_name'),
            default => Instansi::whereIn('id', $values)->pluck('id'),
        };

        if ($valid->isEmpty()) {
            throw ValidationException::withMessages([
                'targets' => 'Pilih minimal satu ' . match ($visibility) {
                    ManualBook::VISIBILITY_SELECTED => 'user',
                    ManualBook::VISIBILITY_ROLE => 'role',
                    default => 'instansi',
                } . ' tujuan.',
            ]);
        }

        return $valid->map(fn($value) => (string) $value)->all();
    }

    private function syncTargets(ManualBook $book, string $visibility, array $targets): void
    {
        $book->targets()->delete();

        if ($visibility === ManualBook::VISIBILITY_ALL) {
            return;
        }

        $type = match ($visibility) {
            ManualBook::VISIBILITY_SELECTED => ManualBookTarget::TYPE_USER,
            ManualBook::VISIBILITY_ROLE => ManualBookTarget::TYPE_ROLE,
            default => ManualBookTarget::TYPE_INSTANSI,
        };

        $now = now();

        $book->targets()->insert(array_map(fn($value) => [
            'manual_book_id' => $book->id,
            'target_type' => $type,
            'target_id' => $value,
            'created_at' => $now,
            'updated_at' => $now,
        ], $targets));
    }

    /**
     * @return array{path: string, name: string, mime: string, size: int}
     */
    private function storeFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // Metadata dibaca sebelum file dipindahkan dari lokasi sementara.
        $meta = [
            'name' => $this->safeFileName($file->getClientOriginalName(), $extension),
            'mime' => (string) $file->getClientMimeType(),
            'size' => (int) $file->getSize(),
        ];

        $storedName = 'manual_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $meta['path'] = $file->storeAs(self::STORAGE_DIR, $storedName, 'local');

        return $meta;
    }

    /** Nama unduhan dibersihkan agar aman dipakai di header Content-Disposition. */
    private function safeFileName(string $original, string $extension): string
    {
        $base = Str::limit(Str::slug(pathinfo($original, PATHINFO_FILENAME), '_'), 80, '');

        return ($base !== '' ? $base : 'manual-book') . '.' . $extension;
    }

    private function targetOptions(): array
    {
        return [
            'userOptions' => t_User::with('instansi')
                ->orderBy('nama')
                ->get(['id_user', 'nama', 'username', 'level_user', 'instansi_id']),
            'roleOptions' => Role::orderBy('role_name')->get(),
            'instansiOptions' => Instansi::orderBy('nama')->get(['id', 'nama']),
        ];
    }

    /** Peta "tipe:id" => nama, untuk menampilkan ringkasan target di tabel kelola. */
    private function targetLabelMap(array $options): array
    {
        $map = [];

        foreach ($options['userOptions'] as $user) {
            $map[ManualBookTarget::TYPE_USER . ':' . $user->id_user] = $user->nama;
        }

        foreach ($options['roleOptions'] as $role) {
            $map[ManualBookTarget::TYPE_ROLE . ':' . $role->role_name] = $role->role_name;
        }

        foreach ($options['instansiOptions'] as $instansi) {
            $map[ManualBookTarget::TYPE_INSTANSI . ':' . $instansi->id] = $instansi->nama;
        }

        return $map;
    }
}
