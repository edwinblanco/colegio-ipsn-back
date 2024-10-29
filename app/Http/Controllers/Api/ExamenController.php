<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Examen;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Pregunta;

class ExamenController extends Controller
{

    // Mostrar una lista de exámenes
    public function index()
    {
        try {
            $examenes = Examen::with(['materia', 'profesor'])->get();
            return response()->json([
                'status' => 1,
                'msg' => 'Exámenes recuperados exitosamente.',
                'data' => $examenes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al recuperar los exámenes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mostrar el formulario para crear un nuevo examen
    public function create()
    {
        try {
            $materias = Materia::all();
            $profesores = User::where('role', 'profesor')->get();
            return response()->json([
                'status' => 1,
                'msg' => 'Materias y profesores recuperados exitosamente.',
                'data' => [
                    'materias' => $materias,
                    'profesores' => $profesores,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al recuperar materias o profesores.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Almacenar un nuevo examen
    public function store(Request $request)
    {
        try {
            $request->validate([
                'materia_id' => 'nullable|exists:materias,id',
                'profesor_id' => 'nullable|exists:users,id',
                'titulo' => 'required|string|max:255',
                //'descripcion' => 'required|string',
                'fecha_limite' => 'required|date',
                'estado' => 'required|in:activo,cerrado',
            ]);

            $examen = Examen::create($request->all());

            return response()->json([
                'status' => 1,
                'msg' => 'Examen creado exitosamente.',
                'data' => $examen,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al crear el examen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $materiaId)
    {
        try {
            // Obtener el ID del usuario en sesión utilizando Auth
            $userId = Auth::id();

            // Recuperar los exámenes relacionados con la materia y el usuario
            $examenes = Examen::where('materia_id', $materiaId)
                ->where('profesor_id', $userId)
                ->with('materia') // Cargar la relación 'materia'
                ->get();

            // Verificar si se encontraron exámenes
            if ($examenes->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'No se encontraron exámenes para esta materia.',
                ], 404);
            }

            return response()->json([
                'status' => 1,
                'msg' => 'Exámenes recuperados exitosamente.',
                'data' => $examenes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al recuperar los exámenes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function ver_examenes_estudiante(Request $request, $materiaId)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Obtener el grado del usuario autenticado
            $gradoId = $user->grado ? $user->grado->id : null;

            // Verificar si el usuario tiene un grado asignado
            if (!$gradoId) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'El estudiante no tiene un grado asignado.',
                ], 404);
            }

            // Obtener exámenes que correspondan a la materia y al grado del usuario
            $examenes = Examen::where('materia_id', $materiaId)
                ->whereHas('grados', function ($query) use ($gradoId) {
                    $query->where('grado_id', $gradoId);
                })->get();

            // Retornar los exámenes encontrados
            return response()->json([
                'status' => 1,
                'msg' => 'Exámenes recuperados con éxito.',
                'data' => $examenes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al recuperar los exámenes asociados.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtener_examen_con_preguntas_y_opciones($examenId)
    {
        try {
            // Cargar el examen junto con las preguntas y sus opciones
            $examen = Examen::with(['preguntas.opciones'])
                ->findOrFail($examenId);

            return response()->json([
                'status' => 1,
                'msg' => 'Examen recuperado con éxito.',
                'data' => $examen,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al recuperar el examen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtener_pregunta($examenId, $preguntaIndex) {
        $pregunta = Pregunta::where('examen_id', $examenId)
                            ->orderBy('id') // Asegúrate de tener un orden correcto
                            ->skip($preguntaIndex)
                            ->take(1)
                            ->with('opciones') // Cargar las opciones de la pregunta
                            ->first();

        if ($pregunta) {
            return response()->json([
                'status' => 1,
                'msg' => 'Pregunta encontrada',
                'data' => $pregunta,
            ], 200);
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Pregunta no encontrada',
            'data' => [],
        ], 404);

    }

    // Mostrar el formulario para editar un examen
    public function edit(Examen $examen)
    {
        try {
            $materias = Materia::all();
            $profesores = User::where('role', 'profesor')->get();
            return response()->json([
                'status' => 1,
                'msg' => 'Examen y opciones recuperados exitosamente.',
                'data' => [
                    'examen' => $examen,
                    'materias' => $materias,
                    'profesores' => $profesores,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al recuperar datos para editar el examen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Actualizar un examen existente
    public function update(Request $request, Examen $examen)
    {
        try {
            $request->validate([
                'materia_id' => 'nullable|exists:materias,id',
                'profesor_id' => 'nullable|exists:users,id',
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'fecha_limite' => 'required|date',
                'estado' => 'required|in:activo,cerrado',
            ]);

            $examen->update($request->all());

            return response()->json([
                'status' => 1,
                'msg' => 'Examen actualizado exitosamente.',
                'data' => $examen,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al actualizar el examen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Eliminar un examen
    public function destroy(Examen $examen)
    {
        try {
            $examen->delete();

            return response()->json([
                'status' => 1,
                'msg' => 'Examen eliminado exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al eliminar el examen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function asignar_examen_a_grado(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'examen_id' => 'required|exists:examenes,id',
            'grado_id' => 'required|exists:grados,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Validación fallida.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Obtener el examen
            $examen = Examen::with('preguntas.opciones')->findOrFail($request->examen_id);

            // Verificar si el examen ya está asignado al grado
            if ($examen->grados()->where('grado_id', $request->grado_id)->exists()) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'El examen ya está asignado a este grado.',
                ], 409); // Conflict
            }

            // Validación de la suma de valores de las preguntas
            $valorTotal = $examen->preguntas->sum('valor');
            if ($valorTotal !== 100) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'La suma de los valores de las preguntas debe ser 100%.',
                ], 422);
            }

            // Validación de cada pregunta
            foreach ($examen->preguntas as $pregunta) {
                $opciones = $pregunta->opciones;

                // Verificar que cada pregunta tenga al menos dos opciones
                if ($opciones->count() < 2) {

                    $resumen_pregunta = Str::limit($pregunta->contenido, 30);
                    return response()->json([
                        'status' => 0,
                        'msg' => "La pregunta '{$resumen_pregunta}' debe tener al menos dos opciones.",
                    ], 422);
                }

                // Verificar que haya al menos una opción correcta
                $correcta = $opciones->where('correcta', true)->count();
                if ($correcta < 1) {

                    $resumen_pregunta = Str::limit($pregunta->contenido, 30);
                    return response()->json([
                        'status' => 0,
                        'msg' => "La pregunta '{$resumen_pregunta }' debe tener al menos una opción correcta.",
                    ], 422);
                }
            }

            // Asignar el examen al grado
            $examen->grados()->attach($request->grado_id, ['fecha_asignacion' => now()]);

            return response()->json([
                'status' => 1,
                'msg' => 'Examen asignado al grado exitosamente.',
                'data' => $examen,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al asignar el examen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function grados_asignados($examen_id)
    {
        // Verificar si el examen existe
        $examen = Examen::find($examen_id);
        if (!$examen) {
            return response()->json([
                'status' => 0,
                'msg' => 'Examen no encontrado.',
            ], 404);
        }

        // Obtener los grados asignados al examen
        $grados = $examen->grados; // Asegúrate de tener la relación definida en el modelo Examen

        if ($grados->isEmpty()) {
            return response()->json([
                'status' => 0,
                'msg' => 'No hay grados asignados a este examen.',
            ], 200);
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Grados encontrados para el examen.',
            'data' => $grados,
        ], 200);
    }

    public function eliminar_asignacion(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'examen_id' => 'required|exists:examenes,id',
            'grado_id' => 'required|exists:grados,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Validación fallida.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Obtener el examen
            $examen = Examen::findOrFail($request->examen_id);

            // Eliminar la asignación del examen al grado
            $examen->grados()->detach($request->grado_id);

            return response()->json([
                'status' => 1,
                'msg' => 'Asignación eliminada exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al eliminar la asignación.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
