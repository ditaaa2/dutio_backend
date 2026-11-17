<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Jadwal;
use App\Models\Laporan;
use App\Models\Penghuni;
use App\Models\CrewPoint;
use App\Models\UserDevice;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'kamar',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relasi dengan jadwal piket
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    // Relasi dengan laporan piket
    public function laporans()
    {
        return $this->hasMany(Laporan::class);
    }

    // Relasi dengan Crew Points
    public function crewPoints()
    {
        return $this->hasMany(CrewPoint::class);
    }

    // Relasi dengan data penghuni
    public function penghuni()
    {
        return $this->hasOne(Penghuni::class);
    }

    // Relasi dengan perangkat pengguna
    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }
}
