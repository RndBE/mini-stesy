<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori_logger extends Model
{
    protected $table = 'kategori_logger';
    protected $primaryKey = 'id_katlogger';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nama_kategori',
        'kepanjangan',
        'icon_app',
        'view'
    ];

    public function logger()
    {
        return $this->hasMany(t_Logger::class, 'id_katlogger', 'id_katlogger');
    }

    public function thresholds()
    {
        return $this->hasMany(KlasifikasiThreshold::class, 'id_kategori', 'id_katlogger');
    }
}
