<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temp_50s extends Model
{
    protected $table = 'temp_s50_latest';

    protected $fillable = [
        'waktu',
        'sensor1', 'sensor2', 'sensor3', 'sensor4', 'sensor5',
        'sensor6', 'sensor7', 'sensor8', 'sensor9', 'sensor10',
        'sensor11', 'sensor12', 'sensor13', 'sensor14', 'sensor15',
        'sensor16', 'sensor17', 'sensor18', 'sensor19', 'sensor20',
        'sensor21', 'sensor22', 'sensor23', 'sensor24', 'sensor25',
        'sensor26', 'sensor27', 'sensor28', 'sensor29', 'sensor30',
        'sensor31', 'sensor32', 'sensor33', 'sensor34', 'sensor35',
        'sensor36', 'sensor37', 'sensor38', 'sensor39', 'sensor40',
        'sensor41', 'sensor42', 'sensor43', 'sensor44', 'sensor45',
        'sensor46', 'sensor47', 'sensor48', 'sensor49', 'sensor50',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger', 'id_logger');
    }
}
