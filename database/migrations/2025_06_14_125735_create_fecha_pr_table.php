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
        Schema::create('fechapr', function (Blueprint $table) {
            $table->id('id_fechapr');
            $table->unsignedBigInteger('proyecto_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin_aprox')->nullable();
            $table->date('fecha_fin_true')->nullable();
            $table->unsignedInteger('dias_totales')->virtualAs('IFNULL(DATEDIFF(COALESCE(fecha_fin_true, fecha_fin_aprox), fecha_inicio), 0)');
            $table->timestamps();

            $table->foreign('proyecto_id')->references('id_proyecto')->on('proyectos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechapr');
    }
};
