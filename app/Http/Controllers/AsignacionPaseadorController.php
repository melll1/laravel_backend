<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionPaseador;
use Illuminate\Support\Facades\Auth;

class AsignacionPaseadorController extends Controller
{
    // 🔹 Asignar paseador (acceso solo por dueño)
    public function store(Request $request)
    {
        $request->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'paseador_id' => 'required|exists:users,id',
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $user = Auth::user();

        // Verificación opcional: asegurar que la mascota es del dueño autenticado
        if ($user->role !== 'dueno') {
            return response()->json(['error' => 'Solo dueños pueden asignar paseadores'], 403);
        }

        $asignacion = AsignacionPaseador::create($request->only(['mascota_id', 'paseador_id', 'desde', 'hasta']));
        return response()->json($asignacion, 201);
    }

    // 🔹 Ver asignaciones de un paseador actual (opcional)
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'paseador') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return AsignacionPaseador::where('paseador_id', $user->id)->whereDate('hasta', '>=', now())->get();
    }

    // 🔹 Eliminar asignación (opcional, solo dueño)
    public function destroy($id)
    {
        $asignacion = AsignacionPaseador::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'dueno') {
            return response()->json(['error' => 'Solo dueños pueden eliminar asignaciones'], 403);
        }

        $asignacion->delete();
        return response()->json(['message' => 'Asignación eliminada']);
    }
}
