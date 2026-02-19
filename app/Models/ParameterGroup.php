<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterGroup extends Model
{
    protected $table = 'parameter_groups';
    public $timestamps = false;

    protected $fillable = [
        'kode_group',
        'nama_group',
        'deskripsi',
        'sort_order',
    ];

    public function parameters()
    {
        return $this->hasMany(Parameter::class, 'parameter_group_id', 'id');
    }
}

