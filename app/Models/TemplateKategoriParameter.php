<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateKategoriParameter extends Model
{
    protected $table = 'template_kategori_parameter';

    protected $fillable = [
        'id_katlogger',
        'list_parameter_id',
        'urutan',
        'kolom_sensor_default',
        'satuan_override',
        'parameter_group_id',
    ];

    protected $casts = [
        'id_katlogger' => 'integer',
        'list_parameter_id' => 'integer',
        'urutan' => 'integer',
        'parameter_group_id' => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori_logger::class, 'id_katlogger', 'id_katlogger');
    }

    public function listParameter()
    {
        return $this->belongsTo(ListParameter::class, 'list_parameter_id', 'id');
    }

    public function parameterGroup()
    {
        return $this->belongsTo(ParameterGroup::class, 'parameter_group_id', 'id');
    }
}
