<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonJiatData extends Model
{
    protected $table = 'nonjiat_data';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'jenis_sensor',
        'jenis_pemasangan',
        'jarak_sensor_ke_air',
        'tinggi_sensor',
        'elevasi_max',
        'elevasi_min',
        'elevasi_apex',
        'kedalaman_notch',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger', 'id_logger');
    }
}
