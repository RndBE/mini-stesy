<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $table = 'instansi';

    protected $fillable = [
        'nama',
        'judul_mobile',
        'alamat',
        'telp',
        'latitude',
        'longitude',
        'zoom',
        'logo',
        'logo_mobile',
        'control_pin_hash',
        'control_pin_enabled',
        'control_pin_updated_at',
    ];

    protected $hidden = [
        'control_pin_hash',
    ];

    protected $appends = [
        'has_control_pin',
    ];

    protected $casts = [
        'control_pin_enabled' => 'boolean',
        'control_pin_updated_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(t_User::class, 'instansi_id', 'id');
    }

    public function loggers()
    {
        return $this->hasMany(t_Logger::class, 'instansi_id', 'id');
    }

    public function getHasControlPinAttribute(): bool
    {
        return (bool) $this->control_pin_enabled && filled($this->control_pin_hash);
    }
}
