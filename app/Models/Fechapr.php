<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fechapr extends Model
{
    use SoftDeletes;

    protected $table = 'fechapr';
    protected $primaryKey = 'id_fechapr';
    protected $fillable = ['proyecto_id', 'fecha_inicio', 'fecha_fin_aprox', 'fecha_fin_true'];
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin_aprox' => 'date',
        'fecha_fin_true' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'proyecto_id', 'id_proyecto');
    }
}
