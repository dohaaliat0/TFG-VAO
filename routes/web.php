<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Principales
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('torneos.index');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Perfil de Usuario
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Torneos
    |--------------------------------------------------------------------------
    */
    Route::resource('torneos', TorneoController::class);
    Route::post('/torneos/{torneo}/iniciar', [TorneoController::class, 'iniciar'])->name('torneos.iniciar');
    Route::post('/torneos/{torneo}/finalizar', [TorneoController::class, 'finalizar'])->name('torneos.finalizar');
    Route::get('/torneos/{torneo}/categorias', [TorneoController::class, 'mostrarCategorias'])->name('torneos.categorias');
    Route::post('/torneos/{torneo}/crear-grupo', [TorneoController::class, 'crearGrupo'])->name('torneos.crear-grupo');
    Route::get('/torneos/{torneo}/resumen-completo', [TorneoController::class, 'resumenCompleto'])->name('torneos.resumen-completo');
    Route::get('/torneos/{torneo}/eliminatorias', [TorneoController::class, 'eliminatorias'])->name('torneos.eliminatorias');

    /*
    |--------------------------------------------------------------------------
    | Equipos
    |--------------------------------------------------------------------------
    */
    // Rutas específicas ANTES del resource para evitar conflictos
    Route::get('/equipos/import', [EquipoController::class, 'importForm'])->name('equipos.import.form');
    Route::post('/equipos/import', [EquipoController::class, 'importCSV'])->name('equipos.import');

    // Resource routes
    Route::resource('equipos', EquipoController::class);
    Route::post('/equipos/{equipo}/asignar', [EquipoController::class, 'asignarGrupo'])->name('equipos.asignar');

    /*
    |--------------------------------------------------------------------------
    | Grupos
    |--------------------------------------------------------------------------
    */
    Route::resource('grupos', GrupoController::class)->except(['index', 'create']);

    // Gestión de grupos en torneos
    Route::post('/torneos/{torneo}/grupos', [GrupoController::class, 'store'])->name('grupos.store');
    Route::get('/grupos/{grupo}', [GrupoController::class, 'show'])->name('grupos.show');
    Route::put('/grupos/{grupo}', [GrupoController::class, 'update'])->name('grupos.update');
    Route::delete('/grupos/{grupo}', [GrupoController::class, 'destroy'])->name('grupos.destroy');

    // Gestión de equipos en grupos
    Route::post('/grupos/{grupo}/equipos', [GrupoController::class, 'agregarEquipo'])->name('grupos.equipos.agregar');
    Route::post('/torneos/{torneo}/grupos/{grupo}/equipos', [GrupoController::class, 'agregarEquipo'])->name('grupos.agregar-equipos');
    Route::delete('/grupos/{grupo}/equipos/{equipo}', [GrupoController::class, 'quitarEquipo'])->name('grupos.equipos.quitar');
    Route::delete('/torneos/{torneo}/grupos/{grupo}/equipos/{equipo}', [GrupoController::class, 'eliminarEquipo'])->name('grupos.eliminar-equipo');
    Route::post('/grupos/{grupo}/equipos/{equipo}/cabeza', [GrupoController::class, 'marcarCabezaGrupo'])->name('grupos.equipos.cabeza');

    // Generación y gestión de partidos
    Route::post('/grupos/{grupo}/generar-partidos', [GrupoController::class, 'generarPartidos'])->name('grupos.generar-partidos');
    Route::post('/grupos/{grupo}/partidos/generar', [GrupoController::class, 'generarPartidos'])->name('grupos.partidos.generar');
    Route::delete('/grupos/{grupo}/partidos', [GrupoController::class, 'eliminarTodosPartidos'])->name('grupos.partidos.eliminar-todos');

    // Clasificación
    Route::post('/grupos/{grupo}/clasificacion', [GrupoController::class, 'actualizarClasificacion'])->name('grupos.clasificacion.actualizar');

    /*
    |--------------------------------------------------------------------------
    | Partidos
    |--------------------------------------------------------------------------
    */
    Route::resource('partidos', PartidoController::class);

    // Registro de resultados
    Route::get('/partidos/{partido}/registrar-resultado', [PartidoController::class, 'registrarResultadoForm'])->name('partidos.registrar-resultado.form');
    Route::post('/partidos/{partido}/registrar-resultado', [PartidoController::class, 'registrarResultado'])->name('partidos.registrar-resultado');
    Route::post('/partidos/{partido}/resultado', [PartidoController::class, 'registrarResultado'])->name('partidos.resultado');

    // Asignación de horarios
    Route::post('/partidos/{partido}/horario', [PartidoController::class, 'asignarHorario'])->name('partidos.horario.asignar');
    Route::post('/partidos/{partido}/asignar-horario', [PartidoController::class, 'asignarHorario'])->name('partidos.asignar-horario');

    // Partidos en grupos
    Route::post('/grupos/{grupo}/partidos', [PartidoController::class, 'store'])->name('grupos.partidos.store');

    /*
    |--------------------------------------------------------------------------
    | Categorías
    |--------------------------------------------------------------------------
    */
    Route::resource('categorias', CategoriaController::class);
    Route::post('/categorias/{categoria}/generar-cuartos', [CategoriaController::class, 'generarCuartosCategoria'])->name('categorias.generar-cuartos');
    Route::post('/categorias/{categoria}/generar-semifinales-directas', [CategoriaController::class, 'generarSemifinalesDirectasCategoria'])->name('categorias.generar-semifinales-directas');
    Route::post('/categorias/{categoria}/generar-final-directa', [CategoriaController::class, 'generarFinalDirectaCategoria'])->name('categorias.generar-final-directa');
    Route::post('/categorias/{categoria}/generar-siguiente-fase', [CategoriaController::class, 'generarSiguienteFaseCategoria'])->name('categorias.generar-siguiente-fase-categoria');

    // Gestión de categorías en torneos
    Route::get('/torneos/{torneo}/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::post('/torneos/{torneo}/categorias', [CategoriaController::class, 'store'])->name('categorias.store.torneo');
    Route::post('/torneos/{torneo}/categorias/generar', [CategoriaController::class, 'generarCategorias'])->name('categorias.generar');

    // Asignación de equipos a categorías
    Route::get('/categorias/{categoria}/asignar-equipos', [CategoriaController::class, 'asignarEquiposForm'])->name('categorias.asignar-equipos');
    Route::post('/categorias/{categoria}/asignar-equipos', [CategoriaController::class, 'asignarEquipos'])->name('categorias.asignar-equipos.store');
    Route::delete('/categorias/{categoria}/equipos/{equipo}', [CategoriaController::class, 'eliminarEquipo'])->name('categorias.eliminar-equipo');
    Route::post('/torneos/{torneo}/asignar-por-clasificacion', [CategoriaController::class, 'asignarPorClasificacion'])->name('categorias.asignar-por-clasificacion');

    // Reparto automático de categorías
    Route::get('/torneos/{torneo}/categorias/reparto', [CategoriaController::class, 'asignarCategoriasForm'])->name('categorias.reparto.form');
    Route::post('/torneos/{torneo}/categorias/reparto', [CategoriaController::class, 'repartirCategorias'])->name('categorias.reparto');
    Route::post('/torneos/{torneo}/categorias/limpiar-asignaciones', [CategoriaController::class, 'limpiarAsignaciones'])->name('categorias.limpiar-asignaciones');

    // **RUTAS PARA FASES ELIMINATORIAS PROGRESIVAS - CORREGIDAS**
    Route::post('/torneos/{torneo}/categorias/generar-siguiente-fase', [CategoriaController::class, 'generarSiguienteFase'])->name('categorias.generar-siguiente-fase');
    Route::post('/torneos/{torneo}/categorias/generar-cuartos', [CategoriaController::class, 'generarCuartosDeFinal'])->name('categorias.generar-cuartos');
    Route::post('/torneos/{torneo}/categorias/generar-semifinales', [CategoriaController::class, 'generarSemifinales'])->name('categorias.generar-semifinales');
    Route::post('/torneos/{torneo}/categorias/generar-final', [CategoriaController::class, 'generarFinal'])->name('categorias.generar-final');
    Route::get('/torneos/{torneo}/categorias/{categoria}/fase-actual', [CategoriaController::class, 'obtenerFaseActual'])->name('categorias.fase-actual');
    Route::get('/torneos/{torneo}/verificar-estado', [TorneoController::class, 'verificarEstado'])->name('torneos.verificar-estado');

    /*
    |--------------------------------------------------------------------------
    | Horarios
    |--------------------------------------------------------------------------
    */
    Route::resource('horarios', HorarioController::class);
    Route::get('/torneos/{torneo}/horarios', [HorarioController::class, 'index'])->name('horarios.index');
    Route::get('/torneos/{torneo}/horarios/crear', [HorarioController::class, 'create'])->name('horarios.create');
    Route::post('/torneos/{torneo}/horarios', [HorarioController::class, 'store'])->name('horarios.store');
    Route::delete('/horarios/{horario}', [HorarioController::class, 'destroy'])->name('horarios.destroy');
    Route::get('/torneos/{torneo}/horarios/calendario', [HorarioController::class, 'calendario'])->name('horarios.calendario');
    Route::get('/horarios/disponibles/{fecha}', [HorarioController::class, 'disponibles'])->name('horarios.disponibles');

    /*
    |--------------------------------------------------------------------------
    | Rutas de Desarrollo y Debug
    |--------------------------------------------------------------------------
    */
    Route::get('/debug/asignaciones', function () {
        return view('categorias.debug-asignaciones');
    })->name('debug.asignaciones');
});

require __DIR__.'/auth.php';
