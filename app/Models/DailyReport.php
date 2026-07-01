<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $primaryKey = 'daily_report_id';

    protected $fillable = [
        'user_id',
        'path_dokumen'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }


    public function scopeFilterTanggal($query, $tanggalAwal, $tanggalAkhir)
    {
        return $query->when($tanggalAwal && $tanggalAkhir, function ($q) use ($tanggalAwal, $tanggalAkhir) {
            $q->whereBetween('created_at', [
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
