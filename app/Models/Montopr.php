<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Montopr extends Model
{
    use SoftDeletes;

    protected $table = 'montopr';
    protected $primaryKey = 'id_montopr';
    protected $fillable = ['proyecto_id', 'monto_inicial', 'monto_deseado'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'proyecto_id', 'id_proyecto');
    }
}
