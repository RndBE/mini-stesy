<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klasifikasi_hujan extends Model
{
    protected $table = 'klasifikasi_hujan';
    protected $primaryKey = 'id_klasifikasi';
    public $timestamps = false;
    protected $fillable = [
        'logger_id',
        'waktu',
        'debit_air',
        'intensitas',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'logger_id', 'id_logger');
    }
}
