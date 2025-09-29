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
        Schema::create('planilla', function (Blueprint $table) {
            $table->id('id_planilla');
            $table->foreignId('id_trabajadores')->constrained('trabajadores', 'id_trabajadores')->onDelete('cascade');
            $table->foreignId('id_proyecto')->constrained('proyectos', 'id_proyecto')->onDelete('cascade');
            $table->unsignedInteger('dias_trabajados');
            $table->decimal('pago', 10, 2);
            $table->decimal('alimentacion_trabajador', 10, 2);
            $table->decimal('hospedaje_trabajador', 10, 2)->default(0.00);
            $table->decimal('pasajes_trabajador', 10, 2)->default(0.00);
            $table->enum('estado', ['LIQUIDADO', 'NO LIQUIDADO']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planilla');
    }
};
