<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacuna extends Model
{
    use HasFactory;

    protected $fillable = [
        'mascota_id',
        'nombre',
        'fecha_aplicacion',
        'proxima_dosis',
        'lote',
        'observaciones',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function historial()
{
    return $this->hasOne(HistorialMedico::class);
}

}
