<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMascotaPaseadorTable extends Migration
{
    public function up()
    {
        Schema::create('mascota_paseador', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mascota_id');
            $table->unsignedBigInteger('paseador_id'); // user_id del paseador
            $table->timestamps();

            $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('cascade');
            $table->foreign('paseador_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['mascota_id', 'paseador_id']); // evitar duplicados
        });
    }

    public function down()
    {
        Schema::dropIfExists('mascota_paseador');
    }
}
