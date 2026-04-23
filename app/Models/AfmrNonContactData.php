<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AfmrNonContactData extends Model
{
    protected $table = 'afmr_noncontact_data';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'tinggi_sensor',
        'jarak_sensor_ke_air',
        'elevasi_max',
        'elevasi_min',
        'catatan',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger', 'id_logger');
    }
}
