<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths que habilitan CORS
    |--------------------------------------------------------------------------
    |
    | Aquí defines qué rutas aceptan solicitudes CORS.
    | Por ejemplo, aquí permitimos todas las rutas que empiecen con "api/"
    |
    */
    'paths' => ['api/*', '/login', '/logout', '/sanctum/csrf-cookie'],


    /*
    |--------------------------------------------------------------------------
    | Métodos HTTP permitidos
    |--------------------------------------------------------------------------
    |
    | Los métodos que permitirás para solicitudes CORS.
    | El asterisco '*' permite todos los métodos (GET, POST, PUT, DELETE, etc).
    |
    */
   'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:4200'],



    /*
    |--------------------------------------------------------------------------
    | Orígenes permitidos
    |--------------------------------------------------------------------------
    |
    | Define qué dominios pueden hacer solicitudes CORS.
    | Aquí permitimos localhost en el puerto 4200 (Angular dev server).
    |
    */
    
    'allowed_origins_patterns' => [],


    /*
    |--------------------------------------------------------------------------
    | Headers permitidos
    |--------------------------------------------------------------------------
    |
    | Qué headers puede enviar el cliente en la solicitud.
    | El asterisco '*' permite todos los headers.
    |
    */
   'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],

    /*
    |--------------------------------------------------------------------------
    | Headers expuestos al cliente
    |--------------------------------------------------------------------------
    |
    | Headers que el navegador puede leer en la respuesta CORS.
    | Por defecto está vacío.
    |
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Tiempo máximo que el navegador puede cachear la respuesta preflight
    |--------------------------------------------------------------------------
    |
    | En segundos. 0 significa que no se cachea.
    |
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Soporte para credenciales (cookies, autenticación)
    |--------------------------------------------------------------------------
    |
    | Si usas cookies o autenticación con CORS, debe ser true.
    | De lo contrario, déjalo en false.
    |
    */
    'supports_credentials' => true,

    

];
