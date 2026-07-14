<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class t_User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\TUserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 't_user';

    protected static function newFactory(): \Database\Factories\TUserFactory
    {
        return \Database\Factories\TUserFactory::new();
    }
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'username',
        'password',
        'level_user',
        'instansi_id',
        'status',
        'suspend_reason',
        'decimal_places',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function subuser()
    {
        return $this->hasMany(sub_user::class);
    }

    public function logger()
    {
        return $this->hasMany(t_Logger::class, 'instansi_id', 'instansi_id');
    }

    public function accessibleLoggers()
    {
        return $this->belongsToMany(
            t_Logger::class,
            'user_logger_access',
            'user_id',
            'logger_id',
            'id_user',
            'id_logger'
        );
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'level_user', 'role_name');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $this->loadMissing('role.permissions');

        return $this->role
            && $this->role->permissions->contains('permission_name', $permission);
    }

    public function isSuperAdmin(): bool
    {
        return strtolower((string) $this->level_user) === 'superadmin';
    }

    public function isInstansiAdmin(): bool
    {
        return in_array(strtolower((string) $this->level_user), ['instansi_admin', 'admin'], true);
    }

    public function isPegawai(): bool
    {
        return in_array(strtolower((string) $this->level_user), ['pegawai', 'user'], true);
    }

    /**
     * Apakah user tergabung di instansi SPAM Wosusokas. Dicocokkan dari nama
     * instansi (toleran terhadap "SPAM WOSUSOKAS" / "SPAM Wosusokas").
     */
    public function isWosusokas(): bool
    {
        return str_contains(
            strtolower((string) optional($this->instansi)->nama),
            'wosusokas'
        );
    }

    /** Skema pipa hanya untuk instansi Wosusokas (superadmin tetap bisa lihat). */
    public function canViewSkemaPipa(): bool
    {
        return $this->isSuperAdmin() || $this->isWosusokas();
    }

    /** Mengelola titik pin butuh admin dari instansi Wosusokas. */
    public function canManageSkemaPipa(): bool
    {
        return $this->isSuperAdmin() || ($this->isWosusokas() && $this->isInstansiAdmin());
    }

    public function isActive(): bool
    {
        return strtolower((string) ($this->status ?? 'aktif')) === 'aktif';
    }

    public function isSuspended(): bool
    {
        return strtolower((string) ($this->status ?? 'aktif')) === 'suspend';
    }

    public function isNonActive(): bool
    {
        return strtolower((string) ($this->status ?? 'aktif')) === 'non-aktif';
    }
}
