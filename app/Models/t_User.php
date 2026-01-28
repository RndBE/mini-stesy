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
        'latitude',
        'longtitude',
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
        return $this->hasMany(t_Logger::class);
    }
}
