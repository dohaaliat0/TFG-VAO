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
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->nullable()->constrained()->onDelete('cascade'); // Nullable para partidos eliminatorios
            $table->foreignId('equipo_local_id')->nullable()->constrained('equipos')->onDelete('cascade'); // Nullable para partidos sin equipos definidos
            $table->foreignId('equipo_visitante_id')->nullable()->constrained('equipos')->onDelete('cascade'); // Nullable para partidos sin equipos definidos
            $table->integer('resultado_local')->nullable();
            $table->integer('resultado_visitante')->nullable();
            $table->integer('puntos_local')->nullable(); // Puntos totales del equipo local
            $table->integer('puntos_visitante')->nullable(); // Puntos totales del equipo visitante
            $table->dateTime('fecha')->nullable();
            $table->boolean('completado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
