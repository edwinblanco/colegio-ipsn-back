<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

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

        // Verificar si el usuario autenticado es administrador
        $usuarioAutenticado = auth()->user();
        if ($usuarioAutenticado->hasRole('admin')) {
            // Retornar todas las materias para el administrador
            $materias = Materia::all(); // Ajusta según el modelo de materias
            return response()->json([
                'status' => 1,
                'msg' => '¡Todas las materias cargadas!',
                'data' => $materias,
            ], 200);
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

    public function ver_materias(Request $request)
    {

        $materias = Materia::all();

        // Respuesta JSON exitosa
        return response()->json([
            'status' => 1,
            'msg' => '¡Materias cargadas!',
            'data' => $materias,
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
    // Método para crear una nueva materia
    public function store(Request $request)
    {
        // Eliminar espacios en blanco y convertir el nombre a minúsculas
        $nombre = strtolower(trim($request->nombre));

        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        // Verificar si el nombre ya existe en la base de datos de manera insensible a mayúsculas/minúsculas
        $existingMateria = Materia::whereRaw('LOWER(nombre) = ?', [$nombre])->first();

        if ($existingMateria) {
            return response()->json([
                'status' => 0,
                'msg' => 'El nombre de la materia ya está registrado.',
            ], 400);
        }

        // Crear la nueva materia
        $materia = new Materia();
        $materia->nombre = $nombre;  // Guardar el nombre en minúsculas y sin espacios
        $materia->descripcion = $request->descripcion;
        $materia->save();

        // Respuesta JSON exitosa
        return response()->json([
            'status' => 1,
            'msg' => '¡Materia creada exitosamente!',
            'data' => $materia
        ], 201);
    }

    // Método para eliminar una materia
    public function destroy($id)
    {
        // Buscar la materia por ID
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json([
                'status' => 0,
                'msg' => 'Materia no encontrada',
            ], 404);
        }

        // Eliminar la materia
        $materia->delete();

        // Respuesta JSON exitosa
        return response()->json([
            'status' => 1,
            'msg' => '¡Materia eliminada exitosamente!',
        ], 200);
    }
}
