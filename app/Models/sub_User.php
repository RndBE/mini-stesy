<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sub_User extends Model
{
    protected $fillable = [
        'id_user',
        'nama',
        'level',
        'no',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(t_User::class);
    }
}
