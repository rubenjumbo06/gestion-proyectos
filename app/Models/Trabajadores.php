<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajadores extends Model
{
    use SoftDeletes;

    protected $table = 'trabajadores';
    protected $primaryKey = 'id_trabajadores';
    protected $fillable = ['nombre_trab', 'apellido_trab', 'dni_trab', 'correo_trab', 'num_telef', 'sexo_trab', 'fecha_nac', 'id_departamento'];
    protected $casts = [
        'fecha_nac' => 'datetime',
        'fecha_creacion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Agregado para soft deletes
    ];
    public $timestamps = true;

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function asistencia()
    {
        return $this->hasMany(Asistencia::class, 'id_trabajadores', 'id_trabajadores');
    }

    public function planilla()
    {
        return $this->hasMany(Planilla::class, 'id_trabajadores', 'id_trabajadores');
    }

 public function setNombreTrabAttribute($value)
{
    $this->attributes['nombre_trab'] = ucwords(mb_strtolower($value));
}

public function setApellidoTrabAttribute($value)
{
    $this->attributes['apellido_trab'] = ucwords(mb_strtolower($value));
}

}
