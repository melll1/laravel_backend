<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacunas', function (Blueprint $table) {
            $table->id(); // ID de la vacuna
            $table->foreignId('mascota_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->date('fecha_aplicacion');
            $table->date('proxima_dosis')->nullable();
            $table->string('lote')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacunas');
    }
};
