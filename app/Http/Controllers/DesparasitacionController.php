<?php

namespace App\Http\Controllers;

use App\Models\Desparasitacion;
use App\Models\HistorialMedico;
use Illuminate\Http\Request;

class DesparasitacionController extends Controller
{
    public function index()
    {
        return response()->json(Desparasitacion::with('mascota')->get());
    }

  public function store(Request $request)
{
    $request->validate([
        'mascota_id' => 'required|exists:mascotas,id',
        'nombre' => 'required|string',
        'fecha_aplicacion' => 'required|date',
        'proxima_dosis' => 'nullable|date',
        'tipo' => 'required|in:Interna,Externa',
        'observaciones' => 'nullable|string'
    ]);

    // 1. Crear desparasitación
    $desparasitacion = Desparasitacion::create([
        'mascota_id' => $request->mascota_id,
        'nombre' => $request->nombre,
        'fecha_aplicacion' => $request->fecha_aplicacion,
        'proxima_dosis' => $request->proxima_dosis,
        'tipo' => $request->tipo,
        'observaciones' => $request->observaciones
    ]);

    // Verificar si ya existe un historial con esta desparasitación
        $existeHistorial = HistorialMedico::where('mascota_id', $request->mascota_id)
            ->where('desparasitacion_id', $desparasitacion->id)
            ->exists();

        if (!$existeHistorial) {
            $descripcion = 'Desparasitación ' . strtolower($request->tipo) . ': ' . $request->nombre;

            HistorialMedico::create([
                'mascota_id' => $request->mascota_id,
                'desparasitacion_id' => $desparasitacion->id,
                'descripcion' => $descripcion,
                'fecha' => $request->fecha_aplicacion,
                'tipo' => 'Desparasitación',
            ]);
        }

        return response()->json([
            'mensaje' => 'Desparasitación y historial guardados correctamente.',
            'desparasitacion' => $desparasitacion
        ], 201);
    }


    public function porMascota($mascotaId)
{
    $desparasitaciones = Desparasitacion::where('mascota_id', $mascotaId)
        ->orderBy('fecha_aplicacion', 'desc')
        ->get();

    return response()->json($desparasitaciones);
}

}
// 🐾 Controlador para manejar desparasitaciones
