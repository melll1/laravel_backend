<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\HistorialMedicoController;
use App\Http\Controllers\VacunaController;
use App\Http\Controllers\DesparasitacionController;
use App\Http\Controllers\NotificacionController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\AsignacionPaseadorController;
use App\Http\Controllers\MessageController;



// Registro de usuario
Route::post('/register', [AuthController::class, 'register']);

// Login de usuario
Route::post('/login', [AuthController::class, 'login']);


// Verificación de correo electrónico mediante enlace personalizado
Route::get('/verify-link/{id}/{hash}', function ($id, $hash, Request $request) {
    $user = User::findOrFail($id);

    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        return response()->json(['message' => 'Enlace de verificación inválido.'], 403);
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect('http://localhost:4200/login?verified=1&email=' . urlencode($user->email));
})->name('verification.verify.custom');


// Envío de correo para restablecimiento de contraseña
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => '📧 Instrucciones enviadas al correo.'])
        : response()->json(['message' => '❌ No se pudo enviar el correo.'], 422);
});


// Restablecimiento de contraseña con token
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed|min:6',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Contraseña restablecida con éxito.'])
        : response()->json(['message' => __($status)], 400);
});

// Buscador de usuarios (usado al registrar mascota)
Route::get('/buscar-usuarios', [AuthController::class, 'buscar']);

// Buscador de mascotas por nombre, raza, etc.
Route::get('/mascotas/buscar', [MascotaController::class, 'buscar']);

// Después
Route::middleware('auth:api')->group(function () {
    Route::get('/mascotas/usuario', [MascotaController::class, 'mascotasPorUsuario']);
});



