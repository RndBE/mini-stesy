<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipaPoint extends Model
{
    protected $table = 'pipa_points';

    protected $fillable = [
        'scheme',
        'logger_id',
        'label',
        'kind',
        'x',
        'y',
        'sort_order',
        'pressure',
        'pressure_display',
        'flowrate',
        'totalizer',
    ];

    protected $casts = [
        'x'          => 'float',
        'y'          => 'float',
        'sort_order' => 'integer',
        'pressure'   => 'float',
        'flowrate'   => 'float',
        'totalizer'  => 'float',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'logger_id', 'id_logger');
    }
}
