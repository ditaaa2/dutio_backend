<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaians';

    protected $fillable = [
        'laporan_id',
        'poin',
        'evaluasi',
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function crewPoints()
    {
        return $this->hasMany(CrewPoint::class);
    }
}