<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Planilla extends Model
{
    use SoftDeletes;

    protected $table = 'planilla';
    protected $primaryKey = 'id_planilla';
    protected $fillable = [
        'id_trabajadores',
        'id_proyecto',
        'dias_trabajados',
        'pago_dia',
        'pago',
        'alimentacion_trabajador',
        'hospedaje_trabajador',
        'pasajes_trabajador',
        'estado'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public $timestamps = true;

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'id_proyecto', 'id_proyecto');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajadores::class, 'id_trabajadores', 'id_trabajadores');
    }
}
