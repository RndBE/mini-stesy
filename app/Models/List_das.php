<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class List_das extends Model
{
    protected $fillable = [
        'nama_das',
    ];

    public function lokasi()
    {
        return $this->hasMany(t_Lokasi::class);
    }
}
