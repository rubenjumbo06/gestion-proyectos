<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadProyecto extends Model
{
    use HasFactory;

    protected $table = 'actividades_proyecto';

    protected $fillable = [
        'proyecto_id',
        'nombre', // Nuevo campo
        'descripcion',
        'imagen_url',
        'fecha_actividad',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'proyecto_id');
    }
}
