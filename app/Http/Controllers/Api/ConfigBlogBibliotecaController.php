<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigBlogBiblioteca;
use Validator;

class ConfigBlogBibliotecaController extends Controller
{
    public function index()
    {
       $articulos = ConfigBlogBiblioteca::all();

        $data_articulos = ConfigBlogBiblioteca::where('tipo', 'articulo')
            ->where('activo', true)
            ->orderBy('fecha_publicacion', 'desc') // Ordena por fecha_publicacion de forma descendente (más reciente primero)
            ->get();

        $data_libros = ConfigBlogBiblioteca::where('tipo', 'libro')
            ->where('activo', true)
            ->orderBy('fecha_publicacion', 'desc') // Ordena por fecha_publicacion de forma descendente (más reciente primero)
            ->get();

        return response()->json([
            'status' => 1,
            'msg' => 'articulos y libros cargados correctamente.',
            'data' => $articulos,
            'data_articulos' => $data_articulos,
            'data_libros' => $data_libros
        ], 200);
    }

    // Crear un articulo/libro
    public function store(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255|unique:config_blog_biblioteca,titulo',
            'descripcion' => 'nullable|string',
            'fecha_publicacion' => 'required|date',
            'tipo' => 'required|in:articulo,libro',
            'activo' => 'required|boolean',
            'imagen' => 'nullable|image',
            'archivo' => 'nullable|file',
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

        if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
            $nombreArchivo = uniqid() . '.' . $request->archivo->getClientOriginalExtension();
            $rutaArchivo = $request->archivo->storeAs('archivos_biblioteca', $nombreArchivo, 'public');
            $url_archivo = 'storage/' . $rutaArchivo;
        }

        // Crear articulo/libro
        $articulo = ConfigBlogBiblioteca::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_publicacion' => $request->fecha_publicacion,
            'tipo' => $request->tipo,
            'activo' => $request->activo,
            'url_imagen' => $url_imagen,
            'url_archivo' => $url_archivo
        ]);

        return response()->json([
            'status' => 1,
            'msg' => '¡Libro/articulo creado exitosamente!',
            'data' => $articulo
        ], 201);
    }

    // Mostrar una galeria específico
    public function show($id)
    {
        $anuncio = ConfigBlogBiblioteca::find($id);

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

    // Actualizar libro/articulo
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255|unique:config_blog_biblioteca,titulo,' . $id,
            'descripcion' => 'nullable|string',
            'fecha_publicacion' => 'required|date',
            'tipo' => 'required|in:articulo,libro',
            'activo' => 'required|boolean',
            'imagen' => 'nullable|image',
            'archivo' => 'nullable|file',
        ]);

        if ($validator->fails()) {
           return response()->json([
               'status' => 0,
               'msg' => 'Datos inválidos al actualizar',
               'errors' => $validator->errors(),
               'request' => $request->all(), // Incluye el request completo aquí
           ], 400);
       }

       $articulo = ConfigBlogBiblioteca::find($id);

        if (!$articulo) {
            return response()->json([
                'status' => 0,
                'msg' => 'No encontrado.'
            ], 404);
        }

        $articulo->titulo = $request->titulo;
        $articulo->descripcion = $request->descripcion;
        $articulo->fecha_publicacion = $request->fecha_publicacion;
        $articulo->tipo = $request->tipo;
        $articulo->activo = $request->activo;

        if ($request->hasFile('imagen')) {
           $nombreArchivo = uniqid() . '.' . $request->imagen->getClientOriginalExtension();
           $ruta = $request->imagen->storeAs('imagenes_galeria', $nombreArchivo, 'public');
           $articulo->url_imagen = 'storage/' . $ruta;
        }
 
        if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
            $nombreArchivo = uniqid() . '.' . $request->archivo->getClientOriginalExtension();
            $rutaArchivo = $request->archivo->storeAs('archivos_biblioteca', $nombreArchivo, 'public');
            $articulo->url_archivo = 'storage/' . $rutaArchivo;
        }

        $articulo->save();

        return response()->json([
            'status' => 1,
            'msg' => '¡Actualizado exitosamente!',
            'data' => $articulo
        ], 200);
    }

   // Eliminar galeria
   public function destroy($id){
        $galeria = ConfigBlogBiblioteca::find($id);

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
