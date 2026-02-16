<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class t_Informasi extends Model
{
    protected $table = 't_informasi';
    protected $primaryKey = 'id_inf';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'logger_id',
        'seri_logger',
        'sensor',
        'serial_number',
        'elevasi',
        'nosell',
        'nama_pic',
        'no_pic',
        'tanggal_pemasangan',
        'garansi',
        'awal_kontrak',
        'imei',
        'gps1',
        'gps2',
        'gps3',
        'ad',
        'kd',
        'mr',
        'wdt',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'logger_id', 'id_logger');
    }
}
