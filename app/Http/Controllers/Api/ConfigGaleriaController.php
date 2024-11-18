<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigGaleria;
use Validator;

class ConfigGaleriaController extends Controller
{
    public function index()
     {
        $galeria = ConfigGaleria::all();

         return response()->json([
             'status' => 1,
             'msg' => 'Galería cargada correctamente.',
             'data' => $galeria,
         ], 200);
     }

     // Crear una nueva galeria
     public function store(Request $request)
     {
         // Validación de los datos
         $validator = Validator::make($request->all(), [
             'titulo' => 'required|string|max:255|unique:config_galeria,titulo',
             'descripcion' => 'nullable|string',
             'fecha_publicacion' => 'required|date',
             'tipo' => 'required|in:noticia,evento,anuncio',
             'activo' => 'required|boolean',
             'imagen' => 'nullable|image',
         ]);

         if ($validator->fails()) {
             return response()->json([
                 'status' => 0,
                 'msg' => 'Datos inválidos',
                 'errors' => $validator->errors()
             ], 400);
         }

         if ($request->hasFile('imagen')) {
            $nombreArchivo = uniqid() . '.' . $request->imagen->getClientOriginalExtension();
            $ruta = $request->imagen->storeAs('imagenes_galeria', $nombreArchivo, 'public');
            $url_imagen = 'storage/' . $ruta;
         }

         // Crear el nueva galeria
         $galeria = ConfigGaleria::create([
             'titulo' => $request->titulo,
             'descripcion' => $request->descripcion,
             'fecha_publicacion' => $request->fecha_publicacion,
             'tipo' => $request->tipo,
             'activo' => $request->activo,
             'url_imagen' => $url_imagen
         ]);

         return response()->json([
             'status' => 1,
             'msg' => '¡Creado exitosamente!',
             'data' => $galeria
         ], 201);
     }

     // Mostrar una galeria específico
     public function show($id)
     {
         $anuncio = ConfigGaleria::find($id);

         if (!$anuncio) {
             return response()->json([
                 'status' => 0,
                 'msg' => 'No encontrado.'
             ], 404);
         }

         return response()->json([
             'status' => 1,
             'msg' => 'Cargado correctamente.',
             'data' => $anuncio
         ], 200);
     }

     // Actualizar galeria
     public function update(Request $request, $id)
     {
         $validator = Validator::make($request->all(), [
             'titulo' => 'required|string|max:255|unique:config_galeria,titulo,' . $id,
             'descripcion' => 'nullable|string',
             'fecha_publicacion' => 'required|date',
             'tipo' => 'required|in:noticia,evento,anuncio',
             'activo' => 'required|boolean',
             'imagen' => 'nullable|image',
         ]);

         if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Datos inválidos al actualizar',
                'errors' => $validator->errors(),
                'request' => $request->all(), // Incluye el request completo aquí
            ], 400);
        }

        $galeria = ConfigGaleria::find($id);

         if (!$galeria) {
             return response()->json([
                 'status' => 0,
                 'msg' => 'No encontrado.'
             ], 404);
         }

         $galeria->titulo = $request->titulo;
         $galeria->descripcion = $request->descripcion;
         $galeria->fecha_publicacion = $request->fecha_publicacion;
         $galeria->tipo = $request->tipo;
         $galeria->activo = $request->activo;

         if ($request->hasFile('imagen')) {
            $nombreArchivo = uniqid() . '.' . $request->imagen->getClientOriginalExtension();
            $ruta = $request->imagen->storeAs('imagenes_galeria', $nombreArchivo, 'public');
            $galeria->url_imagen = 'storage/' . $ruta;
         }

         $galeria->save();

         return response()->json([
             'status' => 1,
             'msg' => '¡Actualizado exitosamente!',
             'data' => $galeria
         ], 200);
     }

    // Eliminar galeria
    public function destroy($id){
         $galeria = ConfigGaleria::find($id);

         if (!$galeria ) {
             return response()->json([
                 'status' => 0,
                 'msg' => 'No encontrado.'
             ], 404);
         }

         // Eliminar el anuncio
         $galeria ->delete();

         return response()->json([
             'status' => 1,
             'msg' => '¡Eliminado exitosamente!'
         ], 200);
    }
}
