<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagenPregunta;
use Illuminate\Http\Request;
use App\Models\Pregunta;
use App\Models\Respuesta;

class ImagenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $imagen = ImagenPregunta::find($id);

        if (!$imagen) {
            return response()->json([
                'status' => 0,
                'msg' => 'Imagen no encontrada.',
            ], 404);
        }

        $pregunta = Pregunta::where('id', $imagen->id_pregunta)->first();

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

        $imagen->delete();

        return response()->json([
            'status' => 1,
            'msg' => 'Imagen eliminada exitosamente.',
            'data' => null,
        ], 200);
    }
}
