<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\HistorialMedico;
use Illuminate\Http\Request;

class TratamientoController extends Controller
{
    public function index()
    {
        return response()->json(Tratamiento::with('mascota')->get());
    }

    public function store(Request $request)
{
    $request->validate([
        'mascota_id' => 'required|exists:mascotas,id',
        'nombre' => 'required|string',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'nullable|date',
        'descripcion' => 'nullable|string',
        'observaciones' => 'nullable|string'
    ]);

    // Crear el tratamiento
    $tratamiento = Tratamiento::create([
        'mascota_id' => $request->mascota_id,
        'nombre' => $request->nombre,
        'fecha_inicio' => $request->fecha_inicio,
        'fecha_fin' => $request->fecha_fin,
        'descripcion' => $request->descripcion,
        'observaciones' => $request->observaciones
    ]);

    // Crear historial médico asociado
    HistorialMedico::create([
        'mascota_id' => $tratamiento->mascota_id,
        'tratamiento_id' => $tratamiento->id,
        'descripcion' => 'Tratamiento: ' . $tratamiento->nombre,
        'fecha' => $tratamiento->fecha_inicio,
        'tipo' => 'Tratamiento'
    ]);

    return response()->json([
        'mensaje' => 'Tratamiento y historial guardados correctamente',
        'tratamiento' => $tratamiento
    ], 201);
}


public function update(Request $request, $id)
{
    $tratamiento = Tratamiento::find($id);
    if (!$tratamiento) {
        return response()->json(['mensaje' => 'Tratamiento no encontrado'], 404);
    }

    $request->validate([
        'nombre' => 'required|string',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'nullable|date',
        'descripcion' => 'nullable|string',
        'observaciones' => 'nullable|string'
    ]);

    $tratamiento->update($request->all());

    // Actualizar historial médico vinculado
    $historial = HistorialMedico::where('tratamiento_id', $tratamiento->id)->first();
    if ($historial) {
        $historial->update([
            'descripcion' => 'Tratamiento: ' . $tratamiento->nombre,
            'fecha' => $tratamiento->fecha_inicio,
            'tipo' => 'Tratamiento'
        ]);
    }

    return response()->json([
        'mensaje' => 'Tratamiento y historial actualizados correctamente',
        'tratamiento' => $tratamiento
    ]);
}


public function destroy($id)
{
    $tratamiento = Tratamiento::find($id);
    if (!$tratamiento) {
        return response()->json(['mensaje' => 'Tratamiento no encontrado'], 404);
    }

    $tratamiento->delete(); // Si la FK en historial tiene `onDelete('cascade')`, eliminará el historial automáticamente

    return response()->json(['mensaje' => 'Tratamiento eliminado correctamente']);
}

public function porMascota($mascotaId)
{
    $tratamientos = Tratamiento::where('mascota_id', $mascotaId)
        ->orderBy('fecha_inicio', 'desc')
        ->get();

    return response()->json($tratamientos);
}


}
