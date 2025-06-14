<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'emisor_id',
        'receptor_id',
        'mascota_id',
        'contenido',
        'leido',
    ];

    // Relaciones
    // app/Models/Mensaje.php
public function emisor()
{
    return $this->belongsTo(\App\Models\User::class, 'emisor_id');
}

public function receptor()
{
    return $this->belongsTo(\App\Models\User::class, 'receptor_id');
}


    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}
