<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipoCategoria extends Model
{
    use HasFactory;

    protected $table = 'equipo_categoria';

    protected $fillable = [
        'equipo_grupo_id',
        'categoria_id',
        'posicion',
    ];

    public function equipoGrupo()
    {
        return $this->belongsTo(EquipoGrupo::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function equipo()
    {
        return $this->hasOneThrough(
            Equipo::class,
            EquipoGrupo::class,
            'id',
            'id',
            'equipo_grupo_id',
            'equipo_id'
        );
    }

    public function grupo()
    {
        return $this->hasOneThrough(
            Grupo::class,
            EquipoGrupo::class,
            'id',
            'id',
            'equipo_grupo_id',
            'grupo_id'
        );
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopeOrdenadoPorPosicion($query)
    {
        return $query->orderBy('posicion');
    }

    public function getPosicionFormateadaAttribute()
    {
        return $this->posicion . 'º';
    }

    public function getEstadisticasAttribute()
    {
        return [
            'puntos' => $this->equipoGrupo->puntos,
            'sets_favor' => $this->equipoGrupo->sets_favor,
            'sets_contra' => $this->equipoGrupo->sets_contra,
            'puntos_favor' => $this->equipoGrupo->puntos_favor,
            'puntos_contra' => $this->equipoGrupo->puntos_contra,
            'diferencia_sets' => $this->equipoGrupo->sets_favor - $this->equipoGrupo->sets_contra,
            'diferencia_puntos' => $this->equipoGrupo->puntos_favor - $this->equipoGrupo->puntos_contra,
        ];
    }
}
