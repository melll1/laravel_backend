<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Laravel: Método que agrega columnas a la tabla `users`
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Laravel: Agrega una columna 'telefono' de tipo string (longitud 20)
            $table->string('telefono', 20)->nullable();

            // Laravel: Agrega una columna 'role' de tipo string para almacenar el rol del usuario
            $table->string('role')->default('dueno'); // puedes ajustar el valor por defecto si deseas
        });
    }

    /**
     * Laravel: Método que revierte los cambios si se hace rollback
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('telefono');
            $table->dropColumn('role');
        });
    }
};
