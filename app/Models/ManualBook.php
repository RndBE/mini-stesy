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
     * Batas yang kita inginkan, dalam kilobyte (satuan rule `max` Laravel).
     * Batas sesungguhnya belum tentu sebesar ini — lihat maxUploadKb().
     */
    public const MAX_FILE_KB = 51200;

    /** Sisihkan ruang untuk field non-file di dalam body POST. */
    private const POST_OVERHEAD_KB = 512;

    /**
     * Batas unggahan efektif: yang terkecil antara MAX_FILE_KB, post_max_size,
     * dan upload_max_filesize milik server.
     *
     * Wajib dihitung, bukan dipatok: kalau batas aplikasi melebihi batas PHP,
     * ValidatePostSize melempar PostTooLargeException sebelum session dimulai,
     * sehingga user cuma dapat halaman 413 tanpa pesan apa pun. Server produksi
     * sering memakai default 8M sementara lokal jauh lebih besar.
     */
    public static function maxUploadKb(): int
    {
        $batas = [self::MAX_FILE_KB];

        $postKb = self::iniKb('post_max_size');
        if ($postKb !== null) {
            $batas[] = $postKb - self::POST_OVERHEAD_KB;
        }

        $uploadKb = self::iniKb('upload_max_filesize');
        if ($uploadKb !== null) {
            $batas[] = $uploadKb;
        }

        return max(1, min($batas));
    }

    /** Baca direktif ini_get berukuran (contoh "8M", "100M") jadi kilobyte. */
    private static function iniKb(string $direktif): ?int
    {
        $nilai = trim((string) ini_get($direktif));

        // 0 atau kosong berarti tanpa batas.
        if ($nilai === '' || $nilai === '0') {
            return null;
        }

        $angka = (float) $nilai;
        $satuan = strtolower(substr($nilai, -1));

        $bytes = match ($satuan) {
            'g' => $angka * 1024 * 1024 * 1024,
            'm' => $angka * 1024 * 1024,
            'k' => $angka * 1024,
            default => $angka,
        };

        return $bytes <= 0 ? null : (int) ($bytes / 1024);
    }

    /** Label batas efektif untuk ditampilkan ke user, contoh "7 MB". */
    public static function maxUploadLabel(): string
    {
        $kb = self::maxUploadKb();

        return $kb >= 1024
            ? rtrim(rtrim(number_format($kb / 1024, 1, '.', ''), '0'), '.') . ' MB'
            : $kb . ' KB';
    }

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
