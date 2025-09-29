<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PersonasGastosExtra extends Model
{
    use SoftDeletes;

    protected $table = 'personas_gastos_extra';
    protected $primaryKey = 'id_gasto';

    protected $fillable = [
        'id_proyecto',
        'alimentacion_general',
        'hospedaje',
        'pasajes',
        'gasto_total',
    ];

    protected $casts = [
        'alimentacion_general' => 'decimal:2',
        'hospedaje' => 'decimal:2',
        'pasajes' => 'decimal:2',
        'gasto_total' => 'decimal:2',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}
