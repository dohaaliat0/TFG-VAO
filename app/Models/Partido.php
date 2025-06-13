<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Partido extends Model
{
    use HasFactory;

    protected $fillable = [
        'grupo_id',
        'equipo_local_id',
        'equipo_visitante_id',
        'resultado_local',
        'resultado_visitante',
        'puntos_local',
        'puntos_visitante',
        'fecha',
        'completado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'completado' => 'boolean',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function equipoLocal()
    {
        return $this->belongsTo(Equipo::class, 'equipo_local_id');
    }

    public function equipoVisitante()
    {
        return $this->belongsTo(Equipo::class, 'equipo_visitante_id');
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }

    public function categoriaPartido()
    {
        return $this->hasOne(CategoriaPartido::class);
    }

    public function categoria()
    {
        return $this->hasOneThrough(Categoria::class, CategoriaPartido::class, 'partido_id', 'id', 'id', 'categoria_id');
    }

    public function scopeDeGrupo($query)
    {
        return $query->whereNotNull('grupo_id');
    }

    public function scopeEliminatorios($query)
    {
        return $query->whereNull('grupo_id');
    }

    public function scopeCompletados($query)
    {
        return $query->where('completado', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('completado', false);
    }

    public function esPartidoDeGrupo()
    {
        return !is_null($this->grupo_id);
    }

    public function esPartidoEliminatorio()
    {
        return is_null($this->grupo_id);
    }

    public function getGanadorAttribute()
    {
        if (!$this->completado) {
            return null;
        }

        if ($this->resultado_local > $this->resultado_visitante) {
            return $this->equipoLocal;
        } elseif ($this->resultado_visitante > $this->resultado_local) {
            return $this->equipoVisitante;
        }

        return null;
    }

    public function getPerdedorAttribute()
    {
        if (!$this->completado) {
            return null;
        }

        if ($this->resultado_local < $this->resultado_visitante) {
            return $this->equipoLocal;
        } elseif ($this->resultado_visitante < $this->resultado_local) {
            return $this->equipoVisitante;
        }

        return null;
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('d/m/Y H:i') : null;
    }

    public function getResultadoFormateadoAttribute()
    {
        if (!$this->completado) {
            return 'Pendiente';
        }

        return "{$this->resultado_local} - {$this->resultado_visitante}";
    }

    public function tieneEquiposDefinidos()
    {
        return $this->equipo_local_id && $this->equipo_visitante_id;
    }

    public function puedeJugarse()
    {
        if ($this->esPartidoDeGrupo()) {
            return $this->tieneEquiposDefinidos();
        }

        if ($this->categoriaPartido) {
            return $this->categoriaPartido->puedeJugarse() && $this->tieneEquiposDefinidos();
        }

        return $this->tieneEquiposDefinidos();
    }

    public function calcularPuntosTotales()
    {
        $puntosLocal = 0;
        $puntosVisitante = 0;

        foreach ($this->sets as $set) {
            $puntosLocal += $set->puntos_local;
            $puntosVisitante += $set->puntos_visitante;
        }

        $this->update([
            'puntos_local' => $puntosLocal,
            'puntos_visitante' => $puntosVisitante,
        ]);
    }

    public function marcarComoCompletado()
    {
        $this->update(['completado' => true]);

        if ($this->esPartidoEliminatorio() && $this->categoriaPartido) {
            $this->actualizarPartidosSiguientes();
        }
    }

    private function actualizarPartidosSiguientes()
    {
        $categoriaPartido = $this->categoriaPartido;
        $ganador = $this->ganador;

        if (!$ganador) {
            return;
        }

        $partidosSiguientes = CategoriaPartido::where('categoria_id', $categoriaPartido->categoria_id)
            ->whereJsonContains('dependencias', "ganador_{$categoriaPartido->numero_partido}")
            ->with('partido')
            ->get();

        foreach ($partidosSiguientes as $partidoSiguiente) {
            $partido = $partidoSiguiente->partido;
            $dependencias = $partidoSiguiente->dependencias;

            $posicionEnDependencias = array_search("ganador_{$categoriaPartido->numero_partido}", $dependencias);

            if ($posicionEnDependencias === 0) {
                $partido->update(['equipo_local_id' => $ganador->id]);
            } elseif ($posicionEnDependencias === 1) {
                $partido->update(['equipo_visitante_id' => $ganador->id]);
            }
        }
    }
}
