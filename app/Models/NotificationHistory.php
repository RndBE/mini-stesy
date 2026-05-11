<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationHistory extends Model
{
    protected $table = 'notification_histories';

    protected $fillable = [
        'type',
        'title',
        'body',
        'data',
        'sent_by',
        'recipient_type',
        'recipient_ids',
        'recipient_count',
    ];

    protected $casts = [
        'data'          => 'array',
        'recipient_ids' => 'array',
    ];

    public function sender()
    {
        return $this->belongsTo(t_User::class, 'sent_by', 'id_user');
    }
}
