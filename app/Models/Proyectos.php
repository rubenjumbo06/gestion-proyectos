<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

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
        'fecha_creacion',
        'user_id'
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

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'id_proyecto', 'id_proyecto');
    }

    // Usar id_proyecto para Route Model Binding
    public function getRouteKeyName()
    {
        return 'id_proyecto';
    }

    public function actividades()
    {
        return $this->hasMany(ActividadProyecto::class, 'proyecto_id', 'id_proyecto');
    }

    // === LO NUEVO: VALIDACIÓN ÚNICA DE NOMBRE + LIMPIEZA ===
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($proyecto) {
            // Limpiar espacios y normalizar
            $proyecto->nombre_proyecto = trim($proyecto->nombre_proyecto);

            // Si es nuevo o el nombre cambió → verificar duplicado
            if ($proyecto->isDirty('nombre_proyecto')) {
                $query = self::whereRaw('LOWER(TRIM(nombre_proyecto)) = ?', 
                    [strtolower($proyecto->nombre_proyecto)]);

                if ($proyecto->exists) {
                    $query->where('id_proyecto', '!=', $proyecto->id_proyecto);
                }

                if ($query->withTrashed()->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'nombre_proyecto' => 'Ya existe un proyecto con este nombre (incluso eliminado).'
                    ]);
                }
            }
        });
    }
}