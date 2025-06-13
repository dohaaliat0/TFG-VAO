<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_categoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_grupo_id');
            $table->unsignedBigInteger('categoria_id');
            $table->integer('posicion')->default(1);
            $table->timestamps();
            $table->foreign('equipo_grupo_id')->references('id')->on('equipo_grupo')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            $table->unique(['equipo_grupo_id', 'categoria_id']);
            $table->index(['categoria_id', 'posicion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_categoria');
    }
};
