<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table= 'parameter_sensor';
    protected $primaryKey = 'id_param';   // 🔥 ini kuncinya
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'logger_id',
        'nama_parameter',
        'kolom_sensor',
        'satuan',
        'parameter_group_id',
        'tipe_graf',
        'icon_app',
        'debit_awlr',
        'parameter_utama',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'logger_id', 'id_logger');
    }

    public function parameterGroup()
    {
        return $this->belongsTo(ParameterGroup::class, 'parameter_group_id', 'id');
    }
}
