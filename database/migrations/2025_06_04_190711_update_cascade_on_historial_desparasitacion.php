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
            // Elimina la foreign key actual
            $table->dropForeign(['desparasitacion_id']);
            
            // Vuelve a crearla con ON DELETE CASCADE
            $table->foreign('desparasitacion_id')
                  ->references('id')
                  ->on('desparasitaciones')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropForeign(['desparasitacion_id']);
    
            // Revertir a SET NULL si hiciera falta
            $table->foreign('desparasitacion_id')
                  ->references('id')
                  ->on('desparasitaciones')
                  ->onDelete('set null');
        });
    }
};
