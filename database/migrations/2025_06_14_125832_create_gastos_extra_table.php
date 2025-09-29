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
        Schema::create('gastos_extra', function (Blueprint $table) {
            $table->id('id_gasto');
            $table->foreignId('id_proyecto')->constrained('proyectos', 'id_proyecto')->onDelete('cascade');
            $table->decimal('alimentacion_general', 10, 2)->default(0.00);
            $table->decimal('hospedaje', 10, 2)->default(0.00);
            $table->decimal('pasajes', 10, 2)->default(0.00);
            $table->decimal('gasto_total', 10, 2)->virtualAs('alimentacion_general + hospedaje + pasajes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_extra');
    }
};
