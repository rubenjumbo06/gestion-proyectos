<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use SoftDeletes;

    protected $table = 'departamento';
    protected $primaryKey = 'id_departamento';
    protected $fillable = ['nombre_dep', 'descripcion_dep'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Agregado para soft deletes    
    ];
    public $timestamps = true;

    public function trabajadores()
    {
        return $this->hasMany(Trabajadores::class, 'id_departamento', 'id_departamento');
    }
}
