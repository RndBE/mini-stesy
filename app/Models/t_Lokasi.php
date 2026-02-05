<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class t_Lokasi extends Model
{
    protected $table = 't_lokasi';
    protected $primaryKey = 'idlokasi';   // 🔥 ini kuncinya
    public $incrementing = false;          // karena bukan auto increment
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'logtitude',
        'alamat',
        'das_id',
    ];

    public function das()
    {
        return $this->belongsTo(List_das::class, 'das_id', 'id');
    }

    public function logger()
    {
        return $this->hasMany(t_Logger::class,'idlokasi','idlokasi');
    }

}
