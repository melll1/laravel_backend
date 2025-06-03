<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desparasitacion extends Model
{
    use HasFactory;

    protected $table = 'desparasitaciones'; 

   protected $fillable = [
    'mascota_id',
    'nombre',
    'fecha_aplicacion', // ✅ Este es el correcto
    'proxima_dosis',
    'tipo',
    'observaciones',
];


    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function historial()
{
    return $this->hasOne(HistorialMedico::class, 'desparasitacion_id');
}

}
// 🐾 Modelo para Desparasitaciones