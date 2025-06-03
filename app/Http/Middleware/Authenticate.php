<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
{
    if (!$request->expectsJson()) {
        // Evita redirigir, o puedes lanzar excepción o devolver JSON
        abort(401, 'No autenticado');
    }
}

}
