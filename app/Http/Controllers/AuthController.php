<?php

namespace App\Http\Controllers;use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\LoginHistory;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validar entrada del frontend
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'telefono' => 'required|string',
            'role' => 'required|string',
        ]);

        // Si falla la validación
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Crear nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'role' => $request->role,
        ]);

        // Enviar notificación de verificación de email
        event(new Registered($user));

        // Respuesta al frontend
        return response()->json([
            'message' => 'Usuario registrado. Por favor revisa tu correo para verificar la cuenta.'
        ], 201);
    }
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $user = \Illuminate\Support\Facades\Auth::user();

    // ✅ Verificar email si el enlace fue accedido antes del login
    if (!$user->hasVerifiedEmail() && $request->query('verified') === '1') {
        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    // ✅ Crear token
    $token = $user->createToken('auth_token')->plainTextToken;

    // Crear registro de inicio de sesión
    UserSession::create([
        'user_id' => $user->id,
        'action' => 'login',
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return response()->json([
        'message' => 'Inicio de sesión exitoso',
        'user' => $user,
        'token' => $token
    ]);
}



public function logout(Request $request)
{
    $user = $request->user();

    // Actualiza la última sesión activa (sin logout)
    UserSession::where('user_id', $user->id)
        ->whereNull('logout_at')
        ->latest()
        ->first()?->update([
            'logout_at' => now()
        ]);

    // ✅ Eliminar token actual
  /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
$token = $request->user()->currentAccessToken();
if ($token) {
    $token->delete();
}
    return response()->json(['message' => 'Cierre de sesión exitoso']);
}


public function buscar(Request $request)
{
    $query = $request->input('query');
    $rol = $request->input('rol'); // ✅ Nuevo parámetro para el rol

    $usuarios = User::when($rol, function ($q) use ($rol) {
            return $q->where('role', $rol); // ✅ Solo si se pasa el rol
        })
        ->where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
              ->orWhere('email', 'like', '%' . $query . '%');
        })
        ->get(['id', 'name', 'email', 'role']);

    return response()->json($usuarios);
}


public function listarPaseadores()
{
    $paseadores = User::where('role', 'paseador')->get();
    return response()->json($paseadores);
}



}

