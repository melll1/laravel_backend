<?php

return [

    // 👇 El guard por defecto debe ser 'web' para flujos de autenticación por sesión
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    // 👇 Definición de los guards
    'guards' => [
        'web' => [
            'driver' => 'session',   // ✅ Necesario para verificación de email (usa sesiones)
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'sanctum',   // ✅ Para API token authentication con Sanctum
            'provider' => 'users',
        ],
    ],

    // 👇 Definición de los providers
    'providers' => [
        'users' => [
            'driver' => 'eloquent', // Esto normalmente está bien
            'model' => App\Models\User::class,
        ],
    ],

    // 👇 Configuración de resets de contraseña (opcional si usas recuperación)
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    // ⏱️ Configuración de throttling de verificación, inicio de sesión, etc.
    'password_timeout' => 10800,

    'verification' => [
    'expire' => 60,
],
];
