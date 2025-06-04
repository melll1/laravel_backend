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
    Schema::create('tratamientos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');
        $table->string('nombre');
        $table->date('fecha_inicio');
        $table->date('fecha_fin')->nullable();
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });

    Schema::table('historial_medicos', function (Blueprint $table) {
        $table->unsignedBigInteger('tratamiento_id')->nullable()->after('desparasitacion_id');
        $table->foreign('tratamiento_id')
              ->references('id')
              ->on('tratamientos')
              ->onDelete('cascade');
    });
}


public function down(): void
{
    Schema::table('historial_medicos', function (Blueprint $table) {
        $table->dropForeign(['tratamiento_id']);
        $table->dropColumn('tratamiento_id');
    });

    Schema::dropIfExists('tratamientos');
}

};
