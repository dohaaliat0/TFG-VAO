<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'torneo_id',
        'fecha',
        'pista'
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function partidos()
    {
        return $this->belongsToMany(Partido::class, 'partido_horario')
            ->withTimestamps();
    }
}