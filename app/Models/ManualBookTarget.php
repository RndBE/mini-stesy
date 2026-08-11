<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualBookTarget extends Model
{
    protected $table = 'manual_book_targets';

    public const TYPE_USER = 'user';
    public const TYPE_ROLE = 'role';
    public const TYPE_INSTANSI = 'instansi';

    protected $fillable = [
        'manual_book_id',
        'target_type',
        'target_id',
    ];

    public function manualBook()
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }
}
