<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller {

    public function index() {
        return response()->json(Permission::all());
    }

    public function store(Request $request) {
        $data = $request->validate([
            'nom'         => 'required|string|unique:permissions',
            'description' => 'nullable|string',
        ]);
        return response()->json(Permission::create($data), 201);
    }

    public function show($id) {
        return response()->json(Permission::with('roles')->findOrFail($id));
    }

    public function update(Request $request, $id) {
        $permission = Permission::findOrFail($id);
        $permission->update($request->validate([
            'nom'         => 'sometimes|string|unique:permissions,nom,'.$id,
            'description' => 'nullable|string',
        ]));
        return response()->json($permission);
    }

    public function destroy($id) {
        Permission::findOrFail($id)->delete();
        return response()->json(['message' => 'Permission supprimée']);
    }
}
