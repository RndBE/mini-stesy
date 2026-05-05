<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto_pos extends Model
{
    protected $table = 'foto_pos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'url_foto',
        'foto_utama'
    ];
}
