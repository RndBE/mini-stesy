<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class t_User extends Authenticatable
{
    use Notifiable;

    protected $table = 't_user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;
    protected $fillable = [
        'nama',
        'username',
        'password',
        'level_user',
        'alamat',
        'telp',
        'instansi',
        'instansi_id',
        'latitude',
        'longitude',
        'longtitude',
        'zoom',
        'logo',
        'logo_mobile'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        // return parent::getAuthPassword();
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
        if ($this->level_user === 'superadmin') {
            return true;
        }

        $this->loadMissing('role.permissions');

        return $this->role
            && $this->role->permissions->contains('permission_name', $permission);
    }
}
