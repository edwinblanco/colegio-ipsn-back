<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Examen;
use App\Models\Materia;
use App\Models\Opcion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Carbon\Carbon;
use App\Models\Sede;

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
            $sedeId = $user->sede ? $user->sede->id : null;

            // Verificar si el usuario tiene un grado asignado
            if (!$gradoId) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'El estudiante no tiene un grado asignado.',
                ], 404);
            }

            // Obtener exámenes que correspondan a la materia y al grado del usuario,
            // incluyendo el estado del examen para el estudiante autenticado
            $examenes = Examen::where('materia_id', $materiaId)
                ->whereHas('grados', function ($query) use ($gradoId, $sedeId) {
                    $query->where('grado_id', $gradoId)->where('sede_id', $sedeId);
                })
                ->with(['estudiantes' => function ($query) use ($user) {
                    $query->where('estudiante_id', $user->id)
                          ->select('examen_estudiante.examen_id', 'examen_estudiante.estado as estado_pivote'); // Asignar un alias a estado en la tabla pivote
                }])
                ->get();

            // Transformar los datos para incluir el estado de cada examen
            $examenesData = $examenes->map(function ($examen) use ($user) {
                // Verificar el estado en la tabla pivote (examen_estudiante) si existe
                $estado = $examen->estudiantes->isNotEmpty()
                    ? $examen->estudiantes->first()->pivot->estado
                    : 'pendiente'; // Estado por defecto si no ha iniciado

                return [
                    'id' => $examen->id,
                    'titulo' => $examen->titulo,
                    'descripcion' => $examen->descripcion,
                    'fecha_limite' => $examen->fecha_limite,
                    'estado_examen' => $examen->estado, // Estado de la tabla examen
                    'estado_estudiante' => $estado,     // Estado de la tabla pivote para el estudiante
                ];
            });

            // Retornar los exámenes encontrados con su estado
            return response()->json([
                'status' => 1,
                'msg' => 'Exámenes recuperados con éxito.',
                'data' => $examenesData,
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

    public function obtener_examen_con_preguntas($examenId)
    {
        try {
            // Cargar el examen junto con las preguntas y sus opciones
            $examen = Examen::with(['preguntas.opciones'])->findOrFail($examenId);

            // Obtener el ID del usuario autenticado
            $user_id = Auth::id();

            // Obtener las respuestas del usuario para este examen
            $respuestasUsuario = Respuesta::where('examen_id', $examenId)
                                           ->where('estudiante_id', $user_id)
                                           ->pluck('pregunta_id'); // Obtener solo los IDs de las preguntas respondidas

            // Agregar un atributo para verificar si la pregunta tiene respuesta
            foreach ($examen->preguntas as $pregunta) {
                $pregunta->tiene_respuesta = $respuestasUsuario->contains($pregunta->id);

                // Ocultar el campo 'correcta' en las opciones de cada pregunta
                foreach ($pregunta->opciones as $opcion) {
                    $opcion->makeHidden(['correcta']); // Asegúrate de que 'correcta' sea el nombre del campo que deseas ocultar
                }
            }

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
                            ->with('imagenes')
                            ->first();

        if ($pregunta) {
            $user_id = Auth::id();
            $opcionesIds = Opcion::where('pregunta_id', $pregunta->id)->pluck('id'); // Obtener IDs de las opciones

            // verificar si ya tiene una respuesta dicha pregunta
            $respuesta = Respuesta::whereIn('respuesta_id', $opcionesIds)
                                  ->where('estudiante_id', $user_id)
                                  ->first();

            // Contar el total de preguntas del examen
            $totalPreguntas = Pregunta::where('examen_id', $examenId)->count();

            // Contar las respuestas del usuario para el examen
            $totalRespuestas = Respuesta::where('examen_id',  $examenId)
                                        ->where('estudiante_id', $user_id)
                                        ->distinct('pregunta_id')
                                        ->count('pregunta_id');

            return response()->json([
                'status' => 1,
                'msg' => 'Pregunta encontrada',
                'data' => $pregunta,
                'resp' => $respuesta,
                'examen_completado' => $totalRespuestas === $totalPreguntas
            ], 200);
        }

        return response()->json([
            'status' => 0,
            'msg' => 'Pregunta no encontrada',
            'data' => [],
        ], 404);
    }

    public function iniciar_examen($examenId, $estudianteId)
    {
        // Buscar el examen por su ID
        $examen = Examen::findOrFail($examenId);

        // Verificar si el estudiante ya ha iniciado o completado el examen
        $registro = $examen->estudiantes()->where('estudiante_id', $estudianteId)->first();

        if ($registro) {
            if ($registro->pivot->estado == 'completado') {
                return response()->json([
                    'status' => 1,
                    'msg' => 'Este examen ya ha sido completado.',
                ], 400);
            }
            if ($registro->pivot->estado == 'en proceso') {
                return response()->json([
                    'status' => 1,
                    'msg' => 'Este examen está en proceso.',
                ], 200);
            }
        }

        // Registrar el inicio del examen en la tabla pivote
        $examen->estudiantes()->attach($estudianteId, [
            'estado' => 'en proceso',
        ]);

        return response()->json([
            'status' => 1,
            'msg' => 'Examen iniciado exitosamente.',
        ], 200);

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
    public function update(Request $request)
    {
        try {
            $request->validate([
                'examen_id' => 'nullable|exists:examenes,id',
                'materia_id' => 'nullable|exists:materias,id',
                'profesor_id' => 'nullable|exists:users,id',
                'titulo' => 'required|string|max:255',
                'fecha_limite' => 'required|date',
                'estado' => 'required|in:activo,cerrado',
            ]);

            $examen = Examen::findOrFail($request->examen_id);
            $respuesta = Respuesta::where('examen_id', $examen->id)->first();

            if ($respuesta) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                    'data' => [],
                ], 403);
            }

            // Verificar si el examen ya tiene un grado asignado
            if ($examen->grados()->exists()) {
                // Si el examen ya tiene un grado asignado, impedir la actualización
                return response()->json([
                    'status' => 0,
                    'msg' => 'Este examen ya tiene un grado asignado y no se puede modificar, debe eliminar la asingación.',
                    'data' => []
                ], 403); // 403 Forbidden
            }

            $examen->update($request->all());

            return response()->json([
                'status' => 1,
                'msg' => 'Examen actualizado exitosamente.',
                'data' => $examen,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al actualizar el examen: ', $e,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Eliminar un examen
    public function destroy($id)
    {
        $examen = Examen::find($id);

        if (!$examen) {
            return response()->json([
                'status' => 0,
                'msg' => 'Examen no encontrado.',
            ], 404);
        }

        $respuesta = Respuesta::where('examen_id', $examen->id)->first();

        if ($respuesta) {
            return response()->json([
                'status' => 0,
                'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                'data' => [],
            ], 400);
        }

        // Verificar si el examen ya tiene un grado asignado
        if ($examen->grados()->exists()) {
            // Si el examen ya tiene un grado asignado, impedir la actualización
            return response()->json([
                'status' => 0,
                'msg' => 'Este examen ya tiene un grado asignado y no se puede modificar, debe eliminar la asingación.',
                'data' => []
            ], 403); // 403 Forbidden
        }

        $examen->delete();

        return response()->json([
            'status' => 1,
            'msg' => 'Examen eliminado exitosamente.',
            'data' => null,
        ], 200);
    }

    public function asignar_examen_a_grado(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'examen_id' => 'required|exists:examenes,id',
            'grado_id' => 'required|exists:grados,id',
            'sede_id' => 'required|exists:sedes,id'
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

            // Verificar si el examen ya está asignado al grado y a la sede
            if ($examen->grados()
                    ->where('grado_id', $request->grado_id)
                    ->wherePivot('sede_id', $request->sede_id)
                    ->exists()) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'El examen ya está asignado a este grado y sede.',
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

            // Asignar el examen al grado y a la sede
            $examen->grados()->attach($request->grado_id, [
                'fecha_asignacion' => now(),
                'sede_id' => $request->sede_id, // Agregar sede_id
            ]);

            return response()->json([
                'status' => 1,
                'msg' => 'Examen asignado al grado y sede exitosamente.',
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
    
        // Obtener los grados asignados al examen, incluyendo la sede
        $grados = $examen->grados()->withPivot('sede_id', 'fecha_asignacion')->get();
    
        if ($grados->isEmpty()) {
            return response()->json([
                'status' => 0,
                'msg' => 'No hay grados asignados a este examen.',
            ], 200);
        }
    
        // Formatear la respuesta con la información de los grados y sedes
        $data = $grados->map(function ($grado) {
            return [
                'grado_id' => $grado->id,
                'nombre_grado' => $grado->grado.'° - '.$grado->salon,
                'fecha_asignacion' => $grado->pivot->fecha_asignacion,
                'sede' => $grado->pivot->sede_id ? Sede::find($grado->pivot->sede_id) : null,
            ];
        });
    
        return response()->json([
            'status' => 1,
            'msg' => 'Grados encontrados para el examen.',
            'data' => $data,
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

            $respuesta = Respuesta::where('examen_id', $examen->id)->first();

            if ($respuesta) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                    'data' => [],
                ], 400);
            }

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

    public function enviar_todo_y_terminar(Request $request, $examenId){

        try {
            // Obtener el usuario autenticado (asumimos que es el estudiante)
            $user = Auth::user();

            // Buscar el examen
            $examen = Examen::findOrFail($examenId);

            $puntajeTotal = Respuesta::where('examen_id', $examenId)
                ->where('estudiante_id', $user->id)
                ->sum('calificacion');

            // Actualizar los datos en la tabla pivote
            $examen->estudiantes()->updateExistingPivot($user->id, [
                'fecha_presentacion' => now(),
                'puntaje' => $puntajeTotal,
                'estado' => 'completado',
            ]);

            return response()->json([
                'status' => 1,
                'msg' => 'Examen completado y datos actualizados con éxito.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al completar el examen.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function obtener_examen_con_estudiantes($examenId)
    {
        // Obtiene el ID del profesor autenticado
        $profesorId = Auth::id();

        // Busca el examen que pertenece al profesor y trae los grados y estudiantes asociados
        $examen = Examen::with([
            'grados', // Trae los grados asociados al examen
            'estudiantes' => function ($query) use ($examenId) {
                // Filtra los estudiantes relacionados con el examen específico usando el pivot
                $query->wherePivot('examen_id', $examenId)
                    ->withPivot('fecha_presentacion', 'puntaje', 'estado'); // Incluye datos de la tabla pivot
            }
        ])
        ->where('id', $examenId)
        ->where('profesor_id', $profesorId) // Asegura que el examen pertenece al profesor
        ->first();

        if (!$examen) {
            return response()->json(['error' => 'Examen no encontrado o no pertenece a este profesor'], 404);
        }

        // Filtramos los estudiantes que pertenecen a los grados asociados al examen
        $estudiantesData = $examen->grados->flatMap(function ($grado) use ($examen) {
            return $grado->estudiantes->map(function ($estudiante) use ($examen) {
                // Busca el registro del estudiante en la tabla pivote del examen
                $registroExamen = $examen->estudiantes->firstWhere('id', $estudiante->id);
                $nota = $registroExamen ? (($registroExamen->pivot->puntaje * 100)/100) : null;

                // Obtén las fechas de fecha_limite y fecha_presentacion
                $fecha_limite = Carbon::parse($examen->fecha_limite);
                $fecha_presentacion = $registroExamen && $registroExamen->pivot->fecha_presentacion
                    ? Carbon::parse($registroExamen->pivot->fecha_presentacion)
                    : null;

                // Si el examen no ha sido presentado, la fecha de presentación es null, así que no calculamos la diferencia
                if ($fecha_presentacion) {
                    // Verifica si se presentó el examen antes de la fecha límite
                    if ($fecha_presentacion->lessThanOrEqualTo($fecha_limite)) {
                        $estado_entrega = 'A tiempo';
                        $diferencia = $fecha_presentacion->diff($fecha_limite);
                    } else {
                        $estado_entrega = 'Con retraso';
                        $diferencia = $fecha_limite->diff($fecha_presentacion);
                    }

                    $diferencia_fecha = [
                        'dias' => $diferencia->days,
                        'horas' => $diferencia->h,
                        'minutos' => $diferencia->i
                    ];
                } else {
                    $diferencia_fecha = null;
                    $estado_entrega = 'No presentado';
                }

                return [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->primer_nombre.' '.$estudiante->segundo_nombre.' '.$estudiante->primer_apellido.' '.$estudiante->segundo_apellido,
                    'fecha_presentacion' => $fecha_presentacion ? $fecha_presentacion->toDateTimeString() : null,
                    'diferencia_fecha' => $diferencia_fecha,
                    'estado_entrega' => $estado_entrega,
                    'puntaje' => $nota,
                    'estado' => $registroExamen ? $registroExamen->pivot->estado : null,
                ];
            });
        });

        return response()->json([
            'examen' => $examen,
            'estudiantes' => $estudiantesData,
        ]);
    }

    public function ver_examen_estudiante($examenId, $estudianteId)
    {
        // Cargar el examen con sus preguntas, respuestas y el estudiante que lo presentó
        $examen = Examen::with([
            'preguntas.respuestas', // Para obtener todas las respuestas posibles de cada pregunta
            'estudiantes' => function($query) use ($estudianteId) {
                // Filtrar por el estudiante que presentó el examen
                $query->where('estudiante_id', $estudianteId);
            }
        ])->findOrFail($examenId);

        // Armar la estructura de datos para cada pregunta con la respuesta correcta y la seleccionada
        $preguntasConRespuestas = $examen->preguntas->map(function($pregunta) use ($estudianteId) {
            // Obtener la respuesta correcta
            $respuestaCorrecta = $pregunta->opciones->where('correcta', true)->first();

            // Obtener la respuesta seleccionada por el estudiante
            $respuestaSeleccionada = $pregunta->respuestas->where('estudiante_id', $estudianteId)->where('pregunta_id', $pregunta->id)->first();
            $opcionSeleccionada = $pregunta->opciones->where('id', $respuestaSeleccionada->respuesta_id)->first();

            $acerto = false;

            if($respuestaCorrecta->contenido && $opcionSeleccionada->contenido){
                $acerto = $respuestaCorrecta->id == $opcionSeleccionada->id;
            }

            return [
                'pregunta' => $pregunta->contenido,
                'valor' => $pregunta->valor,
                'respuesta_correcta' => $respuestaCorrecta->contenido ?? null,
                'respuesta_seleccionada' => $opcionSeleccionada->contenido ?? null,
                'correcta' => $acerto
            ];
        });

        return response()->json([
            'examen' => $examen->titulo,
            'preguntas' => $preguntasConRespuestas,
        ]);
    }

}
