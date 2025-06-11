<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::rename('asignacion_paseadors', 'asignaciones_paseadores');
}

public function down(): void
{
    Schema::rename('asignaciones_paseadores', 'asignacion_paseadors');
}

};
