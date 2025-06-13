<?php
namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Torneo;
use App\Models\Partido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EquipoController extends Controller
{
    public function index()
    {
        $equipos = Equipo::with('torneo')->orderBy('nombre')->get();
        return view('equipos.index', compact('equipos'));
    }

    public function create()
    {
        $torneos = Torneo::orderBy('nombre')->get();
        return view('equipos.create', compact('torneos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'torneo_id' => 'required|exists:torneos,id',
        ]);
        $equipoExistente = Equipo::where('torneo_id', $validated['torneo_id'])
            ->where('nombre', $validated['nombre'])
            ->first();

        if ($equipoExistente) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un equipo con ese nombre en este torneo'
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe un equipo con ese nombre en este torneo');
        }

        $equipo = Equipo::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'equipo' => $equipo,
                'message' => 'Equipo creado correctamente'
            ]);
        }

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo creado correctamente');
    }

    public function show(Equipo $equipo)
    {
        $equipo->load(['grupos.torneo', 'partidosLocal', 'partidosVisitante', 'torneo']);
        return view('equipos.show', compact('equipo'));
    }

    public function edit(Equipo $equipo)
    {
        $equipoOcupado = $this->verificarEquipoOcupado($equipo);
        $torneos = Torneo::orderBy('nombre')->get();

        return view('equipos.edit', compact('equipo', 'equipoOcupado', 'torneos'));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $equipoOcupado = $this->verificarEquipoOcupado($equipo);

        $validationRules = [
            'nombre' => 'required|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
        ];

        if (!$equipoOcupado) {
            $validationRules['torneo_id'] = 'required|exists:torneos,id';
        }

        $validated = $request->validate($validationRules);

        $torneoIdParaValidacion = $equipoOcupado ? $equipo->torneo_id : $validated['torneo_id'];

        $equipoExistente = Equipo::where('torneo_id', $torneoIdParaValidacion)
            ->where('nombre', $validated['nombre'])
            ->where('id', '!=', $equipo->id)
            ->first();

        if ($equipoExistente) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe un equipo con ese nombre en este torneo');
        }

        $updateData = [
            'nombre' => $validated['nombre'],
            'telefono_contacto' => $validated['telefono_contacto'],
        ];

        if (!$equipoOcupado && isset($validated['torneo_id'])) {
            $updateData['torneo_id'] = $validated['torneo_id'];
        }

        $equipo->update($updateData);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo actualizado correctamente');
    }

    public function destroy(Equipo $equipo)
    {
        if ($equipo->grupos()->exists()) {
            return redirect()->route('equipos.index')
                ->with('error', 'No se puede eliminar el equipo porque está asignado a uno o más grupos');
        }

        $equipo->delete();

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado correctamente');
    }

    private function verificarEquipoOcupado(Equipo $equipo)
    {
        $enGrupos = $equipo->grupos()->exists();

        $enPartidos = Partido::where('equipo_local_id', $equipo->id)
            ->orWhere('equipo_visitante_id', $equipo->id)
            ->exists();

        return $enGrupos || $enPartidos;
    }

    public function importForm()
    {
        $torneos = Torneo::orderBy('nombre')->get();
        return view('equipos.import', compact('torneos'));
    }

    public function importCSV(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt',
            'torneo_id' => 'required|exists:torneos,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $file = $request->file('csv_file');
        $torneoId = $request->input('torneo_id');
        $torneo = Torneo::find($torneoId);
        $equiposImportados = 0;
        $equiposActualizados = 0;
        $errores = [];
        $filaActual = 0;

        if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {
            if ($request->has('has_headers')) {
                fgetcsv($handle, 1000, ",");
                $filaActual = 1;
            }

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $filaActual++;

                if (empty(array_filter($data))) {
                    $errores[] = "Fila $filaActual: Fila vacía omitida";
                    continue;
                }

                if (empty(trim($data[0] ?? ''))) {
                    $errores[] = "Fila $filaActual: El nombre del equipo es requerido";
                    continue;
                }

                $nombreEquipo = trim($data[0]);
                $telefonoContacto = isset($data[1]) && !empty(trim($data[1])) ? trim($data[1]) : null;

                if (isset($data[2]) && !empty(trim($data[2]))) {
                    $nombreTorneoCSV = trim($data[2]);
                    if (strtolower($nombreTorneoCSV) !== strtolower($torneo->nombre)) {
                        $errores[] = "Fila $filaActual: El torneo '$nombreTorneoCSV' no coincide con el seleccionado '{$torneo->nombre}'";
                        continue;
                    }
                }

                try {
                    $equipo = Equipo::where('torneo_id', $torneoId)
                        ->where('nombre', $nombreEquipo)
                        ->first();

                    if ($equipo) {
                        if ((empty($equipo->telefono_contacto) && !empty($telefonoContacto)) ||
                            (!empty($telefonoContacto) && $request->has('actualizar_telefonos'))) {
                            $equipo->telefono_contacto = $telefonoContacto;
                            $equipo->save();
                            $equiposActualizados++;
                        } else {
                            $errores[] = "Fila $filaActual: El equipo '$nombreEquipo' ya existe en este torneo";
                        }
                    } else {
                        Equipo::create([
                            'nombre' => $nombreEquipo,
                            'telefono_contacto' => $telefonoContacto,
                            'torneo_id' => $torneoId,
                        ]);
                        $equiposImportados++;
                    }
                } catch (\Exception $e) {
                    $errores[] = "Fila $filaActual: Error al procesar equipo '$nombreEquipo' - " . $e->getMessage();
                }
            }
            fclose($handle);
        }

        $mensaje = "Proceso completado para el torneo '{$torneo->nombre}': ";
        $mensajeParts = [];

        if ($equiposImportados > 0) {
            $mensajeParts[] = "$equiposImportados equipos nuevos importados";
        }
        if ($equiposActualizados > 0) {
            $mensajeParts[] = "$equiposActualizados equipos existentes actualizados";
        }
        if (count($errores) > 0) {
            $mensajeParts[] = count($errores) . " errores encontrados";
        }

        if (empty($mensajeParts)) {
            $mensaje .= "No se procesaron cambios";
        } else {
            $mensaje .= implode(', ', $mensajeParts);
        }

        if (count($errores) > 0) {
            return redirect()->route('equipos.index')
                ->with('success', $mensaje)
                ->with('import_errors', $errores);
        }

        return redirect()->route('equipos.index')->with('success', $mensaje);
    }
}
