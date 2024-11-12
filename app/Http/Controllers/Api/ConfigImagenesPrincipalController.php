<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigImagenesPrincipal;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ConfigImagenesPrincipalController extends Controller
{
    // Método para obtener todos los registros
    public function index()
    {
        try {
            $imagenes = ConfigImagenesPrincipal::all();

            return response()->json([
                'status' => 1,
                'msg' => 'Imágenes obtenidas exitosamente.',
                'data' => $imagenes,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al obtener las imágenes: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // Método para almacenar un nuevo registro
    public function store(Request $request)
    {
        try {
            // Validación de los datos de entrada
            $request->validate([
                'nombre' => 'required|string|max:255',
                'estado' => 'required|in:activa,inactiva',
                'imagen' => 'required|image|mimes:jpeg,png,jpg,gif', // Validación para la imagen
            ]);

             // Guardar las imágenes
             if ($request->hasFile('imagen')) {
                $nombreArchivo = uniqid() . '.' . $request->imagen->getClientOriginalExtension();
                $ruta = $request->imagen->storeAs('imagenes_principales', $nombreArchivo, 'public');

                // Crear una nueva entrada en la base de datos con la ruta de la imagen
                $imagen = ConfigImagenesPrincipal::create([
                    'nombre' => $request->input('nombre'),
                    'estado' => $request->input('estado'),
                    'url' => 'storage/' . $ruta,
                ]);
            }

            return response()->json([
                'status' => 1,
                'msg' => 'Imagen guardada exitosamente.',
                'data' => $imagen,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al guardar la imagen: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // Método para mostrar un registro
    public function show($id)
    {
        try {
            $imagen = ConfigImagenesPrincipal::findOrFail($id);

            return response()->json([
                'status' => 1,
                'msg' => 'Imagen encontrada.',
                'data' => $imagen,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Imagen no encontrada.',
                'data' => [],
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al obtener la imagen: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // Método para actualizar un registro
    public function update(Request $request, $id)
    {
        try {
            $imagenS = ConfigImagenesPrincipal::findOrFail($id);

            // Validación de los datos de entrada
            $request->validate([
                'nombre' => 'required|string|max:255',
                'estado' => 'required|in:activa,inactiva',
                'imagen' => 'required|image|mimes:jpeg,png,jpg,gif', // Validación para la imagen
            ]);

            // Guardar las imágenes
            if ($request->hasFile('imagen')) {
                $nombreArchivo = uniqid() . '.' . $request->imagen->getClientOriginalExtension();
                $ruta = $request->imagen->storeAs('imagenes_principales', $nombreArchivo, 'public');

                // Actualizar los campos en el modelo existente
                $imagenS->update([
                    'nombre' => $request->input('nombre'),
                    'estado' => $request->input('estado'),
                    'url' => 'storage/' . $ruta,
                ]);
            }

            return response()->json([
                'status' => 1,
                'msg' => 'Imagen actualizada exitosamente.',
                'data' => $imagenS,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Imagen no encontrada.',
                'data' => [],
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al actualizar la imagen: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // Método para eliminar un registro
    public function destroy($id)
    {
        try {
            $imagen = ConfigImagenesPrincipal::findOrFail($id);

            $imagen->delete();

            return response()->json([
                'status' => 1,
                'msg' => 'Imagen eliminada exitosamente.',
                'data' => [],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Imagen no encontrada.',
                'data' => [],
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Error al eliminar la imagen: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }
}
