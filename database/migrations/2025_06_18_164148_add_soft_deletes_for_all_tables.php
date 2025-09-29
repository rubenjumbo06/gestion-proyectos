<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lista de tablas que recibirán la columna deleted_at.
     *
     * @var array
     */
    protected $tables = [
        'users',
        'allowed_users',
        'proveedores',
        'departamento',
        'trabajadores',
        'proyectos',
        'fechapr',
        'montopr',
        //'asistencia',
        'planilla',
        'materiales',
        'gastos_extra',
        'egresos',
        'control_gastos',
        'balance_general',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes(); // Agrega la columna deleted_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes(); // Elimina la columna deleted_at
            });
        }
    }
};
