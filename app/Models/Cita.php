<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'mascota_id',
        'dueno_id',
        'veterinario_id',
        'fecha_hora',
        'motivo',
        'estado',
    ];

    public function mascota() {
        return $this->belongsTo(Mascota::class);
    }

    public function dueno() {
        return $this->belongsTo(User::class, 'dueno_id');
    }

    public function veterinario() {
        return $this->belongsTo(User::class, 'veterinario_id');
    }
}
