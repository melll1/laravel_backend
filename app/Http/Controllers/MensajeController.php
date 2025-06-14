<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mensaje;
use App\Models\Mascota;
use Illuminate\Support\Facades\Auth;
use App\Models\AsignacionPaseador;
use Illuminate\Support\Facades\Log;

class MensajeController extends Controller
{
    public function index($mascotaId)
    {
        $user = Auth::user();

        $mensajes = Mensaje::where('mascota_id', $mascotaId)
            ->where(function ($query) use ($user) {
                $query->where('emisor_id', $user->id)
                      ->orWhere('receptor_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($mensajes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'receptor_id' => 'required|exists:users,id',
            'contenido' => 'required|string',
        ]);

        $mensaje = Mensaje::create([
            'emisor_id' => Auth::id(),
            'receptor_id' => $request->receptor_id,
            'mascota_id' => $request->mascota_id,
            'contenido' => $request->contenido,
            'leido' => false,
        ]);

        return response()->json($mensaje);
    }

    public function marcarComoLeido($id)
{
    $mensaje = Mensaje::findOrFail($id);

    // Solo el receptor puede marcar el mensaje como leído
    if (Auth::id() !== $mensaje->receptor_id) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    $mensaje->leido = true;
    $mensaje->save();

    return response()->json(['mensaje' => 'Mensaje marcado como leído']);
}

public function conversacionesPorUsuario()
{
    $userId = Auth::id();

    $mensajes = Mensaje::with(['mascota', 'emisor', 'receptor'])
        ->where('emisor_id', $userId)
        ->orWhere('receptor_id', $userId)
        ->orderByDesc('created_at')
        ->get();

    $conversaciones = [];

    foreach ($mensajes as $mensaje) {
        $mascotaId = $mensaje->mascota_id;

        if (!isset($conversaciones[$mascotaId])) {
            $otroUsuario = $mensaje->emisor_id === $userId ? $mensaje->receptor : $mensaje->emisor;

            $conversaciones[$mascotaId] = [
                'mascota' => [
                    'id' => $mensaje->mascota->id,
                    'nombre' => $mensaje->mascota->nombre,
                ],
                'conversacion_con' => [
                    'id' => $otroUsuario->id,
                    'nombre' => $otroUsuario->name,
                    'email' => $otroUsuario->email,
                ],
                'ultimo_mensaje' => [
                    'contenido' => $mensaje->contenido,
                    'fecha' => $mensaje->created_at->toDateTimeString(),
                ],
            ];
        }
    }

    return response()->json(array_values($conversaciones));
}

public function mascotasSinConversacion()
{
    $user = Auth::user();

    // Obtener IDs de mascotas del dueño
    $mascotasDelDueno = \App\Models\Mascota::where('user_id', $user->id)->pluck('id');

    // Obtener asignaciones activas (puedes filtrar por fechas si lo deseas también)
    $asignaciones = \App\Models\AsignacionPaseador::with(['mascota', 'paseador'])
        ->whereIn('mascota_id', $mascotasDelDueno)
        ->get();

    // Obtener IDs de mascotas que ya tienen mensajes
    $mascotasConMensajes = \App\Models\Mensaje::whereIn('mascota_id', $mascotasDelDueno)
        ->pluck('mascota_id')
        ->unique();

    // Filtrar las asignaciones cuya mascota aún no tiene mensajes
    $sinConversacion = $asignaciones->filter(function ($asignacion) use ($mascotasConMensajes) {
        return !$mascotasConMensajes->contains($asignacion->mascota_id);
    });

    // Mapear los resultados
    $resultado = $sinConversacion->map(function ($asignacion) {
        return [
            'mascotaId' => $asignacion->mascota->id,
            'mascotaNombre' => $asignacion->mascota->nombre,
            'paseador' => [
                'id' => $asignacion->paseador->id,
                'nombre' => $asignacion->paseador->name,
                'email' => $asignacion->paseador->email,
            ],
        ];
    });

    return response()->json($resultado->values());
}




}
