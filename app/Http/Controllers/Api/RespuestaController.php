<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Respuesta;
use App\Models\Opcion;
use App\Models\Pregunta;

class RespuestaController extends Controller
{
    public function guardar_respuesta(Request $request)
    {
        $request->validate([
            'pregunta_id' => 'required|exists:preguntas,id',
            //'respuesta_id' => 'required|exists:opciones,id',
            'examen_id' => 'required|exists:examenes,id',
        ]);

        try {

            if(!$request->respuesta_id){
                return response()->json([
                    'status' => 1,
                    'msg' => 'Sin guardar ni calificar',
                ], 200);
            }

            $estudiante_id = Auth::id();

            // Guarda o actualiza la respuesta en la base de datos
            $respuesta = Respuesta::updateOrCreate(
                [
                    'estudiante_id' => $estudiante_id,
                    'pregunta_id' => $request->pregunta_id,
                    'examen_id' => $request->examen_id,
                ],
                [
                    'respuesta_id' => $request->respuesta_id,
                ]
            );

            $pregunta = Pregunta::find($request->pregunta_id);

            // Obtener la opción seleccionada para verificar si es correcta
            $opcion_seleccionada = Opcion::find($request->respuesta_id);

            // Calificar la respuesta basándote en el campo 'es_correcta'
            //$calificacion = $opcion_seleccionada->correcta ? 1 : 0;

            $calificacion = $opcion_seleccionada->correcta ? $pregunta->valor : 0;

            // Guardar la calificación si es necesario
            $respuesta->calificacion = $calificacion;
            $respuesta->save();

            // Contar el total de preguntas del examen
            $totalPreguntas = Pregunta::where('examen_id', $request->examen_id)->count();

            // Contar las respuestas del usuario para el examen
            $totalRespuestas = Respuesta::where('examen_id', $request->examen_id)
                                        ->where('estudiante_id', $estudiante_id)
                                        ->distinct('pregunta_id')
                                        ->count('pregunta_id');

            return response()->json([
                'status' => 1,
                'msg' => 'Respuesta guardada y calificada con éxito.',
                'examen_completado' => $totalRespuestas === $totalPreguntas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al guardar la respuesta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
