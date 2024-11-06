<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Opcion;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Examen;

class OpcionController extends Controller
{
    public function index()
    {
        $opciones = Opcion::all();
        return response()->json([
            'status' => 1,
            'msg' => 'Opciones recuperadas exitosamente.',
            'data' => $opciones,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pregunta_id' => 'required|exists:preguntas,id',
            'contenido' => 'required|string',
            'correcta' => 'required|boolean',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'exists' => 'El campo :attribute debe ser una pregunta ya creada',
        ]);

        $pregunta = Pregunta::where('id', $request->pregunta_id)->first();

        if($pregunta){
            $respuesta = Respuesta::where('examen_id', $pregunta->examen_id)->first();

            if ($respuesta) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                    'data' => [],
                ], 400);
            }
        }

        $examen = Examen::findOrFail($pregunta->examen_id);

        // Verificar si el examen ya tiene un grado asignado
        if ($examen->grados()->exists()) {
            // Si el examen ya tiene un grado asignado, impedir la actualización
            return response()->json([
                'status' => 0,
                'msg' => 'Este examen ya tiene un grado asignado y no se puede modificar, debe eliminar la asingación.',
                'data' => []
            ], 403); // 403 Forbidden
        }

        try {

            if ($request->correcta) {
                // Verificar si ya existe una opción correcta
                $existe_opcion_correcta = Opcion::where('pregunta_id', $request->pregunta_id)
                    ->where('correcta', true)
                    ->first();

                if ($existe_opcion_correcta) {
                    // Si existe una opción correcta, la actualizamos a 'false'
                    $existe_opcion_correcta->correcta = false;
                    $existe_opcion_correcta->save(); // Guardar los cambios
                }
            }

            // Crear la nueva opción
            $opcion = Opcion::create($request->all());

            return response()->json([
                'status' => 1,
                'msg' => 'Opción creada exitosamente.',
                'data' => $opcion,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al crear la opción: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function ver_opciones_por_pregunta($id_pregunta)
    {
        $opcion = Opcion::find($id);

        if (!$opcion) {
            return response()->json([
                'status' => 0,
                'msg' => 'Opción no encontrada.',
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Opción recuperada exitosamente.',
            'data' => $opcion,
        ], 200);
    }

    public function show($id)
    {
        $opcion = Opcion::find($id);

        if (!$opcion) {
            return response()->json([
                'status' => 0,
                'msg' => 'Opción no encontrada.',
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Opción recuperada exitosamente.',
            'data' => $opcion,
        ], 200);
    }

    public function update(Request $request)
    {

        $request->validate([
            'opcion_id' => 'required',
            'contenido' => 'sometimes|string',
            'correcta' => 'sometimes|boolean',
        ]);

        $opcion = Opcion::find($request->opcion_id);

        if (!$opcion) {
            return response()->json([
                'status' => 0,
                'msg' => 'Opción no encontrada.',
            ], 404);
        }

        $pregunta = Pregunta::where('id', $opcion->pregunta_id)->first();

        if($pregunta){
            $respuesta = Respuesta::where('examen_id', $pregunta->examen_id)->first();

            if ($respuesta) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                    'data' => [],
                ], 400);
            }
        }

        $examen = Examen::findOrFail($pregunta->examen_id);

        // Verificar si el examen ya tiene un grado asignado
        if ($examen->grados()->exists()) {
            // Si el examen ya tiene un grado asignado, impedir la actualización
            return response()->json([
                'status' => 0,
                'msg' => 'Este examen ya tiene un grado asignado y no se puede modificar, debe eliminar la asingación.',
                'data' => []
            ], 403); // 403 Forbidden
        }

        $opcion->update($request->all());

        return response()->json([
            'status' => 1,
            'msg' => 'Opción actualizada exitosamente.',
            'data' => $opcion,
        ], 200);
    }

    public function destroy($id)
    {
        $opcion = Opcion::find($id);

        if (!$opcion) {
            return response()->json([
                'status' => 0,
                'msg' => 'Opción no encontrada.',
            ], 404);
        }

        $pregunta = Pregunta::where('id', $opcion->pregunta_id)->first();

        if($pregunta){
            $respuesta = Respuesta::where('examen_id', $pregunta->examen_id)->first();

            if ($respuesta) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                    'data' => [],
                ], 400);
            }
        }

        $examen = Examen::findOrFail($pregunta->examen_id);

        // Verificar si el examen ya tiene un grado asignado
        if ($examen->grados()->exists()) {
            // Si el examen ya tiene un grado asignado, impedir la actualización
            return response()->json([
                'status' => 0,
                'msg' => 'Este examen ya tiene un grado asignado y no se puede modificar, debe eliminar la asingación.',
                'data' => []
            ], 403); // 403 Forbidden
        }

        $opcion->delete();

        return response()->json([
            'status' => 1,
            'msg' => 'Opción eliminada exitosamente.',
            'data' => null,
        ], 200);
    }
}
