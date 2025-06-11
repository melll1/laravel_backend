<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    protected $fillable = [
    'mascota_id',
    'nombre',
    'fecha_inicio',
    'fecha_fin',
    'observaciones',
    'frecuencia_minutos'  // 👈 Añadido
];

    // Relación con la mascota
    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    // Relación inversa con historial médico
    public function historial()
    {
        return $this->hasOne(HistorialMedico::class);
    }
}
