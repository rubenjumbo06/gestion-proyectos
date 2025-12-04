<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActividadProyecto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'actividades_proyecto';
    protected $primaryKey = 'id_actividad';

    public $timestamps = false; // IMPORTANTE si tu tabla NO tiene created_at / updated_at

    protected $fillable = [
        'proyecto_id',
        'nombre',
        'descripcion',
        'imagen_url',
        'fecha_actividad',
    ];

    protected $dates = ['deleted_at']; // para SoftDeletes

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'proyecto_id');
    }
}
