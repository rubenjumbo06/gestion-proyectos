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
        Schema::create('montopr', function (Blueprint $table) {
            $table->id('id_montopr');
            $table->unsignedBigInteger('proyecto_id');
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_deseado', 10, 2);
            $table->decimal('monto_minimo', 10, 2)->virtualAs('monto_deseado - (monto_inicial * 0.2)');
            $table->timestamps();

            $table->foreign('proyecto_id')->references('id_proyecto')->on('proyectos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monto_pr');
    }
};
