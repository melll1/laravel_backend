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
        Schema::create('notificacions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('mascota_id');
    $table->unsignedBigInteger('veterinario_id');
    $table->unsignedBigInteger('dueno_id');
    $table->unsignedBigInteger('paseador_id')->nullable();

    $table->string('tipo'); // tratamiento, vacuna, desparasitacion, cita, etc.
    $table->text('mensaje');
    $table->timestamp('fecha_notificacion');
    $table->boolean('leido')->default(false);

    $table->timestamps();

    // Claves foráneas
    $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('cascade');
    $table->foreign('veterinario_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('dueno_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('paseador_id')->references('id')->on('users')->onDelete('set null');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacions');
    }
};
