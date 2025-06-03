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
       Schema::table('historial_medicos', function (Blueprint $table) {
        $table->unsignedBigInteger('desparasitacion_id')->nullable()->after('vacuna_id');
        $table->foreign('desparasitacion_id')->references('id')->on('desparasitaciones')->onDelete('set null');
    });
        // Agregar columna desparasitacion_id a historial_medicos
        // y establecer la relación con desparasitaciones
        // Nota: Asegúrate de que la tabla desparasitaciones ya exista antes de ejecutar esta migración
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_medicos', function (Blueprint $table) {
        $table->dropForeign(['desparasitacion_id']);
        $table->dropColumn('desparasitacion_id');
    });
        // Eliminar la columna desparasitacion_id de historial_medicos
        // y eliminar la relación con desparasitaciones
    }
};
