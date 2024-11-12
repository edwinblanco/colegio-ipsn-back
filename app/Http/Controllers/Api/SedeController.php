<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Sede;

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
}
