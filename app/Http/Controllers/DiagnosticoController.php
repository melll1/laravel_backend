<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnostico;
use App\Models\HistorialMedico;

class DiagnosticoController extends Controller
{
    public function index()
    {
        return response()->json(Diagnostico::with('mascota')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'titulo' => 'required|string', // <- Añadido
            
        ]);

        $diagnostico = Diagnostico::create($request->all());

        HistorialMedico::create([
            'mascota_id' => $diagnostico->mascota_id,
            'diagnostico_id' => $diagnostico->id,
            'descripcion' => 'Diagnostico: ' . $diagnostico->descripcion,
            'fecha' => $diagnostico->fecha,
            'tipo' => 'Diagnostico'

        ]);

        return response()->json([
            'mensaje' => 'Diagnóstico y historial creado correctamente',
            'diagnostico' => $diagnostico
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $diagnostico = Diagnostico::findOrFail($id);

        $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'titulo' => 'required|string', // <- Añadido
            
        ]);

        $diagnostico->update($request->all());

        $historial = HistorialMedico::where('diagnostico_id', $diagnostico->id)->first();
        if ($historial) {
            $historial->update([
                'descripcion' => 'Diagnostico: ' . $diagnostico->descripcion,
                'fecha' => $diagnostico->fecha,
                'tipo' => 'Diagnostico'
            ]);
        }

        return response()->json(['mensaje' => 'Diagnóstico actualizado correctamente']);
    }

    public function destroy($id)
    {
        $diagnostico = Diagnostico::findOrFail($id);

    // 🔥 Eliminar historial asociado antes
    HistorialMedico::where('diagnostico_id', $diagnostico->id)->delete();

    // Luego eliminar el diagnóstico
    $diagnostico->delete();

    return response()->json(['mensaje' => 'Diagnóstico y su historial eliminado correctamente']);
 }

    public function porMascota($mascotaId)
    {
        $diagnosticos = Diagnostico::where('mascota_id', $mascotaId)->orderBy('fecha', 'desc')->get();
        return response()->json($diagnosticos);
    }
}
