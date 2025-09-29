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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id('id_proyecto');
            $table->string('nombre_proyecto', 100)->notNullable();
            $table->string('cliente_proyecto', 100)->notNullable();
            $table->text('descripcion_proyecto')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->unsignedInteger('cantidad_trabajadores')->notNullable();
            $table->decimal('sueldo', 10, 2)->notNullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
