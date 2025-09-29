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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id('id_egreso');
            $table->foreignId('id_proyecto')->constrained('proyectos', 'id_proyecto')->onDelete('cascade');
            $table->decimal('materiales', 10, 2)->default(0.00); // Monto total jalado de SUMA TOTAL de Tabla Materiales
            $table->decimal('planilla', 10, 2)->default(0.00); // Monto total jalado de SUMA TOTAL de Tabla Planilla
            $table->decimal('scr', 10, 2)->default(0.00); // Ingreso monto total de acuerdo al contrato con SCR sal y pension
            $table->decimal('gastos_administrativos', 10, 2)->default(0.00); // Suma de manera mensual un monto a llegar a 1600
            $table->decimal('gastos_extra', 10, 2)->default(0.00); // Total quedante de utilidad
            $table->decimal('total_egresos', 10, 2)->virtualAs('materiales + planilla + scr + gastos_administrativos + gastos_extra');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
