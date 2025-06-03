<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesparasitacionesTable extends Migration
{
    public function up()
    {
        Schema::create('desparasitaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mascota_id'); // 🐶 Relación con la mascota
            $table->string('nombre'); // 💊 Nombre del producto o medicamento
            $table->date('fecha'); // 📅 Fecha de la aplicación
            $table->date('proxima_dosis')->nullable(); // 📅 Próxima dosis, si aplica
            $table->enum('tipo', ['Interna', 'Externa']); // 🔄 Tipo de desparasitación
            $table->text('observaciones')->nullable(); // 📝 Notas opcionales
            $table->timestamps();

            // 🔗 FK con mascotas
            $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('desparasitaciones');
    }
}

