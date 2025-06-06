<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    use HasFactory;

    protected $fillable = [
        'mascota_id',
        'fecha',
        'descripcion',
        'titulo'
        
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}
