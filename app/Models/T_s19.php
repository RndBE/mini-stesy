<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class T_s19 extends Model
{
    protected $table = 't_s19_01';

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
        'sensor17',
        'sensor18',
        'sensor19',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger','id_logger');
    }
}
