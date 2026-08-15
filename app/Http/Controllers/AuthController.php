<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use App\Services\ImageOptimizer;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Role;
use App\Models\Invitation;
use App\Models\HistoriqueAction;

class AuthController extends Controller {

    public function register(Request $request) {
        $data = $request->validate([
            'entreprise_nom'   => 'required|string',
            'entreprise_type'  => 'required|in:pharmacie,hopital,laboratoire,clinique,autre',
            'entreprise_email' => 'required|email|unique:entreprises,email',
            'entreprise_ville' => 'required|string',
            'entreprise_telephone'   => 'nullable|string',
            'entreprise_adresse'     => 'required|string',
            'entreprise_description' => 'nullable|string',
            'entreprise_logo'        => $request->hasFile('entreprise_logo')
                ? ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2 * 1024)]
                : 'nullable|string',
            'role'             => 'sometimes|in:admin,gestionnaire,operateur',
            'nom'              => 'required|string',
            'prenom'           => 'required|string',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|min:8|confirmed',
            'telephone'        => 'nullable|string',
            'poste'            => 'nullable|string',
        ]);

        $entreprise = Entreprise::create([
            'nom'         => $data['entreprise_nom'],
            'type'        => $data['entreprise_type'],
            'email'       => $data['entreprise_email'],
            'ville'       => $data['entreprise_ville'],
            'telephone'   => $data['entreprise_telephone'] ?? '',
            'adresse'     => $data['entreprise_adresse'],
            'description' => $data['entreprise_description'] ?? '',
            'logo'        => $this->enregistrerLogo($request, 'entreprise_logo', $data['entreprise_logo'] ?? null),
            'statut'      => 'actif',
        ]);

        $user = User::create([
            'entreprise_id' => $entreprise->id,
            'nom'           => $data['nom'],
            'prenom'        => $data['prenom'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'telephone'     => $data['telephone'] ?? null,
            'poste'         => $data['poste'] ?? null,
            'actif'         => true,
        ]);

        $roleName = $data['role'] ?? 'admin';
        $role = Role::where('nom', $roleName)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'    => 'Inscription réussie',
            'user'       => $user->load('roles.permissions', 'permissions', 'entreprise'),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function registerParticulier(Request $request) {
        $data = $request->validate([
            'nom'      => 'required|string',
            'prenom'   => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'telephone' => 'nullable|string',
        ]);

        $user = User::create([
            'entreprise_id' => null,
            'nom'           => $data['nom'],
            'prenom'        => $data['prenom'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'telephone'     => $data['telephone'] ?? null,
            'actif'         => true,
        ]);

        $role = Role::where('nom', 'membre')->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'    => 'Inscription réussie',
            'user'       => $user->load('roles.permissions', 'permissions', 'entreprise'),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request) {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('roles.permissions', 'permissions', 'entreprise')
                    ->where('email', $data['email'])
                    ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        if (!$user->actif) {
            return response()->json([
                'message' => 'Compte désactivé. Contactez votre administrateur.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        HistoriqueAction::create([
            'user_id'       => $user->id,
            'entreprise_id' => $user->entreprise_id,
            'action'        => 'connexion',
            'table_cible'   => 'users',
            'enregistrement_id' => $user->id,
            'ip_address'    => $request->ip(),
        ]);

        return response()->json([
            'message'    => 'Connexion réussie',
            'user'       => $user,
            'token'      => $token,
        ]);
    }

    public function logout(Request $request) {
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function me(Request $request) {
        return response()->json(
            $request->user()->load('roles.permissions', 'permissions', 'entreprise')
        );
    }

    private function enregistrerLogo(Request $request, string $champ, ?string $valeur = null): ?string {
        if (!$request->hasFile($champ)) {
            return $valeur;
        }

        $chemin = ImageOptimizer::storeResized($request->file($champ), 'entreprises/logos');
        return Storage::url($chemin);
    }
}
