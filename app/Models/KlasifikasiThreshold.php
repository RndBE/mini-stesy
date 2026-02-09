<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlasifikasiThreshold extends Model
{
    protected $table = 'klasifikasi_threshold';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'state_key',
        'state_label',
        'min_value',
        'max_value',
        'icon_path',
        'color_hex',
        'sort_order',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori_logger::class, 'id_kategori', 'id_katlogger');
    }
}
