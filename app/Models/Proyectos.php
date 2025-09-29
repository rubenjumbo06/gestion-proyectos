<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proyectos extends Model
{
    use SoftDeletes;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $table = 'proyectos';
    protected $primaryKey = 'id_proyecto';
    protected $fillable = [
        'nombre_proyecto',
        'cliente_proyecto',
        'descripcion_proyecto',
        'cantidad_trabajadores',
        'sueldo',
        'fecha_creacion'
    ];
    protected $casts = [
        'fecha_creacion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function fechapr()
    {
        return $this->hasOne(Fechapr::class, 'proyecto_id', 'id_proyecto');
    }

    public function montopr()
    {
        return $this->hasOne(Montopr::class, 'proyecto_id', 'id_proyecto');
    }

    public function planilla()
    {
        return $this->hasMany(Planilla::class, 'id_proyecto', 'id_proyecto');
    }

    public function materiales()
    {
        return $this->hasMany(Materiales::class, 'id_proyecto', 'id_proyecto');
    }

    public function gastosExtra()
    {
        return $this->hasMany(GastosExtra::class, 'id_proyecto', 'id_proyecto');
    }

    public function egresos()
    {
        return $this->hasOne(Egresos::class, 'id_proyecto', 'id_proyecto');
    }

    public function controlGastos()
    {
        return $this->hasOne(ControlGastos::class, 'id_proyecto', 'id_proyecto');
    }

    public function balanceGeneral()
    {
        return $this->hasOne(BalanceGeneral::class, 'id_proyecto', 'id_proyecto');
    }

    // Nueva relación con Asistencia
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_proyecto', 'id_proyecto');
    }
}