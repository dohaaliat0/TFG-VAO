<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipoGrupo extends Model
{
    use HasFactory;

    protected $table = 'equipo_grupo';

    protected $fillable = [
        'grupo_id',
        'equipo_id',
        'puntos',
        'partidos_jugados',
        'partidos_ganados_2_0',
        'partidos_ganados_2_1',
        'partidos_perdidos_1_2',
        'partidos_perdidos_0_2',
        'sets_favor',
        'sets_contra',
        'puntos_favor',
        'puntos_contra',
        'posicion',
        'eliminado',
    ];

    protected $casts = [
        'eliminado' => 'boolean',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'equipo_categoria', 'equipo_grupo_id', 'categoria_id')
            ->withPivot('posicion')
            ->withTimestamps();
    }

    public function getDiferenciaSetsAttribute()
    {
        return $this->sets_favor - $this->sets_contra;
    }

    public function getDiferenciaPuntosAttribute()
    {
        return $this->puntos_favor - $this->puntos_contra;
    }

    public function getTotalPartidosGanadosAttribute()
    {
        return $this->partidos_ganados_2_0 + $this->partidos_ganados_2_1;
    }

    public function getTotalPartidosPerdidosAttribute()
    {
        return $this->partidos_perdidos_0_2 + $this->partidos_perdidos_1_2;
    }

    public function equipoCategorias(): HasMany
    {
        return $this->hasMany(EquipoCategoria::class);
    }
}
