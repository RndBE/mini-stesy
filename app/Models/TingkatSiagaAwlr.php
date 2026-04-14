<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TingkatSiagaAwlr extends Model
{
    protected $table = 'tingkat_siaga_awlr';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'id_status',
        'nama',
        'nilai',
        'status',
        'warna',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger', 'id_logger');
    }

    /**
     * Accessor: controller menggunakan ->nilai_batas
     * sedangkan kolom DB bernama 'nilai'.
     */
    public function getNilaiBatasAttribute(): ?float
    {
        return $this->nilai !== null ? (float)$this->nilai : null;
    }
}
