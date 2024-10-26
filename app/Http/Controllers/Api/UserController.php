<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function registro(Request $request): JsonResponse
    {

        // Validar los datos del request
        $request->validate([
            'primer_nombre' => 'required|string|max:15',
            'segundo_nombre' => 'nullable|string|max:15',
            'primer_apellido' => 'required|string|max:15',
            'segundo_apellido' => 'nullable|string|max:15',
            'numero_documento' => 'required|string|max:30|unique:users',
            'fecha_nacimiento' => 'required|date',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'estado' => 'required|in:activo,inactivo,graduado,expulsado',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser una dirección de correo válida.',
            'max' => 'El campo :attribute no debe tener más de :max caracteres.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'unique' => 'El campo :attribute ya está registrado.',
            'in' => 'El campo :attribute debe ser uno de los siguientes valores: activo, inactivo, graduado o expulsado.',
        ]);

        try {
            // Crear el usuario usando asignación masiva
            $user = User::create([
                'primer_nombre' => $request->primer_nombre,
                'segundo_nombre' => $request->segundo_nombre,
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'numero_documento' => $request->numero_documento,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => $request->estado,
            ]);

            // Crear un rol
            $role = Role::create(['name' => 'profesor', 'guard_name' => 'api']);

            // Crear un permiso
            $permission = Permission::create(['name' => 'crear examen', 'guard_name' => 'api']);
            $permission2 = Permission::create(['name' => 'editar examen', 'guard_name' => 'api']);
            $role->givePermissionTo($permission);
            $role->givePermissionTo($permission2);

            $user->assignRole($role);

            // Respuesta JSON exitosa
            return response()->json([
                'status' => 1,
                'msg' => '¡Registro de usuario exitoso!',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            // Manejo básico de errores
            return response()->json([
                'status' => 0,
                'msg' => 'Error al registrar el usuario. Intente nuevamente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        // Validar los datos del request
        $request->validate([
            'numero_documento' => 'required|string',
            'password' => 'required|string|min:8',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
        ]);

        // Buscar el usuario por numero_documento
        $user = User::where('numero_documento', $request->numero_documento)->first();

        // Verificar si el usuario existe
        if (!$user) {
            return response()->json([
                'status' => 0,
                'msg' => 'Usuario no registrado.',
            ], 404);
        }

        // Verificar la contraseña
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 0,
                'msg' => 'La contraseña es incorrecta.',
            ], 401);  // 401 Unauthorized
        }

        // Generar el token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;

        // Obtener los roles del usuario
        $roles = $user->getRoleNames(); // Colección de nombres de roles

        // Obtener los permisos del usuario
        $permissions = $user->getAllPermissions(); // Colección de permisos

        // Respuesta exitosa con token
        return response()->json([
            'status' => 1,
            'msg' => '¡Usuario logueado exitosamente!',
            'access_token' => $token,
            'user' => $user,
            'token_type' => 'Bearer',
            'permisos' => $permissions,
            'roles' => $roles,  // Buenas prácticas para identificar el tipo de token
            'materias' => $user->materias
        ], 200);
    }

    public function perfil_usuario(): JsonResponse
    {
        $user = Auth::user(); // Usar Auth para mayor claridad

        return response()->json([
            'status' => 1,
            'msg' => 'Perfil de usuario obtenido exitosamente.',
            'data' => $user
        ], 200); // Asegurarse de devolver el código HTTP adecuado
    }

    public function logout(): JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            // Revocar todos los tokens del usuario autenticado
            $user->tokens()->delete();

            return response()->json([
                'status' => 1,
                'msg' => 'Sesión cerrada correctamente.',
            ], 200); // Código HTTP 200 indicando éxito
        }

        return response()->json([
            'status' => 0,
            'msg' => 'No se encontró un usuario autenticado.',
        ], 401); // 401 Unauthorized si no hay usuario autenticado
    }

    // Función para validar el token
    public function validar_token(Request $request)
    {
        // Verificar si el usuario está autenticado
        if (Auth::guard('sanctum')->check()) {
            return response()->json(['success' => 'Token válido.']);
        }

        return response()->json(['error' => 'Token inválido.'], 401);
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
