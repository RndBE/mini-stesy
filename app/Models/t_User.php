<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class t_User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 't_user';
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
