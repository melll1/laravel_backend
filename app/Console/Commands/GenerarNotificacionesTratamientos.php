<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tratamiento;
use App\Models\Notificacion;
use Carbon\Carbon;

class GenerarNotificacionesTratamientos extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'notificaciones:generar-tratamientos';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Genera notificaciones automáticas para tratamientos según su frecuencia';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle()
    {
        $tratamientos = Tratamiento::with('mascota')->get();

        foreach ($tratamientos as $tratamiento) {
            $fechaInicio = Carbon::parse($tratamiento->fecha_inicio);
            $fechaFin = $tratamiento->fecha_fin ? Carbon::parse($tratamiento->fecha_fin) : $fechaInicio->copy()->addDays(7);
            $frecuenciaHoras = $tratamiento->frecuencia_horas;
            $mascota = $tratamiento->mascota;
            $duenoId = $mascota->usuario_id;

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
                        'veterinario_id' => $tratamiento->veterinario_id,
                        'dueno_id' => $duenoId,
                        'mensaje' => 'Recordatorio: administrar ' . $tratamiento->nombre . ' a ' . $mascota->nombre,
                        'tipo' => 'Tratamiento',
                        'fecha_notificacion' => $hora,
                        'leido' => false,
                    ]);
                }

                $horaActual->addHours($frecuenciaHoras);
            }
        }

        $this->info('Notificaciones generadas correctamente.');
        return 0;
    }
}
