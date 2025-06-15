<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\HistorialMedico;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
            'observaciones' => 'nullable|string',
            'frecuencia_minutos' => 'required|integer|min:1'
        ]);

        $tratamiento = Tratamiento::create([
            'mascota_id' => $request->mascota_id,
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'descripcion' => $request->descripcion,
            'observaciones' => $request->observaciones,
            'frecuencia_minutos' => $request->frecuencia_minutos
        ]);

        HistorialMedico::create([
            'mascota_id' => $tratamiento->mascota_id,
            'tratamiento_id' => $tratamiento->id,
            'descripcion' => 'Tratamiento: ' . $tratamiento->nombre,
            'fecha' => $tratamiento->fecha_inicio,
            'tipo' => 'Tratamiento'
        ]);

        $fechaInicio = Carbon::parse($tratamiento->fecha_inicio);
        $fechaFin = $tratamiento->fecha_fin ? Carbon::parse($tratamiento->fecha_fin) : $fechaInicio->copy()->addDays(7);
        $frecuenciaMinutos = $tratamiento->frecuencia_minutos;
        $mascota = $tratamiento->mascota;
        $duenoId = $mascota->usuario?->id;

        $horaActual = $fechaInicio->copy();
        while ($horaActual->lte($fechaFin)) {
            $horariosNotificacion = [
                $horaActual->copy()->subMinutes(15),
                $horaActual->copy()->subMinutes(5),
                $horaActual->copy(),
            ];

            foreach ($horariosNotificacion as $hora) {
                Notificacion::create([
                    'mascota_id' => $mascota->id,
                    'veterinario_id' => NULL,
                    'dueno_id' => $duenoId,
                    'mensaje' => 'Recordatorio: administrar ' . $tratamiento->nombre . ' a ' . $mascota->nombre,
                    'tipo' => 'Tratamiento',
                    'fecha_notificacion' => $hora,
                    'leido' => false,
                ]);
            }

            $horaActual->addMinutes($frecuenciaMinutos);
        }

        return response()->json([
            'mensaje' => 'Tratamiento, historial y notificaciones generados correctamente',
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
            'observaciones' => 'nullable|string',
            'frecuencia_minutos' => 'required|integer|min:1'
        ]);

        $tratamiento->update($request->all());

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

        $tratamiento->delete();

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
