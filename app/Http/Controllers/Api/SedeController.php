<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Sede;
use Illuminate\Support\Facades\Validator;

class SedeController extends Controller
{
    public function index(): JsonResponse
    {
        $sedes = Sede::all();

        return response()->json([
            'status' => 1,
            'msg' => 'Lista de sedes obtenida con éxito',
            'data' => $sedes,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        // Validación de los datos de la sede
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'descripcion' => 'nullable|string',
        ]);

        // Si la validación falla, devolver error
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error en la validación de datos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verificar si ya existe una sede con el mismo nombre
        $existingSede = Sede::where('nombre', $request->nombre)->first();

        if ($existingSede) {
            return response()->json([
                'status' => 0,
                'msg' => 'Ya existe una sede con ese nombre',
            ], 409); // 409 Conflict
        }

        // Crear la sede
        $sede = Sede::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'status' => 1,
            'msg' => 'Sede creada con éxito',
            'data' => $sede,
        ], 201);
    }

    // Editar una sede existente
    public function update(Request $request, $id): JsonResponse
    {
        // Buscar la sede por su ID
        $sede = Sede::find($id);

        if (!$sede) {
            return response()->json([
                'status' => 0,
                'msg' => 'Sede no encontrada',
            ], 404);
        }

        // Validación de los datos de la sede
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'descripcion' => 'nullable|string',
        ]);

        // Si la validación falla, devolver error
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error en la validación de datos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Actualizar la sede con los nuevos datos
        $sede->nombre = $request->nombre;
        $sede->direccion = $request->direccion;
        $sede->telefono = $request->telefono;
        $sede->descripcion = $request->descripcion;
        $sede->save();

        return response()->json([
            'status' => 1,
            'msg' => 'Sede actualizada con éxito',
            'data' => $sede,
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        // Buscar la sede por su id
        $sede = Sede::find($id);

        // Verificar si la sede existe
        if (!$sede) {
            return response()->json([
                'status' => 0,
                'msg' => 'La sede no existe',
            ], 404);
        }

        // Eliminar la sede
        $sede->delete();

        // Responder con éxito
        return response()->json([
            'status' => 1,
            'msg' => 'Sede eliminada con éxito',
        ], 200);
    }
}
