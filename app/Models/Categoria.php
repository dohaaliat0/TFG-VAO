<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'torneo_id',
        'nombre',
        'descripcion',
    ];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function equiposCategorias()
    {
        return $this->hasMany(EquipoCategoria::class);
    }

    public function equipos()
    {
        return $this->hasManyThrough(
            Equipo::class,
            EquipoCategoria::class,
            'categoria_id',
            'id',
            'id',
            'equipo_grupo_id'
        );
    }

    public function categoriaPartidos()
    {
        return $this->hasMany(CategoriaPartido::class);
    }

    public function partidos()
    {
        return $this->hasManyThrough(
            Partido::class,
            CategoriaPartido::class,
            'categoria_id',
            'id',
            'id',
            'partido_id'
        );
    }

    public function partidosPorFase($fase)
    {
        return $this->categoriaPartidos()
            ->where('fase', $fase)
            ->with(['partido.equipoLocal', 'partido.equipoVisitante'])
            ->get()
            ->map(function($cp) {
                return $cp->partido;
            });
    }

    public function cuartos()
    {
        return $this->partidosPorFase('cuartos');
    }

    public function semifinales()
    {
        return $this->partidosPorFase('semifinal');
    }

    public function final()
    {
        return $this->partidosPorFase('final')->first();
    }

    public function equiposOrdenados()
    {
        return $this->equiposCategorias()
            ->with(['equipoGrupo.equipo', 'equipoGrupo.grupo'])
            ->orderBy('posicion')
            ->get();
    }

    public function tieneEquiposAsignados()
    {
        return $this->equiposCategorias()->count() > 0;
    }

    public function tienePartidosGenerados()
    {
        return $this->categoriaPartidos()->count() > 0;
    }

    public function getEstadoEliminatoriasAttribute()
    {
        if (!$this->tienePartidosGenerados()) {
            return 'no_generadas';
        }

        $cuartosCompletados = $this->cuartos()->where('completado', true)->count();
        $semifinalesCompletadas = $this->semifinales()->where('completado', true)->count();
        $finalCompletada = $this->final()?->completado ?? false;

        if ($finalCompletada) {
            return 'finalizada';
        } elseif ($semifinalesCompletadas > 0) {
            return 'semifinales';
        } elseif ($cuartosCompletados > 0) {
            return 'cuartos';
        } else {
            return 'generadas';
        }
    }

    public function getCampeonAttribute()
    {
        $final = $this->final();
        return $final && $final->completado ? $final->ganador : null;
    }

    public function getSubcampeonAttribute()
    {
        $final = $this->final();
        return $final && $final->completado ? $final->perdedor : null;
    }
}
