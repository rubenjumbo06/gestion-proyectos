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
        Schema::create('control_gastos', function (Blueprint $table) {
            $table->id('id_control');
            $table->foreignId('id_proyecto')->constrained('proyectos', 'id_proyecto')->onDelete('cascade');
            $table->decimal('monto_inicial', 10, 2); // Monto inicial
            $table->decimal('monto_minimo', 10, 2)->virtualAs('monto_inicial * 0.40'); // 40% del monto inicial
            $table->decimal('total_quedante_utilidad', 10, 2); // Total quedante de utilidad
            $table->boolean('alerta')->default(false); // Alerta si monto total > monto mínimo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_gastos');
    }
};
