<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jiat_data extends Model
{
    protected $table = 'jiat_data';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'kedalaman_sumur',
        'kedalaman_pompa',
        'kedalaman_sensor',
        'has_pump',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger','id_logger');
    }
}
