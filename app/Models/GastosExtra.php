<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GastosExtra extends Model
{
    use SoftDeletes;

    protected $table = 'gastos_extra';
    protected $primaryKey = 'id_gasto';
    protected $fillable = ['id_proyecto', 'alimentacion_general', 'hospedaje', 'pasajes'];
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
}
