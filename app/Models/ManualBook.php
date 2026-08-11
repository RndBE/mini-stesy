<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ManualBook extends Model
{
    protected $table = 'manual_books';

    public const VISIBILITY_ALL = 'all';
    public const VISIBILITY_INSTANSI = 'instansi';
    public const VISIBILITY_ROLE = 'role';
    public const VISIBILITY_SELECTED = 'selected';

    /**
     * Batas ukuran unggahan dalam kilobyte (satuan rule `max` Laravel).
     * Dipakai controller untuk validasi dan view untuk penjaga sisi klien,
     * supaya angkanya tidak pernah beda antara keduanya.
     */
    public const MAX_FILE_KB = 51200;

    public const VISIBILITIES = [
        self::VISIBILITY_ALL,
        self::VISIBILITY_INSTANSI,
        self::VISIBILITY_ROLE,
        self::VISIBILITY_SELECTED,
    ];

    protected $fillable = [
        'judul',
        'deskripsi',
        'file_path',
        'file_name',
        'file_mime',
        'file_size',
        'visibility',
        'urutan',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function targets()
    {
        return $this->hasMany(ManualBookTarget::class, 'manual_book_id');
    }

    public function uploader()
    {
        return $this->belongsTo(t_User::class, 'uploaded_by', 'id_user');
    }

    /**
     * Batasi hasil ke dokumen yang boleh dilihat $user. Superadmin melihat semua,
     * guest tidak melihat apa pun.
     */
    public function scopeVisibleFor(Builder $query, ?t_User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $outer) use ($user) {
            $outer->where('visibility', self::VISIBILITY_ALL);

            $outer->orWhere(fn(Builder $q) => $this->whereTargeted(
                $q,
                self::VISIBILITY_SELECTED,
                ManualBookTarget::TYPE_USER,
                (string) $user->id_user
            ));

            $outer->orWhere(fn(Builder $q) => $this->whereTargeted(
                $q,
                self::VISIBILITY_ROLE,
                ManualBookTarget::TYPE_ROLE,
                (string) $user->level_user
            ));

            if ($user->instansi_id !== null) {
                $outer->orWhere(fn(Builder $q) => $this->whereTargeted(
                    $q,
                    self::VISIBILITY_INSTANSI,
                    ManualBookTarget::TYPE_INSTANSI,
                    (string) $user->instansi_id
                ));
            }
        });
    }

    /**
     * Nilai target dibandingkan sebagai string supaya MySQL tetap memakai index
     * pada kolom varchar target_id.
     */
    private function whereTargeted(Builder $query, string $visibility, string $type, string $value): Builder
    {
        return $query
            ->where('visibility', $visibility)
            ->whereHas('targets', fn($target) => $target
                ->where('target_type', $type)
                ->where('target_id', $value));
    }

    public function visibilityLabel(): string
    {
        return match ($this->visibility) {
            self::VISIBILITY_INSTANSI => 'Instansi tertentu',
            self::VISIBILITY_ROLE => 'Role tertentu',
            self::VISIBILITY_SELECTED => 'User terpilih',
            default => 'Semua user',
        };
    }

    public function fileExtension(): string
    {
        return strtolower(pathinfo((string) $this->file_path, PATHINFO_EXTENSION));
    }

    public function isPdf(): bool
    {
        return $this->fileExtension() === 'pdf';
    }

    public function fileSizeLabel(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        return $this->file_size >= 1048576
            ? number_format($this->file_size / 1048576, 2) . ' MB'
            : number_format($this->file_size / 1024) . ' KB';
    }
}
