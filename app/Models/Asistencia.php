<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asistencia extends Model
{
    use SoftDeletes;

    protected $table = 'asistencia';
    protected $primaryKey = 'id';
    // Según el esquema, la asistencia pertenece a una fila de planilla y a un proyecto
    protected $fillable = [
        'id_planilla',
        'id_proyecto',
        'hora',
        'fecha',
        'ubicacion',
        'Dia_de_Semana'
    ];
    protected $casts = [
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public $timestamps = true;

    public function planilla()
    {
        return $this->belongsTo(Planilla::class, 'id_planilla', 'id_planilla');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'id_proyecto', 'id_proyecto');
    }
}
