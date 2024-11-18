<?php

use App\Http\Controllers\Api\ExamenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MateriaController;
use App\Http\Controllers\Api\OpcionController;
use App\Http\Controllers\Api\PreguntaController;
use App\Http\Controllers\Api\GradoController;
use App\Http\Controllers\Api\ImagenController;
use App\Http\Controllers\Api\RespuestaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ConfigImagenesPrincipalController;
use App\Http\Controllers\Api\SedeController;
use App\Http\Controllers\Api\ConfigAnuncioController;
use App\Http\Controllers\Api\ConfigGaleriaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Ruta para registro de usuarios
Route::post('registro-usuario', [UserController::class, 'registro']);
Route::post('login', [UserController::class, 'login']);

Route::group(['middleware' => ["auth:sanctum"]], function(){
    Route::get('perfil-usuario', [UserController::class, 'perfil_usuario']);
    Route::post('registro-usuario', [UserController::class, 'registro_usuario']);
    Route::get('logout', [UserController::class, 'logout']);
    Route::get('validar-token', [UserController::class, 'validar_token']);
    Route::get('ver-usuarios', [UserController::class, 'ver_usuarios'])->middleware('role:profesor|admin');
    Route::put('actualizar-usuario/{id}', [UserController::class, 'actualizar_usuario'])->middleware('role:profesor|admin');
    Route::delete('eliminar-usuario/{id}', [UserController::class, 'eliminar_usuario'])->middleware('role:admin');

    Route::get('materias', [MateriaController::class, 'index'])->middleware('role:estudiante');
    Route::get('materias-por-profesor/{id}', [MateriaController::class, 'materias_por_profesor'])->middleware('role:profesor|admin');
    Route::get('ver-materias', [MateriaController::class, 'ver_materias'])->middleware('role:admin');
    Route::post('crear-materia', [MateriaController::class, 'store'])->middleware('role:admin');
    Route::delete('eliminar-materia/{id}', [MateriaController::class, 'destroy'])->middleware('role:admin');

    // Aplicar el middleware para verificar el rol
    Route::post('crear-examen', [ExamenController::class, 'store'])->middleware('role:profesor|admin');
    Route::post('editar-examen', [ExamenController::class, 'update'])->middleware('role:profesor|admin');
    Route::delete('eliminar-examen/{id}', [ExamenController::class, 'destroy'])->middleware('role:profesor|admin');
    Route::get('ver-grado-asignado/{examen_id}', [ExamenController::class, 'grados_asignados'])->middleware('role:profesor|admin');
    Route::post('asignar-examen-grado', [ExamenController::class, 'asignar_examen_a_grado'])->middleware('role:profesor|admin');
    Route::delete('eliminar-asignacion', [ExamenController::class, 'eliminar_asignacion'])->middleware('role:profesor|admin');
    Route::get('obtener-pregunta/{examenId}/{preguntaIndex}', [ExamenController::class, 'obtener_pregunta'])->middleware('role:estudiante');

    Route::post('crear-pregunta', [PreguntaController::class, 'store'])->middleware('role:profesor');
    Route::get('ver-preguntas-por-examen/{examen_id}', [PreguntaController::class, 'ver_preguntas_por_examen'])->middleware('role:profesor|admin');
    Route::post('editar-pregunta', [PreguntaController::class, 'update'])->middleware('role:profesor|admin');
    Route::delete('eliminar-pregunta/{id}', [PreguntaController::class, 'destroy'])->middleware('role:profesor|admin');

    Route::post('crear-opcion', [OpcionController::class, 'store'])->middleware('role:profesor|admin');
    Route::post('actualizar-opcion', [OpcionController::class, 'update'])->middleware('role:profesor|admin');
    Route::delete('eliminar-opcion/{id}', [OpcionController::class, 'destroy'])->middleware('role:profesor|admin');

    Route::delete('eliminar-imagen/{id}', [ImagenController::class, 'destroy'])->middleware('role:profesor|admin');

    //Grados
    Route::get('ver-grados', [GradoController::class, 'index'])->middleware(middleware: 'role:profesor|admin');
    Route::post('crear-grado', [GradoController::class, 'store'])->middleware(middleware: 'role:admin');
    Route::delete('eliminar-grado/{id}', [GradoController::class, 'destroy'])->middleware(middleware: 'role:admin');

    //Sedes
    Route::get('ver-sedes', [SedeController::class, 'index'])->middleware(middleware: 'role:profesor|admin');
    Route::post('crear-sede', [SedeController::class, 'store'])->middleware(middleware: 'role:admin');
    Route::delete('eliminar-sede/{id}', [SedeController::class, 'destroy'])->middleware(middleware: 'role:admin');

    Route::get('examenes-materia/{materiaId}', [ExamenController::class, 'show'])->middleware('role:profesor|estudiante|admin');
    Route::get('iniciar-examen/{examenId}/{estudianteId}', [ExamenController::class, 'iniciar_examen'])->middleware('role:estudiante');
    Route::get('examenes-materia-estudiante/{materiaId}', [ExamenController::class, 'ver_examenes_estudiante'])->middleware('role:estudiante');
    Route::get('obtener-examen-preguntas-opciones/{examenId}', [ExamenController::class, 'obtener_examen_con_preguntas_y_opciones'])->middleware('role:estudiante');
    Route::get('obtener-examen-preguntas/{examenId}', [ExamenController::class, 'obtener_examen_con_preguntas'])->middleware('role:estudiante');
    Route::post('enviar-y-terminar/{examenId}', [ExamenController::class, 'enviar_todo_y_terminar'])->middleware('role:estudiante');
    Route::get('examen-informe-estudiantes/{examenId}', [ExamenController::class, 'obtener_examen_con_estudiantes']);
    Route::get('ver-examen-estudiante/{examenId}/{estudianteId}', [ExamenController::class, 'ver_examen_estudiante']);

    Route::post('guardar-respuesta', [RespuestaController::class, 'guardar_respuesta'])->middleware('role:estudiante');
    Route::post('actualizar-anuncio/{id}', [ConfigAnuncioController::class, 'update'])->middleware('role:admin');
    Route::post('actualizar-galeria/{id}', [ConfigGaleriaController::class, 'update'])->middleware('role:admin');

});

//CONFIGURACION
Route::apiResource('config-imagenes-principal', ConfigImagenesPrincipalController::class);
Route::apiResource('anuncios', ConfigAnuncioController::class);
Route::apiResource('galeria', ConfigGaleriaController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
