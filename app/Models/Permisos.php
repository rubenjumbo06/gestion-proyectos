<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permisos extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'id_permiso';
    protected $fillable = ['allowed_user_id', 'puede_ver', 'puede_agregar', 'puede_editar', 'puede_descargar', 'puede_eliminar'];

    protected $casts = [
        'puede_ver' => 'boolean',
        'puede_agregar' => 'boolean',
        'puede_editar' => 'boolean',
        'puede_descargar' => 'boolean',
        'puede_eliminar' => 'boolean',
    ];

    public function allowedUser()
    {
        return $this->belongsTo(AllowedUser::class, 'allowed_user_id');
    }
}