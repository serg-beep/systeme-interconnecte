<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller {

    public function index(Request $request) {
        $query = User::with(['roles:id,nom', 'entreprise:id,nom'])
            ->select('id','nom','prenom','email','poste','actif','entreprise_id','created_at')
            ->where('entreprise_id', auth()->user()->entreprise_id)
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = $request->q;

                $builder->where(function ($inner) use ($search) {
                    $inner->where('nom', 'like', '%'.$search.'%')
                        ->orWhere('prenom', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('poste', 'like', '%'.$search.'%');
                });
            })
            ->when($request->has('actif'), fn ($builder) => $builder->where('actif', $request->boolean('actif')))
            ->latest();

        return $this->listResponse($query, $request, 50, 100);
    }

    public function stats() {
        $stats = User::where('entreprise_id', auth()->user()->entreprise_id)
            ->selectRaw('count(*) as total, sum(actif) as actifs')
            ->first();

        return response()->json([
            'total'     => (int) $stats->total,
            'actifs'    => (int) $stats->actifs,
            'inactifs'  => (int) $stats->total - (int) $stats->actifs,
        ]);
    }

    public function store(Request $request) {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('gestionnaire')) {
            return response()->json(['message' => 'Acces refuse - role insuffisant'], 403);
        }

        $data = $request->validate([
            'nom'       => 'required|string',
            'prenom'    => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8',
            'telephone' => 'nullable|string',
            'poste'     => 'nullable|string',
            'role_id'   => 'required_without:roles|exists:roles,id',
            'roles'     => 'sometimes|array',
            'roles.*'   => 'exists:roles,id',
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $data['password'] = Hash::make($data['password']);

        $roleIds = $data['roles'] ?? [$data['role_id']];
        $permissionIds = $data['permissions'] ?? [];
        unset($data['role_id'], $data['roles'], $data['permissions']);

        $user = User::create($data);
        $user->roles()->sync($roleIds);

        if (auth()->user()->hasRole('super_admin')) {
            $user->permissions()->sync($permissionIds);
        }

        return response()->json($user->load('roles.permissions', 'permissions'), 201);
    }

    public function show($id) {
        return response()->json(
            User::with('roles.permissions', 'permissions', 'entreprise')
                ->where('entreprise_id', auth()->user()->entreprise_id)
                ->findOrFail($id)
        );
    }

    public function update(Request $request, $id) {
        $user = User::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $data = $request->validate([
            'nom'       => 'sometimes|string',
            'prenom'    => 'sometimes|string',
            'email'     => 'sometimes|email|unique:users,email,'.$id,
            'telephone' => 'nullable|string',
            'poste'     => 'nullable|string',
            'photo'     => 'nullable|string',
            'bio'       => 'nullable|string',
            'password'  => 'nullable|min:8',
            'roles'     => 'sometimes|array',
            'roles.*'   => 'exists:roles,id',
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $roleIds = $data['roles'] ?? null;
        $permissionIds = $data['permissions'] ?? null;
        unset($data['roles'], $data['permissions']);

        $user->update($data);

        if (is_array($roleIds) && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin'))) {
            $user->roles()->sync($roleIds);
        }

        if (is_array($permissionIds) && auth()->user()->hasRole('super_admin')) {
            $user->permissions()->sync($permissionIds);
        }

        return response()->json($user->load('roles.permissions', 'permissions'));
    }

    public function destroy($id) {
        User::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id)->delete();
        return response()->json(['message' => 'User supprime']);
    }

    public function assignRole(Request $request, $id) {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Acces refuse - role insuffisant'], 403);
        }

        $user = User::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $data = $request->validate(['roles' => 'required|array', 'roles.*' => 'exists:roles,id']);
        $user->roles()->sync($data['roles']);
        return response()->json(['message' => 'Roles assignes avec succes']);
    }

    public function assignPermissions(Request $request, $id) {
        if (!auth()->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Acces refuse - role insuffisant'], 403);
        }

        $user = User::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $data = $request->validate(['permissions' => 'required|array', 'permissions.*' => 'exists:permissions,id']);
        $user->permissions()->sync($data['permissions']);
        return response()->json(['message' => 'Permissions assignees avec succes']);
    }

    public function toggleActif($id) {
        $user = User::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $user->update(['actif' => !$user->actif]);
        return response()->json(['message' => $user->actif ? 'Compte active' : 'Compte desactive']);
    }
}
