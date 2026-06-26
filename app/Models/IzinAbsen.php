<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinAbsen extends Model
{
    protected $primaryKey = 'izin_id';

    protected $fillable = [
        'user_id',
        'tipe_izin',
        'mulai_izin',
        'akhir_izin',
        'dokumen_izin',
        'note',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function hrd()
    {
        return $this->belongsTo(User::class, 'hrd_id', 'user_id');
    }

    public function direktur()
    {
        return $this->belongsTo(User::class, 'direktur_id', 'user_id');
    }
}
