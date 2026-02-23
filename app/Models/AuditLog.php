<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'module',
        'action_type',
        'activity',
        'target',
        'status',
        'ip_address',
        'user_agent',
        'description',
        'metadata',
        'occurred_at',
        'actor_name',
        'actor_username',
        'actor_role',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(t_User::class, 'user_id', 'id_user');
    }
}
