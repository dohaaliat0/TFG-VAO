<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    use HasFactory;

    protected $fillable = [
        'partido_id',
        'numero_set',
        'puntos_local',
        'puntos_visitante'
    ];

    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }
}
