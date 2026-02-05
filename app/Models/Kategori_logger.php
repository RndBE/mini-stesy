<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori_logger extends Model
{
    protected $table = 'kategori_logger';
    protected $primaryKey = 'id_katlogger';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_kategori',
        'controller',
        'tabel',
        'kepanjangan',
        'temp_data',
        'icon_app',
        'view'
    ];

    public function logger()
    {
        return $this->hasMany(t_Logger::class, 'id_katlogger', 'id_katlogger');
    }
}
