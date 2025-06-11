<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AsignacionPaseador extends Model
{
    use HasFactory;
protected $table = 'asignaciones_paseadores';

    protected $fillable = [
        'mascota_id',
        'paseador_id',
        'desde',
        'hasta',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function paseador()
    {
        return $this->belongsTo(User::class, 'paseador_id');
    }
}

