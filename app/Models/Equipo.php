<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'telefono_contacto',
        'torneo_id',
    ];

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'equipo_grupo')
            ->withPivot('posicion', 'puntos')
            ->withTimestamps();
    }

    public function partidosLocal()
    {
        return $this->hasMany(Partido::class, 'equipo_local_id');
    }

    public function partidosVisitante()
    {
        return $this->hasMany(Partido::class, 'equipo_visitante_id');
    }

    public function partidosLocales()
    {
        return $this->hasMany(Partido::class, 'equipo_local_id');
    }

    public function partidosVisitantes()
    {
        return $this->hasMany(Partido::class, 'equipo_visitante_id');
    }

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }
}
