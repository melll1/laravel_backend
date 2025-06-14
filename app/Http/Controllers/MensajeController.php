<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mensaje;
use App\Models\Mascota;
use App\Models\AsignacionPaseador;
use Illuminate\Support\Facades\Auth;
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

        if (Auth::id() !== $mensaje->receptor_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $mensaje->leido = true;
        $mensaje->save();

        return response()->json(['mensaje' => 'Mensaje marcado como leído']);
    }

    public function conversacionesPorUsuario(Request $request)
{
    $user = Auth::user();
    $userId = $user->id;

    Log::info('✅ Entró a conversacionesPorUsuario', ['auth_user_id' => $userId]);

    $mensajes = Mensaje::with(['mascota', 'emisor', 'receptor'])
        ->where(function ($query) use ($userId) {
            $query->where('emisor_id', $userId)
                  ->orWhere('receptor_id', $userId);
        })
        ->orderByDesc('created_at')
        ->get();

    Log::info('📦 Total mensajes recuperados:', ['count' => $mensajes->count()]);

    if ($mensajes->isEmpty()) {
        Log::warning('⚠️ No se encontraron mensajes para el usuario', ['user_id' => $userId]);
        return response()->json([]); // ← Aquí ya cortamos si no hay nada
    }

    $conversaciones = [];

    foreach ($mensajes as $mensaje) {
        $otroUsuario = $mensaje->emisor_id === $userId ? $mensaje->receptor : $mensaje->emisor;

        $key = $mensaje->mascota_id . '-' . $otroUsuario->id;

        if (!isset($conversaciones[$key])) {
            $conversaciones[$key] = [
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

    Log::info('✅ Conversaciones generadas', ['total' => count($conversaciones)]);
    return response()->json(array_values($conversaciones));
}



    public function mascotasSinConversacion()
    {
        $user = Auth::user();

        $mascotasDelDueno = Mascota::where('user_id', $user->id)->pluck('id');

        $asignaciones = AsignacionPaseador::with(['mascota', 'paseador'])
            ->whereIn('mascota_id', $mascotasDelDueno)
            ->get();

        $mensajes = Mensaje::where(function ($query) use ($user) {
            $query->where('emisor_id', $user->id)
                  ->orWhere('receptor_id', $user->id);
        })->get();

        $combinacionesConMensaje = $mensajes->map(function ($msg) use ($user) {
            $otroUsuarioId = $msg->emisor_id === $user->id ? $msg->receptor_id : $msg->emisor_id;
            return $msg->mascota_id . '-' . $otroUsuarioId;
        });

        $sinConversacion = $asignaciones->filter(function ($asignacion) use ($combinacionesConMensaje) {
            $clave = $asignacion->mascota_id . '-' . $asignacion->paseador->id;
            return !$combinacionesConMensaje->contains($clave);
        });

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
