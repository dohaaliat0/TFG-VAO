<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'torneo_id',
    ];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'equipo_grupo')
            ->withPivot('posicion', 'puntos', 'partidos_jugados', 'partidos_ganados_2_0',
                'partidos_ganados_2_1', 'partidos_perdidos_0_2', 'partidos_perdidos_1_2',
                'no_presentados', 'sets_favor', 'sets_contra', 'puntos_favor',
                'puntos_contra', 'eliminado')
            ->withTimestamps();
    }

    public function partidos()
    {
        return $this->hasMany(Partido::class);
    }
}
