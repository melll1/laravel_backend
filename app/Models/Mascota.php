<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    use HasFactory;

   protected $fillable = [
    'user_id',
    'nombre',
    'especie',
    'raza',
    'edad',
    'sexo',
    'fecha_nacimiento',
    'microchip',
    'color',
    'esterilizado',
    'descripcion',
    'foto', // 📷 Campo agregado para guardar la ruta de la foto
];


    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vacunas() {
    return $this->hasMany(Vacuna::class);
}
public function paseadores()
{
    return $this->belongsToMany(User::class, 'asignaciones_paseadores', 'mascota_id', 'paseador_id')
                ->withPivot('desde', 'hasta')
                ->withTimestamps();
}


public function asignaciones()
{
    return $this->hasMany(\App\Models\AsignacionPaseador::class);
}

public function veterinario()
{
    return $this->belongsTo(User::class, 'veterinario_id');  // Asegúrate de que el campo 'veterinario_id' esté en la tabla 'mascotas'
}

    
}
