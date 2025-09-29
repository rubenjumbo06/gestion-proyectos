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
        Schema::create('balance_general', function (Blueprint $table) {
            $table->id('id_balance');
            $table->foreignId('id_proyecto')->constrained('proyectos', 'id_proyecto')->onDelete('cascade');
            $table->decimal('total_servicios', 10, 2); // Monto inicial (de montopr)
            $table->decimal('egresos', 10, 2); // Total de egresos (de egresos)
            $table->decimal('ganancia_neta', 10, 2)->virtualAs('total_servicios - egresos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_general');
    }
};
