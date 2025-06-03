<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Ruta para mostrar mensaje "verifica tu email"
Route::get('/email/verify', function () {
    return redirect('http://localhost:4200/verify-pending'); // Ruta Angular donde muestras mensaje
})->middleware('auth')->name('verification.notice');

// Ruta para verificar el email desde enlace
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // Marca el email como verificado
    return redirect('http://localhost:4200/verified'); // Redirige al frontend
})->middleware(['auth', 'signed'])->name('verification.verify');

// Ruta para reenviar correo de verificación
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Correo reenviado correctamente']);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/login', function () {
    return 'Redirigido correctamente a /login';
})->name('login');
