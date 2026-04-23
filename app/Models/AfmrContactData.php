<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AfmrContactData extends Model
{
    protected $table = 'afmr_contact_data';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'lebar_sungai',
        'kedalaman_rata',
        'koefisien_debit',
        'catatan',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger', 'id_logger');
    }
}
