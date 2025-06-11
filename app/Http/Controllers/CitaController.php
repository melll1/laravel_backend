<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $validated = $request->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'fecha_hora' => 'required|date',
            'motivo' => 'nullable|string',
        ]);

        $cita = new Cita();
        $cita->mascota_id = $validated['mascota_id'];
        $cita->fecha_hora = $validated['fecha_hora'];
        $cita->motivo = $validated['motivo'] ?? null;

        if ($user->role === 'veterinario') {
            $cita->veterinario_id = $user->id;
            $cita->dueno_id = $request->input('dueno_id');
            $cita->estado = 'aceptada';
        } elseif ($user->role === 'dueno') {
            $cita->dueno_id = $user->id;
            $cita->estado = 'pendiente';
            $cita->veterinario_id = $request->input('veterinario_id');
        } else {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cita->save();
        return response()->json($cita, 201);
    }

    // 🔹 Actualizar cita (solo veterinario)
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
