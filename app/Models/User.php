<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function sendEmailVerificationNotification()
{
    $this->notify(new CustomVerifyEmail);
}
public function sendPasswordResetNotification($token)
{
    $this->notify(new CustomResetPassword($token));
}
protected $visible = [
    'id',
    'name',
    'email',
    'telefono',
    'role',
    'email_verified_at',
];

public function mascotasAsignadas()
{
    return $this->belongsToMany(Mascota::class, 'asignaciones_paseadores', 'paseador_id', 'mascota_id')
                ->withPivot('desde', 'hasta')
                ->withTimestamps();
}


public function paseosAsignados()
{
    return $this->hasMany(AsignacionPaseador::class, 'paseador_id');
}

public function asignaciones()
{
    return $this->hasMany(AsignacionPaseador::class, 'paseador_id');
}



}
