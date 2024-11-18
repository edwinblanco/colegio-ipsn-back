<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigAnuncio;
use Validator;

class ConfigAnuncioController extends Controller
{
     // Mostrar todos los anuncios
     public function index()
     {
        $anuncios = ConfigAnuncio::all();
        $data_noticias = ConfigAnuncio::where('tipo', 'noticia')
            ->where('activo', true)
            ->orderBy('fecha_publicacion', 'desc') // Ordena por fecha_publicacion de forma descendente (más reciente primero)
            ->get();

        $data_eventos = ConfigAnuncio::where('tipo', 'evento')
            ->where('activo', true)
            ->orderBy('fecha_publicacion', 'desc') // Ordena por fecha_publicacion de forma descendente (más reciente primero)
            ->get();

        $data_anuncios = ConfigAnuncio::where('tipo', 'anuncio')
            ->where('activo', true)
            ->orderBy('fecha_publicacion', 'desc') // Ordena por fecha_publicacion de forma descendente (más reciente primero)
            ->get();

         return response()->json([
             'status' => 1,
             'msg' => 'Anuncios cargados correctamente.',
             'data' => $anuncios,
             'data_noticias' => $data_noticias,
             'data_eventos' => $data_eventos,
             'data_anuncios' => $data_anuncios
         ], 200);
     }

     // Crear un nuevo anuncio
     public function store(Request $request)
     {
         // Validación de los datos
         $validator = Validator::make($request->all(), [
             'titulo' => 'required|string|max:255|unique:config_anuncios,titulo',
             'descripcion' => 'nullable|string',
             'fecha_publicacion' => 'required|date',
             'tipo' => 'required|in:noticia,evento,anuncio',
             'activo' => 'required|boolean',
             'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            $ruta = $request->imagen->storeAs('imagenes_anuncios', $nombreArchivo, 'public');
            $url_imagen = 'storage/' . $ruta;
         }

         // Crear el nuevo anuncio
         $anuncio = ConfigAnuncio::create([
             'titulo' => $request->titulo,
             'descripcion' => $request->descripcion,
             'fecha_publicacion' => $request->fecha_publicacion,
             'tipo' => $request->tipo,
             'activo' => $request->activo,
             'url_imagen' => $url_imagen
         ]);

         return response()->json([
             'status' => 1,
             'msg' => '¡Anuncio creado exitosamente!',
             'data' => $anuncio
         ], 201);
     }

     // Mostrar un anuncio específico
     public function show($id)
     {
         $anuncio = ConfigAnuncio::find($id);

         if (!$anuncio) {
             return response()->json([
                 'status' => 0,
                 'msg' => 'Anuncio no encontrado.'
             ], 404);
         }

         return response()->json([
             'status' => 1,
             'msg' => 'Anuncio cargado correctamente.',
             'data' => $anuncio
         ], 200);
     }

     // Actualizar un anuncio
     public function update(Request $request, $id)
     {
         $validator = Validator::make($request->all(), [
             'titulo' => 'required|string|max:255|unique:config_anuncios,titulo,' . $id,
             'descripcion' => 'nullable|string',
             'fecha_publicacion' => 'required|date',
             'tipo' => 'required|in:noticia,evento,anuncio',
             'activo' => 'required|boolean',
             'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif',
         ]);

         if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'msg' => 'Datos inválidos al actualizar',
                'errors' => $validator->errors(),
                'request' => $request->all(), // Incluye el request completo aquí
            ], 400);
        }

         $anuncio = ConfigAnuncio::find($id);

         if (!$anuncio) {
             return response()->json([
                 'status' => 0,
                 'msg' => 'Anuncio no encontrado.'
             ], 404);
         }

         $anuncio->titulo = $request->titulo;
         $anuncio->descripcion = $request->descripcion;
         $anuncio->fecha_publicacion = $request->fecha_publicacion;
         $anuncio->tipo = $request->tipo;
         $anuncio->activo = $request->activo;

         if ($request->hasFile('imagen')) {
            $nombreArchivo = uniqid() . '.' . $request->imagen->getClientOriginalExtension();
            $ruta = $request->imagen->storeAs('imagenes_anuncios', $nombreArchivo, 'public');
            $anuncio->url_imagen = 'storage/' . $ruta;
         }

         $anuncio->save();

         return response()->json([
             'status' => 1,
             'msg' => '¡Anuncio actualizado exitosamente!',
             'data' => $anuncio
         ], 200);
     }

    // Eliminar un anuncio
    public function destroy($id){
         $anuncio = ConfigAnuncio::find($id);

         if (!$anuncio) {
             return response()->json([
                 'status' => 0,
                 'msg' => 'Anuncio no encontrado.'
             ], 404);
         }

         // Eliminar el anuncio
         $anuncio->delete();

         return response()->json([
             'status' => 1,
             'msg' => '¡Anuncio eliminado exitosamente!'
         ], 200);
    }
}