// 🔐 Rutas protegidas con autenticación (requieren token Sanctum)
Route::middleware('auth:sanctum')->group(function () {

      // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Perfil del usuario autenticado
    Route::get('/user-profile', function (Request $request) {
        return $request->user();
    });

    // CRUD Mascotas
    Route::post('/mascotas', [MascotaController::class, 'store']); // Crear nueva mascota
    Route::put('/mascotas/{id}', [MascotaController::class, 'update']); // Editar mascota
    Route::delete('/mascotas/{id}', [MascotaController::class, 'destroy']); // Eliminar mascota
    Route::get('/mascotas', [MascotaController::class, 'index']); // Listar mascotas
    Route::get('/mascotas/buscar-avanzado', [MascotaController::class, 'buscarAvanzado']);
    Route::get('/mascotas/{id}', [MascotaController::class, 'show']); // Ver detalles de una mascota
    //Route::get('/mascotas/usuario', [MascotaController::class, 'mascotasPorUsuario']); // Mascotas del usuario autenticado

    

      // 📋 Rutas para historial médico
    Route::get('/mascotas/{id}/historial', [HistorialMedicoController::class, 'index']); // Ver historial por mascota
    Route::post('/historial', [HistorialMedicoController::class, 'store']); // Agregar evento al historial
    Route::delete('/historiales/{id}', [HistorialMedicoController::class, 'destroy']);  // Eliminar evento
    // ✏️ Actualizar un registro del historial médico
Route::put('/historial/{id}', [HistorialMedicoController::class, 'update']);
Route::get('/historiales/buscar', [HistorialMedicoController::class, 'buscar']);



    // ✅ Rutas extra para compatibilidad con Angular (GET y POST /historiales)
    Route::get('/historiales', [HistorialMedicoController::class, 'indexAll']); // Devuelve todos los historiales
    Route::post('/historiales', [HistorialMedicoController::class, 'store']);   // Alternativa para guardar
    Route::get('/historiales/mascota/{id}', [HistorialMedicoController::class, 'historialPorMascota']);
    

    Route::prefix('vacunas')->group(function () {
    Route::get('/', [VacunaController::class, 'index']);
    Route::post('/', [VacunaController::class, 'store']);
    Route::get('/{id}', [VacunaController::class, 'show']);
    Route::put('/{id}', [VacunaController::class, 'update']);
    Route::delete('/{id}', [VacunaController::class, 'destroy']);
    Route::get('/mascota/{mascotaId}', [VacunaController::class, 'porMascota']);

    // 🔧 Esta es la única necesaria para buscar por nombre y fecha:
    
   Route::get('/buscar', [VacunaController::class, 'buscar']);

    



});

Route::put('/historiales/por-vacuna/{vacuna_id}', [HistorialMedicoController::class, 'actualizarPorVacuna']);


Route::prefix('desparasitaciones')->group(function () {
    Route::get('/', [DesparasitacionController::class, 'index']);       // Listar todas
    Route::post('/', [DesparasitacionController::class, 'store']);      // Crear nueva
    Route::get('/{id}', [DesparasitacionController::class, 'show']);
    Route::put('/{id}', [DesparasitacionController::class, 'update']);
    Route::delete('/{id}', [DesparasitacionController::class, 'destroy']);
    Route::get('/mascota/{mascotaId}', [DesparasitacionController::class, 'porMascota']); // ← Nueva
});

Route::prefix('tratamientos')->group(function () {
    Route::get('/', [App\Http\Controllers\TratamientoController::class, 'index']);
    Route::post('/', [App\Http\Controllers\TratamientoController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\TratamientoController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\TratamientoController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\TratamientoController::class, 'destroy']);
    Route::get('/mascota/{mascotaId}', [App\Http\Controllers\TratamientoController::class, 'porMascota']);
});

Route::prefix('diagnosticos')->group(function () {
    Route::get('/', [App\Http\Controllers\DiagnosticoController::class, 'index']);
    Route::post('/', [App\Http\Controllers\DiagnosticoController::class, 'store']);
    Route::get('/mascota/{mascotaId}', [App\Http\Controllers\DiagnosticoController::class, 'porMascota']);
    Route::put('/{id}', [App\Http\Controllers\DiagnosticoController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\DiagnosticoController::class, 'destroy']);
});

Route::get('/historiales/por-tratamiento/{tratamientoId}', [HistorialMedicoController::class, 'porTratamiento']);
Route::get('/historiales/por-diagnostico/{diagnosticoId}', [HistorialMedicoController::class, 'porDiagnostico']);

Route::post('/mascotas/{id}/asignar-paseador', [MascotaController::class, 'asignarPaseador']);
Route::delete('/mascotas/{id}/desasignar-paseador', [MascotaController::class, 'desasignarPaseador']);


   // Paseadores
    Route::get('/paseadores', [AuthController::class, 'listarPaseadores']);

    // Notificaciones
    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::post('/notificaciones', [NotificacionController::class, 'store']);
    Route::put('/notificaciones/{id}', [NotificacionController::class, 'update']);
    // Citas
Route::apiResource('citas', CitaController::class)->only(['index', 'store', 'update', 'destroy']);

// Asignaciones de paseador
Route::get('/asignaciones', [AsignacionPaseadorController::class, 'index']);
Route::post('/asignaciones', [AsignacionPaseadorController::class, 'store']);
Route::delete('/asignaciones/{id}', [AsignacionPaseadorController::class, 'destroy']);
Route::delete('/mascotas/{mascota}/desasignar-paseador/{paseador}', [MascotaController::class, 'desasignarPaseador']);
Route::get('/paseador/mascotas-asignadas', [MascotaController::class, 'mascotasAsignadasAlPaseador']);


Route::post('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas']);

Route::patch('/citas/{id}/responder', [CitaController::class, 'responder']);


    // Mensajes
   Route::get('/messages', [MessageController::class, 'inbox']);
    Route::post('/messages', [MessageController::class, 'send']);
    Route::patch('/messages/{id}/read', [MessageController::class, 'markAsRead']);

// Búsqueda de usuarios por nombre para el formulario de mensajes
Route::get('/users', function (Request $request) {
    $search = $request->query('search');

    return \App\Models\User::where('name', 'like', '%' . $search . '%')
        ->select('id', 'name', 'role')
        ->limit(10)
        ->get();
});
 
Route::get('/destinatarios', function () {
    $user = Auth::user();

    if ($user->role === 'dueno') {
        // El dueño quiere escribir a paseadores asignados a sus mascotas
        $asignaciones = DB::table('asignaciones_paseadores')
            ->join('mascotas', 'asignaciones_paseadores.mascota_id', '=', 'mascotas.id')
            ->join('users as paseadores', 'asignaciones_paseadores.paseador_id', '=', 'paseadores.id')
            ->where('mascotas.user_id', $user->id)
            ->select('paseadores.id', 'paseadores.name', 'paseadores.role', 'mascotas.nombre as mascota', 'mascotas.especie')
            ->get();
    }

    elseif ($user->role === 'paseador') {
        // El paseador quiere escribir a dueños de las mascotas asignadas
        $asignaciones = DB::table('asignaciones_paseadores')
            ->join('mascotas', 'asignaciones_paseadores.mascota_id', '=', 'mascotas.id')
            ->join('users as duenos', 'mascotas.user_id', '=', 'duenos.id')
            ->where('asignaciones_paseadores.paseador_id', $user->id)
            ->select('duenos.id', 'duenos.name', 'duenos.role', 'mascotas.nombre as mascota', 'mascotas.especie')
            ->get();
    }

    else {
        return response()->json([], 403);
    }

    // Agrupar por usuario
    $agrupado = [];

    foreach ($asignaciones as $asig) {
        if (!isset($agrupado[$asig->id])) {
            $agrupado[$asig->id] = [
                'id' => $asig->id,
                'name' => $asig->name,
                'role' => $asig->role,
                'mascotas' => []
            ];
        }
        $agrupado[$asig->id]['mascotas'][] = [
            'nombre' => $asig->mascota,
            'especie' => $asig->especie
        ];
    }

    return array_values($agrupado); // para devolverlo como array limpio
});



});








