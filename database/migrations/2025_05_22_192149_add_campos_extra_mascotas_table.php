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
     Schema::table('mascotas', function (Blueprint $table) {
    if (!Schema::hasColumn('mascotas', 'sexo')) {
        $table->string('sexo')->nullable();
    }
    if (!Schema::hasColumn('mascotas', 'microchip')) {
        $table->string('microchip')->nullable();
    }
    if (!Schema::hasColumn('mascotas', 'color')) {
        $table->string('color')->nullable();
    }
    if (!Schema::hasColumn('mascotas', 'esterilizado')) {
        $table->string('esterilizado')->nullable();
    }
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
