<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter_sensor extends Model
{
    protected $table = 'parameter_sensor';
    protected $primaryKey = 'id_param';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'logger_id',
        'nama_parameter',
        'kolom_sensor',
        'satuan',
        'tipe_graf',
        'icon_app',
        'debit_awlr',
        'parameter_utama',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class);
    }
}
