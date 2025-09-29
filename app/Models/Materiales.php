<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materiales extends Model
{
    use SoftDeletes;

    protected $table = 'materiales';
    protected $primaryKey = 'id_material';
    protected $fillable = ['id_proyecto', 'fecha_mat', 'descripcion_mat', 'id_proveedor', 'monto_mat'];
    protected $casts = [
        'fecha_mat' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public $timestamps = true;

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'id_proyecto', 'id_proyecto');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }
}
