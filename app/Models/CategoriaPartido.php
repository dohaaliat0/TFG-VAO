<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaPartido extends Model
{
    use HasFactory;

    protected $table = 'categoria_partido';

    protected $fillable = [
        'categoria_id',
        'partido_id',
        'fase',
        'numero_partido',
        'dependencias',
    ];

    protected $casts = [
        'dependencias' => 'array',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    public function scopePorFase($query, $fase)
    {
        return $query->where('fase', $fase);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function getDependenciasFormateadasAttribute()
    {
        if (!$this->dependencias) {
            return null;
        }

        return collect($this->dependencias)->map(function($dep) {
            return str_replace('_', ' ', ucfirst($dep));
        })->implode(' vs ');
    }

    public function esPartidoConEquiposDefinidos()
    {
        return $this->partido->equipo_local_id && $this->partido->equipo_visitante_id;
    }

    public function puedeJugarse()
    {
        if ($this->fase === 'cuartos') {
            return true; // Los cuartos siempre pueden jugarse
        }

        // Para semifinales y finales, verificar que las dependencias estén completadas
        if (!$this->dependencias) {
            return false;
        }

        foreach ($this->dependencias as $dependencia) {
            $partidoAnterior = $this->obtenerPartidoAnterior($dependencia);
            if (!$partidoAnterior || !$partidoAnterior->completado) {
                return false;
            }
        }

        return true;
    }

    private function obtenerPartidoAnterior($dependencia)
    {
        $numeroPartido = str_replace('ganador_', '', $dependencia);

        return CategoriaPartido::where('categoria_id', $this->categoria_id)
            ->where('numero_partido', $numeroPartido)
            ->with('partido')
            ->first()?->partido;
    }
}
