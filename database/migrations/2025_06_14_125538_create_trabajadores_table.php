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
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id('id_trabajadores'); // Columna auto_increment y clave primaria
            $table->string('nombre_trab', 100)->notNullable();
            $table->string('apellido_trab', 100)->notNullable();
            $table->string('dni_trab', 8)->notNullable();
            $table->string('correo_trab', 255)->unique()->notNullable();
            $table->string('num_telef', 9)->notNullable(); // Cambiado a string para número de teléfono
            $table->enum('sexo_trab', ['Masculino', 'Femenino'])->notNullable();
            $table->date('fecha_nac')->notNullable();
            $table->string('cargo_trab', 100)->notNullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};
