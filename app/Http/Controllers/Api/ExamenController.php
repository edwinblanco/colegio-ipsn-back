<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Examen;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            $examen = Examen::findOrFail($request->examen_id);

            // Verificar si ya está asignado el examen a este grado
            if ($examen->grados()->where('grado_id', $request->grado_id)->exists()) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'El examen ya está asignado a este grado.',
                ], 409); // Conflict
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
