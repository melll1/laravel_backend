<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * Las cadenas que no deberían ser recortadas.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
