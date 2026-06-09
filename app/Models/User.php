<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $primaryKey = 'user_id';

    protected $table = 'users';

    protected $fillable = [
        'nama_karyawan',
        'nomor_hp',
        'password',
        'is_active',
        'is_password_default',
        'role_id',
        'photo_profile'
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token'
    ];

    protected $casts = [
        'is_password_default' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed'
    ];


    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    // public function pinjams()
    // {
    //     return $this->hasMany(Pinjam::class, 'user_id', 'user_id');
    // }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'user_id', 'user_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('nama_karyawan', 'like', '%' . $search . '%');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo_profile
            ? asset('storage/' . $this->photo_profile)
            : asset('assets/images/profile/user-1.jpg');
    }
}
