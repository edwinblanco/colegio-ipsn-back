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
            /*$role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

            // Crear un permiso
            $permission = Permission::create(['name' => 'crear examen', 'guard_name' => 'api']);
            $permission2 = Permission::create(['name' => 'editar examen', 'guard_name' => 'api']);
            $role->givePermissionTo($permission);
            $role->givePermissionTo($permission2);

            $user->assignRole($role);*/

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
        $user = User::where('numero_documento', $request->numero_documento)->with('grado')->first();

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

    public function ver_estudiantes(Request $request)
    {
        // Obtener los usuarios con el rol 'estudiante' usando Spatie
        $estudiantes = User::role('estudiante')->with('grado')->with('sede')->get();

        // Verificar si hay estudiantes
        if ($estudiantes->isEmpty()) {
            return response()->json([
                'status' => 0,
                'msg' => 'No se encontraron estudiantes.',
                'data' => []
            ], 404);
        }

        // Responder con los estudiantes encontrados
        return response()->json([
            'status' => 1,
            'msg' => 'Estudiantes obtenidos exitosamente.',
            'data' => $estudiantes
        ], 200);
    }

    public function registro_estudiante(Request $request): JsonResponse
    {

        // Validar los datos del request
        $request->validate([
            'primer_nombre' => 'required|string|max:15',
            'segundo_nombre' => 'nullable|string|max:15',
            'primer_apellido' => 'required|string|max:15',
            'segundo_apellido' => 'nullable|string|max:15',
            'numero_documento' => 'required|string|max:30|unique:users',
            'fecha_nacimiento' => 'required|date',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|min:6',
            'estado' => 'required|in:activo,inactivo,graduado,expulsado',
            'grado_id' => 'required|exists:grados,id',
            'sede_id' => 'required|exists:sedes,id'
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
                'grado_id' => $request->grado_id,
                'id_sede' => $request->sede_id
            ]);

            $user->assignRole('estudiante');

            // Respuesta JSON exitosa
            return response()->json([
                'status' => 1,
                'msg' => '¡Registro de estudiante exitoso!',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            // Manejo básico de errores
            return response()->json([
                'status' => 0,
                'msg' => 'Error al registrar el estudiante. Intente nuevamente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizar_estudiante(Request $request, $id): JsonResponse
    {
        // Validar los datos del request
        $request->validate([
            'primer_nombre' => 'required|string|max:15',
            'segundo_nombre' => 'nullable|string|max:15',
            'primer_apellido' => 'required|string|max:15',
            'segundo_apellido' => 'nullable|string|max:15',
            'numero_documento' => 'required|string|max:30|unique:users,numero_documento,' . $id,
            'fecha_nacimiento' => 'required|date',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6', // Opcional si no desea cambiar la contraseña
            'estado' => 'required|in:activo,inactivo,graduado,expulsado',
            'grado_id' => 'required|exists:grados,id',
            'sede_id' => 'required|exists:sedes,id'
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser una dirección de correo válida.',
            'max' => 'El campo :attribute no debe tener más de :max caracteres.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'unique' => 'El campo :attribute ya está registrado.',
            'in' => 'El campo :attribute debe ser uno de los siguientes valores: activo, inactivo, graduado o expulsado.',
        ]);

        try {
            // Buscar el usuario por su ID
            $user = User::findOrFail($id);

            // Actualizar los datos del usuario
            $user->primer_nombre = $request->primer_nombre;
            $user->segundo_nombre = $request->segundo_nombre;
            $user->primer_apellido = $request->primer_apellido;
            $user->segundo_apellido = $request->segundo_apellido;
            $user->numero_documento = $request->numero_documento;
            $user->fecha_nacimiento = $request->fecha_nacimiento;
            $user->email = $request->email;
            $user->estado = $request->estado;
            $user->grado_id = $request->grado_id;
            $user->id_sede = $request->sede_id;

            // Actualizar la contraseña solo si se proporciona
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Guardar los cambios en la base de datos
            $user->save();

            // Respuesta JSON exitosa
            return response()->json([
                'status' => 1,
                'msg' => '¡Actualización de estudiante exitosa!',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            // Manejo básico de errores
            return response()->json([
                'status' => 0,
                'msg' => 'Error al actualizar el estudiante. Intente nuevamente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminar_estudiante($id): JsonResponse
    {
        try {
            // Buscar el usuario por su ID
            $user = User::findOrFail($id);

            // Eliminar el usuario
            $user->delete();

            // Respuesta JSON exitosa
            return response()->json([
                'status' => 1,
                'msg' => '¡Estudiante eliminado exitosamente!',
            ], 200);
        } catch (\Exception $e) {
            // Manejo básico de errores
            return response()->json([
                'status' => 0,
                'msg' => 'Error al eliminar el estudiante. Intente nuevamente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {

    }
}
