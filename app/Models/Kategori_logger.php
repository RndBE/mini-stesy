<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori_logger extends Model
{
    protected $table = 'kategori_logger';

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
        return $this->hasMany(t_Logger::class);
    }
}
