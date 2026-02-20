<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $table = 'instansi';

    protected $fillable = [
        'nama',
        'alamat',
        'telp',
        'latitude',
        'longitude',
        'zoom',
        'logo',
        'logo_mobile',
    ];

    public function users()
    {
        return $this->hasMany(t_User::class, 'instansi_id', 'id');
    }
}
