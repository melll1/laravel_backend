<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Contrato que requiere verificación de email
use Illuminate\Foundation\Auth\User as Authenticatable; // Clase base para autenticación
use Illuminate\Notifications\Notifiable; // Trait para enviar notificaciones
use Laravel\Sanctum\HasApiTokens; // Trait para manejo de tokens API con Sanctum
use App\Notifications\CustomVerifyEmail; // Notificación personalizada para verificar email
use App\Notifications\CustomResetPassword; // Notificación personalizada para restablecer contraseña


class User extends Authenticatable implements MustVerifyEmail
{
     use HasApiTokens, Notifiable; // Incluye funcionalidad para tokens y notificaciones

    // Atributos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'role',
    ];

    // Atributos ocultos cuando el modelo se convierte en array o JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Atributos que se convierten automáticamente a tipos específicos
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Notificación personalizada para verificación de email
    public function sendEmailVerificationNotification()
{
    $this->notify(new CustomVerifyEmail);
}

// Notificación personalizada para restablecimiento de contraseña
public function sendPasswordResetNotification($token)
{
    $this->notify(new CustomResetPassword($token));
}

// Atributos visibles cuando se convierte a array o JSON
protected $visible = [
    'id',
    'name',
    'email',
    'telefono',
    'role',
    'email_verified_at',
];

// Relación muchos a muchos con Mascota a través de la tabla pivot 'asignaciones_paseadores'
public function mascotasAsignadas()
{
    return $this->belongsToMany(Mascota::class, 'asignaciones_paseadores', 'paseador_id', 'mascota_id')
               ->withPivot('desde', 'hasta') // Incluye campos adicionales de la tabla pivot
                    ->withTimestamps(); // Incluye timestamps de la pivot table
}

// Relación uno a muchos: un usuario puede tener múltiples paseos asignados
public function paseosAsignados()
{
    return $this->hasMany(AsignacionPaseador::class, 'paseador_id');
}

// Alias adicional para acceder a asignaciones (similar al anterior)
public function asignaciones()
{
    return $this->hasMany(AsignacionPaseador::class, 'paseador_id');
}

// en User.php
public function mascotas()
{
    return $this->hasMany(Mascota::class);
}

// Mensajes enviados por este usuario
public function sentMessages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

// Mensajes recibidos por este usuario
public function receivedMessages()
{
    return $this->hasMany(Message::class, 'receiver_id');
}


}
