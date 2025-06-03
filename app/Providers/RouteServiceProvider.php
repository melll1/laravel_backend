<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Este namespace se aplica automáticamente a los controladores de rutas.
     * 
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define las rutas para la aplicación.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // Rutas API: prefijo 'api', middleware 'api', y carga rutas de routes/api.php
            Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            // Rutas web: middleware 'web' y carga rutas de routes/web.php
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }
    public const HOME = 'http://localhost:4200/verified'; // Redirección después del clic en verificar

}
