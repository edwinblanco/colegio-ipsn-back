<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use Illuminate\Http\Request;
use App\Models\Pregunta;
use App\Models\ImagenPregunta;
use App\Models\Respuesta;

class PreguntaController extends Controller
{
    public function index()
    {
        $preguntas = Pregunta::all();
        return response()->json([
            'status' => 1,
            'msg' => 'Preguntas recuperadas exitosamente.',
            'data' => $preguntas,
        ], 200);
    }

    public function ver_preguntas_por_examen($examen_id)
    {
        $preguntas = Pregunta::where('examen_id', $examen_id)->with('opciones')->with('imagenes')->get();

        $total_valor_preguntas = Pregunta::where('examen_id', $examen_id)->sum('valor');

        foreach ($preguntas as $pregunta) {
            // Verificar si alguna opción tiene 'correcta' como 1
            $tieneOpcionCorrecta = $pregunta->opciones->contains('correcta', 1);
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Preguntas recuperadas exitosamente.',
            'data' => [
                'preguntas' => $preguntas,
                'total_valor_preguntas' => $total_valor_preguntas
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'examen_id' => 'required|exists:examenes,id',
            'contenido' => 'required|string',
            'valor' => 'required|integer',
            'imagenes.*' => 'image',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'exists' => 'El campo :attribute debe ser un examen ya creado',
            'image' => 'El archivo debe ser una imagen válida.',
        ]);

        $examen_id = $request->examen_id;
        $valor = $request->valor;
        $porcentaje_restante = 100;

        try {

            $respuesta = Respuesta::where('examen_id', $examen_id)->first();

            if ($respuesta) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                    'data' => [],
                ], 400);
            }

            $total_valor_preguntas = Pregunta::where('examen_id', $examen_id)->sum('valor');
            $porcentaje_restante -= $total_valor_preguntas;

            if ($valor > $porcentaje_restante) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'La pregunta no puede superar el valor de: ' . $porcentaje_restante .'%',
                    'data' => [],
                ], 400);
            }

            // Crear la pregunta
            $pregunta = Pregunta::create($request->all());

             // Guardar las imágenes
            if ($request->hasFile('imagenes')) {
                foreach ($request->imagenes as $imagen) {
                    $nombreArchivo = uniqid() . '.' . $imagen->getClientOriginalExtension();
                    $ruta = $imagen->storeAs('imagenes_preguntas', $nombreArchivo, 'public');

                    // Guarda la dirección completa en la base de datos
                    ImagenPregunta::create([
                        'id_pregunta' => $pregunta->id,
                        'url' => 'storage/' . $ruta, // Asumiendo que estás usando el disco 'public'
                    ]);
                }
            }

            return response()->json([
                'status' => 1,
                'msg' => 'Pregunta creada exitosamente.',
                'data' => [
                    'pregunta' => $pregunta,
                    'valor_restante' => $porcentaje_restante - $valor,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al crear la pregunta: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function show($id)
    {
        $pregunta = Pregunta::find($id);

        if (!$pregunta) {
            return response()->json([
                'status' => 0,
                'msg' => 'Pregunta no encontrada.',
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Pregunta recuperada exitosamente.',
            'data' => $pregunta,
        ], 200);
    }

    public function update(Request $request)
    {

        $request->validate([
            'pregunta_id' => 'sometimes',
            'contenido' => 'sometimes|string',
            'valor' => 'sometimes|integer',
        ]);

        $pregunta = Pregunta::find($request->pregunta_id);

        if (!$pregunta) {
            return response()->json([
                'status' => 0,
                'msg' => 'Pregunta no encontrada.',
            ], 404);
        }

        $respuesta = Respuesta::where('examen_id', $pregunta->examen_id)->first();

        if ($respuesta) {
            return response()->json([
                'status' => 0,
                'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                'data' => [],
            ], 400);
        }

        $examen_id = $pregunta->examen_id;
        $valor_actual = $pregunta->valor;
        $valor = $request->valor;

        $total_valor_preguntas = Pregunta::where('examen_id', $examen_id)->sum('valor');
        $valor_disponible = $total_valor_preguntas - $valor_actual;
        $valor_disponible = 100 - $valor_disponible;

        if ($valor > $valor_disponible) {
            return response()->json([
                'status' => 0,
                'msg' => 'La pregunta no puede superar el valor de: ' . $valor_disponible . '%. Debe disminuir primero el valor % en otra pregunta.',
                'data' => [],
            ], 400);
        }

        $pregunta->update($request->all());

        // Guardar las imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->imagenes as $imagen) {
                $nombreArchivo = uniqid() . '.' . $imagen->getClientOriginalExtension();
                $ruta = $imagen->storeAs('imagenes_preguntas', $nombreArchivo, 'public');

                // Guarda la dirección completa en la base de datos
                ImagenPregunta::create([
                    'id_pregunta' => $pregunta->id,
                    'url' => 'storage/' . $ruta, // Asumiendo que estás usando el disco 'public'
                ]);
            }
        }

        return response()->json([
            'status' => 1,
            'msg' => 'Pregunta actualizada exitosamente.',
            'data' => $pregunta,
        ], 200);
    }

    public function destroy($id)
    {
        $pregunta = Pregunta::find($id);

        if (!$pregunta) {
            return response()->json([
                'status' => 0,
                'msg' => 'Pregunta no encontrada.',
            ], 404);
        }

        $respuesta = Respuesta::where('examen_id', $pregunta->examen_id)->first();

        if ($respuesta) {
            return response()->json([
                'status' => 0,
                'msg' => 'No puede realizar la acción porque hay estudiantes presentando el examen.',
                'data' => [],
            ], 400);
        }

        $pregunta->delete();

        return response()->json([
            'status' => 1,
            'msg' => 'Pregunta eliminada exitosamente.',
            'data' => null,
        ], 200);
    }
}
