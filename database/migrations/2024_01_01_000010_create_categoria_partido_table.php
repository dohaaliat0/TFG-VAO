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
        Schema::create('categoria_partido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade');
            $table->foreignId('partido_id')->constrained()->onDelete('cascade');
            $table->enum('fase', ['cuartos', 'semifinal', 'final']);
            $table->string('numero_partido', 10);
            $table->json('dependencias')->nullable();
            $table->timestamps();
            
            $table->unique(['categoria_id', 'partido_id']);
            $table->index(['categoria_id', 'fase']);
            $table->index('numero_partido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_partido');
    }
};
