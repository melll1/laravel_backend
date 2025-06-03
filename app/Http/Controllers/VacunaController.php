<?php

namespace App\Http\Controllers;

use App\Models\Vacuna;
use Illuminate\Http\Request;
use App\Models\HistorialMedico;




class VacunaController extends Controller
{
    public function index()
    {
        return response()->json(Vacuna::with('mascota')->get());
    }

    public function store(Request $request)
{
    $request->validate([
        'mascota_id' => 'required|exists:mascotas,id',
        'nombre' => 'required|string',
        'fecha_aplicacion' => 'required|date',
        'proxima_dosis' => 'nullable|date',
        'lote' => 'nullable|string',
        'observaciones' => 'nullable|string',
    ]);

    // 1. Primero crea la vacuna y la guarda
    $vacuna = new Vacuna($request->all());
    $vacuna->save(); // 💾 Se guarda y genera el ID

    // 2. Solo si la vacuna se guardó correctamente
    if ($vacuna->id) {
        // 3. Crear el historial con el ID correcto de la vacuna
        HistorialMedico::create([
            'mascota_id' => $vacuna->mascota_id,
            'vacuna_id' => $vacuna->id, // ✅ Este campo ya no será null
            'descripcion' => 'Vacuna aplicada: ' . $vacuna->nombre,
            'fecha' => $vacuna->fecha_aplicacion,
            'tipo' => 'Vacuna'
        ]);
    }

    return response()->json([
        'mensaje' => 'Vacuna y historial guardados correctamente',
        'vacuna' => $vacuna
    ], 201);
}




    public function show($id)
    {
        $vacuna = Vacuna::find($id);
        return $vacuna ? response()->json($vacuna) : response()->json(['mensaje' => 'Vacuna no encontrada'], 404);
    }

    public function update(Request $request, $id)
{
    $vacuna = Vacuna::find($id);
    if (!$vacuna) return response()->json(['mensaje' => 'Vacuna no encontrada'], 404);

    $vacuna->update($request->all());

    // Buscar y actualizar historial médico vinculado
    $historial = HistorialMedico::where('vacuna_id', $vacuna->id)->first();
    if ($historial) {
        $historial->update([
            'descripcion' => 'Vacuna aplicada: ' . $vacuna->nombre,
            'fecha' => $vacuna->fecha_aplicacion,
            'tipo' => 'Vacuna',
        ]);
    }

    return response()->json([
        'mensaje' => 'Vacuna y historial actualizados correctamente',
        'vacuna' => $vacuna
    ]);
}

     public function destroy($id)
    {
        $vacuna = Vacuna::find($id);
        if (!$vacuna) return response()->json(['mensaje' => 'Vacuna no encontrada'], 404);

        $vacuna->delete(); // Elimina automáticamente el historial si la FK tiene onDelete('cascade')

        return response()->json(['mensaje' => 'Vacuna eliminada correctamente']);
    }


    public function porMascota($mascotaId)
    {
        $vacunas = Vacuna::where('mascota_id', $mascotaId)->get();
        return response()->json($vacunas);
    }

 public function buscar(Request $request)
{
    $vacuna = Vacuna::where('mascota_id', $request->mascota_id)
        ->whereDate('fecha_aplicacion', $request->fecha_aplicacion)
        ->where('nombre', 'like', '%' . trim($request->nombre) . '%')
        ->first();

    return $vacuna
        ? response()->json($vacuna)
        : response()->json(['mensaje' => 'Vacuna no encontrada'], 404);
}








}

