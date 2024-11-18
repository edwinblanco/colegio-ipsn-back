<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grado;
use Illuminate\Support\Facades\Validator;

class GradoController extends Controller
{
    /**
     * Obtener todos los grados.
     */
    public function index()
    {
        try {
            $grados = Grado::all();

            return response()->json([
                'status' => 1,
                'msg' => 'Grados obtenidos exitosamente.',
                'data' => $grados,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al obtener los grados.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Almacenar un nuevo grado.
     */
    public function store(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'grado' => 'required|string|max:255',
            'salon' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Verificar si ya existe un grado con el mismo grado y salón
            $gradoExistente = Grado::where('grado', $request->grado)
                ->where('salon', $request->salon)
                ->first();

            if ($gradoExistente) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'Ya existe un grado con el mismo grado y salón.',
                    'data' => null,
                ], 409); // Código de conflicto
            }

            // Crear el nuevo grado
            $grado = Grado::create($request->all());

            return response()->json([
                'status' => 1,
                'msg' => 'Grado creado exitosamente.',
                'data' => $grado,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al crear el grado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener un grado específico.
     */
    public function show($id)
    {
        try {
            $grado = Grado::findOrFail($id);

            return response()->json([
                'status' => 1,
                'msg' => 'Grado obtenido exitosamente.',
                'data' => $grado,
            ], 200);
        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Grado no encontrado.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al obtener el grado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
    public function destroy($id)
    {
        try {
            $grado = Grado::findOrFail($id);
            $grado->delete();

            return response()->json([
                'status' => 1,
                'msg' => 'Grado eliminado exitosamente.',
            ], 200);
        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Grado no encontrado.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al eliminar el grado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verificar_examen_asignado($gradoId)
    {
        // Verificar si el grado existe
        $grado = Grado::find($gradoId);
        if (!$grado) {
            return response()->json([
                'status' => 0,
                'msg' => 'Grado no encontrado.',
            ], 404);
        }

        // Obtener los exámenes asignados al grado
        $examenes = $grado->examenes; // Asegúrate de tener la relación definida en el modelo Grado

        if ($examenes->isEmpty()) {
            return response()->json([
                'status' => 0,
                'msg' => 'No hay exámenes asignados para este grado.',
            ], 200);
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Exámenes encontrados para el grado.',
            'data' => $examenes,
        ], 200);
    }



}
