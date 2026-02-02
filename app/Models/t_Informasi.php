<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class t_Informasi extends Model
{
    protected $table = 't_informasi';

    protected $fillable = [
        'id_informasi',
        'id_logger',
        'deskripsi',
        'versi_firmware',
        'kapasitas_memori',
        'tipe_sensor',
        'jumlah_sensor'
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'logger_id', 'id_logger');
    }
}
