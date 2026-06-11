<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{

    protected $primaryKey = 'absensi_id';

    protected $fillable = [
        'user_id',
        'tipe_absensi',
        'photo_masuk',
        'photo_pulang',
        'waktu_absen_masuk',
        'waktu_absen_pulang',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'lokasi_masuk',
        'lokasi_pulang',
        'status_absensi_masuk',
        'status_absensi_pulang',
        'note_masuk',
        'note_pulang'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    protected $casts = [
        'waktu_absen_masuk' => 'datetime',
        'waktu_absen_pulang' => 'datetime'
    ];

    public function scopeFilterTanggal($query, $tanggalAwal, $tanggalAkhir)
    {
        return $query->when($tanggalAwal && $tanggalAkhir, function ($q) use ($tanggalAwal, $tanggalAkhir) {
            $q->whereBetween('waktu_absen_masuk', [
                $tanggalAwal . ' 00:00:00',
                $tanggalAkhir . ' 23:59:59',
            ]);
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nama_karyawan', 'like', '%' . $search . '%');
            });
        });
    }
}
