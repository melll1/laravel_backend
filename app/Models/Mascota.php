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
    'foto', // 📷 Campo agregado para guardar la ruta de la foto
];


    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vacunas() {
    return $this->hasMany(Vacuna::class);
}
    
}
