<?php

namespace App\Http\Controllers;

use App\Models\AllowedUser;
use App\Models\Permisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllowedUserController extends Controller
{
    public function index()
    {
        $allowedUsers = AllowedUser::all();
        return view('allowed_users.index', compact('allowedUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:allowed_users,email',
        ]);

        $permInputs = $request->input('permissions', []);
        $puede_agregar = isset($permInputs['puede_agregar']);
        $puede_editar = isset($permInputs['puede_editar']);
        $puede_descargar = isset($permInputs['puede_descargar']);

        DB::beginTransaction();
        try {
            $allowed = AllowedUser::create([
                'email' => $request->input('email'),
                'is_superadmin' => 0,
                'is_active' => 1,
            ]);

            Permisos::create([
                'allowed_user_id' => $allowed->id,
                'puede_ver' => true,
                'puede_agregar' => $puede_agregar,
                'puede_editar' => $puede_editar,
                'puede_descargar' => $puede_descargar,
            ]);

            DB::commit();
            return redirect()->route('allowed_users.index')->with('success', 'Usuario autorizado creado correctamente.');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: '.$ex->getMessage());
        }
    }

    public function updatePermissions(Request $request, $id)
    {
        $allowed = AllowedUser::findOrFail($id);

        $permInputs = $request->input('permissions', []);
        $puede_agregar = isset($permInputs['puede_agregar']);
        $puede_editar = isset($permInputs['puede_editar']);
        $puede_descargar = isset($permInputs['puede_descargar']);
        $is_superadmin = $request->has('is_superadmin');
        $is_active = $request->has('is_active');

        DB::beginTransaction();
        try {
            $allowed->update([
                'is_superadmin' => $is_superadmin,
                'is_active' => $is_active,
            ]);

            $perm = Permisos::where('allowed_user_id', $allowed->id)->first();
            if (!$perm) {
                $perm = new Permisos();
                $perm->allowed_user_id = $allowed->id;
            }
            $perm->puede_ver = true;
            $perm->puede_agregar = $puede_agregar;
            $perm->puede_editar = $puede_editar;
            $perm->puede_descargar = $puede_descargar;
            $perm->save();

            DB::commit();
            return redirect()->route('allowed_users.index')->with('success','Permisos actualizados.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error','Error: '.$e->getMessage());
        }
    }

    public function destroy(AllowedUser $allowedUser)
    {
        try {
            $allowedUser->delete();
            return redirect()->route('allowed_users.index')->with('success', 'Usuario eliminado.');
        } catch (\Exception $e) {
            return redirect()->route('allowed_users.index')->with('error', 'Error: '.$e->getMessage());
        }
    }
}
