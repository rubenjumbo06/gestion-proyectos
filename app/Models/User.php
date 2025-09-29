<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'img',
        'telefono',
        'fecha_nacimiento',
        'is_superadmin',
        'auth_method',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_superadmin' => 'boolean',
    ];

    /**
     * Relación con las actividades del usuario.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Relación con AllowedUser por email.
     */
    public function allowedUser()
    {
        return $this->hasOne(AllowedUser::class, 'email', 'email');
    }

    /**
     * Relación encadenada con Permisos a través de AllowedUser.
     */
    public function permisos()
    {
        return $this->hasOneThrough(
            Permisos::class,
            AllowedUser::class,
            'email', // Clave foránea en AllowedUser
            'allowed_user_id', // Clave foránea en Permisos
            'email', // Clave local en User
            'id' // Clave local en AllowedUser
        );
    }

public function getPuedeDescargarAttribute()
{
    return $this->allowedUser && $this->allowedUser->is_superadmin ? true : ($this->permisos ? $this->permisos->puede_descargar : false);
}

public function getPuedeVerAttribute()
{
    return $this->allowedUser && $this->allowedUser->is_superadmin ? true : ($this->permisos ? $this->permisos->puede_ver : false);
}

public function getPuedeEditarAttribute()
{
    return $this->allowedUser && $this->allowedUser->is_superadmin ? true : ($this->permisos ? $this->permisos->puede_editar : false);
}

public function getPuedeAgregarAttribute()
{
    return $this->allowedUser && $this->allowedUser->is_superadmin ? true : ($this->permisos ? $this->permisos->puede_agregar : false);
}

public function getPuedeEliminarAttribute()
{
    return $this->allowedUser && $this->allowedUser->is_superadmin ? true : ($this->permisos ? $this->permisos->puede_eliminar : false);
}
}