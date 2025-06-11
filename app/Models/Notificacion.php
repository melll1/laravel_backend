<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'mascota_id',
        'veterinario_id',
        'dueno_id',
        'paseador_id',
        'tipo',
        'mensaje',
        'fecha_notificacion',
        'leido',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function veterinario()
    {
        return $this->belongsTo(User::class, 'veterinario_id');
    }

    public function dueno()
    {
        return $this->belongsTo(User::class, 'dueno_id');
    }

    public function paseador()
    {
        return $this->belongsTo(User::class, 'paseador_id');
    }
}
