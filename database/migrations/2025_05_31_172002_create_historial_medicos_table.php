<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_medicos', function (Blueprint $table) {
            $table->id(); // ID del historial
            $table->foreignId('mascota_id')->constrained()->onDelete('cascade');
            $table->foreignId('vacuna_id')->nullable()->constrained('vacunas')->onDelete('cascade');
            $table->string('tipo');
            $table->string('descripcion');
            $table->date('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_medicos');
    }
};
