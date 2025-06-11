<?php

// database/migrations/xxxx_xx_xx_xxxxxx_change_frecuencia_horas_to_minutos.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tratamientos', function (Blueprint $table) {
            $table->dropColumn('frecuencia_horas');
            $table->integer('frecuencia_minutos')->nullable(); // minutos entre dosis
        });
    }

    public function down(): void
    {
        Schema::table('tratamientos', function (Blueprint $table) {
            $table->dropColumn('frecuencia_minutos');
            $table->integer('frecuencia_horas')->nullable();
        });
    }
};

