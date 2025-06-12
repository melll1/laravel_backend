<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacion;
use App\Models\Mascota;
use App\Models\User;



class CitaController extends Controller
{
    // 🔹 Ver citas según el rol
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'veterinario') {
             return Cita::with(['mascota.usuario', 'mascota.asignaciones', 'dueno'])->get();
        }

        if ($user->role === 'dueno') {
                return Cita::with(['mascota.usuario', 'mascota.asignaciones'])->where('dueno_id', $user->id)->get();

        }

        if ($user->role === 'paseador') {
             return Cita::with(['mascota.usuario', 'mascota.asignaciones'])
                ->whereHas('mascota', function ($query) use ($user) {
                    $query->whereHas('asignaciones', function ($q) use ($user) {
                        $q->where('paseador_id', $user->id)
                          ->whereDate('desde', '<=', now())
                          ->whereDate('hasta', '>=', now());
                    });
                })->get();
        }

        return response()->json(['error' => 'Rol no autorizado'], 403);
    }

    // 🔹 Crear una cita (solo veterinario o dueno)
   public function store(Request $request)
{
    $user = Auth::user();

    // Validamos los datos de entrada
    $validated = $request->validate([
        'mascota_id' => 'required|exists:mascotas,id',
        'fecha_hora' => 'required|date',
        'motivo' => 'nullable|string',
    ]);

    // Creamos una nueva cita
    $cita = new Cita();
    $cita->mascota_id = $validated['mascota_id'];
    $cita->fecha_hora = $validated['fecha_hora'];
    $cita->motivo = $validated['motivo'] ?? null;

    // Asignamos siempre el veterinario con ID 1 al dueño al crear una cita
    if ($user->role === 'veterinario') {
        $cita->veterinario_id = $user->id;
        $cita->dueno_id = $request->input('dueno_id');
        $cita->estado = 'aceptada';
    } elseif ($user->role === 'dueno') {
        $mascota = Mascota::findOrFail($validated['mascota_id']);

        // Asignamos siempre al veterinario con ID 1
        $cita->dueno_id = $user->id;
        $cita->veterinario_id = 1; // Siempre asignamos el veterinario con ID 1
        $cita->estado = 'pendiente';
    } else {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    // Guardamos la cita
    $cita->save();

    // Enviar notificación si la cita fue creada por un dueño
    if ($user->role === 'dueno') {
        Notificacion::create([
            'mascota_id' => $cita->mascota_id,
            'dueno_id' => $cita->dueno_id,
            'veterinario_id' => $cita->veterinario_id,
            'tipo' => 'Cita',
            'mensaje' => "Nueva cita solicitada para revisar a la mascota el {$cita->fecha_hora}",
            'fecha_notificacion' => now(),
            'leido' => false,
        ]);
    }

    // Retornamos la cita creada
    return response()->json($cita, 201);
}

public function update(Request $request, $id)
{
    $cita = Cita::findOrFail($id);
    $user = Auth::user();

    if ($user->id !== $cita->veterinario_id) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    $validated = $request->validate([
        'fecha_hora' => 'nullable|date',
        'motivo' => 'nullable|string',
        'estado' => 'nullable|in:pendiente,aceptada,modificada,rechazada'
    ]);

    $cita->update($validated);

    if (isset($validated['estado'])) {
        // Enviar notificaciones a todos los veterinarios
        if ($validated['estado'] === 'aceptada') {
            $veterinarios = User::where('role', 'veterinario')->get();
            foreach ($veterinarios as $veterinario) {
                Notificacion::create([
                    'mascota_id' => $cita->mascota_id,
                    'dueno_id' => $cita->dueno_id,
                    'veterinario_id' => $veterinario->id,
                    'tipo' => 'Cita Aceptada',
                    'mensaje' => "Tu cita para el {$cita->fecha_hora} fue aceptada por el veterinario.",
                    'fecha_notificacion' => now(),
                    'leido' => false,
                ]);
            }
        }

        if ($validated['estado'] === 'rechazada') {
            $veterinarios = User::where('role', 'veterinario')->get();
            foreach ($veterinarios as $veterinario) {
                Notificacion::create([
                    'mascota_id' => $cita->mascota_id,
                    'dueno_id' => $cita->dueno_id,
                    'veterinario_id' => $veterinario->id,
                    'tipo' => 'Cita Rechazada',
                    'mensaje' => "Tu cita fue rechazada por el veterinario. Por favor, elige otra fecha.",
                    'fecha_notificacion' => now(),
                    'leido' => false,
                ]);
            }
        }
    }

    return response()->json($cita);
}


    // 🔹 Eliminar cita (opcional)
    public function destroy($id)
    {
        $cita = Cita::findOrFail($id);

        if (Auth::id() !== $cita->veterinario_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cita->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }
    
}
