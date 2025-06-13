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
        Schema::create('equipo_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained()->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->integer('puntos')->default(0);
            $table->integer('partidos_jugados')->default(0);
            $table->integer('partidos_ganados_2_0')->default(0);
            $table->integer('partidos_ganados_2_1')->default(0);
            $table->integer('partidos_perdidos_0_2')->default(0);
            $table->integer('partidos_perdidos_1_2')->default(0);
            $table->integer('no_presentados')->default(0);
            $table->integer('sets_favor')->default(0);
            $table->integer('sets_contra')->default(0);
            $table->integer('puntos_favor')->default(0);
            $table->integer('puntos_contra')->default(0);
            $table->integer('posicion')->nullable();
            $table->boolean('eliminado')->default(false);
            $table->timestamps();
            $table->unique(['equipo_id', 'grupo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipo_grupo');
    }
};
