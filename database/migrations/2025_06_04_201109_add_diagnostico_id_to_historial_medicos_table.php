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
            $table->unsignedBigInteger('diagnostico_id')->nullable()->after('tratamiento_id');
            $table->foreign('diagnostico_id')->references('id')->on('diagnosticos')->onDelete('cascade');
        });
    }
    
    public function down(): void
    {
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropForeign(['diagnostico_id']);
            $table->dropColumn('diagnostico_id');
        });
    }
    
};
