<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwgcCommandLog extends Model
{
    protected $table = 'awgc_command_log';

    protected $fillable = [
        'node_skema_id',
        'id_logger',
        'target_bukaan_cm',
        'target_bukaan_persen',
        'status_command',
        'pesan_error',
        'sent_at',
        'confirmed_at',
        'commanded_by',
        'commanded_by_name',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Relasi ke logger AWGC yang diperintah.
     */
    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger', 'id_logger');
    }

    /**
     * Relasi ke user yang memberikan perintah.
     */
    public function commander()
    {
        return $this->belongsTo(\App\Models\t_User::class, 'commanded_by', 'id_user');
    }

    /**
     * Scope: Ambil hanya perintah yang masih dalam status pending/sent.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status_command', ['pending', 'sent']);
    }

    /**
     * Scope: Perintah untuk node skema tertentu.
     */
    public function scopeForNode($query, string $nodeId)
    {
        return $query->where('node_skema_id', $nodeId);
    }

    /**
     * Apakah perintah ini sudah selesai (sukses/gagal)?
     */
    public function isFinished(): bool
    {
        return in_array($this->status_command, ['success', 'error', 'timeout']);
    }
}
