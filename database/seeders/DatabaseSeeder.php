<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Torneo;
use App\Models\Grupo;
use App\Models\Equipo;
use App\Models\EquipoGrupo;
use App\Models\Partido;
use App\Models\Set;
use App\Models\Categoria;
use App\Models\EquipoCategoria;
use App\Models\CategoriaPartido;
use App\Models\Horario;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeder del Sistema de Gestión de Torneos...');

        // Deshabilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->truncateTables();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Crear datos base
        $this->crearUsuarios();
        $torneos = $this->crearTorneos();

        // Procesar cada torneo
        foreach ($torneos as $torneo) {
            $this->command->info("📋 Procesando torneo: {$torneo->nombre}");

            $equipos = $this->crearEquipos($torneo);
            $grupos = $this->crearGrupos($torneo);
            $this->asignarEquiposAGrupos($grupos, $equipos);
            $partidos = $this->crearPartidos($grupos);
            $this->registrarResultados($partidos, $torneo);
            $categorias = $this->crearCategorias($torneo);

            // Solo generar eliminatorias para torneos en curso o finalizados
            if (in_array($torneo->estado, ['en_curso', 'finalizado'])) {
                $this->generarSistemaEliminatorias($torneo, $categorias);
            }

            $this->crearHorarios($torneo);
        }

        $this->command->info('✅ ¡Seeder completado con éxito!');
        $this->mostrarResumen();
    }

    private function truncateTables(): void
    {
        $this->command->info('🗑️ Limpiando base de datos...');

        $tablas = [
            'categoria_partido',
            'equipo_categoria',
            'categorias',
            'sets',
            'partidos',
            'equipo_grupo',
            'equipos',
            'grupos',
            'horarios',
            'torneos',
            'users'
        ];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                DB::table($tabla)->truncate();
            }
        }
    }

    private function crearUsuarios(): void
    {
        $this->command->info('👥 Creando usuarios...');

        $usuarios = [
            [
                'name' => 'Administrador Principal',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Carlos Organizador',
                'email' => 'carlos@example.com',
                'password' => Hash::make('password'),
                'role' => 'organizador',
            ],
            [
                'name' => 'María Coordinadora',
                'email' => 'maria@torneo.com',
                'password' => Hash::make('coordinadora123'),
                'role' => 'organizador',
            ],
            [
                'name' => 'Juan Supervisor',
                'email' => 'juan@torneo.com',
                'password' => Hash::make('supervisor123'),
                'role' => 'organizador',
            ]
        ];

        foreach ($usuarios as $userData) {
            User::create(array_merge($userData, [
                'email_verified_at' => now(),
            ]));
        }
    }

    private function crearTorneos(): array
    {
        $this->command->info('🏆 Creando torneos...');

        $torneos = [];

        // Torneo 4x4 2025 (preparación)
        $torneos[] = Torneo::create([
            'nombre' => 'Torneo 4x4 2025',
            'fecha_inicio' => Carbon::now()->addMonth(),
            'fecha_fin' => Carbon::now()->addMonths(2),
            'num_grupos' => 8,
            'num_categorias' => 2,
            'estado' => 'preparacion',
        ]);

        // Liga Primavera 2024 (en curso)
        $torneos[] = Torneo::create([
            'nombre' => 'Liga Primavera 2024',
            'fecha_inicio' => Carbon::now()->subDays(30),
            'fecha_fin' => Carbon::now()->addDays(15),
            'num_grupos' => 6,
            'num_categorias' => 3,
            'estado' => 'en_curso',
        ]);

        // Campeonato Verano 2023 (finalizado)
        $torneos[] = Torneo::create([
            'nombre' => 'Campeonato Verano 2023',
            'fecha_inicio' => Carbon::now()->subMonths(6),
            'fecha_fin' => Carbon::now()->subMonths(5),
            'num_grupos' => 4,
            'num_categorias' => 2,
            'estado' => 'finalizado',
        ]);

        return $torneos;
    }

    private function crearEquipos(Torneo $torneo): array
    {
        $this->command->info("⚽ Creando equipos para: {$torneo->nombre}");

        $nombresEquiposPorTorneo = [
            'Torneo 4x4 2025' => [
                'Águilas Doradas', 'Leones Rojos', 'Tigres Blancos', 'Panteras Negras',
                'Halcones Azules', 'Toros Bravos', 'Lobos Grises', 'Osos Pardos',
                'Delfines Plateados', 'Tiburones Feroces', 'Dragones Verdes', 'Serpientes Venenosas',
                'Búhos Nocturnos', 'Zorros Astutos', 'Gacelas Veloces', 'Rinocerontes Fuertes',
                'Elefantes Poderosos', 'Cóndores Altos', 'Jaguares Manchados', 'Cocodrilos Acuáticos',
                'Escorpiones Letales', 'Murciélagos Oscuros', 'Caballos Salvajes', 'Gorilas Montañeses',
                'Pumas Veloces', 'Cobras Venenosas', 'Orcas Marinas', 'Buitres Negros',
                'Linces Salvajes', 'Hipopótamos Gigantes', 'Mantarrayas Azules', 'Canguros Saltarines',
                'Flamencos Rosados', 'Nutrias Juguetonas', 'Mapaches Traviesos', 'Koalas Dormilones',
                'Pingüinos Antárticos', 'Lemures Saltarines', 'Suricatas Vigilantes', 'Tapires Robustos',
                'Armadillos Blindados', 'Perezosos Lentos', 'Tucanes Coloridos', 'Quetzales Sagrados',
                'Jaguarundis Ágiles', 'Ocelotes Manchados', 'Margays Trepadores', 'Pizotes Curiosos'
            ],
            'Liga Primavera 2024' => [
                'Real Madrid CF', 'FC Barcelona', 'Atlético Madrid', 'Valencia CF',
                'Sevilla FC', 'Real Betis', 'Athletic Bilbao', 'Real Sociedad',
                'Villarreal CF', 'Celta de Vigo', 'RCD Espanyol', 'Getafe CF',
                'Levante UD', 'Deportivo Alavés', 'CA Osasuna', 'Granada CF',
                'SD Huesca', 'Real Valladolid', 'Cádiz CF', 'SD Eibar',
                'Rayo Vallecano', 'Elche CF', 'Mallorca', 'Girona FC',
                'Real Oviedo', 'Sporting Gijón', 'Racing Santander', 'Real Zaragoza',
                'CD Tenerife', 'UD Las Palmas', 'CD Mirandés', 'FC Cartagena',
                'Real Sociedad B', 'FC Barcelona B', 'Villarreal B', 'Atlético Madrid B'
            ],
            'Campeonato Verano 2023' => [
                'Juventus FC', 'AC Milan', 'Inter Milan', 'AS Roma',
                'SSC Napoli', 'SS Lazio', 'Atalanta BC', 'ACF Fiorentina',
                'Torino FC', 'UC Sampdoria', 'Bologna FC', 'US Sassuolo',
                'Udinese Calcio', 'Hellas Verona', 'Cagliari Calcio', 'Genoa CFC',
                'Spezia Calcio', 'Venezia FC', 'Salernitana', 'Empoli FC',
                'US Lecce', 'AC Monza', 'Cremonese', 'US Palermo'
            ]
        ];

        $nombresEquipos = $nombresEquiposPorTorneo[$torneo->nombre] ?? [];
        $equipos = [];
        $equiposNecesarios = $torneo->num_grupos * 6; // 6 equipos por grupo

        for ($i = 0; $i < $equiposNecesarios; $i++) {
            $nombre = $nombresEquipos[$i] ?? "Equipo " . chr(65 + $i);

            $equipos[] = Equipo::create([
                'torneo_id' => $torneo->id,
                'nombre' => $nombre,
                'telefono_contacto' => '+34 ' . rand(600, 699) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
            ]);
        }

        return $equipos;
    }

    private function crearGrupos(Torneo $torneo): array
    {
        $this->command->info("📊 Creando grupos para: {$torneo->nombre}");

        $grupos = [];
        for ($i = 1; $i <= $torneo->num_grupos; $i++) {
            $grupos[] = Grupo::create([
                'torneo_id' => $torneo->id,
                'nombre' => 'Grupo ' . chr(64 + $i), // A, B, C, etc.
            ]);
        }

        return $grupos;
    }

    private function asignarEquiposAGrupos(array $grupos, array $equipos): void
    {
        $this->command->info("🔄 Asignando equipos a grupos...");

        $equipoIndex = 0;
        foreach ($grupos as $grupo) {
            for ($i = 0; $i < 6; $i++) {
                if ($equipoIndex >= count($equipos)) break;

                EquipoGrupo::create([
                    'equipo_id' => $equipos[$equipoIndex]->id,
                    'grupo_id' => $grupo->id,
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'partidos_ganados_2_0' => 0,
                    'partidos_ganados_2_1' => 0,
                    'partidos_perdidos_0_2' => 0,
                    'partidos_perdidos_1_2' => 0,
                    'no_presentados' => 0,
                    'sets_favor' => 0,
                    'sets_contra' => 0,
                    'puntos_favor' => 0,
                    'puntos_contra' => 0,
                    'posicion' => $i + 1,
                    'eliminado' => false,
                ]);

                $equipoIndex++;
            }
        }
    }

    private function crearPartidos(array $grupos): array
    {
        $this->command->info("⚽ Generando partidos de grupos...");

        $partidos = [];
        foreach ($grupos as $grupo) {
            $equiposGrupo = EquipoGrupo::where('grupo_id', $grupo->id)->get();

            // Generar todos los enfrentamientos posibles (round-robin)
            for ($i = 0; $i < count($equiposGrupo); $i++) {
                for ($j = $i + 1; $j < count($equiposGrupo); $j++) {
                    $partidos[] = Partido::create([
                        'grupo_id' => $grupo->id,
                        'equipo_local_id' => $equiposGrupo[$i]->equipo_id,
                        'equipo_visitante_id' => $equiposGrupo[$j]->equipo_id,
                        'fecha' => Carbon::now()->addDays(rand(1, 30))->addHours(rand(9, 18)),
                        'completado' => false,
                        'resultado_local' => null,
                        'resultado_visitante' => null,
                        'puntos_local' => null,
                        'puntos_visitante' => null,
                    ]);
                }
            }
        }

        return $partidos;
    }

    private function registrarResultados(array $partidos, Torneo $torneo): void
    {
        $this->command->info("📊 Registrando resultados...");

        // Completar diferentes porcentajes según el estado del torneo
        $porcentajeCompletado = match($torneo->estado) {
            'preparacion' => 0,
            'en_curso' => 0.8,
            'finalizado' => 1.0,
            default => 0.7
        };

        $partidosACompletar = array_slice($partidos, 0, (int)(count($partidos) * $porcentajeCompletado));

        foreach ($partidosACompletar as $partido) {
            $this->simularPartido($partido);
        }

        // Actualizar posiciones finales para todos los grupos
        foreach (Grupo::all() as $grupo) {
            $this->actualizarPosicionesGrupo($grupo->id);
        }
    }

    private function simularPartido(Partido $partido): void
    {
        // Posibles resultados de sets
        $resultadosPosibles = [
            [2, 0], [2, 1], [1, 2], [0, 2]
        ];

        $resultado = $resultadosPosibles[array_rand($resultadosPosibles)];
        $totalSets = $resultado[0] + $resultado[1];
        $puntosLocalTotal = 0;
        $puntosVisitanteTotal = 0;

        // Generar sets
        for ($set = 1; $set <= $totalSets; $set++) {
            $ganadorSet = ($set <= $resultado[0]) ? 'local' : 'visitante';

            if ($ganadorSet === 'local') {
                $puntosLocal = rand(21, 25);
                $puntosVisitante = rand(15, 20);
            } else {
                $puntosLocal = rand(15, 20);
                $puntosVisitante = rand(21, 25);
            }

            Set::create([
                'partido_id' => $partido->id,
                'numero_set' => $set,
                'puntos_local' => $puntosLocal,
                'puntos_visitante' => $puntosVisitante,
            ]);

            $puntosLocalTotal += $puntosLocal;
            $puntosVisitanteTotal += $puntosVisitante;
        }

        // Actualizar partido
        $partido->update([
            'resultado_local' => $resultado[0],
            'resultado_visitante' => $resultado[1],
            'puntos_local' => $puntosLocalTotal,
            'puntos_visitante' => $puntosVisitanteTotal,
            'completado' => true,
        ]);

        // Actualizar estadísticas de equipos
        $this->actualizarEstadisticasEquipos($partido);
    }

    private function actualizarEstadisticasEquipos(Partido $partido): void
    {
        $equipoLocal = EquipoGrupo::where('equipo_id', $partido->equipo_local_id)
            ->where('grupo_id', $partido->grupo_id)
            ->first();

        $equipoVisitante = EquipoGrupo::where('equipo_id', $partido->equipo_visitante_id)
            ->where('grupo_id', $partido->grupo_id)
            ->first();

        $resultadoLocal = $partido->resultado_local;
        $resultadoVisitante = $partido->resultado_visitante;

        // Calcular puntos según el resultado
        [$puntosLocal, $puntosVisitante] = match([$resultadoLocal, $resultadoVisitante]) {
            [2, 0] => [3, 0],
            [2, 1] => [2, 1],
            [1, 2] => [1, 2],
            [0, 2] => [0, 3],
            default => [0, 0]
        };

        // Actualizar equipo local
        $equipoLocal->update([
            'puntos' => $equipoLocal->puntos + $puntosLocal,
            'partidos_jugados' => $equipoLocal->partidos_jugados + 1,
            'sets_favor' => $equipoLocal->sets_favor + $resultadoLocal,
            'sets_contra' => $equipoLocal->sets_contra + $resultadoVisitante,
            'puntos_favor' => $equipoLocal->puntos_favor + $partido->puntos_local,
            'puntos_contra' => $equipoLocal->puntos_contra + $partido->puntos_visitante,
            'partidos_ganados_2_0' => $equipoLocal->partidos_ganados_2_0 + (($resultadoLocal == 2 && $resultadoVisitante == 0) ? 1 : 0),
            'partidos_ganados_2_1' => $equipoLocal->partidos_ganados_2_1 + (($resultadoLocal == 2 && $resultadoVisitante == 1) ? 1 : 0),
            'partidos_perdidos_1_2' => $equipoLocal->partidos_perdidos_1_2 + (($resultadoLocal == 1 && $resultadoVisitante == 2) ? 1 : 0),
            'partidos_perdidos_0_2' => $equipoLocal->partidos_perdidos_0_2 + (($resultadoLocal == 0 && $resultadoVisitante == 2) ? 1 : 0),
        ]);

        // Actualizar equipo visitante
        $equipoVisitante->update([
            'puntos' => $equipoVisitante->puntos + $puntosVisitante,
            'partidos_jugados' => $equipoVisitante->partidos_jugados + 1,
            'sets_favor' => $equipoVisitante->sets_favor + $resultadoVisitante,
            'sets_contra' => $equipoVisitante->sets_contra + $resultadoLocal,
            'puntos_favor' => $equipoVisitante->puntos_favor + $partido->puntos_visitante,
            'puntos_contra' => $equipoVisitante->puntos_contra + $partido->puntos_local,
            'partidos_ganados_2_0' => $equipoVisitante->partidos_ganados_2_0 + (($resultadoVisitante == 2 && $resultadoLocal == 0) ? 1 : 0),
            'partidos_ganados_2_1' => $equipoVisitante->partidos_ganados_2_1 + (($resultadoVisitante == 2 && $resultadoLocal == 1) ? 1 : 0),
            'partidos_perdidos_1_2' => $equipoVisitante->partidos_perdidos_1_2 + (($resultadoVisitante == 1 && $resultadoLocal == 2) ? 1 : 0),
            'partidos_perdidos_0_2' => $equipoVisitante->partidos_perdidos_0_2 + (($resultadoVisitante == 0 && $resultadoLocal == 2) ? 1 : 0),
        ]);
    }

    private function actualizarPosicionesGrupo(int $grupoId): void
    {
        $equipos = EquipoGrupo::where('grupo_id', $grupoId)
            ->orderByRaw('puntos DESC, sets_favor DESC, sets_contra ASC, puntos_favor DESC, puntos_contra ASC, (puntos_favor - puntos_contra) DESC')
            ->get();

        foreach ($equipos as $index => $equipo) {
            $equipo->update(['posicion' => $index + 1]);
        }
    }

    private function crearCategorias(Torneo $torneo): array
    {
        $this->command->info("🏅 Creando categorías para: {$torneo->nombre}");

        $categorias = [];
        $nombresCategorias = ['Oro', 'Plata', 'Bronce'];

        for ($i = 1; $i <= $torneo->num_categorias; $i++) {
            $nombre = $nombresCategorias[$i - 1] ?? "Categoría $i";

            $categorias[] = Categoria::create([
                'torneo_id' => $torneo->id,
                'nombre' => $nombre,
                'descripcion' => "Categoría $nombre del torneo {$torneo->nombre}",
            ]);
        }

        return $categorias;
    }

    private function generarSistemaEliminatorias(Torneo $torneo, array $categorias): void
    {
        $this->command->info("🏆 Generando sistema de eliminatorias para: {$torneo->nombre}");

        // Simular reparto automático de categorías
        $this->simularRepartoAutomatico($torneo, $categorias);

        // Generar partidos eliminatorios
        $this->generarPartidosEliminatorios($torneo, $categorias);

        // Si el torneo está finalizado, simular algunos resultados
        if ($torneo->estado === 'finalizado') {
            $this->simularResultadosEliminatorios($categorias);
        }
    }

    private function simularRepartoAutomatico(Torneo $torneo, array $categorias): void
    {
        $grupos = Grupo::where('torneo_id', $torneo->id)->get();

        // Obtener equipos por posición
        $equiposPorPosicion = [];
        foreach ($grupos as $grupo) {
            $equiposGrupo = EquipoGrupo::where('grupo_id', $grupo->id)
                ->orderBy('posicion')
                ->get();

            foreach ($equiposGrupo as $index => $equipoGrupo) {
                $posicion = $index + 1;
                if (!isset($equiposPorPosicion[$posicion])) {
                    $equiposPorPosicion[$posicion] = [];
                }
                $equiposPorPosicion[$posicion][] = $equipoGrupo;
            }
        }

        // Ordenar equipos dentro de cada posición
        foreach ($equiposPorPosicion as $posicion => $equipos) {
            usort($equiposPorPosicion[$posicion], function($a, $b) {
                if ($a->puntos != $b->puntos) return $b->puntos <=> $a->puntos;
                if ($a->sets_favor != $b->sets_favor) return $b->sets_favor <=> $a->sets_favor;
                if ($a->sets_contra != $b->sets_contra) return $a->sets_contra <=> $b->sets_contra;
                if ($a->puntos_favor != $b->puntos_favor) return $b->puntos_favor <=> $a->puntos_favor;
                if ($a->puntos_contra != $b->puntos_contra) return $a->puntos_contra <=> $b->puntos_contra;
                return ($b->puntos_favor - $b->puntos_contra) <=> ($a->puntos_favor - $a->puntos_contra);
            });
        }

        // Realizar reparto según número de categorías
        $asignaciones = $this->calcularAsignacionesCategorias($equiposPorPosicion, count($categorias));

        // Guardar asignaciones
        foreach ($asignaciones as $catIndex => $equiposCategoria) {
            $categoria = $categorias[$catIndex];
            foreach ($equiposCategoria as $index => $equipoGrupo) {
                EquipoCategoria::create([
                    'equipo_grupo_id' => $equipoGrupo->id,
                    'categoria_id' => $categoria->id,
                    'posicion' => $index + 1,
                ]);
            }
        }
    }

    private function calcularAsignacionesCategorias(array $equiposPorPosicion, int $numCategorias): array
    {
        $asignaciones = array_fill(0, $numCategorias, []);

        if ($numCategorias == 2) {
            $primeros = $equiposPorPosicion[1] ?? [];
            $segundos = $equiposPorPosicion[2] ?? [];
            $terceros = $equiposPorPosicion[3] ?? [];

            $asignaciones[0] = array_merge(
                array_slice($primeros, 0, 6),
                array_slice($segundos, 0, 2)
            );

            $asignaciones[1] = array_merge(
                array_slice($segundos, 2, 6),
                array_slice($terceros, 0, 2)
            );

        } elseif ($numCategorias == 3) {
            $primeros = $equiposPorPosicion[1] ?? [];
            $segundos = $equiposPorPosicion[2] ?? [];
            $terceros = $equiposPorPosicion[3] ?? [];
            $cuartos = $equiposPorPosicion[4] ?? [];

            $asignaciones[0] = array_merge(
                array_slice($primeros, 0, 6),
                array_slice($segundos, 0, 2)
            );

            $asignaciones[1] = array_merge(
                array_slice($segundos, 2, 6),
                array_slice($terceros, 0, 2)
            );

            $asignaciones[2] = array_merge(
                array_slice($terceros, 2, 6),
                array_slice($cuartos, 0, 2)
            );
        }

        return $asignaciones;
    }

    private function generarPartidosEliminatorios(Torneo $torneo, array $categorias): void
    {
        $fechaBase = Carbon::parse($torneo->fecha_fin)->addDay();

        foreach ($categorias as $categoria) {
            $equiposCategoria = EquipoCategoria::where('categoria_id', $categoria->id)
                ->orderBy('posicion')
                ->get();

            if ($equiposCategoria->count() != 8) continue;

            $equipos = $equiposCategoria->map(function($ec) {
                return [
                    'id' => $ec->equipoGrupo->equipo_id,
                    'posicion' => $ec->posicion
                ];
            })->toArray();

            $this->generarCuartos($categoria, $equipos, $fechaBase);
            $this->generarSemifinales($categoria, $fechaBase);
            $this->generarFinal($categoria, $fechaBase);
        }
    }

    private function generarCuartos(Categoria $categoria, array $equipos, Carbon $fechaBase): void
    {
        $cruces = [
            ['local' => $equipos[0], 'visitante' => $equipos[7], 'numero' => 'QF1', 'hora' => 9],
            ['local' => $equipos[3], 'visitante' => $equipos[4], 'numero' => 'QF2', 'hora' => 11],
            ['local' => $equipos[1], 'visitante' => $equipos[6], 'numero' => 'QF3', 'hora' => 13],
            ['local' => $equipos[2], 'visitante' => $equipos[5], 'numero' => 'QF4', 'hora' => 15],
        ];

        foreach ($cruces as $cruce) {
            $fechaPartido = $fechaBase->copy()->setTime($cruce['hora'], 0);

            $partido = Partido::create([
                'grupo_id' => null,
                'equipo_local_id' => $cruce['local']['id'],
                'equipo_visitante_id' => $cruce['visitante']['id'],
                'fecha' => $fechaPartido,
                'completado' => false,
            ]);

            CategoriaPartido::create([
                'categoria_id' => $categoria->id,
                'partido_id' => $partido->id,
                'fase' => 'cuartos',
                'numero_partido' => $cruce['numero'],
                'dependencias' => null,
            ]);
        }
    }

    private function generarSemifinales(Categoria $categoria, Carbon $fechaBase): void
    {
        $semifinales = [
            ['numero' => 'SF1', 'dependencias' => ['ganador_QF1', 'ganador_QF2'], 'hora' => 10],
            ['numero' => 'SF2', 'dependencias' => ['ganador_QF3', 'ganador_QF4'], 'hora' => 12],
        ];

        foreach ($semifinales as $semi) {
            $fechaPartido = $fechaBase->copy()->addDay()->setTime($semi['hora'], 0);

            $partido = Partido::create([
                'grupo_id' => null,
                'equipo_local_id' => null,
                'equipo_visitante_id' => null,
                'fecha' => $fechaPartido,
                'completado' => false,
            ]);

            CategoriaPartido::create([
                'categoria_id' => $categoria->id,
                'partido_id' => $partido->id,
                'fase' => 'semifinal',
                'numero_partido' => $semi['numero'],
                'dependencias' => $semi['dependencias'],
            ]);
        }
    }

    private function generarFinal(Categoria $categoria, Carbon $fechaBase): void
    {
        $fechaFinal = $fechaBase->copy()->addDays(2)->setTime(11, 0);

        $partido = Partido::create([
            'grupo_id' => null,
            'equipo_local_id' => null,
            'equipo_visitante_id' => null,
            'fecha' => $fechaFinal,
            'completado' => false,
        ]);

        CategoriaPartido::create([
            'categoria_id' => $categoria->id,
            'partido_id' => $partido->id,
            'fase' => 'final',
            'numero_partido' => 'F',
            'dependencias' => ['ganador_SF1', 'ganador_SF2'],
        ]);
    }

    private function simularResultadosEliminatorios(array $categorias): void
    {
        foreach ($categorias as $categoria) {
            // Simular cuartos
            $cuartos = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'cuartos')
                ->with('partido')
                ->get();

            $ganadoresCuartos = [];
            foreach ($cuartos as $cuarto) {
                $this->simularPartido($cuarto->partido);
                $ganadoresCuartos[$cuarto->numero_partido] = $cuarto->partido->ganador;
            }

            // Actualizar semifinales con ganadores
            $semifinales = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'semifinal')
                ->with('partido')
                ->get();

            $ganadoresSemifinales = [];
            foreach ($semifinales as $semifinal) {
                $dependencias = $semifinal->dependencias;
                if (count($dependencias) == 2) {
                    $equipoLocal = $ganadoresCuartos[str_replace('ganador_', '', $dependencias[0])] ?? null;
                    $equipoVisitante = $ganadoresCuartos[str_replace('ganador_', '', $dependencias[1])] ?? null;

                    if ($equipoLocal && $equipoVisitante) {
                        $semifinal->partido->update([
                            'equipo_local_id' => $equipoLocal->id,
                            'equipo_visitante_id' => $equipoVisitante->id,
                        ]);

                        $this->simularPartido($semifinal->partido);
                        $ganadoresSemifinales[$semifinal->numero_partido] = $semifinal->partido->ganador;
                    }
                }
            }

            // Actualizar final con ganadores
            $final = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'final')
                ->with('partido')
                ->first();

            if ($final && count($ganadoresSemifinales) == 2) {
                $ganadores = array_values($ganadoresSemifinales);
                $final->partido->update([
                    'equipo_local_id' => $ganadores[0]->id,
                    'equipo_visitante_id' => $ganadores[1]->id,
                ]);

                $this->simularPartido($final->partido);
            }
        }
    }

    private function crearHorarios(Torneo $torneo): void
    {
        $this->command->info("⏰ Creando horarios para: {$torneo->nombre}");

        $pistas = ['Pista Central', 'Pista A', 'Pista B', 'Pista C'];
        $fechaInicio = Carbon::parse($torneo->fecha_inicio);
        $fechaFin = Carbon::parse($torneo->fecha_fin);

        $fecha = $fechaInicio->copy();
        while ($fecha->lte($fechaFin)) {
            for ($hora = 9; $hora <= 21; $hora += 2) {
                foreach ($pistas as $pista) {
                    Horario::create([
                        'torneo_id' => $torneo->id,
                        'fecha' => $fecha->copy()->setTime($hora, 0),
                        'pista' => $pista,
                    ]);
                }
            }
            $fecha->addDay();
        }
    }

    private function mostrarResumen(): void
    {
        $this->command->info('');
        $this->command->info('📊 RESUMEN DE DATOS CREADOS:');
        $this->command->info('================================');
        $this->command->info('👥 Usuarios: ' . User::count());
        $this->command->info('🏆 Torneos: ' . Torneo::count());
        $this->command->info('📊 Grupos: ' . Grupo::count());
        $this->command->info('⚽ Equipos: ' . Equipo::count());
        $this->command->info('🥅 Partidos de grupo: ' . Partido::whereNotNull('grupo_id')->count());
        $this->command->info('🏅 Partidos eliminatorios: ' . Partido::whereNull('grupo_id')->count());
        $this->command->info('🏆 Categorías: ' . Categoria::count());
        $this->command->info('⏰ Horarios: ' . Horario::count());
        $this->command->info('');
        $this->command->info('🔑 CREDENCIALES DE ACCESO:');
        $this->command->info('Admin: admin@torneo.com / admin123');
        $this->command->info('Organizador: carlos@torneo.com / organizador123');
        $this->command->info('');
    }
}
