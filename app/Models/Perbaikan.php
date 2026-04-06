<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perbaikan extends Model
{
    protected $table = 't_perbaikan';
    protected $primaryKey = 'id_perbaikan';
    public $timestamps = true;

    protected $fillable = [
        'id_logger',
        'keterangan',
        'tanggal_perbaikan',
        'petugas',
        'status_akhir',
        'created_by',
    ];

    protected $casts = [
        'tanggal_perbaikan' => 'date',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger', 'id_logger');
    }
}
