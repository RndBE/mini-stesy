<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class t_Logger extends Model
{
    protected $table = 't_logger';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'user_id',
        'nama_logger',
        'tabel_main',
        'jeda_notif',
        'idlokasi',
        'id_kategori',
        'sensor_count'
    ];

    public function user()
    {
        return $this->belongsTo(t_User::class, 'user_id', 'id_user');
    }

    public function lokasi()
    {
        return $this->belongsTo(t_Lokasi::class, 'idlokasi', 'idlokasi');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori_logger::class, 'id_katlogger', 'id_katlogger');
    }

    public function params()
    {
        return $this->hasMany(Parameter::class, 'logger_id', 'id_logger');
    }

    public function jiat()
    {
        return $this->hasOne(Jiat_data::class, 'id_logger', 'id_logger');
    }

    public function temp19()
    {
        return $this->hasOne(Temp_19s::class, 'id_logger', 'id_logger');
    }

    public function temp16()
    {
        return $this->hasOne(Temp_16s::class, 'id_logger', 'id_logger');
    }

    public function getTempAttribute()
    {
        return ((int) $this->sensor_count === 19) ? $this->temp19 : $this->temp16;
    }

    public function informasi()
    {
        return $this->hasOne(t_Informasi::class, 'logger_id', 'id_logger');
    }

    public function fotos()
    {
        return $this->hasMany(Foto_pos::class, 'id_logger', 'id_logger');
    }
}
