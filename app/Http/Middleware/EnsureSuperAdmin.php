<?php

namespace App\Http\Middleware;

use App\Models\AllowedUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $allowedUser = AllowedUser::where('email', $user->email)->first();

        if (!$allowedUser || !$allowedUser->is_active) {
            return redirect('/dashboard')->with('error', 'Acceso no autorizado.');
        }

        // Si es Super Admin, permite todo
        if ($allowedUser->is_superadmin) {
            return $next($request);
        }

        // Verifica permisos según la acción
        $routeName = $request->route()->getName();
        $requiredPermissions = $this->getRequiredPermissions($routeName);

        if ($requiredPermissions && !$this->hasPermissions($allowedUser, $requiredPermissions)) {
            return redirect('/dashboard')->with('error', 'No tienes permisos para esta acción.');
        }

        return $next($request);
    }

    protected function getRequiredPermissions($routeName)
    {
        $permissions = [
            'proyectos.create' => ['create'],
            'proyectos.store' => ['create'],
            'proyectos.edit' => ['edit'],
            'proyectos.update' => ['edit'],
            'proyectos.destroy' => ['delete'],
            // Agrega más rutas y permisos según necesites
        ];
        return $permissions[$routeName] ?? null;
    }

    protected function hasPermissions($allowedUser, $requiredPermissions)
    {
        return !empty(array_intersect($requiredPermissions, $allowedUser->permissions ?? []));
    }
}