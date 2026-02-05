<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class T_s16 extends Model
{
    protected $table = 't_s16_01';

    protected $fillable = [
        'waktu',
        'sensor1',
        'sensor2',
        'sensor3',
        'sensor4',
        'sensor5',
        'sensor6',
        'sensor7',
        'sensor8',
        'sensor9',
        'sensor10',
        'sensor11',
        'sensor12',
        'sensor13',
        'sensor14',
        'sensor15',
        'sensor16',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger','id_logger');
    }
}
