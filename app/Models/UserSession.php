<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
    'action',
    'ip_address',
    'user_agent',
    'logout_at', // opcional, si lo usas
    ];

   
}
