<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notificacion;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    // Obtener notificaciones del usuario autenticado
   public function index(Request $request)
{
    $user = Auth::user(); // Obtiene el usuario autenticado

    // Lógica para obtener las notificaciones según el rol
    if ($user->role === 'dueno') {
        // El dueño solo recibe notificaciones de las mascotas que tiene
        $notificaciones = Notificacion::where('dueno_id', $user->id)
            ->orderBy('fecha_notificacion', 'desc')
            ->get();
    } elseif ($user->role === 'paseador') {
        // El paseador solo recibe notificaciones de las mascotas asignadas
        $notificaciones = Notificacion::where('paseador_id', $user->id)
            ->orderBy('fecha_notificacion', 'desc')
            ->get();
    } elseif ($user->role === 'veterinario') {
        // El veterinario solo recibe notificaciones sobre citas creadas por los dueños
        $notificaciones = Notificacion::where('veterinario_id', $user->id)
            ->orderBy('fecha_notificacion', 'desc')
            ->get();
    } else {
        // Si el rol no está definido, se retorna error
        return response()->json(['error' => 'Rol no autorizado.'], 403);
    }

    return response()->json($notificaciones);  // Devolvemos las notificaciones correspondientes al usuario
}


    // Crear una notificación
    public function store(Request $request)
{
    $validated = $request->validate([
        'mascota_id' => 'required|exists:mascotas,id',
        'tipo' => 'required|string',
        'mensaje' => 'required|string',
        'fecha_notificacion' => 'required|date',
    ]);

    $user = Auth::user(); // Usuario autenticado
    $validated['leido'] = false;
    $mascota = Mascota::with('usuario', 'paseadores', 'veterinario')->findOrFail($validated['mascota_id']);

    // Notificación al dueño de la mascota
    Notificacion::create(array_merge($validated, [
        'dueno_id' => $mascota->usuario->id, // Dueño
    ]));

    // Verificar paseadores activos y enviar notificación a paseadores asignados
    $hoy = now()->toDateString();
    foreach ($mascota->paseadores as $paseador) {
        $desde = $paseador->pivot->desde;
        $hasta = $paseador->pivot->hasta;

        if ($desde <= $hoy && (is_null($hasta) || $hasta >= $hoy)) {
            Notificacion::create(array_merge($validated, [
                'paseador_id' => $paseador->id, // Paseador
            ]));
        }
    }

    // Notificación para el veterinario (si es sobre una cita pruebacreada por un dueño)
    if ($request->tipo === 'Cita') {
        Notificacion::create(array_merge($validated, [
            'veterinario_id' => $mascota->veterinario_id, // Veterinario asignado
        ]));
    }

    return response()->json(['mensaje' => 'Notificaciones creadas'], 201);
}


    // Marcar como leída
    public function update($id)
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->leido = true;
        $notificacion->save();

        return response()->json(['mensaje' => 'Notificación marcada como leída']);
    }

    
    public function marcarTodasLeidas()
{
    $user = Auth::user();

    // Asegúrate de que el usuario tiene permisos para marcar las notificaciones
    $notificaciones = Notificacion::where('veterinario_id', $user->id) // o cualquier otra lógica de autorización
        ->orWhere('dueno_id', $user->id)
        ->orWhere('paseador_id', $user->id)
        ->where('leido', false) // Solo marca las no leídas
        ->get();

    foreach ($notificaciones as $notificacion) {
        $notificacion->leido = true;
        $notificacion->save();
    }

    return response()->json(['mensaje' => 'Todas las notificaciones han sido marcadas como leídas.']);
}

}
