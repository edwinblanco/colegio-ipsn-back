<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use Illuminate\Http\Request;
use App\Models\Pregunta;

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
        $preguntas = Pregunta::where('examen_id', $examen_id)->with('opciones')->get();

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
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'exists' => 'El campo :attribute debe ser un examen ya creado',
        ]);

        $examen_id = $request->examen_id;
        $valor = $request->valor;

        $porcentaje_restante = 100;

        try {
            $total_valor_preguntas = Pregunta::where('examen_id', $examen_id)->sum('valor');
            $porcentaje_restante -= $total_valor_preguntas;

            if ($valor > $porcentaje_restante) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'La pregunta no puede superar el valor de: ' . $porcentaje_restante .'%',
                    'data' => [],
                ], 400);
            }

            $pregunta = Pregunta::create($request->all());

            return response()->json([
                'status' => 1,
                'msg' => 'Pregunta creada exitosamente.',
                'data' => $pregunta,
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
            ], 500); // Código de error interno del servidor
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

        $pregunta->delete();

        return response()->json([
            'status' => 1,
            'msg' => 'Pregunta eliminada exitosamente.',
            'data' => null,
        ], 200);
    }
}
