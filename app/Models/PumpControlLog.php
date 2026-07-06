<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PumpControlLog extends Model
{
    protected $table = 'pump_control_logs';

    protected $fillable = [
        'user_id',
        'instansi_id',
        'id_logger',
        'action',
        'status',
        'message',
        'latitude',
        'longitude',
        'location_permission_status',
        'requested_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];
}
