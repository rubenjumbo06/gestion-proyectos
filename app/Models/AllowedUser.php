<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AllowedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'is_superadmin',
        'is_active',
    ];

    protected $casts = [
        'is_superadmin' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }

    public function permisos()
    {
        return $this->hasOne(Permisos::class, 'allowed_user_id');
    }

    public function allowedUser()
{
    return $this->hasOne(AllowedUser::class, 'email', 'email');
}
}
