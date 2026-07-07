<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    // LISTE OPTIMISÉE (ULTRA LÉGÈRE)
    public function index()
    {
        return Cache::remember('roles_list', 3600, function () {
            return response()->json(
                Role::select('id', 'nom', 'description')
                    ->orderBy('nom')
                    ->get()
            );
        });
    }

    // CREATION
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'          => 'required|string|unique:roles',
            'description'  => 'nullable|string',
            'permissions'  => 'sometimes|array',
            'permissions.*'=> 'exists:permissions,id',
        ]);

        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = Role::create($data);

        if (!empty($permissions)) {
            $role->permissions()->sync($permissions);
        }

        // vider cache
        Cache::forget('roles_list');

        return response()->json($role, 201);
    }

    // DETAILS (chargement complet seulement ici)
    public function show($id)
    {
        return response()->json(
            Role::with(['permissions:id,nom', 'users:id,nom,prenom'])
                ->findOrFail($id)
        );
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'nom'          => 'sometimes|string|unique:roles,nom,' . $id,
            'description'  => 'nullable|string',
            'permissions'  => 'sometimes|array',
            'permissions.*'=> 'exists:permissions,id',
        ]);

        $permissions = $data['permissions'] ?? null;
        unset($data['permissions']);

        $role->update($data);

        if (is_array($permissions)) {
            $role->permissions()->sync($permissions);
        }

        Cache::forget('roles_list');

        return response()->json($role->load('permissions:id,nom'));
    }

    // DELETE
    public function destroy($id)
    {
        Role::findOrFail($id)->delete();

        Cache::forget('roles_list');

        return response()->json(['message' => 'Rôle supprimé']);
    }

    // ASSIGN PERMISSIONS
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->permissions()->sync($data['permissions']);

        Cache::forget('roles_list');

        return response()->json([
            'message' => 'Permissions assignées avec succès'
        ]);
    }
}
