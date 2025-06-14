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
       Schema::create('mensajes', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('emisor_id');
    $table->unsignedBigInteger('receptor_id');
    $table->unsignedBigInteger('mascota_id');
    $table->text('contenido');
    $table->boolean('leido')->default(false);
    $table->timestamps();

    $table->foreign('emisor_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('receptor_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
