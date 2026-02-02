<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto_pos extends Model
{
    protected $table = 'foto_pos';
    protected $primaryKey = 'id_foto';
    public $timestamps = false;

    protected $fillable = [
        'id_foto',
        'logger_id',
        'foto_path',
        'keterangan',
        'tanggal_upload'
    ];
}
