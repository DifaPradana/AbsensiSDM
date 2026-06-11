<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinAbsen extends Model
{
    protected $primaryKey = 'id_izin';

    protected $fillable = [
        'user_id',
        'tipe_izin',
        'mulai_izin',
        'akhir_izin',
        'dokumen_izin',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
