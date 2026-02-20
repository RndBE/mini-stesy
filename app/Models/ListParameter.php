<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListParameter extends Model
{
    protected $table = 'list_parameter';

    protected $fillable = [
        'nama_parameter',
        'parameter_utama',
        'default_satuan',
        'default_kolom_sensor',
        'default_parameter_group_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_parameter_group_id' => 'integer',
    ];

    public function parameterGroup()
    {
        return $this->belongsTo(ParameterGroup::class, 'default_parameter_group_id', 'id');
    }

    public function templates()
    {
        return $this->hasMany(TemplateKategoriParameter::class, 'list_parameter_id', 'id');
    }
}
