<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Egresos extends Model
{
    use SoftDeletes;

    protected $table = 'egresos';
    protected $primaryKey = 'id_egreso';
    protected $fillable = ['id_proyecto', 'materiales', 'planilla', 'scr', 'gastos_administrativos', 'gastos_extra'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public $timestamps = true;

    // Si total_egresos es una columna virtual/calculada
    protected $appends = ['total_egresos'];
    
    public function getTotalEgresosAttribute()
    {
        return (float) $this->materiales + 
               (float) $this->planilla + 
               (float) $this->scr + 
               (float) $this->gastos_administrativos + 
               (float) $this->gastos_extra;
    }


    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'id_proyecto', 'id_proyecto');
    }
}
