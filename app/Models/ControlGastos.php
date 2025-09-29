<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ControlGastos extends Model
{
    use SoftDeletes;

    protected $table = 'control_gastos';
    protected $primaryKey = 'id_control';
    protected $fillable = ['id_proyecto', 'monto_inicial', 'total_quedante_utilidad', 'alerta'];
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
