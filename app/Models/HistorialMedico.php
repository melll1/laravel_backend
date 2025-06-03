<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMedico extends Model
{
    use HasFactory;

    // 🧾 Campos que se pueden llenar masivamente
    protected $fillable = [
        'mascota_id',
    'vacuna_id', // <-- Añade esta línea
    'desparasitacion_id', // <-- Añade esta línea
    'descripcion',
    'fecha',
    'tipo'
    ];

    // 🔗 Relación: un historial pertenece a una mascota
    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function vacuna()
{
    return $this->belongsTo(Vacuna::class);
}
}
