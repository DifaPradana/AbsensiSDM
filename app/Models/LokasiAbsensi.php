<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiAbsensi extends Model
{
    protected $primaryKey = 'lokasi_id';

    protected $fillable = [
        'lokasi_id',
        'nama_lokasi',
        'latitude_lokasi',
        'longitude_lokasi',
        'radius_meter'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopeSearch($query, $search)
    {
        return $query->where('nama_lokasi', 'like', '%' . $search . '%');
    }
}
