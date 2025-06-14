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
    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}
