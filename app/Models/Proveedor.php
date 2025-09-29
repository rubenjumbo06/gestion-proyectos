<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    protected $fillable = ['nombre_prov', 'descripcion_prov'];
    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
    public $timestamps = true;

    /**
     * Obtiene los materiales asociados al proveedor.
     */
    public function materiales()
    {
        return $this->hasMany(\App\Models\Materiales::class, 'id_proveedor', 'id_proveedor');
    }
}