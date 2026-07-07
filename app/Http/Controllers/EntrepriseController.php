<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use App\Services\ImageOptimizer;
use App\Models\Entreprise;

class EntrepriseController extends Controller {

    public function index(Request $request) {
        $query = Entreprise::withCount(['users','alertes','requetes'])
            ->select('id','nom','type','email','ville','statut','created_at')
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = $request->q;

                $builder->where(function ($inner) use ($search) {
                    $inner->where('nom', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('ville', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->latest();

        return $this->listResponse($query, $request, 50, 100);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'nom'         => 'required|string',
            'type'        => 'required|in:pharmacie,hopital,laboratoire,clinique,autre',
            'email'       => 'required|email|unique:entreprises',
            'telephone'   => 'nullable|string',
            'adresse'     => 'nullable|string',
            'ville'       => 'nullable|string',
            'description' => 'nullable|string',
            'logo'        => $request->hasFile('logo')
                ? ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2 * 1024)]
                : 'nullable|string',
        ]);
        $data['logo'] = $this->enregistrerLogo($request, 'logo', $data['logo'] ?? null);

        return response()->json(Entreprise::create($data), 201);
    }

    public function show($id) {
        return response()->json(
            Entreprise::with([
                'users.roles',
                'annuaires',
                'partenaires.entreprisePartenaire',
                'evaluationsRecues',
            ])->withCount(['alertes','requetes','documents'])
              ->findOrFail($id)
        );
    }

    public function update(Request $request, $id) {
        $entreprise = Entreprise::findOrFail($id);
        $data = $request->validate([
            'nom'         => 'sometimes|string',
            'type'        => 'sometimes|in:pharmacie,hopital,laboratoire,clinique,autre',
            'email'       => 'sometimes|email|unique:entreprises,email,'.$id,
            'statut'      => 'sometimes|in:actif,inactif,suspendu',
            'telephone'   => 'nullable|string',
            'adresse'     => 'nullable|string',
            'ville'       => 'nullable|string',
            'logo'        => $request->hasFile('logo')
                ? ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2 * 1024)]
                : 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->enregistrerLogo($request, 'logo', $entreprise->logo);
        }

        $entreprise->update($data);
        return response()->json($entreprise);
    }

    public function destroy($id) {
        Entreprise::findOrFail($id)->delete();
        return response()->json(['message' => 'Entreprise supprimée']);
    }
    private function enregistrerLogo(Request $request, string $champ, ?string $ancienLogo = null): ?string {
        if (!$request->hasFile($champ)) {
            return $ancienLogo;
        }

        if ($ancienLogo && str_starts_with($ancienLogo, '/storage/')) {
            Storage::disk('public')->delete(substr($ancienLogo, strlen('/storage/')));
        }

        $chemin = ImageOptimizer::storeResized($request->file($champ), 'entreprises/logos');
        return Storage::url($chemin);
    }
}
