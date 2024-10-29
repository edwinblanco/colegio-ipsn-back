<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MateriaController extends Controller
{


    public function materias_por_profesor(Request $request, $id)
    {
        // Validar que el ID sea un número y requerido
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ], [
            'id.required' => 'El ID del profesor es obligatorio.',
            'id.numeric' => 'El ID debe ser un número válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => '¡Error de validación!',
                'data' => $validator->errors(),
            ], 400);
        }

        // Intentar obtener el profesor junto con sus materias
        $profesor = User::with('materias')->find($id);

        // Respuesta de validación si el profesor no existe
        if (!$profesor) {
            return response()->json([
                'status' => 0,
                'msg' => '¡Profesor no encontrado!',
                'data' => null,
            ], 404);
        }

        // Verificar si el usuario tiene el rol de profesor
        if (!$profesor->hasRole('profesor')) {
            return response()->json([
                'status' => 0,
                'msg' => '¡El usuario no tiene el rol de profesor!',
                'data' => null,
            ], 403);
        }

        // Verificar si el profesor tiene materias asignadas
        if ($profesor->materias->isEmpty()) {
            return response()->json([
                'status' => 0,
                'msg' => '¡Sin materias asignadas!',
                'data' => [],
            ], 200);
        }

        // Respuesta JSON exitosa
        return response()->json([
            'status' => 1,
            'msg' => '¡Materias cargadas!',
            'data' => $profesor->materias,
        ], 200);
    }


    public function index()
    {

        $materias = Materia::all();

        // Respuesta JSON exitosa
        return response()->json([
            'status' => 1,
            'msg' => '¡Materias cargadas!',
            'data' => $materias
        ], 200);
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
        //
    }
}
