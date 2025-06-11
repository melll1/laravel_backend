<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mascota_id')->constrained()->onDelete('cascade');
        $table->foreignId('dueno_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('veterinario_id')->constrained('users')->onDelete('cascade');
        $table->dateTime('fecha_hora');
        $table->text('motivo')->nullable();
        $table->enum('estado', ['pendiente', 'aceptada', 'modificada', 'rechazada'])->default('pendiente');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
