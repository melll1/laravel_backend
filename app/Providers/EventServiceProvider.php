<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Mapeo de eventos a listeners.
     *
     * @var array
     */
    protected $listen = [
        // 'App\Events\EventName' => [
        //     'App\Listeners\EventListener',
        // ],
    ];

    /**
     * Bootstrap de servicios de eventos.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
