<?php

namespace App\Http\Controllers;

use App\Models\HistorialMedico;
use Illuminate\Http\Request;

class HistorialMedicoController extends Controller
{
   // 🔍 Buscar historial filtrado por mascota_id, fecha y tipo
public function buscar(Request $request)
{
    $query = HistorialMedico::with('mascota.usuario');

    if ($request->has('mascota_id')) {
        $query->where('mascota_id', $request->mascota_id);
    }

    if ($request->has('fecha')) {
        $query->whereDate('fecha', $request->fecha);
    }

    if ($request->has('tipo')) {
        $query->where('tipo', $request->tipo);
    }

    $resultados = $query->get();

    return response()->json($resultados);
}



    // ➕ Registrar un nuevo evento médico
   public function store(Request $request)
{
    $request->validate([
        'mascota_id' => 'required|exists:mascotas,id',
        'descripcion' => 'required|string',
        'fecha' => 'required|date',
        'tipo' => 'required|string',
        'vacuna_id' => 'nullable|exists:vacunas,id', 
        'desparasitacion_id' => 'nullable|exists:desparasitaciones,id',
        'tratamiento_id' => 'nullable|exists:tratamientos,id',
        'diagnostico_id' => 'nullable|exists:diagnosticos,id'
    ]);

    $nuevoRegistro = HistorialMedico::create([
        'mascota_id' => $request->mascota_id,
        'vacuna_id' => $request->vacuna_id,
        'desparasitacion_id' => $request->desparasitacion_id,
        'tratamiento_id' => $request->tratamiento_id,
        'diagnostico_id' => $request->diagnostico_id,
        'descripcion' => $request->descripcion,
        'fecha' => $request->fecha,
        'tipo' => $request->tipo
    ]);

    return response()->json([
        'mensaje' => 'Historial médico agregado',
        'registro' => $nuevoRegistro
    ]);
}


    // ✏️ Actualiza un historial médico existente
    public function update(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
            'tipo' => 'nullable|string'
        ]);

        $historial = HistorialMedico::find($id);

        if (!$historial) {
            return response()->json(['mensaje' => 'Historial no encontrado'], 404);
        }

        $historial->update([
            'descripcion' => $request->descripcion,
            'fecha' => $request->fecha,
            'tipo' => $request->tipo ?? $historial->tipo
        ]);

        return response()->json([
            'mensaje' => 'Historial actualizado correctamente',
            'registro' => $historial
        ]);
    }

    // ❌ Eliminar un historial
    public function destroy($id)
    {
        $historial = HistorialMedico::find($id);

        if (!$historial) {
            return response()->json(['message' => 'Historial no encontrado'], 404);
        }

        $historial->delete();

        return response()->json(['message' => 'Historial eliminado correctamente'], 200);
    }

    // 📋 Todos los historiales (uso general o para testing)
    public function indexAll()
{
    return response()->json(HistorialMedico::with('mascota.usuario')
->get());
}


    // app/Http/Controllers/HistorialMedicoController.php

public function historialPorMascota($id)
{
    $historiales = HistorialMedico::with('mascota.usuario') // ← incluye dueño aquí
        ->where('mascota_id', $id)
        ->orderBy('fecha', 'desc')
        ->get();

    return response()->json($historiales);
}

public function porVacuna($vacunaId)
{
    $historial = HistorialMedico::with('mascota.usuario')
->where('vacuna_id', $vacunaId)->first();

    if ($historial) {
        return response()->json($historial);
    }

    return response()->json(['mensaje' => 'Historial no encontrado para esta vacuna'], 404);
}



public function actualizarPorVacuna(Request $request, $vacuna_id)
{
    $historial = HistorialMedico::where('vacuna_id', $vacuna_id)->first();
    if (!$historial) {
        return response()->json(['mensaje' => 'Historial no encontrado para esta vacuna'], 404);
    }

    $historial->update($request->all());

    return response()->json(['mensaje' => 'Historial actualizado correctamente', 'historial' => $historial]);
}

public function porTratamiento($tratamientoId)
{
    $historial = HistorialMedico::where('tratamiento_id', $tratamientoId)->first();

    if ($historial) {
        return response()->json($historial);
    }

    return response()->json(['mensaje' => 'Historial no encontrado para este tratamiento'], 404);
}


public function porDiagnostico($diagnosticoId)
{
    $historial = HistorialMedico::where('diagnostico_id', $diagnosticoId)->first();

    if ($historial) {
        return response()->json($historial);
    }

    return response()->json(['mensaje' => 'Historial no encontrado para este diagnostico'], 404);
}



}
