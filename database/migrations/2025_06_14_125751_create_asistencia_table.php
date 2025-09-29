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
        //Schema::create('asistencia', function (Blueprint $table) {
          //  $table->id();
            //$table->foreignId('id_trabajadores')->constrained('trabajadores', 'id_trabajadores')->onDelete('cascade');
            //$table->time('hora');
           // $table->date('fecha');
          //  $table->string('ubicacion', 255);
          //  $table->enum('Dia_de_Semana', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
         //   $table->timestamps();
    //    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // Schema::dropIfExists('asistencia');
    }
};
