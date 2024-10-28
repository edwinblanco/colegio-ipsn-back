<?php

use App\Http\Controllers\Api\ExamenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MateriaController;
use App\Http\Controllers\Api\OpcionController;
use App\Http\Controllers\Api\PreguntaController;
use App\Http\Controllers\Api\GradoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('logout', [UserController::class, 'logout']);
    Route::get('validar-token', [UserController::class, 'validar_token']);

    Route::get('materias-por-profesor/{id}', [MateriaController::class, 'materias_por_profesor']);

    // Aplicar el middleware para verificar el rol
    Route::post('crear-examen', [ExamenController::class, 'store'])->middleware('role:profesor');
    Route::get('ver-grado-asignado/{examen_id}', [ExamenController::class, 'grados_asignados'])->middleware('role:profesor');
    Route::post('asignar-examen-grado', [ExamenController::class, 'asignar_examen_a_grado'])->middleware('role:profesor');
    Route::delete('eliminar-asignacion', [ExamenController::class, 'eliminar_asignacion'])->middleware('role:profesor');

    Route::post('crear-pregunta', [PreguntaController::class, 'store'])->middleware('role:profesor');
    Route::get('ver-preguntas-por-examen/{examen_id}', [PreguntaController::class, 'ver_preguntas_por_examen'])->middleware('role:profesor');
    Route::post('editar-pregunta', [PreguntaController::class, 'update'])->middleware('role:profesor');
    Route::delete('eliminar-pregunta/{id}', [PreguntaController::class, 'destroy'])->middleware('role:profesor');

    Route::post('crear-opcion', [OpcionController::class, 'store'])->middleware('role:profesor');
    Route::post('actualizar-opcion', [OpcionController::class, 'update'])->middleware('role:profesor');
    Route::delete('eliminar-opcion/{id}', [OpcionController::class, 'destroy'])->middleware('role:profesor');

    //Grados
    Route::get('ver-grados', [GradoController::class, 'index'])->middleware('role:profesor');

    Route::get('examenes-materia/{materiaId}', [ExamenController::class, 'show'])->middleware('role:profesor,estudiante');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
