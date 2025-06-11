<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    // Obtener notificaciones del usuario autenticado
    public function index(Request $request)
    {
        $user = Auth::user();

        $notificaciones = Notificacion::where(function ($q) use ($user) {
            $q->where('dueno_id', $user->id)
              ->orWhere('paseador_id', $user->id)
              ->orWhere('veterinario_id', $user->id);
        })->orderBy('fecha_notificacion', 'desc')->get();

        return response()->json($notificaciones);
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

        $user = Auth::user();
        $validated['leido'] = false;
        $validated['veterinario_id'] = $user->id;

        $notificacion = Notificacion::create($validated);

        return response()->json($notificacion, 201);
    }

    // Marcar como leída
    public function update($id)
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->leido = true;
        $notificacion->save();

        return response()->json(['mensaje' => 'Notificación marcada como leída']);
    }
}
